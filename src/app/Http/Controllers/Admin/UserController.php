<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;

class UserController extends Controller
{
    public function index()
    {
        $users = User::where('role', 'registered')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.users.index', compact('users'));
    }

    public function block(User $user)
    {
        if ($user->role === 'admin') {
            return back()->with('error', 'Ne možete blokirati administratora.');
        }

        $user->update(['is_blocked' => true]);

        return back()->with('success', "Korisnik {$user->name} je blokiran.");
    }

    public function unblock(User $user)
    {
        $user->update(['is_blocked' => false]);

        return back()->with('success', "Korisnik {$user->name} je odblokiran.");
    }
}
