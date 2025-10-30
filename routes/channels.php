<?php

use App\Models\MasterfileModels\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});
Broadcast::channel('pdf-generation.{userId}', function (User $user, $userId) {
    return (int) $user->id === (int) $userId;
});
Broadcast::channel('transaction-pdf-generation.{userId}', function (User $user, $userId) {
    return (int) $user->id === (int) $userId;
});
Broadcast::channel('textfile-generation.{userId}', function (User $user, $userId) {
    return (int) $user->id === (int) $userId;
});
Broadcast::channel('notification-update.{userId}', function (User $user, $userId) {
    return (int) $user->id === (int) $userId;
});

// Private channel for user messages
Broadcast::channel('user.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Presence channel for online users
Broadcast::channel('users', function ($user) {
    return [
        'id' => $user->id,
        'name' => $user->name,
        'role' => $user->role ?? 'User',
    ];
});

// Channel for typing indicators (optional)
Broadcast::channel('typing.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});

// Channel for message status updates (optional)
Broadcast::channel('message-status.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});
