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

    /**reate
     * Determine if the user can delete the post.
     */
    public function delete(User $user, Post $post)
    {
        return $post->user_id === $user->id || $user->isAdmin(); 
    }

    /**
     * Determine if the user can view the post.
     */
    public function view(User $user = null, Post $post)
    {
        // Permitir que posts públicos sejam vistos por qualquer pessoa (inclusive não autenticados)
        if ($post->is_public) {
            return true;
        }
    
        // Se o usuário não está autenticado, retornar false
        if ($user === null) {
            return false;
        }
    
        // Permitir que o usuário veja seus próprios posts, mesmo que não seja público
        if ($user->id === $post->user_id) {
            return true;
        }
    
        // Permitir que amigos vejam o post
        if ($this->areFriends($user, $post->user_id)) {
            return true;
        }
    
        // Verificação de grupo, se o post está dentro de um grupo
        $group = $post->group; 
        if ($group) {
            // Se o grupo não for público, checar se o usuário está no grupo ou é dono do grupo
            if (!$group->is_public) {
                if ($user->groups->contains($group->id)) {
                    return true;
                }
                if ($user->ownedGroups->contains($group->id)) {
                    return true;
                }
            }
        }
    
        return false;
    }
    
    

    private function areFriends(User $user, $postUserId)
    {
        return $user->friends()->where('friend_id', $postUserId)->exists();
    }
}
