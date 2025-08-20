<form action="{{ route('reports.endorse', $report) }}" method="POST">
  @csrf
  <button class="cd-chip inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-white ring-1 ring-amber-900/10 hover:bg-amber-50 dark:bg-[#1b1f24] dark:text-amber-100 dark:ring-white/10 dark:hover:bg-[#232830]">
    👍 Endorse
  </button>
</form>
