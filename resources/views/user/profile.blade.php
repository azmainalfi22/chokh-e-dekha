@extends('layouts.app')

@section('content')
<div class="bg-[#fdfaf6] min-h-screen py-12">
    <div class="max-w-2xl mx-auto bg-white p-10 rounded-2xl shadow-xl relative">
        <h2 class="text-3xl font-bold text-indigo-700 mb-8">👤 My Profile</h2>

        @if(session('success') || session('status'))
            <div class="mb-6 p-4 bg-green-100 text-green-800 rounded-lg font-semibold shadow-sm">
                {{ session('success') ?? session('status') }}
            </div>
        @endif

        {{-- Profile Summary --}}
        <div class="mb-10 bg-indigo-50 p-6 rounded-lg shadow">
            <h3 class="text-xl font-semibold text-indigo-800 mb-4">👁️ Your Details</h3>
            <ul class="text-gray-700 space-y-2">
                <li><strong>Name:</strong> {{ $user->name }}</li>
                <li><strong>Email:</strong> {{ $user->email }}</li>
                <li><strong>Phone:</strong> {{ $user->phone ?? 'Not added yet' }}</li>
                <li><strong>Address:</strong> {{ $user->address ?? 'Not added yet' }}</li>
                <li><strong>Password:</strong> •••••••• (hidden)</li>
            </ul>
        </div>

        <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
            @csrf
            @method('PATCH')

            {{-- Profile Picture --}}
            <div class="flex items-center mb-8 gap-6">
                <div class="relative w-24 h-24">
                    @if($user->profile_photo)
                        <img id="preview" class="w-24 h-24 rounded-full object-cover border-4 border-indigo-500 shadow"
                             src="{{ asset('storage/profile_photos/' . $user->profile_photo) }}" alt="Profile Photo">
                    @else
                        <div id="preview-container" class="w-24 h-24 rounded-full border-4 border-gray-300 bg-gray-100 text-gray-500 flex items-center justify-center text-xs font-semibold shadow">
                            Profile Pic
                        </div>
                    @endif

                    <label class="absolute bottom-0 right-0 bg-indigo-600 hover:bg-indigo-700 text-white text-xs rounded-full px-2 py-1 cursor-pointer">
                        <input type="file" name="profile_photo" class="hidden" onchange="previewImage(event)">
                        Edit
                    </label>
                </div>
                <div class="text-gray-600 text-sm">
                    JPG or PNG. Max 2MB.
                </div>
            </div>

            {{-- Profile Info --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label class="block font-medium text-sm text-gray-700">Name</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}"
                           class="w-full border p-3 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500 shadow-sm" required>
                </div>

                <div>
                    <label class="block font-medium text-sm text-gray-700">Email</label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}"
                           class="w-full border p-3 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500 shadow-sm" required>
                </div>

                <div>
                    <label class="block font-medium text-sm text-gray-700">Phone</label>
                    <input type="text" name="phone" value="{{ old('phone', $user->phone) }}"
                           class="w-full border p-3 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500 shadow-sm">
                </div>

                <div>
                    <label class="block font-medium text-sm text-gray-700">Address</label>
                    <input type="text" name="address" value="{{ old('address', $user->address) }}"
                           class="w-full border p-3 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500 shadow-sm">
                </div>

                <div>
                    <label class="block font-medium text-sm text-gray-700">New Password</label>
                    <input type="password" name="password"
                           class="w-full border p-3 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500 shadow-sm">
                </div>

                <div>
                    <label class="block font-medium text-sm text-gray-700">Confirm Password</label>
                    <input type="password" name="password_confirmation"
                           class="w-full border p-3 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500 shadow-sm">
                </div>
            </div>

            <div class="text-right">
                <button type="submit"
                    class="bg-indigo-700 hover:bg-indigo-800 text-white font-semibold px-6 py-3 rounded-lg shadow transition duration-200">
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

            if (preview) {
                preview.src = reader.result;
            }
        };
        reader.readAsDataURL(event.target.files[0]);
    }
</script>
@endsection
