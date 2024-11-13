<?php

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Log;
use SebastianBergmann\Type\TrueType;

Broadcast::channel('chat', function ($user) {
    return $user; // Trả về thông tin người dùng để xác thực cho presence channel
});

Broadcast::channel('room.{roomId}', function ($user, int $roomId) {
    return ['id' => $user->id, 'name' => $user->name];
}, ['guards' => ['api']]);
