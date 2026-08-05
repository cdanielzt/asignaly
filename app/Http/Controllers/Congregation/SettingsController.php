<?php

namespace App\Http\Controllers\Congregation;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class SettingsController extends Controller
{
    public function edit()
    {
        $congregation = auth()->user()->congregation;

        return Inertia::render('Congregation/Settings', [
            'congregation'   => $congregation,
            'attendantRoles' => array_values($congregation->attendantRoles()),
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'name'               => 'required|string|max:255',
            'city'               => 'nullable|string|max:255',
            'attendant_roles'    => 'required|array|min:1',
            'attendant_roles.*'  => 'required|string|max:100',
        ], [
            'attendant_roles.required'   => 'Agrega al menos un cargo.',
            'attendant_roles.*.required' => 'El nombre del cargo no puede estar vacío.',
        ]);

        $roles = collect($request->attendant_roles)
            ->map(fn ($label) => trim($label))
            ->filter()
            ->mapWithKeys(fn ($label) => [Str::slug($label, '_') => $label])
            ->all();

        auth()->user()->congregation->update([
            ...$request->only('name', 'city'),
            'attendant_roles' => $roles,
        ]);

        return back()->with('success', 'Configuración guardada correctamente.');
    }
}
