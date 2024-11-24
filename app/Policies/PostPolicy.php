<?php

namespace App\Policies;

use App\Models\Post;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PostPolicy
{
    use HandlesAuthorization;

    /**
     * Determine if the user can create posts.
     */
    public function create(User $user)
    {
        return Auth::check()|| $user->is_admin;  ;   
    }

    /**
     * Determine if the user can update the post.
     */
    public function update(User $user, Post $post)
    {
        return $post->user_id === $user->id || $user->isAdmin();  
    }

    public function edit(User $user, Post $post)
    {
        return $post->user_id === $user->id || $user->isAdmin();  
    }

    /**
     * Determine if the user can delete the post.
     */
    public function delete(User $user, Post $post)
    {
        return $post->user_id === $user->id || $user->isAdmin(); 
    }

    /**
     * Determine if the user can view the post.
     */
    public function view(User $user, Post $post)
    {
        return $post->is_public || $user->id === $post->user_id || $this->areFriends($user, $post->user_id);    }


    private function areFriends(User $user, $postUserId)
    {
        return $user->friends()->where('friend_id', $postUserId)->exists();
    }
}
