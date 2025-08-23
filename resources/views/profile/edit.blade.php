@extends(auth()->user()?->is_admin ? 'layouts.admin' : 'layouts.app')

@section('title', 'My Profile')

@push('styles')
  <style>
    :root {
      --surface: #ffffff;
      --surface-muted: #f3f4f6;
      --text: #1f2937;
      --text-secondary: #4b5563;
      --muted: #6b7280;
      --ring: #d1d5db;
      --ring-focus: #f59e0b;
      --accent: #f59e0b;
      --success-50: #ecfdf5;
      --success-100: #d1fae5;
      --success-700: #047857;
      --error-50: #fef2f2;
      --error-100: #fee2e2;
      --error-700: #b91c1c;
      --shadow: 0 1px 3px rgba(0,0,0,0.1), 0 1px 2px rgba(0,0,0,0.06);
      --shadow-sm: 0 1px 2px rgba(0,0,0,0.05);
      --shadow-md: 0 4px 6px rgba(0,0,0,0.1);
      --shadow-lg: 0 10px 15px rgba(0,0,0,0.1);
      --shadow-xl: 0 20px 25px rgba(0,0,0,0.1);
      --radius-xl: 0.75rem;
      --radius-2xl: 1rem;
      --space-4: 1rem;
      --space-6: 1.5rem;
      --space-8: 2rem;
      --text-sm: 0.875rem;
      --text-base: 1rem;
      --text-xl: 1.25rem;
      --duration-fast: 0.2s;
      --duration-normal: 0.3s;
      --ease-in-out: cubic-bezier(0.4, 0, 0.2, 1);
    }

    .dark {
      --surface: #1f2937;
      --surface-muted: #374151;
      --text: #f3f4f6;
      --text-secondary: #d1d5db;
      --muted: #9ca3af;
      --ring: #4b5563;
      --ring-focus: #fbbf24;
      --accent: #fbbf24;
      --success-50: rgba(16,185,129,0.15);
      --success-100: #10b981;
      --success-700: #6ee7b7;
      --error-50: rgba(254,226,226,0.15);
      --error-100: #f87171;
      --error-700: #fecaca;
    }

    /* Soft blobs (background accents) */
    .blob {
      position: absolute;
      border-radius: 9999px;
      filter: blur(36px);
      opacity: 0.2;
      pointer-events: none;
    }

    /* Glassy card */
    .cd-card {
      position: relative;
      background: var(--surface);
      color: var(--text);
      border: 1px solid var(--ring);
      border-radius: var(--radius-2xl);
      padding: var(--space-6);
      backdrop-filter: blur(10px);
      box-shadow: var(--shadow-lg);
      transition: transform var(--duration-fast) var(--ease-in-out),
                  box-shadow var(--duration-fast) var(--ease-in-out),
                  border-color var(--duration-fast) var(--ease-in-out);
    }
    .cd-card::before {
      content: "";
      position: absolute;
      inset: 0;
      pointer-events: none;
      border-radius: inherit;
      background: radial-gradient(1200px 400px at -10% -10%, rgba(251,191,36,0.12), transparent 40%),
                  radial-gradient(1000px 300px at 110% 110%, rgba(244,63,94,0.10), transparent 45%);
    }
    .cd-card:hover {
      transform: translateY(-2px);
      box-shadow: var(--shadow-xl);
      border-color: var(--accent);
    }

    /* Inputs */
    .cd-input, .cd-input[type="text"], .cd-input[type="email"], .cd-input[type="password"],
    .cd-input[type="file"], .cd-input select, .cd-input textarea {
      width: 100%;
      background: var(--surface);
      color: var(--text);
      border: 1px solid var(--ring);
      border-radius: var(--radius-xl);
      padding: 0.75rem 0.875rem;
      font-size: var(--text-base);
      box-shadow: inset 0 1px 0 rgba(255,255,255,0.65), inset 0 0 0 1px rgba(0,0,0,0.04), 0 1px 2px rgba(0,0,0,0.04);
      transition: box-shadow var(--duration-normal) var(--ease-in-out), border-color var(--duration-normal) var(--ease-in-out);
    }
    .cd-input::placeholder {
      color: var(--muted);
    }
    .cd-input:focus {
      outline: none;
      border-color: var(--ring-focus);
      box-shadow: 0 0 0 4px rgba(245,158,11,0.18), inset 0 1px 0 rgba(255,255,255,0.65), 0 1px 2px rgba(0,0,0,0.06);
    }
    .cd-label {
      display: block;
      font-size: var(--text-sm);
      font-weight: 600;
      color: var(--text);
      margin-bottom: 0.375rem;
    }

    /* Buttons */
    .btn-primary {
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      font-weight: 700;
      color: #fff;
      padding: 0.75rem 1.25rem;
      border-radius: var(--radius-xl);
      border: 0;
      background: linear-gradient(135deg, var(--accent), #f97316);
      box-shadow: var(--shadow-sm);
      transition: transform var(--duration-fast) var(--ease-in-out),
                  filter var(--duration-normal) var(--ease-in-out),
                  box-shadow var(--duration-normal) var(--ease-in-out);
    }
    .btn-primary:hover {
      filter: brightness(1.05);
      box-shadow: var(--shadow-md);
      transform: translateY(-1px);
    }
    .btn-link {
      color: var(--accent);
      font-weight: 600;
    }
    .btn-link:hover {
      text-decoration: underline;
      color: var(--ring-focus);
    }

    /* Alerts */
    .alert {
      border-radius: var(--radius-xl);
      padding: 0.75rem 1rem;
      font-weight: 600;
      border: 1px solid;
      box-shadow: var(--shadow-sm);
      backdrop-filter: blur(6px);
    }
    .alert-success {
      background: var(--success-50);
      color: var(--success-700);
      border-color: var(--success-100);
    }

    /* Avatar */
    .avatar {
      width: 6rem;
      height: 6rem;
      border-radius: 9999px;
      object-fit: cover;
      border: 4px solid var(--accent);
      box-shadow: var(--shadow);
    }
    .avatar-empty {
      width: 6rem;
      height: 6rem;
      border-radius: 9999px;
      border: 4px solid var(--ring);
      background: var(--surface-muted);
      color: var(--muted);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 0.75rem;
      font-weight: 700;
      box-shadow: var(--shadow);
    }
    .edit-pill {
      position: absolute;
      bottom: -2px;
      right: -2px;
      background: linear-gradient(135deg, var(--accent), #f97316);
      color: #fff;
      font-size: 0.75rem;
      border-radius: 9999px;
      padding: 0.25rem 0.5rem;
      cursor: pointer;
      box-shadow: var(--shadow);
    }

    /* Section header */
    .section-title {
      font-size: var(--text-xl);
      font-weight: 800;
      color: var(--text);
    }

    /* Container */
    .container {
      max-width: 48rem;
      margin: 0 auto;
      padding: var(--space-4);
    }
    @media (min-width: 768px) {
      .container {
        padding: var(--space-8);
      }
    }
  </style>
@endpush

@section('content')
<div class="relative grainy min-h-screen">
  <!-- Soft blobs using your palette -->
  <div class="blob -top-20 -right-24 h-80 w-80" style="background: linear-gradient(135deg, var(--accent), #fb7185)"></div>
  <div class="blob -bottom-24 -left-24 h-96 w-96" style="background: linear-gradient(135deg, var(--accent), #f472b6)"></div>

  <div class="container relative" style="z-index: 1">
    <header class="mb-8">
      <h2 class="section-title bg-clip-text text-transparent"
          style="background-image: linear-gradient(90deg, #92400e, #c2410c, #be123c)">👤 My Profile</h2>
      <p class="text-sm text-muted">Manage your account information and keep things up to date.</p>
    </header>

    @if(session('success') || session('status'))
      <div class="alert alert-success mb-6">
        {{ session('success') ?? session('status') }}
      </div>
    @endif

    {{-- Profile Summary (live-updating) --}}
    <div class="cd-card mb-8">
      <h3 class="section-title mb-4">👁️ Your Details</h3>
      <ul class="space-y-2 text-secondary">
        <li><strong class="font-semibold">Name:</strong> <span id="details-name">{{ $user->name }}</span></li>
        <li><strong class="font-semibold">Email:</strong> <span id="details-email">{{ $user->email }}</span></li>
        <li><strong class="font-semibold">Phone:</strong> <span id="details-phone">{{ $user->phone ?? 'Not added yet' }}</span></li>
        <li><strong class="font-semibold">Address:</strong> <span id="details-address">{{ $user->address ?? 'Not added yet' }}</span></li>
        <li><strong class="font-semibold">Password:</strong> ••••••••</li>
      </ul>
    </div>

    <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="cd-card">
      @csrf
      @method('PATCH')

      {{-- Profile Picture --}}
      <div class="flex items-center mb-8 gap-6">
        <div class="relative">
          @if($user->profile_photo)
            <img id="preview" class="avatar"
                 src="{{ asset('storage/profile_photos/' . $user->profile_photo) }}"
                 alt="Profile Photo">
          @else
            <div id="preview-container" class="avatar-empty">Profile Pic</div>
          @endif
          <label for="inp-photo" class="edit-pill">
            <input id="inp-photo" type="file" name="profile_photo" class="sr-only" accept="image/jpeg,image/png" onchange="previewImage(event)">
            Edit
          </label>
        </div>
        <div class="text-sm text-muted">JPG or PNG, max 2MB</div>
      </div>

      {{-- Profile Info --}}
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <div>
          <label class="cd-label" for="inp-name">Name</label>
          <input id="inp-name" type="text" name="name" value="{{ old('name', $user->name) }}"
                 class="cd-input @error('name') border-error-100 @enderror" required>
          @error('name')
            <p class="text-sm text-error-700 mt-1">{{ $message }}</p>
          @enderror
        </div>

        <div>
          <label class="cd-label" for="inp-email">Email</label>
          <input id="inp-email" type="email" name="email" value="{{ old('email', $user->email) }}"
                 class="cd-input @error('email') border-error-100 @enderror" required>
          @error('email')
            <p class="text-sm text-error-700 mt-1">{{ $message }}</p>
          @enderror
        </div>

        <div>
          <label class="cd-label" for="inp-phone">Phone</label>
          <input id="inp-phone" type="tel" name="phone" value="{{ old('phone', $user->phone) }}"
                 class="cd-input @error('phone') border-error-100 @enderror">
          @error('phone')
            <p class="text-sm text-error-700 mt-1">{{ $message }}</p>
          @enderror
        </div>

        <div>
          <label class="cd-label" for="inp-address">Address</label>
          <input id="inp-address" type="text" name="address" value="{{ old('address', $user->address) }}"
                 class="cd-input @error('address') border-error-100 @enderror">
          @error('address')
            <p class="text-sm text-error-700 mt-1">{{ $message }}</p>
          @enderror
        </div>

        <div>
          <label class="cd-label" for="inp-password">New Password</label>
          <input id="inp-password" type="password" name="password"
                 class="cd-input @error('password') border-error-100 @enderror">
          @error('password')
            <p class="text-sm text-error-700 mt-1">{{ $message }}</p>
          @enderror
        </div>

        <div>
          <label class="cd-label" for="inp-password-confirm">Confirm Password</label>
          <input id="inp-password-confirm" type="password" name="password_confirmation"
                 class="cd-input @error('password_confirmation') border-error-100 @enderror">
          @error('password_confirmation')
            <p class="text-sm text-error-700 mt-1">{{ $message }}</p>
          @enderror
        </div>
      </div>

      {{-- Validation errors (consolidated) --}}
      @if ($errors->any())
        <div class="alert mb-6" style="border-color: var(--error-100); background: var(--error-50); color: var(--error-700);">
          <div class="font-semibold mb-1">Please fix the following:</div>
          <ul class="list-disc list-inside text-sm">
            @foreach ($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      <div class="flex items-center justify-between">
        <a href="{{ route('reports.index') }}" class="btn-link">← Back to All Reports</a>
        <button type="submit" class="btn-primary">Save Changes</button>
      </div>
    </form>
  </div>
</div>

<script>
  // Image preview
  function previewImage(event) {
    const file = event.target.files[0];
    if (!file) return;

    // Validate file type and size
    const validTypes = ['image/jpeg', 'image/png'];
    if (!validTypes.includes(file.type)) {
      alert('Please upload a JPG or PNG image.');
      event.target.value = '';
      return;
    }
    if (file.size > 2 * 1024 * 1024) {
      alert('File size must be less than 2MB.');
      event.target.value = '';
      return;
    }

    const reader = new FileReader();
    reader.onload = function() {
      let preview = document.getElementById('preview');
      let container = document.getElementById('preview-container');

      if (!preview && container) {
        container.innerHTML = '';
        preview = document.createElement('img');
        preview.id = 'preview';
        preview.className = 'avatar';
        container.appendChild(preview);
      }
      if (preview) preview.src = reader.result;
    };
    reader.readAsDataURL(file);
  }

  // Live-sync "Your Details" with form inputs
  (function() {
    const byId = id => document.getElementById(id);

    const inputs = {
      name: byId('inp-name'),
      email: byId('inp-email'),
      phone: byId('inp-phone'),
      address: byId('inp-address')
    };

    const displays = {
      name: byId('details-name'),
      email: byId('details-email'),
      phone: byId('details-phone'),
      address: byId('details-address')
    };

    const fallbacks = {
      phone: 'Not added yet',
      address: 'Not added yet'
    };

    function setOrDefault(node, value, fallback = '') {
      if (!node) return;
      const v = (value || '').trim();
      node.textContent = v.length ? v : fallback;
    }

    Object.keys(inputs).forEach(key => {
      const input = inputs[key];
      const target = displays[key];
      if (!input || !target) return;

      const update = () => setOrDefault(target, input.value, fallbacks[key]);
      input.addEventListener('input', update);
      input.addEventListener('change', update);
      update();
    });
  })();
</script>
@endsection