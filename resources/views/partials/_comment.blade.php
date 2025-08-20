<div class="py-3 flex items-start gap-3">
  <div class="h-8 w-8 rounded-full bg-amber-200 grid place-items-center text-amber-900 font-semibold">
    {{ strtoupper(mb_substr($comment->user->name ?? 'U', 0, 1)) }}
  </div>
  <div class="min-w-0">
    <div class="text-sm">
      <span class="font-semibold">{{ $comment->user->name ?? 'User' }}</span>
      <span class="text-amber-900/60 text-xs">• {{ $comment->created_at->diffForHumans() }}</span>
    </div>
    <div class="mt-1 text-sm text-amber-900/90">{{ $comment->body }}</div>
  </div>
</div>
