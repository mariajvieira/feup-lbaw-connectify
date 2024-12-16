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
        'visibility',    // Visibilidade do grupo
        'is_public'      // Se o grupo é público
    ];

    

    // Definir a relação com o usuário (proprietário do grupo)
    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id'); // Relação com o usuário (owner_id)
    }

    // Você pode adicionar outras relações, como posts, se necessário
}