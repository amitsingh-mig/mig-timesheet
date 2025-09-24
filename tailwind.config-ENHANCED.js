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
                
                // Neutral/Gray Scale (Enhanced)
                gray: {
                    50: '#f8fafc',
                    100: '#f1f5f9',
                    200: '#e2e8f0',
                    300: '#cbd5e1',
                    400: '#94a3b8',
                    500: '#64748b',
                    600: '#475569',
                    700: '#334155',
                    800: '#1e293b',
                    900: '#0f172a',
                    950: '#020617',
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
                'xs': ['0.75rem', { lineHeight: '1rem' }],
                'sm': ['0.875rem', { lineHeight: '1.25rem' }],
                'base': ['1rem', { lineHeight: '1.5rem' }],
                'lg': ['1.125rem', { lineHeight: '1.75rem' }],
                'xl': ['1.25rem', { lineHeight: '1.75rem' }],
                '2xl': ['1.5rem', { lineHeight: '2rem' }],
                '3xl': ['1.875rem', { lineHeight: '2.25rem' }],
                '4xl': ['2.25rem', { lineHeight: '2.5rem' }],
                '5xl': ['3rem', { lineHeight: '1' }],
                '6xl': ['3.75rem', { lineHeight: '1' }],
                '7xl': ['4.5rem', { lineHeight: '1' }],
                '8xl': ['6rem', { lineHeight: '1' }],
                '9xl': ['8rem', { lineHeight: '1' }],
            },
            
            // Custom Border Radius
            borderRadius: {
                '4xl': '2rem',
                '5xl': '2.5rem',
            },
            
            // Custom Box Shadows
            boxShadow: {
                'xs': '0 1px 2px 0 rgba(0, 0, 0, 0.05)',
                'sm': '0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px -1px rgba(0, 0, 0, 0.1)',
                'md': '0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -2px rgba(0, 0, 0, 0.1)',
                'lg': '0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -4px rgba(0, 0, 0, 0.1)',
                'xl': '0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1)',
                '2xl': '0 25px 50px -12px rgba(0, 0, 0, 0.25)',
                'inner': 'inset 0 2px 4px 0 rgba(0, 0, 0, 0.05)',
                'elevated': '0 8px 30px rgba(0, 0, 0, 0.12)',
                'focus': '0 0 0 3px rgba(59, 130, 246, 0.1)',
                'focus-admin': '0 0 0 3px rgba(220, 38, 38, 0.1)',
                'focus-employee': '0 0 0 3px rgba(5, 150, 105, 0.1)',
            },
            
            // Custom Animation
            animation: {
                'fade-in': 'fadeIn 0.5s ease-in-out',
                'fade-out': 'fadeOut 0.5s ease-in-out',
                'slide-in-right': 'slideInRight 0.3s ease-out',
                'slide-in-left': 'slideInLeft 0.3s ease-out',
                'slide-up': 'slideUp 0.3s ease-out',
                'bounce-gentle': 'bounceGentle 2s infinite',
                'pulse-soft': 'pulseSoft 2s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                'spin-slow': 'spin 3s linear infinite',
                'wiggle': 'wiggle 1s ease-in-out infinite',
            },
            
            // Custom Keyframes
            keyframes: {
                fadeIn: {
                    '0%': { opacity: '0' },
                    '100%': { opacity: '1' },
                },
                fadeOut: {
                    '0%': { opacity: '1' },
                    '100%': { opacity: '0' },
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
                bounceGentle: {
                    '0%, 100%': { transform: 'translateY(-5%)', animationTimingFunction: 'cubic-bezier(0.8, 0, 1, 1)' },
                    '50%': { transform: 'translateY(0)', animationTimingFunction: 'cubic-bezier(0, 0, 0.2, 1)' },
                },
                pulseSoft: {
                    '0%, 100%': { opacity: '1' },
                    '50%': { opacity: '0.8' },
                },
                wiggle: {
                    '0%, 100%': { transform: 'rotate(-3deg)' },
                    '50%': { transform: 'rotate(3deg)' },
                },
            },
            
            // Custom Transitions
            transitionDuration: {
                '0': '0ms',
                '75': '75ms',
                '100': '100ms',
                '150': '150ms',
                '200': '200ms',
                '300': '300ms',
                '500': '500ms',
                '700': '700ms',
                '1000': '1000ms',
            },
            
            // Grid Template Columns
            gridTemplateColumns: {
                '13': 'repeat(13, minmax(0, 1fr))',
                '14': 'repeat(14, minmax(0, 1fr))',
                '15': 'repeat(15, minmax(0, 1fr))',
                '16': 'repeat(16, minmax(0, 1fr))',
                'sidebar': '250px 1fr',
                'sidebar-collapsed': '70px 1fr',
                'dashboard': '1fr 300px',
                'card-grid': 'repeat(auto-fit, minmax(300px, 1fr))',
            },
            
            // Backdrop Blur
            backdropBlur: {
                xs: '2px',
            },
        },
    },

    // Custom Plugins
    plugins: [
        forms,
        
        // Custom Plugin for Component Variants
        function({ addComponents, theme }) {
            addComponents({
                // Button Components
                '.btn-primary': {
                    backgroundColor: theme('colors.blue.600'),
                    color: theme('colors.white'),
                    padding: `${theme('spacing.2')} ${theme('spacing.4')}`,
                    borderRadius: theme('borderRadius.md'),
                    fontWeight: theme('fontWeight.medium'),
                    transition: 'all 0.2s',
                    '&:hover': {
                        backgroundColor: theme('colors.blue.700'),
                        transform: 'translateY(-1px)',
                        boxShadow: theme('boxShadow.lg'),
                    },
                    '&:focus': {
                        outline: 'none',
                        boxShadow: theme('boxShadow.focus'),
                    },
                    '&:disabled': {
                        opacity: '0.5',
                        cursor: 'not-allowed',
                        transform: 'none',
                    },
                },
                
                '.btn-admin': {
                    backgroundColor: theme('colors.admin.600'),
                    color: theme('colors.white'),
                    padding: `${theme('spacing.2')} ${theme('spacing.4')}`,
                    borderRadius: theme('borderRadius.md'),
                    fontWeight: theme('fontWeight.medium'),
                    transition: 'all 0.2s',
                    '&:hover': {
                        backgroundColor: theme('colors.admin.700'),
                        transform: 'translateY(-1px)',
                        boxShadow: theme('boxShadow.lg'),
                    },
                    '&:focus': {
                        outline: 'none',
                        boxShadow: theme('boxShadow.focus-admin'),
                    },
                },
                
                '.btn-employee': {
                    backgroundColor: theme('colors.employee.600'),
                    color: theme('colors.white'),
                    padding: `${theme('spacing.2')} ${theme('spacing.4')}`,
                    borderRadius: theme('borderRadius.md'),
                    fontWeight: theme('fontWeight.medium'),
                    transition: 'all 0.2s',
                    '&:hover': {
                        backgroundColor: theme('colors.employee.700'),
                        transform: 'translateY(-1px)',
                        boxShadow: theme('boxShadow.lg'),
                    },
                    '&:focus': {
                        outline: 'none',
                        boxShadow: theme('boxShadow.focus-employee'),
                    },
                },
                
                // Card Components
                '.card': {
                    backgroundColor: theme('colors.white'),
                    borderRadius: theme('borderRadius.lg'),
                    boxShadow: theme('boxShadow.md'),
                    padding: theme('spacing.6'),
                    border: `1px solid ${theme('colors.gray.200')}`,
                },
                
                '.card-elevated': {
                    backgroundColor: theme('colors.white'),
                    borderRadius: theme('borderRadius.lg'),
                    boxShadow: theme('boxShadow.elevated'),
                    padding: theme('spacing.6'),
                    border: `1px solid ${theme('colors.gray.100')}`,
                    transition: 'all 0.3s ease',
                    '&:hover': {
                        transform: 'translateY(-2px)',
                        boxShadow: theme('boxShadow.xl'),
                    },
                },
                
                // Form Components
                '.form-input': {
                    appearance: 'none',
                    backgroundColor: theme('colors.white'),
                    borderColor: theme('colors.gray.300'),
                    borderWidth: '1px',
                    borderRadius: theme('borderRadius.md'),
                    padding: `${theme('spacing.2')} ${theme('spacing.3')}`,
                    fontSize: theme('fontSize.sm'),
                    lineHeight: theme('lineHeight.5'),
                    '&:focus': {
                        outline: 'none',
                        borderColor: theme('colors.blue.500'),
                        boxShadow: theme('boxShadow.focus'),
                    },
                    '&[aria-invalid="true"]': {
                        borderColor: theme('colors.red.500'),
                        '&:focus': {
                            borderColor: theme('colors.red.500'),
                            boxShadow: '0 0 0 3px rgba(239, 68, 68, 0.1)',
                        },
                    },
                },
                
                // Status Badges
                '.badge': {
                    display: 'inline-flex',
                    alignItems: 'center',
                    padding: `${theme('spacing.1')} ${theme('spacing.2')}`,
                    borderRadius: theme('borderRadius.full'),
                    fontSize: theme('fontSize.xs'),
                    fontWeight: theme('fontWeight.medium'),
                    textTransform: 'uppercase',
                    letterSpacing: theme('letterSpacing.wider'),
                },
                
                '.badge-success': {
                    backgroundColor: theme('colors.success.50'),
                    color: theme('colors.success.600'),
                    border: `1px solid ${theme('colors.success.200')}`,
                },
                
                '.badge-warning': {
                    backgroundColor: theme('colors.warning.50'),
                    color: theme('colors.warning.600'),
                    border: `1px solid ${theme('colors.warning.200')}`,
                },
                
                '.badge-error': {
                    backgroundColor: theme('colors.error.50'),
                    color: theme('colors.error.600'),
                    border: `1px solid ${theme('colors.error.200')}`,
                },
                
                '.badge-info': {
                    backgroundColor: theme('colors.info.50'),
                    color: theme('colors.info.600'),
                    border: `1px solid ${theme('colors.info.200')}`,
                },
                
                // Loading States
                '.loading-overlay': {
                    position: 'absolute',
                    top: '0',
                    left: '0',
                    right: '0',
                    bottom: '0',
                    backgroundColor: 'rgba(255, 255, 255, 0.8)',
                    display: 'flex',
                    alignItems: 'center',
                    justifyContent: 'center',
                    zIndex: '50',
                },
                
                // Sidebar Components
                '.sidebar-link': {
                    display: 'flex',
                    alignItems: 'center',
                    padding: `${theme('spacing.3')} ${theme('spacing.4')}`,
                    marginBottom: theme('spacing.1'),
                    borderRadius: theme('borderRadius.md'),
                    fontSize: theme('fontSize.sm'),
                    fontWeight: theme('fontWeight.medium'),
                    color: theme('colors.gray.700'),
                    textDecoration: 'none',
                    transition: 'all 0.2s',
                    '&:hover': {
                        backgroundColor: theme('colors.gray.100'),
                        color: theme('colors.gray.900'),
                    },
                    '&.active': {
                        backgroundColor: theme('colors.blue.100'),
                        color: theme('colors.blue.700'),
                    },
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
                '.not-sr-only': {
                    position: 'static',
                    width: 'auto',
                    height: 'auto',
                    padding: '0',
                    margin: '0',
                    overflow: 'visible',
                    clip: 'auto',
                    whiteSpace: 'normal',
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
            });
        },
    ],

    // Dark Mode Support
    darkMode: 'class',

    // Safelist for Dynamic Classes
    safelist: [
        // Color variations that might be generated dynamically
        'text-admin-600',
        'text-employee-600',
        'text-manager-600',
        'text-hr-600',
        'bg-admin-100',
        'bg-employee-100',
        'bg-manager-100',
        'bg-hr-100',
        'bg-admin-600',
        'bg-employee-600',
        'bg-manager-600',
        'bg-hr-600',
        'border-admin-200',
        'border-employee-200',
        'border-manager-200',
        'border-hr-200',
        
        // Animation classes
        'animate-fade-in',
        'animate-slide-in-right',
        'animate-slide-in-left',
        'animate-slide-up',
        
        // Dynamic grid classes
        'grid-cols-1',
        'grid-cols-2',
        'grid-cols-3',
        'grid-cols-4',
        'grid-cols-5',
        'grid-cols-6',
        
        // Responsive visibility
        'hidden',
        'block',
        'sm:hidden',
        'sm:block',
        'md:hidden',
        'md:block',
        'lg:hidden',
        'lg:block',
        
        // Status badges
        'badge-success',
        'badge-warning',
        'badge-error',
        'badge-info',
    ],
};