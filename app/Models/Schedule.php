<?php

namespace App\Models;

use Carbon\Carbon;
use App\Models\Concerns\BelongsToCongregation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Schedule extends Model
{
    use BelongsToCongregation;

    protected $fillable = ['congregation_id', 'month', 'year', 'name', 'roles'];

    protected $casts = ['roles' => 'array'];

    const ROLES = [
        'gate_attendant'  => 'Acomodador de portón',
        'door_attendant'  => 'Acomodador de puerta',
        'microphone_1'    => 'Micrófono 1',
        'microphone_2'    => 'Micrófono 2',
        'platform'        => 'Plataforma',
    ];

    const MEETING_DAYS = ['friday', 'saturday'];

    /** Role key => label map snapshotted at creation, falling back to the defaults. */
    public function rolesMap(): array
    {
        return $this->roles ?: self::ROLES;
    }

    public function weeks(): HasMany
    {
        return $this->hasMany(ScheduleWeek::class)->orderBy('week_number');
    }

    /**
     * Generate the Monday-Sunday weeks that cover every day of this schedule's month.
     * Weeks that span the month boundary (like June 29 – July 5) are included.
     */
    public function generateWeeks(): void
    {
        $firstDay = Carbon::createFromDate($this->year, $this->month, 1);

        // Walk back to Monday of the week containing the 1st
        $weekStart = $firstDay->copy()->startOfWeek(Carbon::MONDAY);

        $lastDay = $firstDay->copy()->endOfMonth();
        $weekNumber = 1;

        while ($weekStart->lte($lastDay)) {
            $weekEnd = $weekStart->copy()->endOfWeek(Carbon::SUNDAY);

            $week = $this->weeks()->create([
                'week_number' => $weekNumber,
                'start_date'  => $weekStart->toDateString(),
                'end_date'    => $weekEnd->toDateString(),
            ]);

            // Seed empty assignment slots for every day × role
            foreach (self::MEETING_DAYS as $day) {
                foreach (array_keys($this->rolesMap()) as $role) {
                    $week->assignments()->create([
                        'meeting_day'  => $day,
                        'role'         => $role,
                        'attendant_id' => null,
                    ]);
                }
            }

            $weekStart->addWeek();
            $weekNumber++;
        }
    }
}
