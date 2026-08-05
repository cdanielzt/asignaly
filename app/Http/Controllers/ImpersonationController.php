<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

class ImpersonationController extends Controller
{
    public function start(User $user)
    {
        abort_if($user->id === Auth::id(), 403, 'No puedes suplantarte a ti mismo.');
        // return banner lives in AppLayout, which super_admins don't render — so don't strand yourself
        abort_if($user->isSuperAdmin(), 403, 'No puedes suplantar a otro super admin.');

        session(['impersonator_id' => Auth::id()]);
        session()->forget('admin_congregation_id');
        Auth::login($user);

        return redirect('/');
    }

    public function stop()
    {
        $id = session()->pull('impersonator_id');
        abort_if(! $id, 403);

        Auth::loginUsingId($id);

        return redirect()->route('admin.users.index')->with('success', 'Volviste a tu cuenta.');
    }
}
