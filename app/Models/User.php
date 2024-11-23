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
        'password',
        'is_public',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
    ];

    /**
     * Get the password for authentication.
     *
     * @return string
     */
    public function getAuthPassword()
    {
        return $this->password;
    }

    public function posts()
    {
        return $this->hasMany(Post::class);
    }


    public function visiblePosts()
    {

        $userId = auth()->id();

        $friendPosts = Post::select('post.*')
            ->join('friendship', function ($join) use ($userId) {
                $join->on('friendship.user_id1', '=', 'post.user_id')
                     ->orOn('friendship.user_id2', '=', 'post.user_id');
            })
            ->where(function ($query) use ($userId) {
                $query->where('friendship.user_id1', $userId)
                      ->orWhere('friendship.user_id2', $userId);
            });
    

        $posts = $friendPosts
            ->orderByDesc('post_date') // Ordenar pela data do post
            ->get();
    
        return $posts;
    }
    


    public function friends()
    {
        // Amizades onde o usuário é o user_id1
        $friends1 = $this->belongsToMany(User::class, 'friendship', 'user_id1', 'user_id2');
        // Amizades onde o usuário é o user_id2
        $friends2 = $this->belongsToMany(User::class, 'friendship', 'user_id2', 'user_id1');

        // Combina ambos os relacionamentos
        return $friends1->union($friends2);
    }

    public function friendships(): HasMany
    {
        return $this->hasMany(Friendship::class, 'user_id1', 'user_id')
            ->orWhere('user_id2', $this->user_id);
    }

    
}



