<?php

namespace App\Http\Controllers;

use App\Models\Attendant;
use App\Models\Meeting;
use App\Models\MeetingPart;
use App\Models\Student;
use App\Services\TenantContext;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class MeetingController extends Controller
{
    public function index()
    {
        return Inertia::render('Meetings/Index', [
            'meetings' => Meeting::orderBy('year')->orderBy('month')->get(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Meetings/Create');
    }

    public function store(Request $request)
    {
        $congregationId = app(TenantContext::class)->get();

        $request->validate([
            'month' => [
                'required', 'integer', 'between:1,12',
                Rule::unique('meetings')
                    ->where('year', $request->year)
                    ->where('congregation_id', $congregationId),
            ],
            'year' => 'required|integer|min:2020|max:2100',
        ], [
            'month.unique' => 'Ya existe un programa para ese mes y año.',
        ]);

        $name = Carbon::createFromDate($request->year, $request->month, 1)
            ->format('F Y');

        $meeting = Meeting::create([
            'month' => $request->month,
            'year'  => $request->year,
            'name'  => $name,
        ]);

        $meeting->generateWeeks();

        return redirect()->route('meetings.show', $meeting)
            ->with('success', "Programa de {$name} creado.");
    }

    public function show(Meeting $meeting)
    {
        $meeting->load(['weeks.parts.assignments.assignable']);

        $sectionKeys = array_keys(MeetingPart::SECTIONS);

        $weeks = $meeting->weeks->map(function ($week) use ($sectionKeys) {
            $sections = [];

            foreach ($sectionKeys as $key) {
                $sections[$key] = [];
            }

            foreach ($week->parts as $part) {
                $assignmentsBySlot = $part->assignments->keyBy('slot');

                $assignments = [
                    'main' => $this->formatAssignment($assignmentsBySlot->get('main')),
                ];

                if ($assignmentsBySlot->has('helper')) {
                    $assignments['helper'] = $this->formatAssignment($assignmentsBySlot->get('helper'));
                }

                $sections[$part->section][] = [
                    'id'          => $part->id,
                    'section'     => $part->section,
                    'position'    => $part->position,
                    'title'       => $part->title,
                    'duration'    => $part->duration,
                    'type'        => $part->type,
                    'assignments' => $assignments,
                ];
            }

            return [
                'id'            => $week->id,
                'week_number'   => $week->week_number,
                'start_date'    => $week->start_date->toDateString(),
                'end_date'      => $week->end_date->toDateString(),
                'bible_reading' => $week->bible_reading,
                'sections'      => $sections,
            ];
        });

        return Inertia::render('Meetings/Show', [
            'meeting'    => $meeting->only('id', 'name', 'month', 'year'),
            'weeks'      => $weeks,
            'sections'   => MeetingPart::SECTIONS,
            'partTypes'  => MeetingPart::TYPES,
            'attendants' => Attendant::orderBy('name')->get(['id', 'name', 'role']),
            'students'   => Student::orderBy('name')->get(['id', 'name', 'gender']),
        ]);
    }

    public function pdf(Meeting $meeting, Request $request)
    {
        $meeting->load(['weeks.parts.assignments.assignable']);

        $spanishMonths = [
            1  => 'ENERO',    2  => 'FEBRERO',   3  => 'MARZO',
            4  => 'ABRIL',    5  => 'MAYO',      6  => 'JUNIO',
            7  => 'JULIO',    8  => 'AGOSTO',    9  => 'SEPTIEMBRE',
            10 => 'OCTUBRE',  11 => 'NOVIEMBRE', 12 => 'DICIEMBRE',
        ];

        $assignmentNames = function ($part) {
            return $part->assignments
                ->sortBy(fn ($a) => $a->slot === 'helper' ? 1 : 0)
                ->map(fn ($a) => $a->assignable->name ?? null)
                ->filter()
                ->values()
                ->all();
        };

        $assignmentLabel = function (string $type): ?string {
            return match ($type) {
                'single_student' => 'Estudiante:',
                'student_helper' => 'Estudiante/Ayudante:',
                'dual_attendant' => 'Conductor/Lector:',
                default          => null,
            };
        };

        $weeks = $meeting->weeks->map(function ($week) use ($spanishMonths, $assignmentNames, $assignmentLabel) {
            $partsBySection = $week->parts->groupBy('section');

            $info = [];
            foreach ([1 => 'Presidente:', 2 => 'Consejero:', 3 => 'Oración:'] as $position => $label) {
                $part = $partsBySection->get('header', collect())->firstWhere('position', $position);
                $names = $part ? $assignmentNames($part) : [];

                if (! empty($names)) {
                    $info[] = ['label' => $label, 'name' => $names[0]];
                }
            }

            $intro = null;
            $bodySections = [];
            $number = 1;

            foreach (['tesoros', 'maestros', 'vida_cristiana'] as $sectionKey) {
                $items = [];

                foreach ($partsBySection->get($sectionKey, collect())->sortBy('position') as $part) {
                    if ($part->type === 'display_only') {
                        $intro = ['title' => $part->title, 'duration' => $part->duration];

                        continue;
                    }

                    $items[] = [
                        'number'   => $number++,
                        'title'    => $part->title,
                        'duration' => $part->duration,
                        'label'    => $assignmentLabel($part->type),
                        'names'    => $assignmentNames($part),
                    ];
                }

                $bodySections[] = [
                    'band_class' => str_replace('_', '-', $sectionKey),
                    'title'      => MeetingPart::SECTIONS[$sectionKey],
                    'items'      => $items,
                ];
            }

            $closingIntro = null;
            $closingPrayer = null;

            foreach ($partsBySection->get('closing', collect())->sortBy('position') as $part) {
                if ($part->type === 'display_only') {
                    $closingIntro = ['title' => $part->title, 'duration' => $part->duration];
                } else {
                    $closingPrayer = $assignmentNames($part)[0] ?? null;
                }
            }

            $start = $week->start_date;
            $end   = $week->end_date;

            if ($start->month === $end->month) {
                $dateLabel = $start->format('j') . '-' . $end->format('j')
                    . ' DE ' . $spanishMonths[(int) $start->format('n')];
            } else {
                $dateLabel = $start->format('j') . ' DE ' . $spanishMonths[(int) $start->format('n')]
                    . ' A ' . $end->format('j') . ' DE ' . $spanishMonths[(int) $end->format('n')];
            }

            return [
                'date_label'     => $dateLabel,
                'bible_reading'  => $week->bible_reading,
                'info'           => $info,
                'intro'          => $intro,
                'body_sections'  => $bodySections,
                'closing_intro'  => $closingIntro,
                'closing_prayer' => $closingPrayer,
            ];
        });

        $pdf = Pdf::loadView('meetings.pdf', [
            'meeting' => $meeting,
            'weeks'   => $weeks,
        ])
        ->setPaper('a4', 'portrait')
        ->setOptions(['isPhpEnabled' => true]);

        $filename = $meeting->name . ' - Programa Reunión Entre Semana.pdf';

        if ($request->query('inline')) {
            return $pdf->stream($filename);
        }

        return $pdf->download($filename);
    }

    public function destroy(Meeting $meeting)
    {
        $meeting->delete();

        return redirect()->route('meetings.index')
            ->with('success', 'Programa eliminado.');
    }

    /**
     * Format a MeetingAssignment (or null row) into the JSON shape shared with the front end.
     */
    private function formatAssignment($assignment): ?array
    {
        if (! $assignment) {
            return null;
        }

        $assignable = $assignment->assignable;

        return [
            'id'         => $assignment->id,
            'slot'       => $assignment->slot,
            'assignable' => $assignable
                ? [
                    'id'     => $assignable->id,
                    'name'   => $assignable->name,
                    'kind'   => $assignable instanceof Student ? 'student' : 'attendant',
                    'role'   => $assignable->role ?? null,
                    'gender' => $assignable->gender ?? null,
                ]
                : null,
        ];
    }
}
