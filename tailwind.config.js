import { info } from 'autoprefixer';
import { Warning } from 'postcss';



/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
    "./app/Filament/**/*.php",
    "./vendor/relaticle/flowforge/resources/views/**/*.blade.php",
    "./vendor/filament/**/*.blade.php",
  ],
  theme: {
    extend: {
        colors: {
            danger: {
                50: 'var(--danger-50)',
                100: 'var(--danger-100)',
                200: 'var(--danger-200)',
                300: 'var(--danger-300)',
                400: 'var(--danger-400)',
                500: 'var(--danger-500)',
                600: 'var(--danger-600)',
                700: 'var(--danger-700)',
                800: 'var(--danger-800)',
                900: 'var(--danger-900)',
                950: 'var(--danger-950)',
            },
            gray: {
                50: 'var(--gray-50)',
                100: 'var(--gray-100)',
                200: 'var(--gray-200)',
                300: 'var(--gray-300)',
                400: 'var(--gray-400)',
                500: 'var(--gray-500)',
                600: 'var(--gray-600)',
                700: 'var(--gray-700)',
                800: 'var(--gray-800)',
                900: 'var(--gray-900)',
                950: 'var(--gray-950)',
            },
            info:{
                50: 'var(--info-50)',
                100: 'var(--info-100)',
                200: 'var(--info-200)',
                300: 'var(--info-300)',
                400: 'var(--info-400)',
                500: 'var(--info-500)',
                600: 'var(--info-600)',
                700: 'var(--info-700)',
                800: 'var(--info-800)',
                900: 'var(--info-900)',
                950: 'var(--info-950)',
            },
            primary: {
                50: 'var(--primary-50)',
                100: 'var(--primary-100)',
                200: 'var(--primary-200)',
                300: 'var(--primary-300)',
                400: 'var(--primary-400)',
                500: 'var(--primary-500)',
                600: 'var(--primary-600)',
                700: 'var(--primary-700)',
                800: 'var(--primary-800)',
                900: 'var(--primary-900)',
                950: 'var(--primary-950)',
            },
            success: {
                50: 'var(--success-50)',
                100: 'var(--success-100)',
                200: 'var(--success-200)',
                300: 'var(--success-300)',
                400: 'var(--success-400)',
                500: 'var(--success-500)',
                600: 'var(--success-600)',
                700: 'var(--success-700)',
                800: 'var(--success-800)',
                900: 'var(--success-900)',
                950: 'var(--success-950)',
            },
            warning: {
                50: 'var(--warning-50)',
                100: 'var(--warning-100)',
                200: 'var(--warning-200)',
                300: 'var(--warning-300)',
                400: 'var(--warning-400)',
                500: 'var(--warning-500)',
                600: 'var(--warning-600)',
                700: 'var(--warning-700)',
                800: 'var(--warning-800)',
                900: 'var(--warning-900)',
                950: 'var(--warning-950)',
            },

        },
    },
  },
  plugins: [],
}

