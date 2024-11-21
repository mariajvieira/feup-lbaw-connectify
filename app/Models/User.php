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

    // Nome da tabela explicitamente definido
    protected $table = 'user_';

    // Desativar timestamps
    public $timestamps = false;

    /**
     * Atributos atribuíveis em massa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'username',
        'email',
        'user_password',
        'profile_picture',
        'is_public',
    ];

    /**
     * Atributos ocultos na serialização.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'user_password',
        'remember_token',
    ];

    /**
     * Cast de atributos para tipos específicos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'user_password' => 'hashed', // Hash automático
    ];

    /**
     * Relacionamento: um usuário pode ter muitos posts.
     */
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class, 'user_id', 'user_id');
    }

    /**
     * Relacionamento: um usuário pode ter muitos amigos.
     */
    public function friendships(): HasMany
    {
        return $this->hasMany(Friendship::class, 'user_id1', 'user_id')
            ->orWhere('user_id2', $this->user_id);
    }

    /**
     * Posts visíveis para o usuário.
     */
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
