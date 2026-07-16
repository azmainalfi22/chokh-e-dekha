{{-- Language Switcher Component --}}
<div class="language-switcher">
    <form action="{{ route('language.switch') }}" method="POST" id="language-form">
        @csrf
        <input type="hidden" name="locale" id="locale-input" value="{{ app()->getLocale() }}">
        
        <button type="button" onclick="toggleLanguage()" class="flex items-center gap-2 px-3 py-2 rounded-lg bg-white/10 hover:bg-white/20 backdrop-blur-sm transition text-sm font-semibold">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"/>
            </svg>
            <span id="current-language">
                {{ app()->getLocale() === 'bn' ? 'EN' : 'বাং' }}
            </span>
        </button>
    </form>
</div>

<script>
function toggleLanguage() {
    const currentLocale = '{{ app()->getLocale() }}';
    const newLocale = currentLocale === 'en' ? 'bn' : 'en';
    document.getElementById('locale-input').value = newLocale;
    document.getElementById('language-form').submit();
}
</script>

<style>
.language-switcher button {
    min-width: 60px;
    justify-content: center;
}
</style>

