<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Congregation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with('congregation')->orderBy('name');

        if ($request->congregation_id) {
            $query->where('congregation_id', $request->congregation_id);
        }

        return Inertia::render('Admin/Users/Index', [
            'users'          => $query->get(),
            'congregations'  => Congregation::orderBy('name')->get(['id', 'name']),
            'filterCongregationId' => $request->congregation_id,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'            => 'required|string|max:255',
            'email'           => 'required|email|unique:users,email',
            'password'        => 'required|string|min:8',
            'role'            => 'required|in:super_admin,congregation_admin,member',
            'congregation_id' => [
                Rule::requiredIf($request->role !== 'super_admin'),
                'nullable',
                'exists:congregations,id',
            ],
        ]);

        User::create([
            'name'            => $request->name,
            'email'           => $request->email,
            'password'        => Hash::make($request->password),
            'role'            => $request->role,
            'congregation_id' => $request->role !== 'super_admin' ? $request->congregation_id : null,
        ]);

        $backUrl = $request->congregation_id
            ? route('admin.users.index', ['congregation_id' => $request->congregation_id])
            : route('admin.users.index');

        return redirect($backUrl)->with('success', 'Usuario creado correctamente.');
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name'            => 'required|string|max:255',
            'email'           => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'password'        => 'nullable|string|min:8',
            'role'            => 'required|in:super_admin,congregation_admin,member',
            'congregation_id' => [
                Rule::requiredIf($request->role !== 'super_admin'),
                'nullable',
                'exists:congregations,id',
            ],
        ]);

        $data = [
            'name'            => $request->name,
            'email'           => $request->email,
            'role'            => $request->role,
            'congregation_id' => $request->role !== 'super_admin' ? $request->congregation_id : null,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->route('admin.users.index')
            ->with('success', 'Usuario actualizado correctamente.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'No puedes eliminar tu propia cuenta.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'Usuario eliminado.');
    }
}
