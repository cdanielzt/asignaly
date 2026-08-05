<?php

namespace App\Http\Controllers;

use App\Models\Attendant;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AttendantController extends Controller
{
    public function index()
    {
        return Inertia::render('Attendants/Index', [
            'attendants' => Attendant::orderBy('name')->get(),
            'roles' => Attendant::ROLES,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'role' => 'required|in:' . implode(',', Attendant::ROLES),
        ]);

        Attendant::create($request->only('name', 'role'));

        return redirect()->route('attendants.index')->with('success', 'Hermano agregado correctamente.');
    }

    public function update(Request $request, Attendant $attendant)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'role' => 'required|in:' . implode(',', Attendant::ROLES),
        ]);

        $attendant->update($request->only('name', 'role'));

        return redirect()->route('attendants.index')->with('success', 'Hermano actualizado correctamente.');
    }

    public function destroy(Attendant $attendant)
    {
        $attendant->delete();

        return redirect()->route('attendants.index')->with('success', 'Hermano eliminado.');
    }
}
