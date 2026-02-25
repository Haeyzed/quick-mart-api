<?php

use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

/**
 * Authorize the live attendance feed.
 * Only allow authenticated users who have permission to view attendances.
 */
Broadcast::channel('attendance.live', function (User $user) {
    // If you are using Spatie Permission (which it looks like you are):
    return $user->can('view attendances');

    // OR, if you just want ANY logged-in user to see it:
    // return $user !== null;
});
