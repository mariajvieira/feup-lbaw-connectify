<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;
    public $timestamps  = false;
    protected $table = 'comment_';
    protected $primaryKey = 'id';
    protected $fillable = [
        'user_id',
        'post_id',
        'comment_content',
        'commentDate',
    ];

    protected $casts = [
        'commentDate' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    public function reactions()
    {
        return $this->morphMany(Reaction::class, 'target');
    }
}
