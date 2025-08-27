@php
  $userName = auth()->user()->name ?? 'Guest';
@endphp

<div id="comments-{{ $report->id }}" class="mt-4">
  <div class="cd-thread-content">
    {{-- Error message display --}}
    <div data-error class="hidden mb-3 p-3 bg-red-100 border border-red-400 text-red-700 rounded-lg text-sm"></div>
    
    {{-- Existing comments (styled) --}}
    <ol class="space-y-3 mb-4 js-thread-list" data-list>
      @foreach ($report->comments as $c)
        <li class="comment-item" data-comment-id="{{ $c->id }}">
          <div class="comment-avatar">
            {{ mb_strtoupper(mb_substr($c->user->name ?? 'A', 0, 1)) }}
          </div>
          <div class="comment-bubble">
            <div class="comment-header flex items-center justify-between">
              <div class="comment-author">
                {{ $c->user->name ?? 'User' }}
                <span class="text-xs ml-2" style="color:var(--muted)">
                  {{ $c->created_at->diffForHumans() }}
                </span>
              </div>
              @if(auth()->check() && auth()->id() === $c->user_id)
                <button class="delete-comment-btn text-red-500 hover:text-red-700 text-xs ml-2 opacity-70 hover:opacity-100 transition-opacity"
                        data-comment-id="{{ $c->id }}"
                        title="Delete comment">
                  ✕
                </button>
              @endif
            </div>
            <div class="comment-text">{{ $c->body }}</div>
          </div>
        </li>
      @endforeach
    </ol>

    {{-- Create comment (styled) --}}
    @auth
      <form data-form action="{{ route('reports.comments.store', $report) }}" method="post" class="js-comment-form mt-3">
        @csrf
        <div class="flex gap-3">
          <div class="comment-avatar">
            {{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}
          </div>
          <div class="flex-1">
            <textarea name="body" rows="2"
                      class="w-full rounded-xl border px-4 py-3 text-sm resize-none focus:ring-2 transition-all duration-200"
                      style="border-color:var(--ring); min-height:44px; color:var(--text);"
                      placeholder="Write a comment…"></textarea>
            <div class="flex justify-end mt-2">
              <button type="submit"
                      class="px-4 py-2 rounded-lg text-sm font-semibold text-white shadow-sm hover:shadow-md transition-all duration-200"
                      style="background: linear-gradient(135deg, var(--accent), #f97316);">
                Post
              </button>
            </div>
          </div>
        </div>
      </form>
    
    @else
      <div class="text-center py-4" style="color:var(--muted)">
        <a href="{{ route('login') }}" class="font-medium" style="color:var(--link)">
          Log in to comment
        </a>
      </div>
    @endauth
  </div>
</div>

{{-- Pass PHP variables to JavaScript --}}
<script>
  window.commentsData = window.commentsData || {};
  window.commentsData['{{ $report->id }}'] = {
    userName: @json($userName),
    userId: @json(auth()->id()),
    csrfToken: @json(csrf_token()),
    deleteRoute: @json(route('reports.comments.destroy', ['report' => $report->id, 'comment' => '__ID__']))
  };
</script>

@push('scripts')
<script>
(function(){
  const reportId = '{{ $report->id }}';
  const root = document.getElementById(`comments-${reportId}`);
  if(!root) return;

  const form = root.querySelector('[data-form]');
  if(!form) return;

  const list = root.querySelector('[data-list]');
  const errEl = root.querySelector('[data-error]');
  const commentsData = window.commentsData[reportId];
  
  if (!commentsData) {
    console.error('Comments data not found');
    return;
  }

  // prevent page-level hotkeys while typing here
  form.addEventListener('keydown', (e) => {
    const noMods = !e.ctrlKey && !e.metaKey && !e.altKey && !e.shiftKey;
    const k = (e.key || '').toLowerCase();
    if (noMods && (k === 'c' || k === 'escape')) e.stopPropagation();
  }, true);

  // Helper function to show error
  function showError(message) {
    if (errEl) {
      errEl.textContent = message;
      errEl.classList.remove('hidden');
    }
  }

  // Helper function to hide error
  function hideError() {
    if (errEl) {
      errEl.classList.add('hidden');
    }
  }

  async function parseMaybeJSON(res){
    const ct = (res.headers.get('content-type') || '').toLowerCase();
    if (ct.includes('application/json')) {
      try { 
        return await res.json(); 
      } catch { 
        return null; 
      }
    }
    return null;
  }

  // Add comment functionality
  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    hideError();

    const body = form.body.value.trim();
    if(!body){
      showError('Comment can\'t be empty.');
      return;
    }

    // optimistic placeholder
    const temp = document.createElement('li');
    temp.className = "comment-item opacity-60";
    temp.innerHTML = `
      <div class="comment-avatar">${commentsData.userName.charAt(0).toUpperCase()}</div>
      <div class="comment-bubble">
        <div class="comment-header flex items-center justify-between">
          <div class="comment-author">
            ${commentsData.userName}
            <span class="text-xs ml-2" style="color:var(--muted)">Just now</span>
          </div>
        </div>
        <div class="comment-text">${body}</div>
      </div>
    `;
    list.appendChild(temp);

    try {
      const res = await fetch(form.action, {
        method: 'POST',
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': commentsData.csrfToken,
          'Accept': 'application/json',
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({ body })
      });

      if (res.status === 401) {
        throw new Error('You must be logged in.');
      }

      const data = await parseMaybeJSON(res);

      if (!res.ok) {
        const msg = (data && (data.message || data.error)) || `HTTP ${res.status}`;
        throw new Error(msg);
      }

      // Success - update with real data
      const commentId = data?.id || Date.now();
      const name = data?.name || commentsData.userName;
      const time = data?.time || 'Just now';
      const confirmedBody = data?.body || body;

      temp.classList.remove('opacity-60');
      temp.setAttribute('data-comment-id', commentId);
      
      let deleteButton = '';
      if (commentsData.userId) {
        deleteButton = `
          <button class="delete-comment-btn text-red-500 hover:text-red-700 text-xs ml-2 opacity-70 hover:opacity-100 transition-opacity"
                  data-comment-id="${commentId}"
                  title="Delete comment">
            ✕
          </button>
        `;
      }

      temp.innerHTML = `
        <div class="comment-avatar">${name.charAt(0).toUpperCase()}</div>
        <div class="comment-bubble">
          <div class="comment-header flex items-center justify-between">
            <div class="comment-author">
              ${name}
              <span class="text-xs ml-2" style="color:var(--muted)">${time}</span>
            </div>
            ${deleteButton}
          </div>
          <div class="comment-text">${confirmedBody}</div>
        </div>
      `;

      form.reset();
      if (form.body) form.body.style.height = 'auto';
      
    } catch (err) {
      temp.remove();
      showError(err.message || 'Failed to post comment');
      console.error('Comment post error:', err);
    }
  });

  // Delete comment functionality
  root.addEventListener('click', async (e) => {
    if (!e.target.classList.contains('delete-comment-btn')) return;
    
    e.preventDefault();
    
    if (!confirm('Are you sure you want to delete this comment?')) return;
    
    const commentId = e.target.getAttribute('data-comment-id');
    const commentItem = e.target.closest('.comment-item');
    
    if (!commentId || !commentItem) return;
    
    // Show deleting state
    commentItem.style.opacity = '0.5';
    e.target.disabled = true;
    
    try {
      const deleteUrl = commentsData.deleteRoute.replace('__ID__', commentId);
      
      const res = await fetch(deleteUrl, {
        method: 'DELETE',
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': commentsData.csrfToken,
          'Accept': 'application/json'
        }
      });
      
      if (res.status === 401) {
        throw new Error('You must be logged in.');
      }
      
      if (!res.ok) {
        const data = await parseMaybeJSON(res);
        const msg = (data && (data.message || data.error)) || `HTTP ${res.status}`;
        throw new Error(msg);
      }
      
      // Success - remove comment with animation
      commentItem.style.transition = 'all 0.3s ease';
      commentItem.style.transform = 'translateX(-100%)';
      commentItem.style.opacity = '0';
      
      setTimeout(() => {
        commentItem.remove();
      }, 300);
      
    } catch (err) {
      // Restore state on error
      commentItem.style.opacity = '1';
      e.target.disabled = false;
      showError(err.message || 'Failed to delete comment');
      console.error('Comment delete error:', err);
    }
  });
})();
</script>
@endpush