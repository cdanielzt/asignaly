<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StudentController extends Controller
{
    public function index()
    {
        return Inertia::render('Students/Index', [
            'students' => Student::orderBy('name')->get(),
            'genders' => Student::GENDERS,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'   => 'required|string|max:255',
            'gender' => 'required|in:brother,sister',
        ]);

        Student::create($request->only('name', 'gender'));

        return redirect()->route('students.index')->with('success', 'Estudiante agregado correctamente.');
    }

    public function update(Request $request, Student $student)
    {
        $request->validate([
            'name'   => 'required|string|max:255',
            'gender' => 'required|in:brother,sister',
        ]);

        $student->update($request->only('name', 'gender'));

        return redirect()->route('students.index')->with('success', 'Estudiante actualizado correctamente.');
    }

    public function destroy(Student $student)
    {
        $student->delete();

        return redirect()->route('students.index')->with('success', 'Estudiante eliminado.');
    }
}
