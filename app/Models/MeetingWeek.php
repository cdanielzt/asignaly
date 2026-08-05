<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MeetingWeek extends Model
{
    protected $fillable = ['meeting_id', 'week_number', 'start_date', 'end_date', 'bible_reading'];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
    ];

    const SCAFFOLD = [
        ['section' => 'header', 'position' => 1, 'title' => 'Presidente', 'duration' => null, 'type' => 'single_attendant', 'slots' => ['main']],
        ['section' => 'header', 'position' => 2, 'title' => 'Consejero', 'duration' => null, 'type' => 'single_attendant', 'slots' => ['main']],
        ['section' => 'header', 'position' => 3, 'title' => 'Oración inicial', 'duration' => null, 'type' => 'single_attendant', 'slots' => ['main']],

        ['section' => 'tesoros', 'position' => 1, 'title' => 'Palabras de introducción', 'duration' => '1 min.', 'type' => 'display_only', 'slots' => []],
        ['section' => 'tesoros', 'position' => 2, 'title' => '', 'duration' => '10 mins.', 'type' => 'single_attendant', 'slots' => ['main']],
        ['section' => 'tesoros', 'position' => 3, 'title' => 'Busquemos perlas escondidas', 'duration' => '10 mins.', 'type' => 'single_attendant', 'slots' => ['main']],
        ['section' => 'tesoros', 'position' => 4, 'title' => 'Lectura de la Biblia', 'duration' => '4 mins.', 'type' => 'single_student', 'slots' => ['main']],

        ['section' => 'maestros', 'position' => 1, 'title' => '', 'duration' => '', 'type' => 'student_helper', 'slots' => ['main', 'helper']],
        ['section' => 'maestros', 'position' => 2, 'title' => '', 'duration' => '', 'type' => 'student_helper', 'slots' => ['main', 'helper']],
        ['section' => 'maestros', 'position' => 3, 'title' => '', 'duration' => '', 'type' => 'student_helper', 'slots' => ['main', 'helper']],

        ['section' => 'vida_cristiana', 'position' => 1, 'title' => '', 'duration' => '15 mins.', 'type' => 'single_attendant', 'slots' => ['main']],
        ['section' => 'vida_cristiana', 'position' => 2, 'title' => 'Estudio bíblico de la congregación', 'duration' => '30 mins.', 'type' => 'dual_attendant', 'slots' => ['main', 'helper']],

        ['section' => 'closing', 'position' => 1, 'title' => 'Palabras de conclusión', 'duration' => '3 min.', 'type' => 'display_only', 'slots' => []],
        ['section' => 'closing', 'position' => 2, 'title' => 'Oración final', 'duration' => null, 'type' => 'single_attendant', 'slots' => ['main']],
    ];

    public function meeting(): BelongsTo
    {
        return $this->belongsTo(Meeting::class);
    }

    public function parts(): HasMany
    {
        return $this->hasMany(MeetingPart::class)->orderBy('position');
    }

    /**
     * Create the default scaffold of meeting parts (and their empty assignment slots)
     * for this week.
     */
    public function generateParts(): void
    {
        foreach (self::SCAFFOLD as $row) {
            $part = $this->parts()->create([
                'section'  => $row['section'],
                'position' => $row['position'],
                'title'    => $row['title'],
                'duration' => $row['duration'],
                'type'     => $row['type'],
            ]);

            foreach ($row['slots'] as $slot) {
                $part->assignments()->create([
                    'slot'            => $slot,
                    'assignable_type' => null,
                    'assignable_id'   => null,
                ]);
            }
        }
    }
}
