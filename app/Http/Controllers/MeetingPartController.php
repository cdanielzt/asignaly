<?php

namespace App\Http\Controllers;

use App\Models\MeetingPart;
use Illuminate\Http\Request;

class MeetingPartController extends Controller
{
    public function update(Request $request, MeetingPart $meetingPart)
    {
        $request->validate([
            'title'    => 'nullable|string|max:255',
            'duration' => 'nullable|string|max:50',
        ]);

        // ponytail: ConvertEmptyStringsToNull turns a cleared field into null; title is NOT NULL
        $meetingPart->update([
            'title'    => $request->title ?? '',
            'duration' => $request->duration,
        ]);

        return response()->json([
            'part' => [
                'id'       => $meetingPart->id,
                'title'    => $meetingPart->title,
                'duration' => $meetingPart->duration,
            ],
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'meeting_week_id' => 'required|exists:meeting_weeks,id',
            'section'         => 'required|in:tesoros,maestros,vida_cristiana',
            'type'            => 'required|in:single_attendant,single_student,student_helper',
            'title'           => 'nullable|string|max:255',
            'duration'        => 'nullable|string|max:50',
        ]);

        $maxPosition = MeetingPart::where('meeting_week_id', $request->meeting_week_id)
            ->where('section', $request->section)
            ->max('position');

        $position = $maxPosition === null ? 1 : $maxPosition + 1;

        $part = MeetingPart::create([
            'meeting_week_id' => $request->meeting_week_id,
            'section'         => $request->section,
            'position'        => $position,
            'title'           => $request->title ?? '',
            'duration'        => $request->duration,
            'type'            => $request->type,
        ]);

        $slots = $request->type === 'student_helper' ? ['main', 'helper'] : ['main'];

        foreach ($slots as $slot) {
            $part->assignments()->create([
                'slot'            => $slot,
                'assignable_type' => null,
                'assignable_id'   => null,
            ]);
        }

        $part->load('assignments');

        $assignments = [
            'main' => optional($part->assignments->firstWhere('slot', 'main'))
                ? ['id' => $part->assignments->firstWhere('slot', 'main')->id, 'slot' => 'main', 'assignable' => null]
                : null,
        ];

        if (in_array('helper', $slots, true)) {
            $assignments['helper'] = ['id' => $part->assignments->firstWhere('slot', 'helper')->id, 'slot' => 'helper', 'assignable' => null];
        }

        return response()->json([
            'id'          => $part->id,
            'section'     => $part->section,
            'position'    => $part->position,
            'title'       => $part->title,
            'duration'    => $part->duration,
            'type'        => $part->type,
            'assignments' => $assignments,
        ]);
    }

    public function destroy(MeetingPart $meetingPart)
    {
        if (! in_array($meetingPart->section, ['tesoros', 'maestros', 'vida_cristiana'], true)) {
            abort(403);
        }

        $meetingPart->delete();

        return response()->json(['deleted' => true]);
    }
}
