<form action="{{ route('reports.comments.store', $report) }}" method="POST" class="flex items-start gap-2">
  @csrf
  <textarea name="body" rows="2" required
            class="w-full rounded-xl border px-3 py-2 text-sm"
            placeholder="Write a comment…"></textarea>
  <button class="cd-chip px-3 py-2 rounded-xl bg-gradient-to-r from-amber-600 to-rose-600 text-white">
    Post
  </button>
</form>
