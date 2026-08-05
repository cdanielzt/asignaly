<?php

namespace App\Http\Controllers\Congregation;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class UserController extends Controller
{
    private function congregationId(): int
    {
        return auth()->user()->congregation_id;
    }

    public function index()
    {
        return Inertia::render('Congregation/Users/Index', [
            'users' => User::ofCongregation($this->congregationId())
                ->orderBy('name')
                ->get(['id', 'name', 'email', 'role']),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'role'     => 'required|in:congregation_admin,member',
        ]);

        User::create([
            'name'            => $request->name,
            'email'           => $request->email,
            'password'        => Hash::make($request->password),
            'role'            => $request->role,
            'congregation_id' => $this->congregationId(),
        ]);

        return redirect()->route('congregation.users.index')
            ->with('success', 'Usuario creado correctamente.');
    }

    public function update(Request $request, User $user)
    {
        abort_unless($user->congregation_id === $this->congregationId(), 403);

        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => 'nullable|string|min:8',
            'role'     => 'required|in:congregation_admin,member',
        ]);

        $data = ['name' => $request->name, 'email' => $request->email, 'role' => $request->role];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->route('congregation.users.index')
            ->with('success', 'Usuario actualizado correctamente.');
    }

    public function destroy(User $user)
    {
        abort_unless($user->congregation_id === $this->congregationId(), 403);

        if ($user->id === auth()->id()) {
            return back()->with('error', 'No puedes eliminar tu propia cuenta.');
        }

        $user->delete();

        return redirect()->route('congregation.users.index')
            ->with('success', 'Usuario eliminado.');
    }
}
