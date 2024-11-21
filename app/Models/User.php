<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use APP\Models\Post;
// Added to define Eloquent relationships.
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    // Don't add create and update timestamps in database.
    public $timestamps  = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'profile_picture',
        'is_public',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Get the cards for a user.
     */
    public function cards(): HasMany
    {
        return $this->hasMany(Card::class);
    }

    public function visiblePosts(){
        //own posts
        $own = Post::select('*')->where('post.user_id',$this->user_id)->get();
        //friends posts
        $friends = Post::select('post.*')
        ->join('friendship', function ($join) {
            $join->on('friendship.user_id1', '=', 'post.user_id')
                ->orOn('friendship.user_id2', '=', 'post.user_id');
        })
        ->where(function ($query) {
            $query->where('friendship.user_id1', $this->user_id)
                  ->orWhere('friendship.user_id2', $this->user_id);
        });
        
        //public 
        $public = Post::public();

        return $own->union($friends)->union($public)->orderBy('post_date','desc');
    }
}
