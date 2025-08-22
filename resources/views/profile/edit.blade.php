@extends(auth()->user()?->is_admin ? 'layouts.admin' : 'layouts.app')


@section('title','My Profile')

@push('styles')
  <style>
    /* Soft blobs (background accents) */
    .blob{ position:absolute; border-radius:9999px; filter:blur(36px); opacity:.2; pointer-events:none; }

    /* Glassy card */
    .cd-card{
      position:relative; background: var(--surface); color: var(--text);
      border:1px solid var(--ring); border-radius: var(--radius-2xl); padding: var(--space-6);
      backdrop-filter: blur(10px); box-shadow: var(--shadow-lg);
      transition: transform var(--duration-fast) var(--ease-in-out),
                  box-shadow var(--duration-fast) var(--ease-in-out),
                  border-color var(--duration-fast) var(--ease-in-out);
    }
    .cd-card::before{
      content:""; position:absolute; inset:0; pointer-events:none; border-radius:inherit;
      background:
        radial-gradient(1200px 400px at -10% -10%, rgba(251,191,36,.12), transparent 40%),
        radial-gradient(1000px 300px at 110% 110%, rgba(244,63,94,.10), transparent 45%);
    }
    .cd-card:hover{ transform: translateY(-2px); box-shadow: var(--shadow-xl); border-color: var(--accent); }

    /* Inputs */
    .cd-input, .cd-input[type="text"], .cd-input[type="email"], .cd-input[type="password"],
    .cd-input[type="file"], .cd-input select, .cd-input textarea{
      width:100%; background:#fff; color:var(--text);
      border:1px solid var(--ring); border-radius: var(--radius-xl); padding: 0.75rem 0.875rem; font-size: var(--text-base);
      box-shadow: inset 0 1px 0 rgba(255,255,255,.65), inset 0 0 0 1px rgba(0,0,0,.04), 0 1px 2px rgba(0,0,0,.04);
      transition: box-shadow var(--duration-normal) var(--ease-in-out), border-color var(--duration-normal) var(--ease-in-out);
    }
    .cd-input::placeholder{ color:#6b7280; }
    .cd-input:focus{
      outline:none; border-color: var(--ring-focus);
      box-shadow: 0 0 0 4px rgba(245,158,11,.18), inset 0 1px 0 rgba(255,255,255,.65), 0 1px 2px rgba(0,0,0,.06);
    }
    .cd-label{ display:block; font-size: var(--text-sm); font-weight:600; color:var(--text); margin-bottom:.375rem; }

    /* Buttons */
    .btn-primary{
      display:inline-flex; align-items:center; gap:.5rem; font-weight:700;
      color:#fff; padding:.75rem 1.25rem; border-radius: var(--radius-xl); border:0;
      background: linear-gradient(135deg, var(--accent), #f97316);
      box-shadow: var(--shadow-sm); transition: transform var(--duration-fast) var(--ease-in-out),
                                      filter var(--duration-normal) var(--ease-in-out),
                                      box-shadow var(--duration-normal) var(--ease-in-out);
    }
    .btn-primary:hover{ filter:brightness(1.05); box-shadow: var(--shadow-md); transform: translateY(-1px); }
    .btn-link{ color: var(--link); font-weight:600; }
    .btn-link:hover{ text-decoration: underline; color: var(--accent); }

    /* Alerts */
    .alert{
      border-radius: var(--radius-xl); padding: .75rem 1rem; font-weight:600;
      border:1px solid; box-shadow: var(--shadow-sm); backdrop-filter: blur(6px);
    }
    .alert-success{ background: var(--success-50); color: var(--success-700); border-color: var(--success-100); }
    .dark .alert-success{ background: rgba(16,185,129,.15); color:#6ee7b7; border-color:#10b981; }

    /* Avatar */
    .avatar{ width:6rem; height:6rem; border-radius:9999px; object-fit:cover; border:4px solid var(--accent); box-shadow: var(--shadow); }
    .avatar-empty{
      width:6rem; height:6rem; border-radius:9999px; border:4px solid var(--ring); background: var(--surface-muted);
      color: var(--muted); display:flex; align-items:center; justify-content:center; font-size:.75rem; font-weight:700; box-shadow: var(--shadow);
    }
    .edit-pill{
      position:absolute; bottom:-2px; right:-2px; background: linear-gradient(135deg, var(--accent), #f97316);
      color:#fff; font-size:.75rem; border-radius:9999px; padding:.25rem .5rem; cursor:pointer; box-shadow: var(--shadow);
    }

    /* Section header */
    .section-title{ font-size: var(--text-xl); font-weight:800; color:var(--text); }

    /* Container */
    .container{ max-width: 48rem; margin: 0 auto; padding: var(--space-4); }
    @media (min-width:768px){ .container{ padding: var(--space-8); } }
  </style>
@endpush

@section('content')
<div class="relative grainy min-h-screen">
  <!-- soft blobs using your palette -->
  <div class="blob -top-20 -right-24 h-80 w-80 surface-elevated" style="background:linear-gradient(135deg,#fbbf24,#fb7185)"></div>
  <div class="blob -bottom-24 -left-24 h-96 w-96 surface-elevated" style="background:linear-gradient(135deg,#fb923c,#f472b6)"></div>

  <div class="container relative" style="z-index:1">
    <header class="mb-8">
      <h2 class="text-3xl md:text-4xl font-black bg-clip-text text-transparent"
          style="background-image:linear-gradient(90deg,#92400e,#c2410c,#be123c)">👤 My Profile</h2>
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
        <li><strong class="text-primary">Name:</strong> <span id="details-name">{{ $user->name }}</span></li>
        <li><strong class="text-primary">Email:</strong> <span id="details-email">{{ $user->email }}</span></li>
        <li><strong class="text-primary">Phone:</strong> <span id="details-phone">{{ $user->phone ?? 'Not added yet' }}</span></li>
        <li><strong class="text-primary">Address:</strong> <span id="details-address">{{ $user->address ?? 'Not added yet' }}</span></li>
        <li><strong class="text-primary">Password:</strong> •••••••• (hidden)</li>
      </ul>
    </div>

    <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="cd-card">
      @csrf
      @method('PATCH')

      {{-- Profile Picture --}}
      <div class="flex items-center mb-8 gap-6">
        <div class="relative" style="width:6rem; height:6rem;">
          @if($user->profile_photo)
            <img id="preview" class="avatar"
                 src="{{ asset('storage/profile_photos/' . $user->profile_photo) }}" alt="Profile Photo">
          @else
            <div id="preview-container" class="avatar-empty">Profile Pic</div>
          @endif
          <label class="edit-pill">
            <input id="inp-photo" type="file" name="profile_photo" class="sr-only" onchange="previewImage(event)">
            Edit
          </label>
        </div>
        <div class="text-sm text-muted">JPG or PNG. Max 2MB.</div>
      </div>

      {{-- Profile Info (IDs added for live sync) --}}
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <div>
          <label class="cd-label" for="inp-name">Name</label>
          <input id="inp-name" type="text" name="name" value="{{ old('name', $user->name) }}" class="cd-input" required>
        </div>

        <div>
          <label class="cd-label" for="inp-email">Email</label>
          <input id="inp-email" type="email" name="email" value="{{ old('email', $user->email) }}" class="cd-input" required>
        </div>

        <div>
          <label class="cd-label" for="inp-phone">Phone</label>
          <input id="inp-phone" type="text" name="phone" value="{{ old('phone', $user->phone) }}" class="cd-input">
        </div>

        <div>
          <label class="cd-label" for="inp-address">Address</label>
          <input id="inp-address" type="text" name="address" value="{{ old('address', $user->address) }}" class="cd-input">
        </div>

        <div>
          <label class="cd-label" for="inp-password">New Password</label>
          <input id="inp-password" type="password" name="password" class="cd-input">
        </div>

        <div>
          <label class="cd-label" for="inp-password-confirm">Confirm Password</label>
          <input id="inp-password-confirm" type="password" name="password_confirmation" class="cd-input">
        </div>
      </div>

      {{-- Validation errors --}}
      @if ($errors->any())
        <div class="mb-6" style="border:1px solid var(--error-100); background:var(--error-50); color:var(--error-700); border-radius:var(--radius-xl); padding:.75rem 1rem; box-shadow:var(--shadow-sm);">
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
    const reader = new FileReader();
    reader.onload = function(){
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
    if (event.target.files && event.target.files[0]) {
      reader.readAsDataURL(event.target.files[0]);
    }
  }

  // Live-sync "Your Details" with form inputs
  (function(){
    const byId = (id) => document.getElementById(id);

    const $name    = byId('inp-name');
    const $email   = byId('inp-email');
    const $phone   = byId('inp-phone');
    const $address = byId('inp-address');

    const dName    = byId('details-name');
    const dEmail   = byId('details-email');
    const dPhone   = byId('details-phone');
    const dAddress = byId('details-address');

    const EMPTY_PHONE   = 'Not added yet';
    const EMPTY_ADDRESS = 'Not added yet';

    function setOrDefault(node, value, fallback){
      if (!node) return;
      const v = (value || '').trim();
      node.textContent = v.length ? v : fallback;
    }

    function wire(input, target, fallback){
      if (!input || !target) return;
      const update = () => setOrDefault(target, input.value, fallback);
      input.addEventListener('input', update);
      input.addEventListener('change', update);
      update(); // initialize on load (covers old() values)
    }

    wire($name,    dName,    '');
    wire($email,   dEmail,   '');
    wire($phone,   dPhone,   EMPTY_PHONE);
    wire($address, dAddress, EMPTY_ADDRESS);
  })();
</script>
@endsection
