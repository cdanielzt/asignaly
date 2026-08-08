<?php

namespace App\Models;

use Carbon\Carbon;
use App\Models\Concerns\BelongsToCongregation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Meeting extends Model
{
    use BelongsToCongregation;

    protected $fillable = ['congregation_id', 'month', 'year', 'name'];

    const MEETING_DAY = 'friday';

    // ponytail: derived from month/year, so old English rows render in Spanish without a data migration
    public function getNameAttribute(): string
    {
        return ucfirst(Carbon::createFromDate($this->year, $this->month, 1)->locale('es')->translatedFormat('F Y'));
    }

    public function weeks(): HasMany
    {
        return $this->hasMany(MeetingWeek::class)->orderBy('week_number');
    }

    /**
     * Generate the Monday-Sunday weeks that cover every day of this meeting's month.
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
                'week_number'   => $weekNumber,
                'start_date'    => $weekStart->toDateString(),
                'end_date'      => $weekEnd->toDateString(),
                'bible_reading' => null,
            ]);

            $week->generateParts();

            $weekStart->addWeek();
            $weekNumber++;
        }
    }
}
