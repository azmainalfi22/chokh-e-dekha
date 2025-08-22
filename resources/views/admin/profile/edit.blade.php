@extends('layouts.admin')
@section('title','Admin Profile')

@push('styles')
<style>
  /* subtle dashed dropzone */
  .dz {
    border: 1.5px dashed rgba(120,53,15,.25);
    transition: background-color .15s ease, border-color .15s ease, box-shadow .15s ease;
  }
  .dz.dragover {
    background: rgba(253,230,138,.25);
    border-color: rgba(120,53,15,.45);
    box-shadow: 0 8px 20px rgba(15,23,42,.15) inset;
  }
  /* Glass panel matches layout */
  .glass {
    background: rgba(255,255,255,.85);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    border: 1px solid rgba(120,53,15,.10);
    box-shadow: 0 18px 40px rgba(15,23,42,.12);
  }
  .dark .glass{
    background: rgba(255,255,255,.08);
    border-color: rgba(255,255,255,.10);
    box-shadow: 0 18px 40px rgba(0,0,0,.35);
  }
  /* Inputs: readable in both themes */
  .field {
    background:#fff; color:#0f172a;
    border:1px solid rgba(120,53,15,.18);
  }
  .field:focus { outline: none; box-shadow: 0 0 0 3px rgba(251,191,36,.35); }
  .dark .field { background:#fff; color:#0f172a; } /* keep light like filters */
</style>
@endpush

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-0">
  <div class="glass rounded-2xl p-6 sm:p-8">

    <div class="mb-6 sm:mb-8">
      <h1 class="text-2xl font-extrabold bg-clip-text text-transparent
                 bg-gradient-to-r from-amber-700 via-orange-700 to-rose-700">
        Admin Profile
      </h1>
      <p class="text-sm text-amber-900/70 dark:text-amber-100/70 mt-1">
        Update your details. Changes apply only to your admin account.
      </p>
    </div>

    @if(session('success'))
      <div class="mb-6 p-4 rounded-xl bg-emerald-50 text-emerald-800 ring-1 ring-emerald-200">
        {{ session('success') }}
      </div>
    @endif

    <form method="POST" action="{{ route('admin.profile.update') }}" enctype="multipart/form-data" id="profileForm">
      @csrf
      @method('PATCH')

      {{-- Avatar --}}
      <div class="mb-8">
        <label class="block text-sm font-semibold text-amber-900 dark:text-amber-100 mb-2">Profile Photo</label>

        <div class="flex items-center gap-5">
          {{-- preview circle --}}
          <div class="relative">
            @php $photo = $admin->profile_photo ?? null; @endphp
            <img id="preview"
                 class="h-24 w-24 rounded-full object-cover ring-2 ring-white shadow-md"
                 src="{{ $photo ? asset('storage/profile_photos/'.$photo) : 'data:image/svg+xml;utf8,'.rawurlencode('<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'96\' height=\'96\'><rect width=\'100%\' height=\'100%\' rx=\'48\' fill=\'#f1f5f9\'/><text x=\'50%\' y=\'52%\' font-size=\'12\' fill=\'#64748b\' text-anchor=\'middle\' font-family=\'sans-serif\'>No Photo</text></svg>') }}"
                 alt="Profile Photo">
          </div>

          {{-- dropzone / buttons --}}
          <div class="flex-1">
            <label for="fileInput"
                   class="inline-flex items-center gap-2 rounded-xl px-3 py-2 bg-gradient-to-r from-amber-600 to-rose-600 text-white shadow hover:shadow-lg cursor-pointer">
              <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M11 5h2v14h-2zM5 11h14v2H5z"/></svg>
              Upload new
            </label>

            <button type="button" id="removeBtn"
                    class="ml-2 inline-flex items-center gap-2 rounded-xl px-3 py-2 bg-white ring-1 ring-amber-900/10 text-amber-900/90 shadow hover:shadow-md">
              Remove
            </button>

            <p class="text-xs text-amber-900/70 dark:text-amber-100/70 mt-2">
              JPG/PNG/WEBP, max 2MB. You can also drag & drop below.
            </p>
          </div>
        </div>

        {{-- hidden input & drop area --}}
        <input id="fileInput" type="file" name="profile_photo" accept="image/*" class="hidden">
        <div id="dropzone" class="dz mt-4 rounded-xl p-4 flex items-center justify-center text-sm text-amber-900/70 dark:text-amber-100/70">
          Drop image here…
        </div>

        {{-- optional remove flag for backend --}}
        <input type="hidden" name="remove_photo" id="removeFlag" value="0">

        @error('profile_photo')
          <p class="text-sm text-rose-600 mt-2">{{ $message }}</p>
        @enderror
      </div>

      {{-- Fields --}}
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <label class="block text-sm font-semibold text-amber-900 dark:text-amber-100 mb-1">Name</label>
          <input type="text" name="name" value="{{ old('name', $admin->name) }}"
                 class="field w-full px-3 py-2.5 rounded-xl" required>
          @error('name') <p class="text-sm text-rose-600 mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
          <label class="block text-sm font-semibold text-amber-900 dark:text-amber-100 mb-1">Email</label>
          <input type="email" name="email" value="{{ old('email', $admin->email) }}"
                 class="field w-full px-3 py-2.5 rounded-xl" required>
          @error('email') <p class="text-sm text-rose-600 mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
          <label class="block text-sm font-semibold text-amber-900 dark:text-amber-100 mb-1">New Password</label>
          <input type="password" name="password" class="field w-full px-3 py-2.5 rounded-xl">
          @error('password') <p class="text-sm text-rose-600 mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
          <label class="block text-sm font-semibold text-amber-900 dark:text-amber-100 mb-1">Confirm Password</label>
          <input type="password" name="password_confirmation" class="field w-full px-3 py-2.5 rounded-xl">
        </div>
      </div>

      <div class="flex items-center justify-end gap-3 mt-8">
        <a href="{{ url()->previous() }}"
           class="inline-flex items-center gap-2 rounded-xl px-4 py-2 bg-white ring-1 ring-amber-900/10 text-amber-900/90 shadow hover:shadow-md">
          Cancel
        </a>
        <button type="submit"
                class="inline-flex items-center gap-2 rounded-xl px-5 py-2.5 text-white font-semibold
                       bg-gradient-to-r from-amber-600 to-rose-600 shadow hover:shadow-lg">
          Save Changes
        </button>
      </div>
    </form>
  </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
  const input = document.getElementById('fileInput');
  const preview = document.getElementById('preview');
  const dz = document.getElementById('dropzone');
  const removeBtn = document.getElementById('removeBtn');
  const removeFlag = document.getElementById('removeFlag');

  function validate(file){
    const okType = /^image\/(jpeg|png|webp)$/i.test(file.type);
    const okSize = file.size <= 2 * 1024 * 1024; // 2MB
    if (!okType) { alert('Please choose a JPG, PNG, or WEBP image.'); return false; }
    if (!okSize) { alert('Image must be 2MB or smaller.'); return false; }
    return true;
  }

  function showPreview(file){
    const reader = new FileReader();
    reader.onload = e => { preview.src = e.target.result; };
    reader.readAsDataURL(file);
    removeFlag.value = '0';
  }

  input.addEventListener('change', e => {
    const file = e.target.files?.[0];
    if (file && validate(file)) showPreview(file);
    else input.value = '';
  });

  // drag & drop
  ['dragenter','dragover'].forEach(ev =>
    dz.addEventListener(ev, e => { e.preventDefault(); dz.classList.add('dragover'); })
  );
  ['dragleave','drop'].forEach(ev =>
    dz.addEventListener(ev, e => { e.preventDefault(); dz.classList.remove('dragover'); })
  );
  dz.addEventListener('drop', e => {
    const file = e.dataTransfer.files?.[0];
    if (file && validate(file)) { showPreview(file); input.files = e.dataTransfer.files; }
  });

  // remove photo (sets flag for backend)
  removeBtn.addEventListener('click', () => {
    preview.src = "data:image/svg+xml;utf8," + encodeURIComponent(
      "<svg xmlns='http://www.w3.org/2000/svg' width='96' height='96'><rect width='100%' height='100%' rx='48' fill='#f1f5f9'/><text x='50%' y='52%' font-size='12' fill='#64748b' text-anchor='middle' font-family='sans-serif'>No Photo</text></svg>"
    );
    input.value = '';
    removeFlag.value = '1';
  });
})();
</script>
@endpush
