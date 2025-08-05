<x-app-layout>
    <div class="max-w-3xl mx-auto px-4 py-10">
        <div class="bg-white shadow-xl rounded-lg p-8 border border-gray-200">
            <h2 class="text-2xl font-bold text-center text-indigo-600 mb-6">📢 Submit a City Issue Report</h2>

            @if ($errors->any())
                <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    <strong>Whoops! Something went wrong.</strong>
                    <ul class="mt-2 list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>• {{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('report.store') }}" enctype="multipart/form-data">
                @csrf

                <!-- Title -->
                <div class="mb-4">
                    <label class="block font-medium text-gray-700">📝 Title</label>
                    <input type="text" name="title" required
                        class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <!-- City Corporation -->
                <div class="mb-4">
                    <label class="block font-medium text-gray-700">🏙️ City Corporation</label>
                    <select name="city_corporation" required
                        class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">-- Select a city --</option>
                        @php
                            $cities = [
                                'Dhaka North City Corporation',
                                'Dhaka South City Corporation',
                                'Chittagong City Corporation',
                                'Rajshahi City Corporation',
                                'Khulna City Corporation',
                                'Sylhet City Corporation',
                                'Barisal City Corporation',
                                'Rangpur City Corporation',
                                'Mymensingh City Corporation',
                                'Narayanganj City Corporation',
                                'Comilla City Corporation',
                                'Bogura City Corporation',
                            ];
                        @endphp
                        @foreach ($cities as $city)
                            <option value="{{ $city }}">{{ $city }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Description -->
                <div class="mb-4">
                    <label class="block font-medium text-gray-700">🗒️ Description</label>
                    <textarea name="description" rows="4" required
                        class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                </div>

                <!-- Category -->
                <div class="mb-4">
                    <label class="block font-medium text-gray-700">📂 Category</label>
                    <select name="category" required
                        class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="Garbage">Garbage</option>
                        <option value="Broken Road">Broken Road</option>
                        <option value="Drainage">Drainage</option>
                        <option value="Electricity">Electricity</option>
                    </select>
                </div>

                <!-- Location -->
                <div class="mb-4">
                    <label class="block font-medium text-gray-700">📍 Location (optional)</label>
                    <input type="text" name="location"
                        class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <!-- Photo -->
                <div class="mb-4">
                    <label class="block font-medium text-gray-700">📸 Upload Photo</label>
                    <input type="file" name="photo"
                        class="mt-1 block w-full text-gray-700 border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 rounded-md shadow-sm">
                </div>

                <!-- Submit Button -->
                <div class="flex justify-center mt-6">
                    <button type="submit"
                        class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-6 rounded shadow">
                        ✅ Submit Report
                    </button>
                </div>
            </form>

            <div class="text-center mt-6">
                <a href="{{ route('home') }}"
                    class="text-indigo-600 hover:text-indigo-800 underline text-sm">← Back to All Reports</a>
            </div>
        </div>
    </div>
</x-app-layout>
