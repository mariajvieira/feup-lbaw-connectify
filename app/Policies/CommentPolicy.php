<?php

namespace App\Policies;

use App\Models\Comment;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Access\Response;

class CommentPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can create models.
     */
    public function create(): bool
    {
        return Auth::check();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function edit(User $user, Comment $comment): bool
    {
        return Auth::check() && ($user->id == $comment->user_id || $user->isAdmin());
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function destroy(User $user, Comment $comment): bool
    {
        return Auth::check() && ($user->id == $comment->user_id || $user->isAdmin());  

    }



}
