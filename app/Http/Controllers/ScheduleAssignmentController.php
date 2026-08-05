<?php

namespace App\Http\Controllers;

use App\Models\ScheduleAssignment;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ScheduleAssignmentController extends Controller
{
    public function update(Request $request, ScheduleAssignment $scheduleAssignment)
    {
        $request->validate([
            'attendant_id' => 'nullable|exists:attendants,id',
        ]);

        $attendantId = $request->attendant_id;

        if ($attendantId) {
            // Rule: one person cannot hold two roles in the same meeting
            $conflict = ScheduleAssignment::where('schedule_week_id', $scheduleAssignment->schedule_week_id)
                ->where('meeting_day', $scheduleAssignment->meeting_day)
                ->where('id', '!=', $scheduleAssignment->id)
                ->where('attendant_id', $attendantId)
                ->exists();

            if ($conflict) {
                throw ValidationException::withMessages([
                    'attendant_id' => 'This person is already assigned to another role in this meeting.',
                ]);
            }
        }

        $scheduleAssignment->update(['attendant_id' => $attendantId]);

        return response()->json([
            'attendant' => $attendantId
                ? $scheduleAssignment->attendant()->select('id', 'name', 'role')->first()
                : null,
        ]);
    }
}
