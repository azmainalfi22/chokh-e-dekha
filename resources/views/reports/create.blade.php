@extends(auth()->user()->is_admin ? 'layouts.admin' : 'layouts.app')

@section('title', 'Submit a Report - চোখে দেখা')

@push('styles')
<style>
  :root {
    --cd-primary: #f59e0b;
    --cd-secondary: #ef4444;
    --cd-success: #10b981;
    --cd-card: rgba(255,255,255,0.95);
    --cd-glass: rgba(255,255,255,0.85);
    --cd-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);
  }

  /* Dark mode variables */
  .dark {
    --cd-card: rgba(30, 41, 59, 0.95);
    --cd-glass: rgba(30, 41, 59, 0.85);
    --cd-shadow: 0 25px 50px -12px rgba(0,0,0,0.5);
  }

  body {
    background: linear-gradient(135deg, #fef3c7 0%, #f3e193 50%, #fcd34d 100%);
    min-height: 100vh;
  }

  .dark body {
    background: linear-gradient(135deg, #1e293b 0%, #334155 50%, #475569 100%);
  }

  .glass-card {
    background: var(--cd-card);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
    box-shadow: var(--cd-shadow);
    transition: all 0.3s ease;
  }

  .dark .glass-card {
    border: 1px solid rgba(255, 255, 255, 0.1);
  }

  .step { 
    display: none; 
    animation: fadeIn 0.3s ease-in;
  }
  
  .step.active { 
    display: block; 
  }

  .drag-over {
    background: rgba(245, 158, 11, 0.1);
    border-color: var(--cd-primary);
    border-width: 2px;
  }

  .file-preview {
    position: relative;
    background: #f3f4f6;
    border-radius: 12px;
    overflow: hidden;
  }

  .dark .file-preview {
    background: #374151;
  }

  .file-preview img,
  .file-preview video {
    width: 100%;
    height: 128px;
    object-fit: cover;
  }

  .remove-file {
    position: absolute;
    top: 8px;
    right: 8px;
    width: 24px;
    height: 24px;
    background: rgba(239, 68, 68, 0.9);
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-size: 14px;
    font-weight: bold;
    transition: all 0.2s ease;
  }

  .remove-file:hover {
    background: #dc2626;
    transform: scale(1.1);
  }

  .progress-bar {
    transition: width 0.3s ease;
    background: linear-gradient(90deg, var(--cd-primary), #f97316);
  }

  .form-input {
    transition: all 0.2s ease;
    border: 1px solid #d1d5db;
    background: white;
    color: #111827;
  }

  .dark .form-input {
    background: #374151;
    border: 1px solid #4b5563;
    color: #f9fafb;
  }

  .form-input:focus {
    border-color: var(--cd-primary);
    box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.1);
    outline: none;
  }

  .form-input.error {
    border-color: var(--cd-secondary);
    background: rgba(239, 68, 68, 0.05);
  }

  .dark .form-input.error {
    background: rgba(239, 68, 68, 0.1);
  }

  .form-input option {
    background: white;
    color: #111827;
  }

  .dark .form-input option {
    background: #374151;
    color: #f9fafb;
  }

  .btn-primary {
    background: linear-gradient(135deg, var(--cd-primary), #f97316);
    color: white;
    font-weight: 600;
    transition: all 0.2s ease;
    box-shadow: 0 4px 14px rgba(245, 158, 11, 0.3);
  }

  .btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(245, 158, 11, 0.4);
  }

  .btn-primary:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
  }

  .toast {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 9999;
    padding: 16px 24px;
    border-radius: 12px;
    color: white;
    font-weight: 500;
    box-shadow: 0 10px 25px rgba(0,0,0,0.2);
    transform: translateX(400px);
    transition: transform 0.3s ease;
  }

  .toast.show { transform: translateX(0); }
  .toast.success { background: var(--cd-success); }
  .toast.error { background: var(--cd-secondary); }

  .location-marker {
    width: 40px;
    height: 40px;
    background: var(--cd-secondary);
    border-radius: 50% 50% 50% 0;
    transform: rotate(-45deg);
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 12px rgba(239, 68, 68, 0.4);
  }

  .location-marker::before {
    content: '';
    width: 16px;
    height: 16px;
    background: white;
    border-radius: 50%;
    transform: rotate(45deg);
  }

  /* Text colors for dark mode */
  .text-primary {
    color: #111827;
  }

  .dark .text-primary {
    color: #f9fafb;
  }

  .text-secondary {
    color: #6b7280;
  }

  .dark .text-secondary {
    color: #9ca3af;
  }

  .text-accent {
    color: #374151;
  }

  .dark .text-accent {
    color: #d1d5db;
  }

  @media (max-width: 768px) {
    .glass-card { margin: 16px; }
    .grid { grid-template-columns: 1fr; }
  }

  .loading-spinner {
    border: 2px solid #f3f4f6;
    border-top: 2px solid var(--cd-primary);
    border-radius: 50%;
    width: 20px;
    height: 20px;
    animation: spin 1s linear infinite;
    display: inline-block;
    margin-right: 8px;
  }

  @keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
  }

  .fade-in {
    animation: fadeIn 0.5s ease-in;
  }

  @keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
  }

  /* Map container improvements for dark mode */
  .map-container {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
  }

  .dark .map-container {
    background: #1e293b;
    border: 1px solid #475569;
  }

  .map-controls {
    background: rgba(255, 255, 255, 0.95);
    border: 1px solid #e2e8f0;
  }

  .dark .map-controls {
    background: rgba(30, 41, 59, 0.95);
    border: 1px solid #475569;
    color: #f8fafc;
  }

  .location-display {
    background: rgba(255, 255, 255, 0.98);
    border: 1px solid #e2e8f0;
    color: #374151;
  }

  .dark .location-display {
    background: rgba(30, 41, 59, 0.98);
    border: 1px solid #475569;
    color: #f8fafc;
  }

  /* Section backgrounds for better dark mode visibility */
  .section-bg-light {
    background: rgba(249, 250, 251, 0.5);
    border: 1px solid rgba(229, 231, 235, 0.5);
  }

  .dark .section-bg-light {
    background: rgba(55, 65, 81, 0.3);
    border: 1px solid rgba(75, 85, 99, 0.5);
  }

  .section-bg-blue {
    background: rgba(239, 246, 255, 0.3);
    border: 1px solid rgba(191, 219, 254, 0.5);
  }

  .dark .section-bg-blue {
    background: rgba(30, 58, 138, 0.1);
    border: 1px solid rgba(59, 130, 246, 0.3);
  }

  .section-bg-green {
    background: rgba(240, 253, 244, 0.3);
    border: 1px solid rgba(167, 243, 208, 0.5);
  }

  .dark .section-bg-green {
    background: rgba(20, 83, 45, 0.1);
    border: 1px solid rgba(34, 197, 94, 0.3);
  }

  .section-bg-purple {
    background: rgba(250, 245, 255, 0.3);
    border: 1px solid rgba(221, 214, 254, 0.5);
  }

  .dark .section-bg-purple {
    background: rgba(88, 28, 135, 0.1);
    border: 1px solid rgba(168, 85, 247, 0.3);
  }

  .section-bg-amber {
    background: rgba(255, 251, 235, 1);
    border: 1px solid rgba(252, 211, 77, 0.5);
  }

  .dark .section-bg-amber {
    background: rgba(120, 53, 15, 0.2);
    border: 1px solid rgba(245, 158, 11, 0.3);
  }
