<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    // Definir a tabela explicitamente
    protected $table = 'group_'; // Tabela que você está usando

    public $timestamps = false;

    // Definir os campos que podem ser preenchidos
    protected $fillable = [
        'group_name',    // Nome do grupo
        'description',   // Descrição do grupo
        'owner_id',      // ID do proprietário do grupo
        'is_public'      // Se o grupo é público
    ];

    /**
     * Relacionamento com o proprietário do grupo (um-para-um)
     */
    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');  // Relacionamento belongsTo
    }

    /**
     * Relacionamento com os usuários do grupo (muitos-para-muitos)
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'group_member', 'group_id', 'user_id');
    }

    /**
     * Verificar se o usuário é membro do grupo
     */
    public function isMember(User $user)
    {
        return $this->users()->where('user_id', $user->id)->exists();
    }

    /**
     * Verificar se o usuário é o proprietário do grupo
     */
    public function isOwner(User $user)
    {
        return $this->owner_id == $user->id;
    }
    public function posts()
{
    return $this->hasMany(Post::class, 'group_id');
}



}