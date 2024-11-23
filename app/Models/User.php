<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'username',
        'email',
        'user_password',
        'is_public',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'user_password',
    ];

    /**
     * Get the password for authentication.
     *
     * @return string
     */
    public function getAuthPassword()
    {
        return $this->user_password;
    }

    public function posts()
    {
        return $this->hasMany(Post::class);
    }


    public function visiblePosts()
    {
        $ownPosts = Post::where('user_id', $this->id);
    
        $friendPosts = Post::select('post.*')
            ->join('friendship', function ($join) {
                $join->on('friendship.user_id1', '=', 'post.user_id')
                     ->orOn('friendship.user_id2', '=', 'post.user_id');
            })
            ->where(function ($query) {
                $query->where('friendship.user_id1', $this->id)
                      ->orWhere('friendship.user_id2', $this->id);
            });
    
        $publicPosts = Post::where('is_public', true);
    

        return $ownPosts->union($friendPosts)
            ->union($publicPosts)
            ->orderBy('post_date', 'desc')
            ->get();
    }
    
}




/*
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'user_';
    protected $primaryKey = 'user_id'; 
    public $incrementing = true; 
    protected $keyType = 'string';


    public $timestamps = false;


    protected $fillable = [
        'username',
        'email',
        'user_password', 
        'profile_picture', 
        'is_public',
    ];


    protected $hidden = [
        'user_password', 
        'remember_token',
    ];

    public function getAuthIdentifierName()
    {
        return 'username';
    }

    public function setPasswordAttribute($password)
    {
        $this->attributes['user_password'] = bcrypt($password);
    }


    public function posts(): HasMany
    {
        return $this->hasMany(Post::class, 'user_id', 'user_id');
    }


    public function friendships(): HasMany
    {
        return $this->hasMany(Friendship::class, 'user_id1', 'user_id')
            ->orWhere('user_id2', $this->user_id);
    }


    public function visiblePosts()
    {
        // Próprios posts
        $ownPosts = Post::where('user_id', $this->user_id);

        // Posts dos amigos
        $friendPosts = Post::select('post.*')
            ->join('friendship', function ($join) {
                $join->on('friendship.user_id1', '=', 'post.user_id')
                    ->orOn('friendship.user_id2', '=', 'post.user_id');
            })
            ->where(function ($query) {
                $query->where('friendship.user_id1', $this->user_id)
                    ->orWhere('friendship.user_id2', $this->user_id);
            });

        // Posts públicos
        $publicPosts = Post::where('is_public', true);

        return $ownPosts->union($friendPosts)
            ->union($publicPosts)
            ->orderBy('post_date', 'desc')
            ->get();
    }
}
*/