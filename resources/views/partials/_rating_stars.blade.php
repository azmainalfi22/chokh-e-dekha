<form action="{{ route('reports.ratings.store', $report) }}" method="POST" class="flex items-center gap-1">
  @csrf
  @for($i=1;$i<=5;$i++)
    <button name="score" value="{{ $i }}" class="p-0.5"
            title="Rate {{ $i }}"
            aria-label="Rate {{ $i }}">
      <svg class="h-5 w-5 @if(($report->avg_rating ?? 0) >= $i) text-amber-500 @else text-amber-300 @endif"
           viewBox="0 0 24 24" fill="currentColor">
        <path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.62L12 2 9.19 8.62 2 9.24l5.46 4.73L5.82 21z"/>
      </svg>
    </button>
  @endfor
</form>
