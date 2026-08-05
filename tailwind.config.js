/** @type {import('tailwindcss').Config} */
// ponytail: dark "hub" theme via palette remap — every existing utility class in the
// app flips to dark without touching each page. Shades keep their ROLE (50=subtle bg,
// 500/600=strong, 700=hover, 800/900=emphasis text); values are dark-theme equivalents.
// indigo-* is now the warm amber accent. bg-white is overridden in app.css.
export default {
    content: [
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.vue',
    ],
    theme: {
        extend: {
            colors: {
                gray: {
                    50: '#232327',
                    100: '#28282d',
                    200: '#34343b',
                    300: '#46464f',
                    400: '#70707c',
                    500: '#97a1ad',
                    600: '#aeb8c4',
                    700: '#cbd5df',
                    800: '#e2e8ee',
                    900: '#f2f6fa',
                },
                indigo: {
                    50: '#2b2115',
                    100: '#3b2b16',
                    200: '#b07a2e',
                    300: '#a07c3c',
                    400: '#f0a63a',
                    500: '#efa02c',
                    600: '#e08316',
                    700: '#f2a339',
                    800: '#f5c26e',
                    900: '#151515',
                },
                red: {
                    50: '#2a1416',
                    100: '#3b1a1d',
                    200: '#5f2328',
                    300: '#8c3136',
                    400: '#e5484d',
                    500: '#ec5d61',
                    600: '#e5484d',
                    700: '#f0797d',
                    800: '#f4a9ab',
                },
                green: {
                    50: '#13271b',
                    100: '#1d3b28',
                    200: '#2f5a3e',
                    500: '#46a758',
                    600: '#55b467',
                    700: '#71c485',
                    800: '#9adfae',
                },
                amber: {
                    50: '#2b2214',
                    100: '#3b2e17',
                    200: '#5a4520',
                    300: '#6b5426',
                    400: '#f0a63a',
                    500: '#f0a63a',
                    600: '#f5b45a',
                    700: '#f6c37b',
                    800: '#f8d093',
                    900: '#fadfae',
                },
                sky: {
                    50: '#12242f',
                    100: '#173141',
                    500: '#4fa9d8',
                    600: '#63b5de',
                    800: '#a6d6ee',
                },
                blue: {
                    100: '#173141',
                    700: '#82bce4',
                },
                purple: {
                    100: '#2b2036',
                    700: '#c9a2e4',
                },
                emerald: {
                    600: '#4cc38a',
                    800: '#98dfbc',
                },
            },
        },
    },
    plugins: [],
};
