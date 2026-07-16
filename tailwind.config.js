const defaultTheme = require('tailwindcss/defaultTheme')
const forms = require('@tailwindcss/forms')

module.exports = {
  darkMode: 'class',
  content: [
    './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
    './storage/framework/views/*.php',
    './resources/views/**/*.blade.php',
    './resources/js/**/*.vue',
    './resources/js/**/*.js',
  ],
  theme: {
    extend: {
      fontFamily: {
        sans: ['Figtree', ...defaultTheme.fontFamily.sans],
      },
      colors: {
        'bd-green': '#059669',
        'bd-green-dark': '#047857',
        'bd-green-light': '#10b981',
        'bd-red': '#dc2626',
        'bd-gold': '#f59e0b',
      },
    },
  },
  plugins: [forms],
}
