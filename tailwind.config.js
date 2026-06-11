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

    safelist: [
        {
            pattern: /^(bg|text|border|ring)-(green|blue|yellow|red|orange|pink|gray)-(50|100|200|300|400|500|600|700|800|900)$/,
            variants: ['dark'],
        },
        {
            pattern: /^(bg|text|border|ring)-(green|blue|yellow|red|orange|pink|gray)-(50|100|200|300|400|500|600|700|800|900)$/,
            variants: ['hover', 'dark'],
        },
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
            },
        },
    },

    plugins: [forms],
};
