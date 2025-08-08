@extends(auth()->user()->is_admin ? 'layouts.admin' : 'layouts.app')

@section('title', 'Submit a Report')

@section('content')
<div class="relative">
  <div class="pointer-events-none absolute -top-20 -right-24 h-80 w-80 rounded-full blur-3xl opacity-20 bg-gradient-to-br from-amber-300 to-rose-300"></div>
  <div class="pointer-events-none absolute -bottom-24 -left-24 h-96 w-96 rounded-full blur-3xl opacity-20 bg-gradient-to-tr from-orange-300 to-pink-300"></div>

  <div class="max-w-4xl mx-auto p-4 md:p-8 relative">
    <header class="mb-8">
      <h1 class="text-3xl font-extrabold bg-clip-text text-transparent bg-gradient-to-r from-amber-700 via-orange-700 to-rose-700">Submit a City Issue</h1>
      <p class="text-sm text-amber-900/70">Describe the problem clearly so authorities can act fast.</p>
    </header>

    @if ($errors->any())
      <div class="mb-6 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-rose-800 shadow-sm">
        <div class="font-semibold mb-1">Please fix the following:</div>
        <ul class="list-disc list-inside text-sm">
          @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <form action="{{ route('report.store') }}" method="POST" enctype="multipart/form-data" class="bg-white/80 backdrop-blur rounded-2xl shadow p-6 space-y-5 ring-1 ring-amber-100">
      @csrf

      <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        {{-- Title --}}
        <div>
          <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Title <span class="text-rose-600">*</span></label>
          <input id="title" type="text" name="title" value="{{ old('title') }}" required class="w-full rounded-xl border-gray-300 focus:border-amber-500 focus:ring-amber-500">
          @error('title') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        {{-- Category (Dropdown) --}}
        <div>
          <label for="category" class="block text-sm font-medium text-gray-700 mb-1">Category <span class="text-rose-600">*</span></label>
          <select id="category" name="category" required class="w-full rounded-xl border-gray-300 focus:border-amber-500 focus:ring-amber-500">
            @php
              $cats = ['Road Damage','Waste Management','Street Light','Water Supply','Other'];
              $oldCat = old('category');
            @endphp
            <option value="">-- Select Category --</option>
            @foreach($cats as $c)
              <option value="{{ $c }}" @selected($oldCat === $c)>{{ $c }}</option>
            @endforeach
          </select>
          @error('category') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        {{-- City Corporation (Dropdown) --}}
        <div>
          <label for="city_corporation" class="block text-sm font-medium text-gray-700 mb-1">City Corporation <span class="text-rose-600">*</span></label>
          <select name="city_corporation" id="city_corporation" class="w-full rounded-xl border-gray-300 focus:border-amber-500 focus:ring-amber-500" required>
              @php $cc = old('city_corporation'); @endphp
              <option value="">-- Select a City Corporation --</option>
              <option value="Dhaka North"      @selected($cc=='Dhaka North')>Dhaka North City Corporation</option>
              <option value="Dhaka South"      @selected($cc=='Dhaka South')>Dhaka South City Corporation</option>
              <option value="Chattogram"       @selected($cc=='Chattogram')>Chattogram City Corporation</option>
              <option value="Gazipur"          @selected($cc=='Gazipur')>Gazipur City Corporation</option>
              <option value="Khulna"           @selected($cc=='Khulna')>Khulna City Corporation</option>
              <option value="Rajshahi"         @selected($cc=='Rajshahi')>Rajshahi City Corporation</option>
              <option value="Sylhet"           @selected($cc=='Sylhet')>Sylhet City Corporation</option>
              <option value="Barishal"         @selected($cc=='Barishal')>Barishal City Corporation</option>
              <option value="Cumilla"          @selected($cc=='Cumilla')>Cumilla City Corporation</option>
              <option value="Narayanganj"      @selected($cc=='Narayanganj')>Narayanganj City Corporation</option>
              <option value="Mymensingh"       @selected($cc=='Mymensingh')>Mymensingh City Corporation</option>
          </select>
          @error('city_corporation') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        {{-- Location (REQUIRED) --}}
        <div>
          <label for="location" class="block text-sm font-medium text-gray-700 mb-1">Location <span class="text-rose-600">*</span></label>
          <input id="location" type="text" name="location" value="{{ old('location') }}" required class="w-full rounded-xl border-gray-300 focus:border-amber-500 focus:ring-amber-500" placeholder="Road name, area...">
          @error('location') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
      </div>

      {{-- Description --}}
      <div>
        <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description <span class="text-rose-600">*</span></label>
        <textarea id="description" name="description" rows="5" class="w-full rounded-xl border-gray-300 focus:border-amber-500 focus:ring-amber-500" placeholder="Explain what’s wrong, since when, and any ID/landmark...">{{ old('description') }}</textarea>
        @error('description') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
      </div>

      {{-- Photo Upload --}}
      <div>
        <label for="photo" class="block text-sm font-medium text-gray-700 mb-2">Attach Photo (optional)</label>
        <input id="photo" type="file" name="photo" accept="image/*" class="block w-full text-sm text-gray-700 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:bg-amber-600 file:text-white hover:file:bg-amber-700">
        @error('photo') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
      </div>

      <div class="flex items-center justify-between">
        <a href="{{ route('reports.index') }}" class="text-gray-600 hover:text-gray-800">← Back to All Reports</a>
        <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-amber-600 text-white hover:bg-amber-700 shadow">
          <span>Submit</span> <span>🚀</span>
        </button>
      </div>
    </form>
  </div>
</div>
@endsection
