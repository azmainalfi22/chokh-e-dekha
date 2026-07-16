<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.official_name') }} - জনগণের কণ্ঠস্বর</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased">
    {{-- Government Branding Bar --}}
    <div class="govt-brand-bar">
        <div class="max-w-7xl mx-auto px-4">
            🇧🇩 Government of the People's Republic of Bangladesh | গণপ্রজাতন্ত্রী বাংলাদেশ সরকার
        </div>
    </div>

    {{-- Main Navigation --}}
    <nav class="bg-white shadow-md sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">
                <div class="flex items-center gap-4">
                    <div class="flex items-center gap-3">
                        <div class="w-14 h-14 bg-gradient-to-br from-bd-green to-bd-green-dark rounded-full flex items-center justify-center text-white font-bold text-xl shadow-lg">
                            চ
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold text-govt-navy">Chokh-e-Dekha</h1>
                            <p class="text-xs text-text-secondary">চোখে দেখা - জনগণের চোখ, সরকারের কান</p>
                        </div>
                    </div>
                </div>

                <div class="hidden md:flex items-center gap-6">
                    <a href="#features" class="text-text-secondary hover:text-govt-navy font-medium transition">সেবাসমূহ</a>
                    <a href="#transparency" class="text-text-secondary hover:text-govt-navy font-medium transition">স্বচ্ছতা</a>
                    <a href="#about" class="text-text-secondary hover:text-govt-navy font-medium transition">আমাদের সম্পর্কে</a>
                    @auth
                        <a href="{{ route('dashboard') }}" class="btn-secondary-govt">ড্যাশবোর্ড</a>
                    @else
                        <a href="{{ route('login') }}" class="text-text-secondary hover:text-govt-navy font-medium transition">প্রবেশ করুন</a>
                        <a href="{{ route('register') }}" class="btn-primary-govt">নিবন্ধন করুন</a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    {{-- Hero Section --}}
    <section class="relative overflow-hidden bg-gradient-to-br from-govt-navy via-govt-navy-dark to-bd-green-dark text-white">
        <div class="absolute inset-0 opacity-10">
            <div class="absolute top-0 left-0 w-96 h-96 bg-bd-green rounded-full filter blur-3xl"></div>
            <div class="absolute bottom-0 right-0 w-96 h-96 bg-govt-blue rounded-full filter blur-3xl"></div>
        </div>

        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24 md:py-32">
            <div class="grid md:grid-cols-2 gap-12 items-center">
                <div>
                    <div class="trust-badge mb-6 bg-white/10 border-white/30 text-white">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        সরকারী অনুমোদিত জাতীয় পোর্টাল
                    </div>

                    <h1 class="text-4xl md:text-5xl font-bold mb-6 leading-tight">
                        আপনার কণ্ঠস্বর,<br>
                        <span class="text-bd-red">সরকার শুনছে</span>
                    </h1>

                    <p class="text-xl text-gray-300 mb-8 leading-relaxed">
                        নাগরিকদের অভিযোগ সরাসরি সিটি কর্পোরেশন, পুলিশ এবং সরকারী সংস্থার কাছে পৌঁছে দিন। 
                        স্বচ্ছতা, জবাবদিহিতা এবং দ্রুত সমাধানের জন্য আমরা প্রতিশ্রুতিবদ্ধ।
                    </p>

                    <div class="flex flex-wrap gap-4">
                        <a href="{{ route('register') }}" class="inline-flex items-center gap-2 bg-bd-red hover:bg-bd-red-dark text-white px-8 py-4 rounded-lg font-bold text-lg shadow-xl transition transform hover:scale-105">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            অভিযোগ দায়ের করুন
                        </a>
                        <a href="#transparency" class="inline-flex items-center gap-2 bg-white/10 hover:bg-white/20 text-white border-2 border-white/30 px-8 py-4 rounded-lg font-bold text-lg backdrop-blur-sm transition">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                            পরিসংখ্যান দেখুন
                        </a>
                    </div>
                </div>

                <div class="hidden md:block">
                    <div class="relative">
                        <div class="bg-white/10 backdrop-blur-lg rounded-2xl p-8 border border-white/20 shadow-2xl">
                            <div class="grid grid-cols-2 gap-6">
                                <div class="text-center">
                                    <div class="text-4xl font-bold text-bd-red mb-2">{{ number_format(\App\Models\Report::count()) }}</div>
                                    <div class="text-sm text-gray-300">মোট অভিযোগ</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-4xl font-bold text-success mb-2">{{ number_format(\App\Models\Report::where('status','resolved')->count()) }}</div>
                                    <div class="text-sm text-gray-300">সমাধান হয়েছে</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-4xl font-bold text-warning mb-2">{{ number_format(\App\Models\Report::where('status','in_progress')->count()) }}</div>
                                    <div class="text-sm text-gray-300">প্রক্রিয়াধীন</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-4xl font-bold text-info mb-2">{{ number_format(\App\Models\User::count()) }}</div>
                                    <div class="text-sm text-gray-300">নিবন্ধিত নাগরিক</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Government Partners --}}
    <section class="py-8 bg-white border-t border-b">
        <div class="max-w-7xl mx-auto px-4">
            <p class="text-center text-sm text-text-muted mb-4 font-semibold">সংযুক্ত সরকারী সংস্থাসমূহ</p>
            <div class="flex flex-wrap justify-center items-center gap-8 text-text-secondary font-medium text-sm">
                @foreach(config('app.govt_entities', []) as $entity)
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-bd-green" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        {{ $entity }}
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Features Section --}}
    <section id="features" class="py-20 bg-bg-secondary">
        <div class="max-w-7xl mx-auto px-4">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-govt-navy mb-4">আমাদের সেবাসমূহ</h2>
                <p class="text-lg text-text-secondary max-w-2xl mx-auto">
                    স্বচ্ছতা, জবাবদিহিতা এবং জনগণের সেবায় আমরা নিবেদিত
                </p>
            </div>

            <div class="grid md:grid-cols-3 gap-8">
                {{-- Feature 1 --}}
                <div class="card-govt p-8 text-center">
                    <div class="w-16 h-16 bg-bd-green/10 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-bd-green" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-govt-navy mb-3">অভিযোগ দায়ের</h3>
                    <p class="text-text-secondary mb-4">সড়ক ক্ষতি, বর্জ্য ব্যবস্থাপনা, পানি-বিদ্যুৎ সমস্যা সরাসরি রিপোর্ট করুন</p>
                    <a href="{{ route('reports.create') }}" class="text-bd-green font-semibold hover:underline">রিপোর্ট করুন →</a>
                </div>

                {{-- Feature 2 --}}
                <div class="card-govt p-8 text-center">
                    <div class="w-16 h-16 bg-govt-blue/10 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-govt-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-govt-navy mb-3">দুর্নীতি প্রতিরোধ</h3>
                    <p class="text-text-secondary mb-4">নিরাপদে এবং গোপনীয়ভাবে দুর্নীতির অভিযোগ দায়ের করুন</p>
                    <a href="#" class="text-bd-green font-semibold hover:underline">আরও জানুন →</a>
                </div>

                {{-- Feature 3 --}}
                <div class="card-govt p-8 text-center">
                    <div class="w-16 h-16 bg-bd-red/10 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-bd-red" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-govt-navy mb-3">আইনি সহায়তা</h3>
                    <p class="text-text-secondary mb-4">তথ্য অধিকার (RTI) এবং আইনি পরামর্শ পান</p>
                    <a href="{{ route('legal.rti.form') }}" class="text-bd-green font-semibold hover:underline">RTI দায়ের করুন →</a>
                </div>
            </div>
        </div>
    </section>

    {{-- Transparency Section --}}
    <section id="transparency" class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-govt-navy mb-4">স্বচ্ছতা ও জবাবদিহিতা</h2>
                <p class="text-lg text-text-secondary max-w-2xl mx-auto">
                    প্রতিটি অভিযোগের অগ্রগতি রিয়েল-টাইমে ট্র্যাক করুন
                </p>
            </div>

            <div class="grid md:grid-cols-2 gap-8 items-center">
                <div>
                    <img src="/images/transparency-illustration.svg" alt="Transparency" class="w-full" onerror="this.style.display='none'">
                    <div class="bg-gradient-to-br from-bd-green to-govt-navy rounded-2xl p-12 text-white text-center">
                        <svg class="w-24 h-24 mx-auto mb-6 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                        <h3 class="text-2xl font-bold mb-2">সম্পূর্ণ স্বচ্ছতা</h3>
                        <p>প্রতিটি পদক্ষেপ জনগণের জন্য উন্মুক্ত</p>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 bg-success/10 rounded-full flex items-center justify-center flex-shrink-0">
                            <svg class="w-6 h-6 text-success" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div>
                            <h4 class="font-bold text-govt-navy mb-2">রিয়েল-টাইম ট্র্যাকিং</h4>
                            <p class="text-text-secondary">আপনার অভিযোগের প্রতিটি আপডেট তাৎক্ষণিক জানুন</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 bg-info/10 rounded-full flex items-center justify-center flex-shrink-0">
                            <svg class="w-6 h-6 text-info" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z"/>
                            </svg>
                        </div>
                        <div>
                            <h4 class="font-bold text-govt-navy mb-2">উন্মুক্ত তথ্য</h4>
                            <p class="text-text-secondary">সকল পরিসংখ্যান এবং প্রতিবেদন জনসাধারণের জন্য</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 bg-warning/10 rounded-full flex items-center justify-center flex-shrink-0">
                            <svg class="w-6 h-6 text-warning" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div>
                            <h4 class="font-bold text-govt-navy mb-2">কর্মকর্তা জবাবদিহিতা</h4>
                            <p class="text-text-secondary">প্রতিটি কেস একজন নির্দিষ্ট কর্মকর্তার দায়িত্বে</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- CTA Section --}}
    <section class="py-20 bg-gradient-to-r from-bd-green to-govt-navy text-white">
        <div class="max-w-4xl mx-auto px-4 text-center">
            <h2 class="text-3xl md:text-4xl font-bold mb-6">আপনার কণ্ঠস্বর গুরুত্বপূর্ণ</h2>
            <p class="text-xl mb-8 text-gray-200">
                আজই যোগ দিন এবং আপনার এলাকায় পরিবর্তন আনুন। একসাথে আমরা একটি উন্নত বাংলাদেশ গড়তে পারি।
            </p>
            <a href="{{ route('register') }}" class="inline-flex items-center gap-2 bg-bd-red hover:bg-bd-red-dark text-white px-10 py-5 rounded-lg font-bold text-xl shadow-2xl transition transform hover:scale-105">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                </svg>
                এখনই নিবন্ধন করুন
            </a>
        </div>
    </section>

    {{-- Footer --}}
    <footer class="bg-govt-navy-dark text-gray-300 py-12">
        <div class="max-w-7xl mx-auto px-4">
            <div class="grid md:grid-cols-4 gap-8 mb-8">
                <div>
                    <h4 class="text-white font-bold mb-4">Chokh-e-Dekha</h4>
                    <p class="text-sm">জনগণের চোখ, সরকারের কান - একটি জাতীয় নাগরিক পোর্টাল</p>
                </div>
                <div>
                    <h4 class="text-white font-bold mb-4">দ্রুত লিঙ্ক</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="{{ route('reports.index') }}" class="hover:text-white">সকল অভিযোগ</a></li>
                        <li><a href="{{ route('legal.rti.form') }}" class="hover:text-white">RTI দায়ের</a></li>
                        <li><a href="/api/v1/surveys" class="hover:text-white">জরিপ</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-white font-bold mb-4">সরকারী সংস্থা</h4>
                    <ul class="space-y-2 text-sm">
                        @foreach(array_slice(config('app.govt_entities', []), 0, 4) as $entity)
                            <li>{{ $entity }}</li>
                        @endforeach
                    </ul>
                </div>
                <div>
                    <h4 class="text-white font-bold mb-4">যোগাযোগ</h4>
                    <p class="text-sm mb-2">📧 support@chokheDekha.gov.bd</p>
                    <p class="text-sm">📞 16xxx (Toll Free)</p>
                </div>
            </div>
            <div class="border-t border-gray-700 pt-8 text-center text-sm">
                <p>&copy; {{ date('Y') }} Government of Bangladesh. All rights reserved. | গণপ্রজাতন্ত্রী বাংলাদেশ সরকার</p>
        </div>
    </div>
    </footer>
</body>
</html>
