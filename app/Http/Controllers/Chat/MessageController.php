<?php

namespace App\Http\Controllers\Chat;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\user;

class MessageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

        $users = User::where('id', '!=', auth()->user()->id)->get();

        return view("pages.chat", compact('users'));

    }

    public function show(Request $request,string $id)
    {

        $reciver = User::where('id', $id)->first();
        if (!$reciver) {
            return redirect('/chat');
        }

        return view("pages.chat-private",[
            'receiver_id' => $id,
            'users' => User::where('id', '!=', auth()->user()->id)->get(),
        ]);


    }
}
