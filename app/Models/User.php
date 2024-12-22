<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

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
        'profile_picture',
        'google_id'
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

    /**
     * Relação com os posts do usuário
     */
    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    /**
     * Relação com os comentários do usuário
     */
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * Relação com as reações do usuário
     */
    public function reactions()
    {
        return $this->hasMany(Reaction::class);
    }

    // Exemplo de método para anonimizar os dados
    public function anonymizeData()
    {
        // ID do usuário anônimo
        $anonymousUserId = 0; // ID do usuário anônimo que você criou

        // Anonimizar os dados relacionados ao usuário
        $this->comments()->update(['user_id' => $anonymousUserId]); // Anonimizar os comentários
        $this->reactions()->update(['user_id' => $anonymousUserId]); // Anonimizar as reações
        $this->posts()->update(['user_id' => $anonymousUserId]); // Anonimizar os posts
    }

    /**
     * Get the visible posts for the user
     */
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
            ->orderByDesc('post_date') 
            ->get();
    
        return $posts;
    }

    public function Friends_Public_Posts()
    {
            // Obtém o usuário logado
            $user = auth()->user();

            // Obtém os posts públicos
            $publicPosts = Post::where('is_public', true)
                 ->orderBy('post_date', 'desc')
                 ->get();
        
            // Obtém os IDs dos amigos diretamente com uma consulta
            $friendIds = DB::table('friendship')
                ->where('user_id1', $user->id)
                ->orWhere('user_id2', $user->id)
                ->pluck('user_id1', 'user_id2')
                ->flatten()
                ->unique();
        
            // Adiciona o próprio usuário para garantir que seus próprios posts também apareçam
            $friendIds->push($user->id);
        
            // Agora, obtém os posts dos amigos
            $friendPosts = Post::whereIn('user_id', $friendIds)
                ->orderBy('post_date', 'desc')
                ->get();
        
            // Combina os posts públicos e os posts dos amigos
            $posts = $publicPosts->merge($friendPosts);
        
            // Ordena todos os posts pela data (descendente)
            $posts = $posts->sortByDesc('post_date');
            return $posts;
    }

    // Relação com os amigos
    public function friends()
    {
        // Amizades onde o usuário é o user_id1
        $friends1 = $this->belongsToMany(User::class, 'friendship', 'user_id1', 'user_id2');
        // Amizades onde o usuário é o user_id2
        $friends2 = $this->belongsToMany(User::class, 'friendship', 'user_id2', 'user_id1');

        // Combina ambos os relacionamentos
        return $friends1->union($friends2);
    }

    // Verifica se o usuário é administrador
    public function isAdmin()
    {
        return Administrator::where('user_id', $this->id)->exists(); 
    }

    // Verifica se o perfil do usuário é público
    public function isPublic()
    {
        return $this->is_public;
    }

    // Verifica se o usuário é amigo de outro
    public function isFriend(User $user)
    {
        return DB::table('friendship')
            ->where(function ($query) use ($user) {
                $query->where('user_id1', $this->id)
                    ->where('user_id2', $user->id);
            })
            ->orWhere(function ($query) use ($user) {
                $query->where('user_id1', $user->id)
                    ->where('user_id2', $this->id);
            })
            ->exists();
    }

    public function hasPendingRequestFrom(User $user)
    {
        return DB::table('friend_request')
            ->where('sender_id', $user->id)
            ->where('receiver_id', $this->id)
            ->where('request_status', 'pending')
            ->exists();
    }

    public function pendingRequests()
    {
        return $this->hasMany(FriendRequest::class, 'receiver_id')
                    ->where('request_status', 'pending');
    }

    // Relacionamento com grupos
    public function groups()
    {
        return $this->belongsToMany(Group::class, 'group_member', 'user_id', 'group_id');
    }

    public function ownedGroups()
    {
        return $this->hasMany(Group::class, 'owner_id');  // Note que a chave estrangeira é 'owner_id'
    }


    // Relacionamento com posts salvos
    public function savedPosts()
    {
        return $this->belongsToMany(Post::class, 'saved_post', 'user_id', 'post_id');
    }
    
    // Relacionamento com posts onde o usuário é marcado
    public function taggedPosts()
    {
        return $this->belongsToMany(Post::class, 'tagged_post', 'user_id', 'post_id')
                    ->withPivot('tagged_by', 'created_at'); // Inclui informações adicionais
    }
    public function isInGroup($groupId)
{
    return $this->groups()->where('group_id', $groupId)->exists();
}

}


