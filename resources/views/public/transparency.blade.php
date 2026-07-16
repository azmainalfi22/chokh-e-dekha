<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>স্বচ্ছতা বোর্ড | {{ config('app.official_name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased bg-bg-secondary">
    {{-- Government Branding Bar --}}
    <div class="govt-brand-bar">
        <div class="max-w-7xl mx-auto px-4">
            🇧🇩 Government of the People's Republic of Bangladesh | গণপ্রজাতন্ত্রী বাংলাদেশ সরকার
        </div>
    </div>

    {{-- Navigation --}}
    <nav class="bg-white shadow-md sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <a href="{{ route('welcome') }}" class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-gradient-to-br from-bd-green to-bd-green-dark rounded-full flex items-center justify-center text-white font-bold text-xl shadow-lg">
                        চ
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-govt-navy">Chokh-e-Dekha</h1>
                        <p class="text-xs text-text-secondary">স্বচ্ছতা বোর্ড</p>
                    </div>
                </a>
                <div class="flex items-center gap-4">
                    <a href="{{ route('welcome') }}" class="text-text-secondary hover:text-govt-navy font-medium transition">প্রথম পাতা</a>
                    <a href="{{ route('login') }}" class="btn-secondary-govt">প্রবেশ করুন</a>
                </div>
            </div>
        </div>
    </nav>

    {{-- Hero Section --}}
    <section class="bg-gradient-to-r from-govt-navy to-bd-green text-white py-16">
        <div class="max-w-7xl mx-auto px-4 text-center">
            <div class="inline-flex items-center gap-2 bg-white/20 backdrop-blur-sm px-4 py-2 rounded-full mb-6">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span class="font-semibold">সরকারী উন্মুক্ত তথ্য পোর্টাল</span>
            </div>
            <h1 class="text-4xl md:text-5xl font-bold mb-4">জনগণের জন্য স্বচ্ছতা</h1>
            <p class="text-xl text-gray-200 max-w-3xl mx-auto">
                প্রতিটি নাগরিক অভিযোগ, এর অগ্রগতি এবং সমাধান - সম্পূর্ণ স্বচ্ছভাবে জনসাধারণের জন্য উন্মুক্ত
            </p>
        </div>
    </section>

    {{-- Key Metrics --}}
    <section class="py-16">
        <div class="max-w-7xl mx-auto px-4">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mb-12">
                <div class="bg-white rounded-xl shadow-lg p-6 text-center border-t-4 border-bd-green">
                    <div class="text-4xl font-bold text-govt-navy mb-2">{{ number_format($totalReports) }}</div>
                    <div class="text-sm text-text-secondary">মোট অভিযোগ</div>
                </div>
                <div class="bg-white rounded-xl shadow-lg p-6 text-center border-t-4 border-success">
                    <div class="text-4xl font-bold text-success mb-2">{{ $resolutionRate }}%</div>
                    <div class="text-sm text-text-secondary">সমাধান হার</div>
                </div>
                <div class="bg-white rounded-xl shadow-lg p-6 text-center border-t-4 border-info">
                    <div class="text-4xl font-bold text-info mb-2">{{ round($avgResolutionTime ?? 0) }}h</div>
                    <div class="text-sm text-text-secondary">গড় সমাধান সময়</div>
                </div>
                <div class="bg-white rounded-xl shadow-lg p-6 text-center border-t-4 border-warning">
                    <div class="text-4xl font-bold text-warning mb-2">{{ $slaComplianceRate }}%</div>
                    <div class="text-sm text-text-secondary">SLA পূরণ</div>
                </div>
            </div>

            <div class="grid lg:grid-cols-2 gap-8">
                {{-- Category Breakdown --}}
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <h2 class="text-2xl font-bold text-govt-navy mb-6">বিভাগ অনুযায়ী রিপোর্ট</h2>
                    <div class="space-y-4">
                        @foreach($categoryStats as $cat)
                            @php
                                $percentage = $totalReports > 0 ? round(($cat->total / $totalReports) * 100) : 0;
                            @endphp
                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <span class="font-semibold text-govt-navy capitalize">{{ $cat->category }}</span>
                                    <span class="text-sm text-text-muted">{{ $cat->total }} ({{ $percentage }}%)</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-gradient-to-r from-bd-green to-govt-navy h-2 rounded-full transition-all duration-500" style="width: {{ $percentage }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Monthly Trend --}}
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <h2 class="text-2xl font-bold text-govt-navy mb-6">মাসিক প্রবণতা</h2>
                    <div class="space-y-4">
                        @foreach($monthlyTrend as $month)
                            @php
                                $maxTrend = $monthlyTrend->max('total');
                                $percentage = $maxTrend > 0 ? round(($month->total / $maxTrend) * 100) : 0;
                            @endphp
                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <span class="font-semibold text-govt-navy">{{ \Carbon\Carbon::parse($month->month)->format('F Y') }}</span>
                                    <span class="text-sm text-text-muted">{{ $month->total }} রিপোর্ট</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-gradient-to-r from-info to-govt-blue h-2 rounded-full transition-all duration-500" style="width: {{ $percentage }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Recent Success Stories --}}
    <section class="py-16 bg-white">
        <div class="max-w-7xl mx-auto px-4">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-govt-navy mb-4">সাম্প্রতিক সমাধান</h2>
                <p class="text-lg text-text-secondary">জনগণের সমস্যা, সরকারের সমাধান</p>
            </div>

            <div class="grid md:grid-cols-2 gap-6">
                @foreach($recentResolved as $report)
                    <div class="bg-gray-50 rounded-lg p-6 border border-gray-200 hover:shadow-lg transition">
                        <div class="flex items-start gap-4 mb-4">
                            <div class="w-12 h-12 bg-gradient-to-br from-success to-success/70 rounded-full flex items-center justify-center flex-shrink-0">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="badge-resolved text-xs px-3 py-1 rounded-full font-semibold">সমাধান হয়েছে</span>
                                    <span class="text-xs text-text-muted">{{ $report->status_updated_at->diffForHumans() }}</span>
                                </div>
                                <h3 class="font-bold text-govt-navy mb-2">{{ $report->title }}</h3>
                                <p class="text-sm text-text-secondary mb-3 line-clamp-2">{{ $report->description }}</p>
                                <div class="flex items-center gap-4 text-xs text-text-muted">
                                    <span class="flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                        </svg>
                                        {{ $report->location ?? 'N/A' }}
                                    </span>
                                    <span class="flex items-center gap-1 capitalize">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                        </svg>
                                        {{ $report->category }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Open Data Section --}}
    <section class="py-16 bg-bg-secondary">
        <div class="max-w-7xl mx-auto px-4 text-center">
            <h2 class="text-3xl font-bold text-govt-navy mb-4">উন্মুক্ত তথ্য API</h2>
            <p class="text-lg text-text-secondary mb-8 max-w-2xl mx-auto">
                গবেষক, সাংবাদিক এবং নাগরিক সমাজের জন্য সম্পূর্ণ ডেটাসেট উন্মুক্ত
            </p>
            <div class="bg-white rounded-xl shadow-lg p-8 max-w-2xl mx-auto text-left">
                <div class="bg-gray-900 rounded-lg p-4 mb-4 overflow-x-auto">
                    <code class="text-success text-sm">
                        GET {{ url('/api/open-data') }}?category=infrastructure&status=resolved
                    </code>
                </div>
                <p class="text-sm text-text-secondary mb-4">
                    Parameters: <code class="bg-gray-100 px-2 py-1 rounded">category</code>, 
                    <code class="bg-gray-100 px-2 py-1 rounded">status</code>, 
                    <code class="bg-gray-100 px-2 py-1 rounded">from_date</code>
                </p>
                <a href="{{ url('/api/open-data') }}" class="btn-primary-govt" target="_blank">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    <span>ডেটা ডাউনলোড করুন</span>
                </a>
            </div>
        </div>
    </section>

    {{-- Footer --}}
    <footer class="bg-govt-navy-dark text-gray-300 py-12">
        <div class="max-w-7xl mx-auto px-4 text-center">
            <p class="mb-4">{{ config('app.official_name') }}</p>
            <p class="text-sm">&copy; {{ date('Y') }} Government of Bangladesh. All rights reserved. | গণপ্রজাতন্ত্রী বাংলাদেশ সরকার</p>
        </div>
    </footer>
</body>
</html>

