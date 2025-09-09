{{-- expects: $comments (Paginator), $report --}}
<div class="comments-list">
  @forelse ($comments as $comment)
    @include('partials.comments._item', ['comment' => $comment, 'report' => $report])
  @empty
    <div class="text-center py-8" style="color:var(--muted)">
      <svg class="h-8 w-8 mx-auto mb-2 opacity-50" viewBox="0 0 24 24" fill="currentColor">
        <path d="M20 2H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h4l4 4 4-4h4c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2z"/>
      </svg>
      <p class="text-sm">No comments yet. Be the first to comment!</p>
    </div>
  @endforelse
</div>

@if ($comments->hasMorePages())
  <div class="mt-3">
    {{-- Optional: a "Load more" button could hit ?page=2 via AJAX if you want. --}}
  </div>
@endif
