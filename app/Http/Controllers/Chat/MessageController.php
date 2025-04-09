<?php

namespace App\Http\Controllers\Chat;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\user;
use Illuminate\Support\Facades\Cache;

class MessageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

        $currentUserId = auth()->id();

        $users = Cache::rememberForever("chat_users_except_{$currentUserId}", function () use ($currentUserId) {
            return User::where('id', '!=', $currentUserId)->get();
        });

        return view("pages.chat", compact('users'));

    }

    public function show(Request $request,string $id)
    {

        $reciver = User::where('id', $id)->first();
        if (!$reciver) {
            return redirect('/chat');
        }

        $currentUserId = auth()->id();

        $users = Cache::rememberForever("chat_users_except_{$currentUserId}", function () use ($currentUserId) {
            return User::where('id', '!=', $currentUserId)->get();
        });

        return view("pages.chat-private",[
            'receiver_id' => $id,
            'users' => $users,
        ]);


    }
}
