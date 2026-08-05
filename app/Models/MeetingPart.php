<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MeetingPart extends Model
{
    protected $fillable = ['meeting_week_id', 'section', 'position', 'title', 'duration', 'type'];

    const SECTIONS = [
        'header'         => 'Encabezado',
        'tesoros'        => 'Tesoros de la Biblia',
        'maestros'       => 'Seamos Mejores Maestros',
        'vida_cristiana' => 'Nuestra Vida Cristiana',
        'closing'        => 'Conclusión',
    ];

    const TYPES = [
        'display_only'     => 'Display only',
        'single_attendant' => 'Single (Attendant)',
        'single_student'   => 'Single (Student)',
        'student_helper'   => 'Student + Helper',
        'dual_attendant'   => 'Two (Attendants)',
    ];

    public function week(): BelongsTo
    {
        return $this->belongsTo(MeetingWeek::class, 'meeting_week_id');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(MeetingAssignment::class);
    }
}