</style>
@endpush

@section('content')
<div class="relative min-h-screen">
  <!-- Background Decorations -->
  <div class="fixed inset-0 overflow-hidden pointer-events-none">
    <div class="absolute -top-20 -right-24 h-80 w-80 rounded-full blur-3xl opacity-20 bg-gradient-to-br from-amber-300 to-rose-300"></div>
    <div class="absolute -bottom-24 -left-24 h-96 w-96 rounded-full blur-3xl opacity-20 bg-gradient-to-tr from-orange-300 to-pink-300"></div>
  </div>

  <div class="relative min-h-screen py-8">
    <div class="max-w-4xl mx-auto px-4">
      <!-- Header -->
      <header class="text-center mb-8 fade-in">
        <div class="inline-flex items-center gap-3 mb-4">
          <div class="w-12 h-12 bg-gradient-to-br from-amber-500 to-orange-600 rounded-xl flex items-center justify-center shadow-lg">
            <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24">
              <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
            </svg>
          </div>
          <div>
            <h1 class="text-3xl md:text-4xl font-extrabold bg-clip-text text-transparent bg-gradient-to-r from-amber-700 via-orange-700 to-rose-700">
              চোখে দেখা - Submit Report
            </h1>
            <p class="text-sm text-secondary mt-1">Your Eyes, Your Voice, Your City</p>
          </div>
        </div>
        <p class="text-secondary max-w-2xl mx-auto">
          Help improve your city by reporting issues directly to authorities. Your voice matters in building a better community.
        </p>
      </header>

      <!-- Error Messages -->
      @if ($errors->any())
        <div class="glass-card rounded-2xl p-6 mb-8 border-red-200 bg-red-50/90">
          <div class="flex items-center gap-3 mb-4">
            <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center">
              <svg class="w-5 h-5 text-red-600" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/>
              </svg>
            </div>
            <h3 class="text-lg font-semibold text-red-800">Please fix the following issues:</h3>
          </div>
          <ul class="list-disc list-inside text-red-700 space-y-1">
            @foreach ($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      <!-- Progress Indicator -->
      <div class="glass-card rounded-2xl p-6 mb-8 fade-in">
        <div class="flex items-center justify-between mb-4">
          <span class="text-sm font-medium text-primary">Report Submission Progress</span>
          <span id="progressText" class="text-sm text-secondary">0% Complete</span>
        </div>
        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-3 overflow-hidden">
          <div id="progressBar" class="progress-bar h-3 rounded-full" style="width: 0%"></div>
        </div>
      </div>

      <!-- Main Form -->
      <form id="reportForm" action="{{ route('report.store') }}" method="POST" enctype="multipart/form-data" class="glass-card rounded-2xl shadow-2xl overflow-hidden">
        @csrf
        
        <!-- Step 1: Basic Information -->
        <div class="step active fade-in" data-step="1">
          <div class="p-8">
            <div class="flex items-center gap-3 mb-8">
              <div class="w-10 h-10 bg-gradient-to-br from-amber-500 to-orange-600 text-white rounded-full flex items-center justify-center font-bold text-lg shadow-lg">1</div>
              <div>
                <h2 class="text-2xl font-bold text-primary">Basic Information</h2>
                <p class="text-secondary text-sm">Tell us about the issue you've observed</p>
              </div>
            </div>

            <div class="space-y-6">
              <div class="grid md:grid-cols-2 gap-6">
                <div>
                  <label class="block text-sm font-semibold text-primary mb-2">
                    Report Title <span class="text-red-500">*</span>
                  </label>
                  <input type="text" name="title" value="{{ old('title') }}" required 
                         class="form-input w-full px-4 py-3 rounded-xl"
                         placeholder="e.g., Broken streetlight on Main Road">
                  <p class="text-xs text-secondary mt-1">Keep it clear and specific</p>
                </div>

                <div>
                  <label class="block text-sm font-semibold text-primary mb-2">
                    Category <span class="text-red-500">*</span>
                  </label>
                  <select name="category" required class="form-input w-full px-4 py-3 rounded-xl">
                    <option value="">Select Category</option>
                    <option value="Road Damage" {{ old('category') === 'Road Damage' ? 'selected' : '' }}>🛣️ Road Damage</option>
                    <option value="Street Light" {{ old('category') === 'Street Light' ? 'selected' : '' }}>💡 Street Light</option>
                    <option value="Water Supply" {{ old('category') === 'Water Supply' ? 'selected' : '' }}>💧 Water Supply</option>
                    <option value="Waste Management" {{ old('category') === 'Waste Management' ? 'selected' : '' }}>🗑️ Waste Management</option>
                    <option value="Drainage" {{ old('category') === 'Drainage' ? 'selected' : '' }}>🌊 Drainage</option>
                    <option value="Public Safety" {{ old('category') === 'Public Safety' ? 'selected' : '' }}>🚨 Public Safety</option>
                    <option value="Traffic" {{ old('category') === 'Traffic' ? 'selected' : '' }}>🚦 Traffic Issues</option>
                    <option value="Electricity" {{ old('category') === 'Electricity' ? 'selected' : '' }}>⚡ Electricity</option>
                    <option value="Other" {{ old('category') === 'Other' ? 'selected' : '' }}>📝 Other</option>
                  </select>
                </div>
              </div>

              <div class="grid md:grid-cols-2 gap-6">
                <div>
                  <label class="block text-sm font-semibold text-primary mb-2">
                    City Corporation <span class="text-red-500">*</span>
                  </label>
                  <select name="city_corporation" required class="form-input w-full px-4 py-3 rounded-xl">
                    <option value="">Select City Corporation</option>
                    @php
                      $corporations = [
                        'Dhaka North' => 'Dhaka North City Corporation',
                        'Dhaka South' => 'Dhaka South City Corporation',
                        'Chattogram' => 'Chattogram City Corporation',
                        'Gazipur' => 'Gazipur City Corporation',
                        'Khulna' => 'Khulna City Corporation',
                        'Rajshahi' => 'Rajshahi City Corporation',
                        'Sylhet' => 'Sylhet City Corporation',
                        'Barishal' => 'Barishal City Corporation',
                        'Cumilla' => 'Cumilla City Corporation',
                        'Narayanganj' => 'Narayanganj City Corporation',
                        'Mymensingh' => 'Mymensingh City Corporation'
                      ];
                    @endphp
                    @foreach($corporations as $value => $label)
                      <option value="{{ $value }}" {{ old('city_corporation') === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                  </select>
                </div>

                <div>
                  <label class="block text-sm font-semibold text-primary mb-2">
                    Priority Level
                  </label>
                  <select name="priority" class="form-input w-full px-4 py-3 rounded-xl">
                    <option value="medium" {{ old('priority', 'medium') === 'medium' ? 'selected' : '' }}>🟡 Medium Priority</option>
                    <option value="low" {{ old('priority') === 'low' ? 'selected' : '' }}>🟢 Low Priority</option>
                    <option value="high" {{ old('priority') === 'high' ? 'selected' : '' }}>🔴 High Priority (Urgent)</option>
                  </select>
                  <p class="text-xs text-secondary mt-1">High priority for safety issues</p>
                </div>
              </div>

              <div>
                <label class="block text-sm font-semibold text-primary mb-2">
                  Detailed Description <span class="text-red-500">*</span>
                </label>
                <textarea name="description" required rows="5"
                          class="form-input w-full px-4 py-3 rounded-xl resize-none"
                          placeholder="Provide detailed information about the issue. Include:&#10;• When you first noticed it&#10;• How it affects the community&#10;• Any safety concerns&#10;• Nearby landmarks for reference">{{ old('description') }}</textarea>
                <div class="flex justify-between items-center mt-2">
                  <p class="text-xs text-secondary">Be specific to help authorities understand and act quickly</p>
                  <span class="text-xs text-secondary" id="charCount">0/500 characters</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Step 2: Location & Media -->
        <div class="step fade-in" data-step="2">
          <div class="p-8">
            <div class="flex items-center gap-3 mb-8">
              <div class="w-10 h-10 bg-gradient-to-br from-amber-500 to-orange-600 text-white rounded-full flex items-center justify-center font-bold text-lg shadow-lg">2</div>
              <div>
                <h2 class="text-2xl font-bold text-primary">Location & Evidence</h2>
                <p class="text-secondary text-sm">Pinpoint the exact location and add visual evidence</p>
              </div>
            </div>

            <div class="space-y-8">
              <!-- Location Section -->
              <div class="section-bg-light rounded-2xl p-6">
                <h3 class="text-lg font-semibold text-primary mb-4">📍 Precise Location</h3>
                
                <div class="space-y-4">
                  <div>
                    <label class="block text-sm font-semibold text-primary mb-2">Search Location</label>
                    <div class="relative">
                      <input type="text" id="locationSearch" autocomplete="off"
                             class="form-input w-full px-4 py-3 pl-12 rounded-xl"
                             placeholder="Search for address, landmark, or area...">
                      <div class="absolute left-4 top-1/2 transform -translate-y-1/2">
                        <svg class="w-5 h-5 text-secondary" fill="currentColor" viewBox="0 0 24 24">
                          <path d="M10 2a8 8 0 0 1 6.32 12.9l4.39 4.39-1.42 1.42-4.39-4.39A8 8 0 1 1 10 2z"/>
                        </svg>
                      </div>
                    </div>
                  </div>

                  <!-- Map Container -->
                  <div class="relative">
                    <div id="mapContainer" class="map-container h-80 rounded-xl overflow-hidden shadow-inner">
                      <div id="reportMap" class="w-full h-full"></div>
                      
                      <!-- Map Controls -->
                      <div class="absolute top-4 left-4 flex flex-col gap-2">
                        <button type="button" id="useLocationBtn"
                                class="map-controls inline-flex items-center gap-2 px-3 py-2 backdrop-blur-sm rounded-lg text-sm font-medium hover:shadow-md transition-all">
                          <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2C8 2 5 5 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-4-3-7-7-7zm0 9.5a2.5 2.5 0 110-5 2.5 2.5 0 010 5z"/>
                          </svg>
                          Use My Location
                        </button>
                        <button type="button" id="centerMapBtn"
                                class="map-controls px-3 py-2 backdrop-blur-sm rounded-lg text-sm font-medium hover:shadow-md transition-all">
                          Center on Pin
                        </button>
                      </div>

                      <!-- Location Display -->
                      <div id="locationDisplay" class="location-display absolute bottom-4 left-4 right-4 backdrop-blur-sm rounded-lg p-3 shadow-lg hidden">
                        <p class="text-sm font-medium" id="selectedLocation">Click on the map or use search to select location</p>
                        <p class="text-xs opacity-75 mt-1">Drag the pin to fine-tune the exact position</p>
                      </div>
                    </div>
                  </div>

                  <!-- Hidden Location Fields -->
                  <input type="hidden" name="latitude" id="latitude" value="{{ old('latitude') }}">
                  <input type="hidden" name="longitude" id="longitude" value="{{ old('longitude') }}">
                  <input type="hidden" name="place_id" id="place_id" value="{{ old('place_id') }}">
                  <input type="hidden" name="formatted_address" id="formatted_address" value="{{ old('formatted_address') }}">
                  <input type="hidden" name="location" id="locationField" value="{{ old('location') }}">
                </div>
              </div>

              <!-- Media Upload Section -->
              <div class="section-bg-blue rounded-2xl p-6">
                <h3 class="text-lg font-semibold text-primary mb-4">📸 Visual Evidence</h3>
                
                <div>
                  <label class="block text-sm font-semibold text-primary mb-3">Upload Photos or Videos</label>
                  
                  <!-- File Drop Zone -->
                  <div id="fileDropZone" class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-xl p-8 text-center hover:border-amber-400 hover:bg-amber-50/30 dark:hover:bg-amber-900/20 transition-all cursor-pointer">
                    <div class="space-y-4">
                      <div class="mx-auto w-16 h-16 bg-gradient-to-br from-amber-100 to-orange-100 dark:from-amber-900/30 dark:to-orange-900/30 rounded-2xl flex items-center justify-center">
                        <svg class="w-8 h-8 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                      </div>
                      <div>
                        <p class="text-primary font-medium">Drop files here or click to browse</p>
                        <p class="text-sm text-secondary mt-2">
                          Support: JPG, PNG, MP4, MOV (Max 10MB each)<br>
                          Clear photos help authorities understand the issue better
                        </p>
                      </div>
                    </div>
                    <input type="file" id="fileInput" name="photos[]" class="hidden" multiple accept="image/*,video/*">
                  </div>

                  <!-- File Preview Area -->
                  <div id="filePreviewArea" class="mt-6 grid grid-cols-2 md:grid-cols-4 gap-4 hidden"></div>
                  
                  <!-- Photo Tips -->
                  <div class="mt-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4 border border-blue-200 dark:border-blue-800">
                    <h4 class="text-sm font-semibold text-blue-800 dark:text-blue-200 mb-2">📝 Photo Tips for Better Results:</h4>
                    <ul class="text-xs text-blue-700 dark:text-blue-300 space-y-1">
                      <li>• Take multiple angles of the issue</li>
                      <li>• Include nearby landmarks for context</li>
                      <li>• Ensure good lighting and clear focus</li>
                      <li>• Show the scale of the problem if possible</li>
                    </ul>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Step 3: Contact & Submit -->
        <div class="step fade-in" data-step="3">
          <div class="p-8">
            <div class="flex items-center gap-3 mb-8">
              <div class="w-10 h-10 bg-gradient-to-br from-amber-500 to-orange-600 text-white rounded-full flex items-center justify-center font-bold text-lg shadow-lg">3</div>
              <div>
                <h2 class="text-2xl font-bold text-primary">Final Details</h2>
                <p class="text-secondary text-sm">Contact information and submission preferences</p>
              </div>
            </div>

            <div class="space-y-8">
              <!-- Contact Information -->
              <div class="section-bg-green rounded-2xl p-6">
                <h3 class="text-lg font-semibold text-primary mb-4">👤 Contact Information</h3>
                
                <div class="grid md:grid-cols-2 gap-6">
                  <div>
                    <label class="block text-sm font-semibold text-primary mb-2">
                      Your Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="reporter_name" value="{{ old('reporter_name', auth()->user()->name ?? '') }}" required 
                           class="form-input w-full px-4 py-3 rounded-xl"
                           placeholder="Full name">
                    <p class="text-xs text-secondary mt-1">This helps authorities contact you for updates</p>
                  </div>

                  <div>
                    <label class="block text-sm font-semibold text-primary mb-2">
                      Contact Number
                    </label>
                    <input type="tel" name="phone" value="{{ old('phone') }}" 
                           class="form-input w-full px-4 py-3 rounded-xl"
                           placeholder="+880 1XXX XXXXXX">
                    <p class="text-xs text-secondary mt-1">Optional: For urgent follow-ups</p>
                  </div>
                </div>

                <div class="mt-6">
                  <label class="block text-sm font-semibold text-primary mb-2">Email Address</label>
                  <input type="email" name="email" value="{{ old('email', auth()->user()->email ?? '') }}" 
                         class="form-input w-full px-4 py-3 rounded-xl"
                         placeholder="your@email.com">
                  <p class="text-xs text-secondary mt-1">We'll send status updates to this email</p>
                </div>
              </div>

              <!-- Preferences -->
              <div class="section-bg-purple rounded-2xl p-6">
                <h3 class="text-lg font-semibold text-primary mb-4">⚙️ Notification Preferences</h3>
                
                <div class="space-y-4">
                  <label class="flex items-start gap-3">
                    <input type="checkbox" name="notify_status" value="1" {{ old('notify_status', true) ? 'checked' : '' }} 
                           class="mt-0.5 rounded border-gray-300 text-amber-500 focus:ring-amber-500">
                    <div>
                      <span class="text-primary font-medium">Status Update Notifications</span>
                      <p class="text-sm text-secondary">Get notified when authorities update your report status</p>
                    </div>
                  </label>

                  <label class="flex items-start gap-3">
                    <input type="checkbox" name="notify_comments" value="1" {{ old('notify_comments') ? 'checked' : '' }}
                           class="mt-0.5 rounded border-gray-300 text-amber-500 focus:ring-amber-500">
                    <div>
                      <span class="text-primary font-medium">Community Comments</span>
                      <p class="text-sm text-secondary">Receive notifications when others comment on your report</p>
                    </div>
                  </label>

                  <label class="flex items-start gap-3">
                    <input type="checkbox" name="anonymous" value="1" {{ old('anonymous') ? 'checked' : '' }}
                           class="mt-0.5 rounded border-gray-300 text-amber-500 focus:ring-amber-500">
                    <div>
                      <span class="text-primary font-medium">Submit Anonymously</span>
                      <p class="text-sm text-secondary">Hide your name from public view (authorities can still contact you)</p>
                    </div>
                  </label>
                </div>
              </div>

              <!-- Report Summary -->
              <div class="section-bg-amber rounded-2xl p-6">
                <h3 class="text-lg font-semibold text-primary mb-4">📋 Report Summary</h3>
                <div id="reportSummary" class="space-y-3 text-sm">
                  <!-- Summary will be populated by JavaScript -->
                  <div class="text-secondary">Complete all steps to see your report summary</div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Navigation Controls -->
        <div class="bg-gray-50/80 dark:bg-gray-800/80 backdrop-blur-sm px-8 py-6 flex items-center justify-between border-t border-gray-200 dark:border-gray-700">
          <button type="button" id="prevBtn" class="px-6 py-3 text-secondary hover:text-primary font-semibold transition-all hidden">
            ← Previous Step
          </button>
          
          <div class="flex gap-4">
            <button type="button" id="nextBtn" class="btn-primary px-8 py-3 rounded-xl">
              Next Step →
            </button>
            <button type="submit" id="submitBtn" class="btn-primary px-8 py-3 rounded-xl hidden">
              <svg class="w-5 h-5 inline-block mr-2" fill="currentColor" viewBox="0 0 24 24">
                <path d="M3 12l18-9-9 18-2-7-7-2z"/>
              </svg>
              Submit Report
            </button>
          </div>
        </div>
      </form>

      <!-- Success Message -->
      @if(session('success'))
        <div class="glass-card rounded-2xl p-6 mt-8 border-green-200 bg-green-50/90">
          <div class="flex items-center gap-3">
            <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
              <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
              </svg>
            </div>
            <div>
              <h3 class="text-lg font-semibold text-green-800">Report Submitted Successfully!</h3>
              <p class="text-green-700">{{ session('success') }}</p>
            </div>
          </div>
        </div>
      @endif

      <!-- Features Showcase -->
      <div class="mt-16 grid md:grid-cols-3 gap-8">
        <div class="glass-card rounded-2xl p-6 text-center">
          <div class="w-14 h-14 bg-gradient-to-br from-blue-100 to-blue-200 dark:from-blue-900/30 dark:to-blue-800/30 rounded-2xl flex items-center justify-center mx-auto mb-4">
            <svg class="w-7 h-7 text-blue-600 dark:text-blue-400" fill="currentColor" viewBox="0 0 24 24">
              <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
            </svg>
          </div>
          <h3 class="font-bold text-primary mb-2">Real-time Tracking</h3>
          <p class="text-sm text-secondary">Track your report status from submission to resolution with instant updates</p>
        </div>

        <div class="glass-card rounded-2xl p-6 text-center">
          <div class="w-14 h-14 bg-gradient-to-br from-green-100 to-green-200 dark:from-green-900/30 dark:to-green-800/30 rounded-2xl flex items-center justify-center mx-auto mb-4">
            <svg class="w-7 h-7 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 24 24">
              <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
            </svg>
          </div>
          <h3 class="font-bold text-primary mb-2">Community Impact</h3>
          <p class="text-sm text-secondary">Your reports help improve infrastructure and services for the entire community</p>
        </div>

        <div class="glass-card rounded-2xl p-6 text-center">
          <div class="w-14 h-14 bg-gradient-to-br from-purple-100 to-purple-200 dark:from-purple-900/30 dark:to-purple-800/30 rounded-2xl flex items-center justify-center mx-auto mb-4">
            <svg class="w-7 h-7 text-purple-600 dark:text-purple-400" fill="currentColor" viewBox="0 0 24 24">
              <path d="M12 1L9 9l-8 3 8 3 3 8 3-8 8-3-8-3-3-8z"/>
            </svg>
          </div>
          <h3 class="font-bold text-primary mb-2">Direct Authority Contact</h3>
          <p class="text-sm text-secondary">Reports are sent directly to relevant city authorities for faster action</p>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Loading Overlay -->
<div id="loadingOverlay" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden flex items-center justify-center">
  <div class="bg-white dark:bg-gray-800 rounded-2xl p-8 mx-4 max-w-sm w-full text-center shadow-2xl">
    <div class="loading-spinner mx-auto mb-4"></div>
    <h3 class="text-lg font-semibold text-primary mb-2">Submitting Report</h3>
    <p class="text-secondary">Please wait while we process your submission...</p>
  </div>
</div>
@endsection

@push('scripts')
<script src="https://maps.googleapis.com/maps/api/js?key={{config('services.google_maps.key')}}&libraries=places&callback=initGoogleMaps"></script>
<script>
  // Add this at the top of your script section
function initGoogleMaps() {
    console.log('Google Maps loaded successfully');
    if (!document.querySelector('meta[name="csrf-token"]')) {
        const meta = document.createElement('meta');
        meta.name = 'csrf-token';
        meta.content = '{{ csrf_token() }}';
        document.head.appendChild(meta);
    }
    new EnhancedReportForm();
}
class EnhancedReportForm {
  constructor() {
    this.currentStep = 1;
    this.totalSteps = 3;
    this.uploadedFiles = [];
    this.locationData = null;
    this.map = null;
    this.marker = null;
    this.geocoder = null;
    this.autocomplete = null;
    
    this.init();
  }

  init() {
    this.setupEventListeners();
    this.setupFileUpload();
    this.initializeMap();
    this.updateProgress();
  }

  setupEventListeners() {
    // Navigation
    document.getElementById('nextBtn').addEventListener('click', () => this.nextStep());
    document.getElementById('prevBtn').addEventListener('click', () => this.prevStep());
    
    // Form submission
    document.getElementById('reportForm').addEventListener('submit', (e) => this.handleSubmit(e));
    
    // Form validation
    const inputs = document.querySelectorAll('input, select, textarea');
    inputs.forEach(input => {
      input.addEventListener('input', () => this.validateCurrentStep());
      input.addEventListener('change', () => this.validateCurrentStep());
    });

    // Character counter for description
    const description = document.querySelector('[name="description"]');
    const charCount = document.getElementById('charCount');
    if (description && charCount) {
      description.addEventListener('input', (e) => {
        charCount.textContent = `${e.target.value.length}/500 characters`;
      });
    }
  }

  setupFileUpload() {
    const dropZone = document.getElementById('fileDropZone');
    const fileInput = document.getElementById('fileInput');
    const previewArea = document.getElementById('filePreviewArea');

    // Click to select files
    dropZone.addEventListener('click', () => fileInput.click());

    // Drag and drop handlers
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
      dropZone.addEventListener(eventName, this.preventDefaults, false);
    });

    ['dragenter', 'dragover'].forEach(eventName => {
      dropZone.addEventListener(eventName, () => dropZone.classList.add('drag-over'), false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
      dropZone.addEventListener(eventName, () => dropZone.classList.remove('drag-over'), false);
    });

    dropZone.addEventListener('drop', (e) => this.handleFiles(e.dataTransfer.files));
    fileInput.addEventListener('change', (e) => this.handleFiles(e.target.files));
  }

  preventDefaults(e) {
    e.preventDefault();
    e.stopPropagation();
  }

  handleFiles(files) {
    Array.from(files).forEach(file => {
      // Validate file size (10MB limit)
      if (file.size > 10 * 1024 * 1024) {
        this.showToast(`${file.name} is too large. Maximum size is 10MB.`, 'error');
        return;
      }

      // Validate file type
      if (!file.type.match(/^(image|video)\//)) {
        this.showToast(`${file.name} is not a supported file type.`, 'error');
        return;
      }

      this.uploadedFiles.push(file);
      this.createFilePreview(file);
    });

    if (this.uploadedFiles.length > 0) {
      document.getElementById('filePreviewArea').classList.remove('hidden');
    }
  }

  createFilePreview(file) {
    const previewArea = document.getElementById('filePreviewArea');
    const previewDiv = document.createElement('div');
    previewDiv.className = 'file-preview relative';

    if (file.type.startsWith('image/')) {
      const img = document.createElement('img');
      img.src = URL.createObjectURL(file);
      img.onload = () => URL.revokeObjectURL(img.src);
      previewDiv.appendChild(img);
    } else if (file.type.startsWith('video/')) {
      const video = document.createElement('video');
      video.src = URL.createObjectURL(file);
      video.controls = true;
      video.onload = () => URL.revokeObjectURL(video.src);
      previewDiv.appendChild(video);
    }

    // Remove button
    const removeBtn = document.createElement('button');
    removeBtn.type = 'button';
    removeBtn.innerHTML = '×';
    removeBtn.className = 'remove-file';
    removeBtn.addEventListener('click', () => {
      const index = this.uploadedFiles.indexOf(file);
      if (index > -1) {
        this.uploadedFiles.splice(index, 1);
      }
      previewDiv.remove();
      
      if (this.uploadedFiles.length === 0) {
        document.getElementById('filePreviewArea').classList.add('hidden');
      }
    });

    previewDiv.appendChild(removeBtn);
    previewArea.appendChild(previewDiv);
  }

async initializeMap() {
    // Ensure Google Maps is loaded
    let retries = 0;
    const maxRetries = 50; // 5 seconds max wait
    
    while (!window.google && retries < maxRetries) {
        await new Promise(resolve => setTimeout(resolve, 100));
        retries++;
    }
    
    if (!window.google || !window.google.maps) {
        console.error('Google Maps API failed to load');
        this.showToast('Failed to load map. Please refresh the page.', 'error');
        return;
    }

    console.log('Initializing Google Maps...');

    this.geocoder = new google.maps.Geocoder();

    // Get the current theme
    const isDarkMode = document.documentElement.classList.contains('dark') || 
                       window.matchMedia('(prefers-color-scheme: dark)').matches;

    const lightStyles = [
      { featureType: 'poi', stylers: [{ visibility: 'simplified' }] },
      { featureType: 'road', elementType: 'geometry', stylers: [{ lightness: 20 }] }
    ];

    const darkStyles = [
      { elementType: "geometry", stylers: [{ color: "#212121" }] },
      { elementType: "labels.icon", stylers: [{ visibility: "off" }] },
      { elementType: "labels.text.fill", stylers: [{ color: "#757575" }] },
      { elementType: "labels.text.stroke", stylers: [{ color: "#212121" }] },
      { featureType: "administrative", elementType: "geometry", stylers: [{ color: "#757575" }] },
      { featureType: "administrative.country", elementType: "labels.text.fill", stylers: [{ color: "#9e9e9e" }] },
      { featureType: "administrative.locality", elementType: "labels.text.fill", stylers: [{ color: "#bdbdbd" }] },
      { featureType: "poi", elementType: "labels.text.fill", stylers: [{ color: "#757575" }] },
      { featureType: "poi.park", elementType: "geometry", stylers: [{ color: "#181818" }] },
      { featureType: "poi.park", elementType: "labels.text.fill", stylers: [{ color: "#616161" }] },
      { featureType: "poi.park", elementType: "labels.text.stroke", stylers: [{ color: "#1b1b1b" }] },
      { featureType: "road", elementType: "geometry.fill", stylers: [{ color: "#2c2c2c" }] },
      { featureType: "road", elementType: "labels.text.fill", stylers: [{ color: "#8a8a8a" }] },
      { featureType: "road.arterial", elementType: "geometry", stylers: [{ color: "#373737" }] },
      { featureType: "road.highway", elementType: "geometry", stylers: [{ color: "#3c3c3c" }] },
      { featureType: "road.highway.controlled_access", elementType: "geometry", stylers: [{ color: "#4e4e4e" }] },
      { featureType: "road.local", elementType: "labels.text.fill", stylers: [{ color: "#616161" }] },
      { featureType: "transit", elementType: "labels.text.fill", stylers: [{ color: "#757575" }] },
      { featureType: "water", elementType: "geometry", stylers: [{ color: "#000000" }] },
      { featureType: "water", elementType: "labels.text.fill", stylers: [{ color: "#3d3d3d" }] }
    ];

    this.map = new google.maps.Map(document.getElementById('reportMap'), {
      center: { lat: 23.777176, lng: 90.399452 }, // Dhaka center
      zoom: 12,
      mapTypeControl: false,
      fullscreenControl: false,
      streetViewControl: true,
      styles: isDarkMode ? darkStyles : lightStyles
    });

    this.marker = new google.maps.Marker({
      position: this.map.getCenter(),
      map: this.map,
      draggable: true,
      animation: google.maps.Animation.DROP,
      icon: {
        path: google.maps.SymbolPath.CIRCLE,
        fillColor: '#ef4444',
        fillOpacity: 1,
        strokeColor: '#ffffff',
        strokeWeight: 2,
        scale: 10
      }
    });

    // Event listeners
    this.marker.addListener('dragend', () => {
      const position = this.marker.getPosition();
      this.reverseGeocode(position.lat(), position.lng());
    });

    this.map.addListener('click', (e) => {
      this.marker.setPosition(e.latLng);
      this.reverseGeocode(e.latLng.lat(), e.latLng.lng());
    });

    // Location button
    document.getElementById('useLocationBtn').addEventListener('click', () => this.getCurrentLocation());
    document.getElementById('centerMapBtn').addEventListener('click', () => this.centerOnMarker());

    // Initialize autocomplete
    const searchInput = document.getElementById('locationSearch');
    this.autocomplete = new google.maps.places.Autocomplete(searchInput, {
      componentRestrictions: { country: 'BD' },
      fields: ['geometry', 'formatted_address', 'place_id']
    });

    this.autocomplete.addListener('place_changed', () => {
      const place = this.autocomplete.getPlace();
      if (place.geometry) {
        const location = place.geometry.location;
        this.marker.setPosition(location);
        this.map.panTo(location);
        this.map.setZoom(16);
        this.setLocationData(location.lat(), location.lng(), place.place_id, place.formatted_address);
      }
    });

    // Listen for theme changes
    const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
    mediaQuery.addEventListener('change', (e) => {
      this.map.setOptions({ styles: e.matches ? darkStyles : lightStyles });
    });

    // Listen for manual theme toggle
    const observer = new MutationObserver((mutations) => {
      mutations.forEach((mutation) => {
        if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
          const isDarkMode = document.documentElement.classList.contains('dark');
          this.map.setOptions({ styles: isDarkMode ? darkStyles : lightStyles });
        }
      });
    });
    
    observer.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });

    // Load existing location if available
    this.loadExistingLocation();
  }

  getCurrentLocation() {
    const btn = document.getElementById('useLocationBtn');
    const originalText = btn.textContent;
    
    if (!navigator.geolocation) {
      this.showToast('Geolocation is not supported by this browser.', 'error');
      return;
    }

    btn.textContent = 'Getting location...';
    btn.disabled = true;

    navigator.geolocation.getCurrentPosition(
      (position) => {
        const { latitude, longitude } = position.coords;
        const pos = new google.maps.LatLng(latitude, longitude);
        
        this.marker.setPosition(pos);
        this.map.panTo(pos);
        this.map.setZoom(17);
        
        this.reverseGeocode(latitude, longitude);
        
        btn.textContent = 'Location Set!';
        btn.style.background = 'linear-gradient(135deg, #10b981, #059669)';
        
        setTimeout(() => {
          btn.textContent = originalText;
          btn.disabled = false;
          btn.style.background = '';
        }, 2000);
      },
      (error) => {
        console.error('Geolocation error:', error);
        this.showToast('Unable to get your location. Please allow location access.', 'error');
        btn.textContent = originalText;
        btn.disabled = false;
      },
      { enableHighAccuracy: true, timeout: 10000, maximumAge: 60000 }
    );
  }

  centerOnMarker() {
    if (this.marker) {
      this.map.panTo(this.marker.getPosition());
      this.map.setZoom(Math.max(this.map.getZoom(), 16));
    }
  }

  reverseGeocode(lat, lng) {
    this.geocoder.geocode({ location: { lat, lng } }, (results, status) => {
      if (status === 'OK' && results[0]) {
        const address = results[0].formatted_address;
        const placeId = results[0].place_id;
        this.setLocationData(lat, lng, placeId, address);
      } else {
        this.setLocationData(lat, lng, null, `Coordinates: ${lat.toFixed(6)}, ${lng.toFixed(6)}`);
      }
    });
  }

  setLocationData(lat, lng, placeId, address) {
    this.locationData = { latitude: lat, longitude: lng, placeId, address };
    
    // Update hidden fields
    document.getElementById('latitude').value = lat;
    document.getElementById('longitude').value = lng;
    document.getElementById('place_id').value = placeId || '';
    document.getElementById('formatted_address').value = address || '';
    document.getElementById('locationField').value = address || '';
    
    // Update display
    document.getElementById('selectedLocation').textContent = address || `Location: ${lat.toFixed(6)}, ${lng.toFixed(6)}`;
    document.getElementById('locationDisplay').classList.remove('hidden');
  }

  loadExistingLocation() {
    const lat = parseFloat(document.getElementById('latitude').value);
    const lng = parseFloat(document.getElementById('longitude').value);
    const address = document.getElementById('formatted_address').value;

    if (!isNaN(lat) && !isNaN(lng)) {
      const pos = new google.maps.LatLng(lat, lng);
      this.marker.setPosition(pos);
      this.map.panTo(pos);
      this.map.setZoom(16);
      
      if (address) {
        document.getElementById('selectedLocation').textContent = address;
        document.getElementById('locationDisplay').classList.remove('hidden');
      }
      
      this.locationData = { latitude: lat, longitude: lng, address };
    }
  }

  nextStep() {
    if (!this.validateCurrentStep()) {
      this.showValidationErrors();
      return;
    }

    if (this.currentStep < this.totalSteps) {
      this.currentStep++;
      this.updateStepDisplay();
      this.updateProgress();
      
      if (this.currentStep === this.totalSteps) {
        this.updateSummary();
      }
    }
  }

  prevStep() {
    if (this.currentStep > 1) {
      this.currentStep--;
      this.updateStepDisplay();
      this.updateProgress();
    }
  }

  updateStepDisplay() {
    // Hide all steps
    document.querySelectorAll('.step').forEach(step => {
      step.classList.remove('active');
      step.style.display = 'none';
    });

    // Show current step
    const currentStepEl = document.querySelector(`[data-step="${this.currentStep}"]`);
    if (currentStepEl) {
      currentStepEl.classList.add('active');
      currentStepEl.style.display = 'block';
    }

    // Update navigation buttons
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    const submitBtn = document.getElementById('submitBtn');

    prevBtn.classList.toggle('hidden', this.currentStep === 1);
    
    if (this.currentStep === this.totalSteps) {
      nextBtn.classList.add('hidden');
      submitBtn.classList.remove('hidden');
    } else {
      nextBtn.classList.remove('hidden');
      submitBtn.classList.add('hidden');
    }

    // Smooth scroll to top
    currentStepEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
  }

  updateProgress() {
    const progress = (this.currentStep / this.totalSteps) * 100;
    const progressBar = document.getElementById('progressBar');
    const progressText = document.getElementById('progressText');

    if (progressBar && progressText) {
      progressBar.style.width = `${progress}%`;
      progressText.textContent = `${Math.round(progress)}% Complete`;
    }
  }

  validateCurrentStep() {
    const currentStepEl = document.querySelector(`[data-step="${this.currentStep}"]`);
    if (!currentStepEl) return false;

    const requiredFields = currentStepEl.querySelectorAll('[required]');
    let isValid = true;

    requiredFields.forEach(field => {
      field.classList.remove('error');
      if (!field.value.trim()) {
        isValid = false;
        field.classList.add('error');
      }
    });

    // Step 2 specific validation (location required)
    if (this.currentStep === 2) {
      if (!this.locationData || !this.locationData.latitude || !this.locationData.longitude) {
        isValid = false;
      }
    }

    return isValid;
  }

  showValidationErrors() {
    const currentStepEl = document.querySelector(`[data-step="${this.currentStep}"]`);
    const invalidFields = currentStepEl.querySelectorAll('[required]');
    
    let firstInvalid = null;
    invalidFields.forEach(field => {
      if (!field.value.trim()) {
        field.classList.add('error');
        if (!firstInvalid) firstInvalid = field;
      }
    });

    if (this.currentStep === 2 && (!this.locationData || !this.locationData.latitude)) {
      this.showToast('Please select a location on the map or use your current location.', 'error');
      return;
    }

    if (firstInvalid) {
      firstInvalid.focus();
      this.showToast('Please fill in all required fields.', 'error');
    }
  }

  updateSummary() {
    const summaryEl = document.getElementById('reportSummary');
    if (!summaryEl) return;

    const title = document.querySelector('[name="title"]')?.value || 'Not set';
    const category = document.querySelector('[name="category"]')?.value || 'Not set';
    const city = document.querySelector('[name="city_corporation"]')?.value || 'Not set';
    const priority = document.querySelector('[name="priority"]')?.value || 'Medium';
    const description = document.querySelector('[name="description"]')?.value || '';
    
    const summaryHTML = `
      <div class="grid grid-cols-2 gap-4 text-sm">
        <div class="flex justify-between border-b border-gray-200 dark:border-gray-600 pb-2">
          <span class="text-secondary">Category:</span>
          <strong class="text-primary">${category}</strong>
        </div>
        <div class="flex justify-between border-b border-gray-200 dark:border-gray-600 pb-2">
          <span class="text-secondary">City:</span>
          <strong class="text-primary">${city}</strong>
        </div>
        <div class="flex justify-between border-b border-gray-200 dark:border-gray-600 pb-2">
          <span class="text-secondary">Priority:</span>
          <strong class="text-primary capitalize">${priority}</strong>
        </div>
        <div class="flex justify-between border-b border-gray-200 dark:border-gray-600 pb-2">
          <span class="text-secondary">Media Files:</span>
          <strong class="text-primary">${this.uploadedFiles.length} files</strong>
        </div>
        <div class="flex justify-between border-b border-gray-200 dark:border-gray-600 pb-2">
          <span class="text-secondary">Location:</span>
          <strong class="text-primary">${this.locationData ? 'Set' : 'Not set'}</strong>
        </div>
      </div>
      ${description ? `
        <div class="mt-4 p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
          <span class="text-xs font-semibold text-secondary block mb-1">DESCRIPTION:</span>
          <p class="text-sm text-primary">${description.substring(0, 200)}${description.length > 200 ? '...' : ''}</p>
        </div>
      ` : ''}
    `;

    summaryEl.innerHTML = summaryHTML;
  }

  async handleSubmit(e) {
    e.preventDefault();

    if (!this.validateCurrentStep()) {
      this.showValidationErrors();
      return;
    }

    const submitBtn = document.getElementById('submitBtn');
    const originalHTML = submitBtn.innerHTML;
    const loadingOverlay = document.getElementById('loadingOverlay');

    // Show loading state
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<div class="loading-spinner"></div>Submitting...';
    loadingOverlay.classList.remove('hidden');

    try {
      // Create FormData with all the form data
      const formData = new FormData(document.getElementById('reportForm'));

      // Add uploaded files
      this.uploadedFiles.forEach((file, index) => {
        formData.append(`photos[${index}]`, file);
      });

      // Add location data
      if (this.locationData) {
        formData.set('latitude', this.locationData.latitude);
        formData.set('longitude', this.locationData.longitude);
        formData.set('place_id', this.locationData.placeId || '');
        formData.set('formatted_address', this.locationData.address || '');
        formData.set('location', this.locationData.address || '');
      }

      // Submit the form
      const response = await fetch(document.getElementById('reportForm').action, {
        method: 'POST',
        body: formData,
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        }
      });

      if (response.ok) {
        const result = await response.json().catch(() => null);
        
        // Success - redirect or show success message
        if (result && result.redirect) {
          window.location.href = result.redirect;
        } else {
          this.showSuccessMessage();
        }
      } else {
        throw new Error('Submission failed');
      }

    } catch (error) {
      console.error('Submission error:', error);
      this.showToast('Failed to submit report. Please check your internet connection and try again.', 'error');
      
      // Restore button state
      submitBtn.disabled = false;
      submitBtn.innerHTML = originalHTML;
    } finally {
      loadingOverlay.classList.add('hidden');
    }
  }

  showSuccessMessage() {
    // Create success message overlay
    const successHTML = `
      <div class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-8 max-w-md w-full shadow-2xl">
          <div class="text-center">
            <div class="w-16 h-16 bg-green-100 dark:bg-green-900/30 rounded-full flex items-center justify-center mx-auto mb-4">
              <svg class="w-8 h-8 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
              </svg>
            </div>
            <h3 class="text-2xl font-bold text-primary mb-3">Report Submitted Successfully!</h3>
            <p class="text-secondary mb-6">
              Your report has been sent to the relevant authorities. You'll receive email updates on the progress.
            </p>
            <div class="space-y-3">
              <button onclick="window.location.href='/reports'" class="w-full px-6 py-3 bg-gradient-to-r from-amber-500 to-orange-600 text-white rounded-xl font-semibold hover:shadow-lg transition-all">
                View All Reports
              </button>
              <button onclick="window.location.reload()" class="w-full px-6 py-3 border border-gray-300 dark:border-gray-600 text-primary rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                Submit Another Report
              </button>
            </div>
          </div>
        </div>
      </div>
    `;

    document.body.insertAdjacentHTML('beforeend', successHTML);
  }

  showToast(message, type = 'success') {
    // Remove existing toasts
    document.querySelectorAll('.toast').forEach(toast => toast.remove());

    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.textContent = message;

    document.body.appendChild(toast);

    // Show toast
    setTimeout(() => toast.classList.add('show'), 100);

    // Auto hide
    setTimeout(() => {
      toast.classList.remove('show');
      setTimeout(() => toast.remove(), 300);
    }, 4000);
  }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
  // Add CSRF token to meta if not present
  if (!document.querySelector('meta[name="csrf-token"]')) {
    const meta = document.createElement('meta');
    meta.name = 'csrf-token';
    meta.content = '{{ csrf_token() }}';
    document.head.appendChild(meta);
  }

  new EnhancedReportForm();
});

// Image optimization utility
function compressImage(file, maxWidth = 1200, quality = 0.8) {
  return new Promise((resolve) => {
    const canvas = document.createElement('canvas');
    const ctx = canvas.getContext('2d');
    const img = new Image();

    img.onload = () => {
      // Calculate new dimensions
      let { width, height } = img;
      
      if (width > maxWidth) {
        height = (height * maxWidth) / width;
        width = maxWidth;
      }

      canvas.width = width;
      canvas.height = height;

      // Draw and compress
      ctx.drawImage(img, 0, 0, width, height);
      canvas.toBlob(resolve, 'image/jpeg', quality);
    };

    img.src = URL.createObjectURL(file);
  });
}

// Service Worker for offline functionality (optional)
if ('serviceWorker' in navigator) {
  window.addEventListener('load', () => {
    navigator.serviceWorker.register('/sw.js').catch(() => {
      // Silent fail - service worker is optional
    });
  });
}
</script>
@endpush