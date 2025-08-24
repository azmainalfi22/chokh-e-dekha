{{-- resources/views/partials/_comment.blade.php --}}
@props(['comment'])

<li class="comment-item">
  <div class="comment-avatar">
    {{ substr($comment->user->name ?? 'A', 0, 1) }}
  </div>
  <div class="comment-bubble">
    <div class="comment-author">
      {{ $comment->user->name ?? 'Anonymous' }}
      <span class="text-xs ml-2" style="color:var(--muted)">
        {{ optional($comment->created_at)->diffForHumans() }}
      </span>
    </div>
    <div class="comment-text">{{ $comment->body }}</div>
  </div>
</li>
