<?php

namespace App\Http\Controllers;

use App\Models\Attendant;
use App\Models\Schedule;
use App\Services\TenantContext;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class ScheduleController extends Controller
{
    public function index()
    {
        return Inertia::render('Schedules/Index', [
            'schedules' => Schedule::orderBy('year')->orderBy('month')->get(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Schedules/Create');
    }

    public function store(Request $request)
    {
        $congregationId = app(TenantContext::class)->get();

        $request->validate([
            'month' => [
                'required', 'integer', 'between:1,12',
                Rule::unique('schedules')
                    ->where('year', $request->year)
                    ->where('congregation_id', $congregationId),
            ],
            'year'  => 'required|integer|min:2020|max:2100',
        ], [
            'month.unique' => 'Ya existe un programa para ese mes y año.',
        ]);

        $name = ucfirst(Carbon::createFromDate($request->year, $request->month, 1)
            ->locale('es')->translatedFormat('F Y'));

        $schedule = Schedule::create([
            'month' => $request->month,
            'year'  => $request->year,
            'name'  => $name,
            'roles' => \App\Models\Congregation::findOrFail($congregationId)->attendantRoles(),
        ]);

        $schedule->generateWeeks();

        return redirect()->route('schedules.show', $schedule)
            ->with('success', "Programa de {$name} creado.");
    }

    public function show(Schedule $schedule)
    {
        $schedule->load(['weeks.assignments.attendant']);

        $weeks = $schedule->weeks->map(function ($week) {
            $meetings = [];

            foreach (Schedule::MEETING_DAYS as $day) {
                $assignments = $week->assignments
                    ->where('meeting_day', $day)
                    ->keyBy('role')
                    ->map(fn($a) => [
                        'id'        => $a->id,
                        'role'      => $a->role,
                        'attendant' => $a->attendant
                            ? ['id' => $a->attendant->id, 'name' => $a->attendant->name, 'role' => $a->attendant->role]
                            : null,
                    ]);

                $meetings[$day] = $assignments;
            }

            return [
                'id'          => $week->id,
                'week_number' => $week->week_number,
                'start_date'  => $week->start_date->toDateString(),
                'end_date'    => $week->end_date->toDateString(),
                'meetings'    => $meetings,
            ];
        });

        return Inertia::render('Schedules/Show', [
            'schedule'   => $schedule->only('id', 'name', 'month', 'year'),
            'weeks'      => $weeks,
            'roles'      => $schedule->rolesMap(),
            'attendants' => Attendant::orderBy('name')->get(['id', 'name', 'role']),
        ]);
    }

    public function pdf(Schedule $schedule, Request $request)
    {
        $schedule->load(['weeks.assignments.attendant']);

        $spanishDays = [
            'Friday'   => 'VIERNES',
            'Saturday' => 'SÁBADO',
        ];

        $spanishMonths = [
            1  => 'ENERO',    2  => 'FEBRERO', 3  => 'MARZO',
            4  => 'ABRIL',    5  => 'MAYO',    6  => 'JUNIO',
            7  => 'JULIO',    8  => 'AGOSTO',  9  => 'SEPTIEMBRE',
            10 => 'OCTUBRE',  11 => 'NOVIEMBRE', 12 => 'DICIEMBRE',
        ];

        $weeks = $schedule->weeks
            ->filter(fn($week) => $week->assignments->contains(fn($a) => $a->attendant_id))
            ->map(function ($week) use ($spanishDays, $spanishMonths) {
            $friday   = $week->start_date->copy()->addDays(4);
            $saturday = $week->start_date->copy()->addDays(5);

            $meetings = [];
            foreach (Schedule::MEETING_DAYS as $day) {
                $meetings[$day] = $week->assignments
                    ->where('meeting_day', $day)
                    ->keyBy('role')
                    ->map(fn($a) => [
                        'role'      => $a->role,
                        'attendant' => $a->attendant
                            ? ['id' => $a->attendant->id, 'name' => $a->attendant->name]
                            : null,
                    ])
                    ->toArray();
            }

            return [
                'friday_label'   => $spanishDays['Friday']   . ' ' . $friday->format('d')   . ' DE ' . $spanishMonths[(int)$friday->format('n')],
                'saturday_label' => $spanishDays['Saturday']  . ' ' . $saturday->format('d') . ' DE ' . $spanishMonths[(int)$saturday->format('n')],
                'meetings'       => $meetings,
            ];
        });

        $spanishMonthLabel = $spanishMonths[(int)$schedule->month] . ' ' . $schedule->year;

        $pdf = Pdf::loadView('schedules.pdf', [
            'schedule'          => $schedule,
            'weeks'             => $weeks,
            'roles'             => $schedule->rolesMap(),
            'spanishMonthLabel' => $spanishMonthLabel,
        ])
        ->setPaper('a4', 'landscape')
        ->setOptions(['isPhpEnabled' => true]);

        $filename = $schedule->name . ' - Programa de Acomodadores.pdf';

        if ($request->query('inline')) {
            return $pdf->stream($filename);
        }

        return $pdf->download($filename);
    }

    public function destroy(Schedule $schedule)
    {
        $schedule->delete();

        return redirect()->route('schedules.index')
            ->with('success', 'Programa eliminado.');
    }
}
