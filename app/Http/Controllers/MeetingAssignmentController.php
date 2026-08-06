<?php

namespace App\Http\Controllers;

use App\Models\Attendant;
use App\Models\MeetingAssignment;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class MeetingAssignmentController extends Controller
{
    public function update(Request $request, MeetingAssignment $meetingAssignment)
    {
        $request->validate([
            'assignable_kind' => 'nullable|in:attendant,student',
            'assignable_id'   => 'nullable|integer',
        ]);

        $kind = $request->assignable_kind;
        $id   = $request->assignable_id;

        $map = [
            'attendant' => Attendant::class,
            'student'   => Student::class,
        ];

        $assignableType = $kind ? $map[$kind] : null;
        $assignableId   = $kind ? $id : null;

        if ($assignableType && $assignableId) {
            $table = $kind === 'attendant' ? 'attendants' : 'students';

            $exists = \Illuminate\Support\Facades\DB::table($table)->where('id', $assignableId)->exists();

            if (! $exists) {
                throw ValidationException::withMessages([
                    'assignable_id' => 'The selected person could not be found.',
                ]);
            }

            // One person may hold several parts in the same week; the picker
            // flags who is already assigned but leaves the call to the user.
        }

        $meetingAssignment->update([
            'assignable_type' => $assignableType,
            'assignable_id'   => $assignableId,
        ]);

        $meetingAssignment->refresh();
        $assignable = $meetingAssignment->assignable;

        $synced = [];

        // Rule: whoever is assigned as "Presidente" (header, position 1)
        // automatically also gives the "Oración inicial" (header, position 3).
        $part = $meetingAssignment->part;

        if ($part->section === 'header' && $part->position === 1) {
            $prayerAssignment = MeetingAssignment::whereHas('part', function ($query) use ($part) {
                $query->where('meeting_week_id', $part->meeting_week_id)
                    ->where('section', 'header')
                    ->where('position', 3);
            })->where('slot', 'main')->first();

            if ($prayerAssignment && $prayerAssignment->id !== $meetingAssignment->id) {
                $prayerAssignment->update([
                    'assignable_type' => $assignableType,
                    'assignable_id'   => $assignableId,
                ]);

                $prayerAssignment->refresh();

                $synced[] = [
                    'assignment_id' => $prayerAssignment->id,
                    'assignable'    => $this->formatAssignable($prayerAssignment->assignable),
                ];
            }
        }

        return response()->json([
            'assignable' => $this->formatAssignable($assignable),
            'synced'     => $synced,
        ]);
    }

    private function formatAssignable($assignable): ?array
    {
        if (! $assignable) {
            return null;
        }

        return [
            'id'     => $assignable->id,
            'name'   => $assignable->name,
            'kind'   => $assignable instanceof Student ? 'student' : 'attendant',
            'role'   => $assignable->role ?? null,
            'gender' => $assignable->gender ?? null,
        ];
    }
}
