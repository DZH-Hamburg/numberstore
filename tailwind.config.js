import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Poppins', ...defaultTheme.fontFamily.sans],
            },
            fontSize: {
                opta: ['9pt', { lineHeight: '1.5' }],
            },
            colors: {
                opta: {
                    teal: {
                        dark: '#2f6e80',
                        light: '#77c5b8',
                    },
                    lime: '#a0c341',
                    green: '#6bba82',
                    sky: '#50b1d1',
                    periwinkle: '#7391c8',
                    grey: '#4a4a49',
                    berry: '#ce234e',
                },
            },
        },
    },

    plugins: [forms],
};
