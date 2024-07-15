<?php

namespace App\Policies;

use App\Models\Like;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class LikePolicy
{
    public function modifyforceDelete(User $user, Like $like): Response
    {
        return $user->id === $like->user_id
            ? Response::allow()
            : Response::deny('You do not own this like');
    }
}
