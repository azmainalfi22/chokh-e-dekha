@extends('layouts.admin')

@section('content')
<div class="bg-[#f9f7f3] min-h-screen py-10">
    <div class="max-w-3xl mx-auto bg-white p-8 rounded-2xl shadow-2xl relative">
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-indigo-900">Admin Profile</h1>
            <p class="text-gray-600 mt-1">Update your details. Changes apply only to your admin account.</p>
        </div>

        @if(session('success'))
            <div class="mb-6 p-4 bg-green-100 text-green-800 rounded-lg font-semibold shadow-sm">
                {{ session('success') }}
            </div>
        @endif

        <form method="POST" action="{{ route('admin.profile.update') }}" enctype="multipart/form-data">
            @csrf
            @method('PATCH')

            <div class="flex items-center gap-6 mb-8">
                <div class="relative w-24 h-24">
                    @php $photo = $admin->profile_photo ?? null; @endphp

                    @if($photo)
                        <img id="preview"
                             class="w-24 h-24 rounded-full object-cover border-4 border-indigo-500 shadow"
                             src="{{ asset('storage/profile_photos/'.$photo) }}"
                             alt="Profile Photo">
                    @else
                        <div id="preview-container"
                             class="w-24 h-24 rounded-full border-4 border-gray-300 bg-gray-100 text-gray-500 flex items-center justify-center text-xs font-semibold shadow">
                            Profile
                        </div>
                    @endif

                    <label class="absolute bottom-0 right-0 bg-indigo-600 hover:bg-indigo-700 text-white text-xs rounded-full px-2 py-1 cursor-pointer">
                        <input type="file" name="profile_photo" class="hidden" onchange="previewImage(event)">
                        Edit
                    </label>
                </div>
                <div class="text-gray-500 text-sm">
                    JPG/PNG/WEBP, up to 2MB.
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Name</label>
                    <input type="text" name="name" value="{{ old('name', $admin->name) }}"
                           class="w-full border p-3 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
                    @error('name') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Email</label>
                    <input type="email" name="email" value="{{ old('email', $admin->email) }}"
                           class="w-full border p-3 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
                    @error('email') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">New Password</label>
                    <input type="password" name="password"
                           class="w-full border p-3 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    @error('password') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Confirm Password</label>
                    <input type="password" name="password_confirmation"
                           class="w-full border p-3 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>

            <div class="text-right mt-8">
                <button type="submit"
                        class="bg-indigo-700 hover:bg-indigo-800 text-white font-semibold px-6 py-3 rounded-lg shadow-md transition">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function previewImage(event) {
    const reader = new FileReader();
    reader.onload = function(){
        let preview = document.getElementById('preview');
        let container = document.getElementById('preview-container');

        if (!preview && container) {
            container.innerHTML = '';
            preview = document.createElement('img');
            preview.id = 'preview';
            preview.className = 'w-24 h-24 rounded-full object-cover border-4 border-indigo-500 shadow';
            container.appendChild(preview);
        }
        if (preview) preview.src = reader.result;
    };
    reader.readAsDataURL(event.target.files[0]);
}
</script>
@endsection
