@php
  /** @var \App\Models\Comment $c */
  $name = trim($c->user->name ?? 'User');
  // Simple initial for the avatar chip (works with multibyte names)
  $initial = mb_strtoupper(mb_substr($name, 0, 1));
@endphp

<li id="comment-{{ $c->id }}" data-comment-id="{{ $c->id }}" class="flex items-start gap-2">
  {{-- avatar placeholder --}}
  <div class="mt-0.5 h-7 w-7 flex-none rounded-full ring-1
              bg-amber-200/60 ring-amber-200 text-amber-900
              dark:bg-white/10 dark:ring-white/10 dark:text-amber-200
              flex items-center justify-center text-[11px] font-semibold">
    {{ $initial }}
  </div>

  <div class="flex-1">
    <div class="inline-block rounded-2xl px-3 py-2 text-[13px]
                bg-amber-50 ring-1 ring-amber-100
                dark:bg-white/5 dark:ring-white/10">
      <span class="font-semibold text-slate-900 dark:text-slate-100">{{ $name }}</span>
      <span class="ml-1 text-slate-800 dark:text-slate-100">{!! nl2br(e($c->body)) !!}</span>
    </div>

    <div class="mt-0.5 text-[11px] text-slate-500 dark:text-slate-400"
         title="{{ optional($c->created_at)->toDayDateTimeString() }}">
      {{ optional($c->created_at)->diffForHumans() }}
    </div>
  </div>
</li>
