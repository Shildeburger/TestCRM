<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('telegram.chat.{chatId}', function ($user, int $chatId) {
    \Log::info('Auth telegram.chat', ['user_id' => $user?->id, 'chatId' => $chatId]);
    return $user !== null;
});

Broadcast::channel('contacts', function ($user) {
    \Log::info('Auth contacts channel', ['user_id' => $user?->id]);
    return $user !== null; 
});