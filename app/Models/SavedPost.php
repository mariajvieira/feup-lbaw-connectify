<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SavedPost extends Model
{
    use HasFactory;

    protected $table = 'saved_post'; // Nome da tabela no banco de dados.

    protected $fillable = [
        'user_id',
        'post_id',
    ];

    public $timestamps = false; // Desativa os timestamps

    public $incrementing = false; // Indica que a chave primária não é auto-incrementada

    protected $primaryKey = ['user_id', 'post_id']; // Define a chave primária composta
}
