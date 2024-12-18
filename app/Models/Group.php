<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    // Definir a tabela explicitamente, já que o nome da tabela não segue a convenção plural
    protected $table = 'group_'; // Tabela que você está usando

    public $timestamps = false;
    
    // Definir os campos que podem ser preenchidos
    protected $fillable = [
        'group_name',    // Nome do grupo
        'description',   // Descrição do grupo
        'owner_id',      // ID do proprietário do grupo (assumindo que é um usuário)
        'is_public'      // Se o grupo é público
    ];

    // Definir a relação com o usuário (proprietário do grupo)
    public function owner()
    {
        return $this->belongsToMany(User::class, 'group_owner', 'group_id', 'user_id')->wherePivot('user_id', $this->owner_id)->limit(1);
    }

    // Relacionamento com os usuários do grupo
    public function users()
    {
        return $this->belongsToMany(User::class, 'group_member', 'group_id', 'user_id');
    }
}
