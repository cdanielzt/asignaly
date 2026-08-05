<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class MeetingAssignment extends Model
{
    protected $fillable = ['meeting_part_id', 'slot', 'assignable_type', 'assignable_id'];

    public function part(): BelongsTo
    {
        return $this->belongsTo(MeetingPart::class, 'meeting_part_id');
    }

    public function assignable(): MorphTo
    {
        return $this->morphTo();
    }
}
