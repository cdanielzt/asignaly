<?php

use App\Http\Controllers\Admin\CongregationController as AdminCongregationController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\AttendantController;
use App\Http\Controllers\Congregation\SettingsController;
use App\Http\Controllers\ImpersonationController;
use App\Http\Controllers\Congregation\UserController as CongregationUserController;
use App\Http\Controllers\MeetingAssignmentController;
use App\Http\Controllers\MeetingController;
use App\Http\Controllers\MeetingPartController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\StudentController;
use App\Models\Attendant;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Super admin panel
Route::middleware(['auth', 'role:super_admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/', fn () => redirect()->route('admin.congregations.index'));
        Route::resource('congregations', AdminCongregationController::class)->except(['show', 'create', 'edit']);
        Route::post('congregations/{congregation}/switch', [AdminCongregationController::class, 'switchTo'])->name('congregations.switch');
        Route::post('switch-back', [AdminCongregationController::class, 'switchBack'])->name('switch-back');
        Route::resource('users', AdminUserController::class)->except(['show', 'create', 'edit']);
        Route::post('users/{user}/impersonate', [ImpersonationController::class, 'start'])->name('users.impersonate');
    });

// Leave impersonation — reachable as the impersonated (non-admin) user, so it sits outside the admin group
Route::post('impersonate/stop', [ImpersonationController::class, 'stop'])
    ->middleware('auth')->name('impersonate.stop');

// Regular congregation app
Route::middleware(['auth', 'set.tenant', 'role:congregation_admin,member'])
    ->group(function () {
        Route::get('/', function () {
            $counts = Attendant::selectRaw('role, count(*) as total')
                ->groupBy('role')
                ->pluck('total', 'role');

            return Inertia::render('Dashboard', [
                'stats' => [
                    'total'      => Attendant::count(),
                    'elders'     => $counts['Elder'] ?? 0,
                    'servants'   => $counts['Ministerial Servant'] ?? 0,
                    'publishers' => $counts['Publisher'] ?? 0,
                ],
            ]);
        })->name('dashboard');

        Route::resource('attendants', AttendantController::class)->except(['show', 'create', 'edit']);
        Route::resource('schedules', ScheduleController::class)->except(['create', 'edit', 'update']);
        Route::get('schedules/{schedule}/pdf', [ScheduleController::class, 'pdf'])->name('schedules.pdf');
        Route::patch('schedule-assignments/{scheduleAssignment}', [\App\Http\Controllers\ScheduleAssignmentController::class, 'update'])->name('schedule-assignments.update');

        Route::resource('students', StudentController::class)->except(['show', 'create', 'edit']);
        Route::resource('meetings', MeetingController::class)->except(['create', 'edit', 'update']);
        Route::get('meetings/{meeting}/pdf', [MeetingController::class, 'pdf'])->name('meetings.pdf');
        Route::patch('meeting-assignments/{meetingAssignment}', [MeetingAssignmentController::class, 'update'])->name('meeting-assignments.update');
        Route::patch('meeting-parts/{meetingPart}', [MeetingPartController::class, 'update'])->name('meeting-parts.update');
        Route::post('meeting-parts', [MeetingPartController::class, 'store'])->name('meeting-parts.store');
        Route::delete('meeting-parts/{meetingPart}', [MeetingPartController::class, 'destroy'])->name('meeting-parts.destroy');

        // Congregation admin only
        Route::middleware('role:congregation_admin')
            ->prefix('congregation')
            ->name('congregation.')
            ->group(function () {
                Route::get('settings', [SettingsController::class, 'edit'])->name('settings');
                Route::put('settings', [SettingsController::class, 'update'])->name('settings.update');
                Route::resource('users', CongregationUserController::class)->except(['show', 'create', 'edit']);
            });
    });
