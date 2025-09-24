import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
        './resources/js/**/*.js',
    ],

    theme: {
        extend: {
            // Custom Fonts
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            
            // Brand Colors with Role-Based Theming
            colors: {
                // Admin Theme - Red/Orange Based
                admin: {
                    50: '#fef2f2',
                    100: '#fee2e2',
                    200: '#fecaca',
                    300: '#fca5a5',
                    400: '#f87171',
                    500: '#ef4444',
                    600: '#dc2626',
                    700: '#b91c1c',
                    800: '#991b1b',
                    900: '#7f1d1d',
                    950: '#450a0a',
                },
                
                // Employee Theme - Green Based
                employee: {
                    50: '#ecfdf5',
                    100: '#d1fae5',
                    200: '#a7f3d0',
                    300: '#6ee7b7',
                    400: '#34d399',
                    500: '#10b981',
                    600: '#059669',
                    700: '#047857',
                    800: '#065f46',
                    900: '#064e3b',
                    950: '#022c22',
                },
                
                // Manager Theme - Blue Based
                manager: {
                    50: '#eff6ff',
                    100: '#dbeafe',
                    200: '#bfdbfe',
                    300: '#93c5fd',
                    400: '#60a5fa',
                    500: '#3b82f6',
                    600: '#2563eb',
                    700: '#1d4ed8',
                    800: '#1e40af',
                    900: '#1e3a8a',
                    950: '#172554',
                },
                
                // HR Theme - Purple Based
                hr: {
                    50: '#faf5ff',
                    100: '#f3e8ff',
                    200: '#e9d5ff',
                    300: '#d8b4fe',
                    400: '#c084fc',
                    500: '#a855f7',
                    600: '#9333ea',
                    700: '#7c3aed',
                    800: '#6b21a8',
                    900: '#581c87',
                    950: '#3b0764',
                },
                
                // Enhanced gradients mapped to existing color patterns
                'gradient-1': {
                    'start': '#667eea',
                    'end': '#764ba2',
                },
                'gradient-2': {
                    'start': '#f093fb',
                    'end': '#f5576c',
                },
                'gradient-3': {
                    'start': '#4facfe',
                    'end': '#00f2fe',
                },
                'gradient-4': {
                    'start': '#43e97b',
                    'end': '#38f9d7',
                },
                'gradient-5': {
                    'start': '#fa709a',
                    'end': '#fee140',
                },
                'gradient-admin': {
                    'start': '#4e54c8',
                    'end': '#8f94fb',
                },
                
                // Status Colors
                success: {
                    50: '#ecfdf5',
                    500: '#10b981',
                    600: '#059669',
                },
                warning: {
                    50: '#fffbeb',
                    500: '#f59e0b',
                    600: '#d97706',
                },
                error: {
                    50: '#fef2f2',
                    500: '#ef4444',
                    600: '#dc2626',
                },
                info: {
                    50: '#eff6ff',
                    500: '#3b82f6',
                    600: '#2563eb',
                },
            },
            
            // Custom Spacing
            spacing: {
                '18': '4.5rem',
                '22': '5.5rem',
                '26': '6.5rem',
                '30': '7.5rem',
                '88': '22rem',
                '92': '23rem',
                '96': '24rem',
                '100': '25rem',
                '104': '26rem',
                '108': '27rem',
                '112': '28rem',
            },
            
            // Custom Typography
            fontSize: {
                '2xs': ['0.625rem', { lineHeight: '0.75rem' }],
            },
            
            // Custom Border Radius
            borderRadius: {
                '4xl': '2rem',
                '5xl': '2.5rem',
            },
            
            // Custom Box Shadows
            boxShadow: {
                'elevated': '0 8px 30px rgba(0, 0, 0, 0.12)',
                'focus': '0 0 0 3px rgba(59, 130, 246, 0.1)',
                'focus-admin': '0 0 0 3px rgba(220, 38, 38, 0.1)',
                'focus-employee': '0 0 0 3px rgba(5, 150, 105, 0.1)',
            },
            
            // Custom Animation
            animation: {
                'fade-in': 'fadeIn 0.5s ease-in-out',
                'slide-in-right': 'slideInRight 0.3s ease-out',
                'slide-in-left': 'slideInLeft 0.3s ease-out',
                'slide-up': 'slideUp 0.3s ease-out',
                'pulse-soft': 'pulseSoft 2s cubic-bezier(0.4, 0, 0.6, 1) infinite',
            },
            
            // Custom Keyframes
            keyframes: {
                fadeIn: {
                    '0%': { opacity: '0' },
                    '100%': { opacity: '1' },
                },
                slideInRight: {
                    '0%': { transform: 'translateX(100%)', opacity: '0' },
                    '100%': { transform: 'translateX(0)', opacity: '1' },
                },
                slideInLeft: {
                    '0%': { transform: 'translateX(-100%)', opacity: '0' },
                    '100%': { transform: 'translateX(0)', opacity: '1' },
                },
                slideUp: {
                    '0%': { transform: 'translateY(100%)', opacity: '0' },
                    '100%': { transform: 'translateY(0)', opacity: '1' },
                },
                pulseSoft: {
                    '0%, 100%': { opacity: '1' },
                    '50%': { opacity: '0.8' },
                },
            },
            
            // Grid Template Columns
            gridTemplateColumns: {
                'sidebar': '250px 1fr',
                'sidebar-collapsed': '70px 1fr',
                'card-grid': 'repeat(auto-fit, minmax(300px, 1fr))',
            },
        },
    },

    plugins: [
        forms,
        
        // Custom Plugin for Component Variants
        function({ addComponents, theme }) {
            addComponents({
                // Gradient Backgrounds (replacing inline styles)
                '.bg-gradient-1': {
                    background: 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
                },
                '.bg-gradient-2': {
                    background: 'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)',
                },
                '.bg-gradient-3': {
                    background: 'linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)',
                },
                '.bg-gradient-4': {
                    background: 'linear-gradient(135deg, #43e97b 0%, #38f9d7 100%)',
                },
                '.bg-gradient-5': {
                    background: 'linear-gradient(135deg, #fa709a 0%, #fee140 100%)',
                },
                '.bg-gradient-admin': {
                    background: 'linear-gradient(90deg, #4e54c8, #8f94fb)',
                },
                '.bg-gradient-admin-green': {
                    background: 'linear-gradient(90deg, #28a745, #20c997)',
                },
                '.bg-gradient-vertical': {
                    background: 'linear-gradient(180deg, #4e54c8 0%, #8f94fb 100%)',
                },
                
                // Card Components
                '.card-gradient': {
                    borderRadius: theme('borderRadius.lg'),
                    boxShadow: theme('boxShadow.sm'),
                    transition: 'all 0.3s ease',
                    color: 'white',
                    '&:hover': {
                        transform: 'translateY(-2px)',
                        boxShadow: theme('boxShadow.lg'),
                    },
                },
                
                // Icon Containers
                '.icon-circle': {
                    width: '40px',
                    height: '40px',
                    borderRadius: '50%',
                    display: 'flex',
                    alignItems: 'center',
                    justifyContent: 'center',
                },
                '.icon-circle-lg': {
                    width: '10px',
                    height: '10px',
                    borderRadius: '50%',
                },
                
                // Progress Bars
                '.progress-custom': {
                    height: '12px',
                    borderRadius: '10px',
                    background: theme('colors.gray.200'),
                },
                '.progress-bar-custom': {
                    background: 'linear-gradient(90deg, #4e54c8, #8f94fb)',
                    borderRadius: '10px',
                    transition: 'width 0.6s ease',
                },
                
                // Container Heights
                '.h-96': {
                    height: '400px',
                },
                '.h-72': {
                    height: '300px',
                },
                '.h-30': {
                    height: '120px',
                },
                '.h-38': {
                    height: '150px',
                },
                
                // Text Sizes
                '.text-2_5xl': {
                    fontSize: '2.5rem',
                    opacity: '0.8',
                },
                '.text-display-4': {
                    fontSize: '2.5rem',
                    fontWeight: theme('fontWeight.bold'),
                },
                '.text-tiny': {
                    fontSize: '0.75rem',
                    padding: '6px 10px',
                },
                '.text-xs-custom': {
                    fontSize: '0.85rem',
                },
            });
        },
        
        // Accessibility Plugin
        function({ addUtilities }) {
            addUtilities({
                '.sr-only': {
                    position: 'absolute',
                    width: '1px',
                    height: '1px',
                    padding: '0',
                    margin: '-1px',
                    overflow: 'hidden',
                    clip: 'rect(0, 0, 0, 0)',
                    whiteSpace: 'nowrap',
                    border: '0',
                },
                '.focus-visible-only': {
                    '&:not(:focus-visible)': {
                        position: 'absolute',
                        width: '1px',
                        height: '1px',
                        padding: '0',
                        margin: '-1px',
                        overflow: 'hidden',
                        clip: 'rect(0, 0, 0, 0)',
                        whiteSpace: 'nowrap',
                        border: '0',
                    },
                },
                '.max-h-72': {
                    maxHeight: '300px',
                },
                '.max-h-38': {
                    maxHeight: '150px',
                },
                '.max-w-50': {
                    maxWidth: '200px',
                },
                '.max-w-55': {
                    maxWidth: '220px',
                },
            });
        },
    ],

    darkMode: 'class',
}
