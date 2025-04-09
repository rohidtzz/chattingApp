<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Cache;

// use App\Http\Requests\RegisterRequest;
use App\Models\User;

class RegisterController extends Controller
{
    public function create()
    {
        return view('auth.register');
    }

    public function store()
    {
        $attributes = request()->validate([
            'username' => 'required|max:255|min:2',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|min:5|max:255',
            'terms' => 'required'
        ]);

        $user = User::create($attributes);

        $allUserIds = User::pluck('id');
        foreach ($allUserIds as $id) {
            Cache::forget("chat_users_except_{$id}");
        }

        auth()->login($user);

        return redirect('/dashboard');
    }
}
