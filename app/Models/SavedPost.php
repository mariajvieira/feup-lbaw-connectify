<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SavedPost extends Model
{
    use HasFactory;

    // Nome da tabela no banco de dados.
    protected $table = 'saved_post'; 

    // Atributos que podem ser preenchidos
    protected $fillable = [
        'user_id',
        'post_id',
    ];

    // Desativa os timestamps
    public $timestamps = false;

    // Indica que a chave primária não é auto-incrementada
    public $incrementing = false;

    // Define a chave primária composta
    protected $primaryKey = ['user_id', 'post_id'];

    // Quando Laravel precisa da chave primária, ele irá usar o método a seguir
    public function getKeyName()
    {
        return $this->primaryKey;
    }
}
