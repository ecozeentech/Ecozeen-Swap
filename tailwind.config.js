import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',

    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                brand: {
                    50: '#eaf5ef',
                    100: '#cfe8da',
                    200: '#a1d1b7',
                    300: '#72b992',
                    400: '#4a9c73',
                    500: '#2d6a4f', // primary brand green
                    600: '#255a42',
                    700: '#1e4a37',
                    800: '#173a2b',
                    900: '#102a1f',
                    950: '#081b14',
                },
                charcoal: {
                    50: '#f4f5f6',
                    100: '#e5e7ea',
                    200: '#c6cad1',
                    300: '#a1a8b3',
                    400: '#71798a',
                    500: '#4c5566',
                    600: '#39404e',
                    700: '#2a2f3a',
                    800: '#1c1f27',
                    900: '#121419',
                    950: '#0a0b0e',
                },
            },
        },
    },

    plugins: [forms],
};
