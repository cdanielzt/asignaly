<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScheduleAssignment extends Model
{
    protected $fillable = ['schedule_week_id', 'meeting_day', 'role', 'attendant_id'];

    public function week(): BelongsTo
    {
        return $this->belongsTo(ScheduleWeek::class, 'schedule_week_id');
    }

    public function attendant(): BelongsTo
    {
        return $this->belongsTo(Attendant::class);
    }
}
