<?php

namespace App\Models;
use App\Models\Post;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Post extends Model
{
    use HasFactory;

    // Disable timestamps since your table doesn't include 'created_at' and 'updated_at' fields.
    public $timestamps = false;

    protected $table = 'post';

    protected $primaryKey = 'id';

    protected $fillable = [
        'user_id', 'group_id', 'content', 'IMAGE1', 'IMAGE2', 'IMAGE3', 'is_public', 'post_date',
    ];

    protected $casts = [
        'post_date' => 'datetime',
    ];

    //Define public post
    public function public()
    {
        return Post::select('post.*')
                ->join('user_', 'user_.user_id', '=', 'post.user_id')
                ->where('user_.is_public', true)
                ->where('post.is_public', true);
    }

    /**
     * Get the user that owns the post.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    /**
     * Get the group that the post belongs to (if applicable).
     */
    public function group()
    {
        return $this->belongsTo(Group::class, 'group_id', 'group_id');
    }
}
