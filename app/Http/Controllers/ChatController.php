<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function sendMessage(Request $request)
    {
        $user = auth()->guard('api')->user();
        $message = $request->message;

        broadcast(new MessageSent($user, $message));

        return response()->json(['status' => 'Message Sent!']);
    }
}
