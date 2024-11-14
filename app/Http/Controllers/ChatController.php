<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ChatController extends Controller
{
    public function getFriend(Request $request)
    {
        return response()->json(User::where('id', '!=', auth()->guard('api')->id())->get());
    }

    public function getMessage(Request $request)
    {
        return Message::with('user')->oldest()->get();
    }

    public function sendMessage(Request $request)
    {
        try {
            return DB::transaction(function () use ($request) {
                $user = auth()->guard('api')->user();
                $message = $request->message;

                Message::create([
                    'message' => $message,
                    'user_id' => $user->id,
                    'conversation_id' => null
                ]);
                broadcast(new MessageSent($user, $message));

                return response()->json(['status' => 'Message Sent!']);
            });
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
