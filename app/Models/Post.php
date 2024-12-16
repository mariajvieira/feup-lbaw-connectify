<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'post';

    protected $primaryKey = 'id';

    protected $fillable = [
        'user_id', 'group_id', 'content', 'image1', 'image2', 'image3', 'is_public', 'post_date',
    ];

    protected $casts = [
        'post_date' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function group()
    {
        return $this->belongsTo(Group::class, 'group_id', 'group_id');
    }

    public static function public()
    {
        return self::where('is_public', true);
    }

    public function reactions()
    {
        return $this->morphMany(Reaction::class, 'target')
        ->where('target_type', 'post');    
    }

    public function comments()
    {
        return $this->hasMany(Comment::class, 'post_id', 'id');
    }
    public function isSavedByUser()
{
    return SavedPost::where('user_id', auth()->id())
                    ->where('post_id', $this->post_id)
                    ->exists();}
public function savedPosts()
{
    return $this->belongsToMany(User::class, 'saved_post', 'post_id', 'user_id');
}
public function usersWhoSaved()
{
    return $this->belongsToMany(User::class, 'saved_post', 'post_id', 'user_id');
}

public function tags()
{
    return $this->belongsToMany(User::class, 'post_user', 'post_id', 'user_id');
}




}
