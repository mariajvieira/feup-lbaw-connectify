<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    /**
     * Determina se o usuário pode atualizar seu perfil.
     */
    public function updateProfile(User $authUser, User $user)
    {
        return $authUser->id === $user->id || $authUser->isAdmin();
    }

    /**
     * Determina se o usuário pode visualizar o perfil.
     */
    public function getProfile(User $authUser, User $user)
    {
        // Permitir se o usuário for o mesmo, se o perfil for público ou se for administrador
        return $authUser->id === $user->id || $user->is_public || $authUser->isAdmin();
    }

    /**
     * Determina se o usuário pode editar o perfil.
     */
    public function editProfile(User $authUser, User $user)
    {
        // Permitir se o usuário for o mesmo ou se for administrador
        return $authUser->id === $user->id || $authUser->isAdmin();
    }

    /**
     * Determina se o usuário pode excluir o perfil.
     */
    public function deleteUser(User $authUser, User $user)
    {
        // Permitir se o usuário for o mesmo ou se for administrador
        return $authUser->id === $user->id || $authUser->isAdmin();
    }
}
