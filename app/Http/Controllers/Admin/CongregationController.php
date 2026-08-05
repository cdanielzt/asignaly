<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Congregation;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CongregationController extends Controller
{
    public function index()
    {
        $congregations = Congregation::withCount(['users', 'attendants', 'students'])
            ->orderBy('name')
            ->get();

        return Inertia::render('Admin/Congregations/Index', [
            'congregations' => $congregations,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'city' => 'nullable|string|max:255',
        ]);

        Congregation::create($request->only('name', 'city'));

        return redirect()->route('admin.congregations.index')
            ->with('success', 'Congregación creada correctamente.');
    }

    public function update(Request $request, Congregation $congregation)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'city' => 'nullable|string|max:255',
        ]);

        $congregation->update($request->only('name', 'city'));

        return redirect()->route('admin.congregations.index')
            ->with('success', 'Congregación actualizada correctamente.');
    }

    public function destroy(Congregation $congregation)
    {
        $congregation->delete();

        return redirect()->route('admin.congregations.index')
            ->with('success', 'Congregación eliminada.');
    }

    public function switchTo(Congregation $congregation)
    {
        session(['admin_congregation_id' => $congregation->id]);

        return redirect()->route('dashboard');
    }

    public function switchBack()
    {
        session()->forget('admin_congregation_id');

        return redirect()->route('admin.congregations.index');
    }
}
