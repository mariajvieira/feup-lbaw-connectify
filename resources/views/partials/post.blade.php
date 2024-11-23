<div class="post-item">
    <h4>{{ $post->title }}</h4>
    <p>{{ $post->content }}</p> <!-- Mostra o conteúdo completo -->
    <span class="post-date">
        Published at {{ \Carbon\Carbon::parse($post->post_date)->format('d/m/Y  H:i') }}
    </span>

    @if($post->image)
        <div class="post-image">
            <img src="{{ asset('storage/' . $post->image) }}" alt="Imagem do Post">
        </div>
    @endif
</div>
