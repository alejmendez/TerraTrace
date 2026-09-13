/** @type {import('tailwindcss').Config} */
import defaultTheme from 'tailwindcss/defaultTheme';

export default {
  darkMode: ['class'],
  safelist: ['dark'],
  prefix: '',
  content: [
    './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
    './storage/framework/views/*.php',
    './resources/views/**/*.blade.php',
    './resources/js/**/*.{js,jsx,vue}',
    './Modules/**/*.{js,jsx,vue}',
  ],
  theme: {
    screens: {
      ...defaultTheme.screens,
      'sm': '375px',
    },
    extend: {
      transitionDuration: {
        '400': '400ms',
      },
    },
  },
  plugins: [],
};
