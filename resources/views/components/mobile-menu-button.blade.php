{{-- 
    Mobile Menu Button Component
    
    Usage: <x-mobile-menu-button />
    
    Features:
    - Animated hamburger to X icon
    - Screen reader accessible
    - Integrates with Alpine.js sidebar state
--}}

<button 
    @click="$dispatch('toggle-sidebar')"
    type="button" 
    class="lg:hidden inline-flex items-center justify-center p-2 rounded-md text-gray-600 hover:text-gray-900 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500 transition-colors duration-200"
    aria-controls="mobile-menu"
    aria-expanded="false"
    x-data="{ open: false }"
    @toggle-sidebar.window="open = !open"
>
    <span class="sr-only">Open main menu</span>
    
    {{-- Hamburger Icon --}}
    <svg 
        class="h-6 w-6 transition-transform duration-300" 
        :class="{ 'rotate-90': open }"
        xmlns="http://www.w3.org/2000/svg" 
        fill="none" 
        viewBox="0 0 24 24" 
        stroke="currentColor" 
        aria-hidden="true"
    >
        {{-- Top line --}}
        <path 
            stroke-linecap="round" 
            stroke-linejoin="round" 
            stroke-width="2" 
            :d="open ? 'M6 18L18 6' : 'M4 6h16'"
            class="transition-all duration-300"
        />
        
        {{-- Middle line --}}
        <path 
            stroke-linecap="round" 
            stroke-linejoin="round" 
            stroke-width="2" 
            d="M4 12h16"
            :class="{ 'opacity-0': open }"
            class="transition-opacity duration-300"
        />
        
        {{-- Bottom line --}}
        <path 
            stroke-linecap="round" 
            stroke-linejoin="round" 
            stroke-width="2" 
            :d="open ? 'M6 6l12 12' : 'M4 18h16'"
            class="transition-all duration-300"
        />
    </svg>
</button>

{{-- Alternative Bootstrap Icons Version --}}
<button 
    @click="$dispatch('toggle-sidebar')"
    type="button" 
    class="lg:hidden inline-flex items-center justify-center p-2 rounded-md text-gray-600 hover:text-gray-900 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500 transition-all duration-200 ml-2"
    aria-controls="mobile-menu"
    aria-expanded="false"
    x-data="{ open: false }"
    @toggle-sidebar.window="open = !open"
>
    <span class="sr-only">Toggle navigation menu</span>
    
    {{-- Bootstrap Icons Version --}}
    <i 
        :class="open ? 'bi-x-lg' : 'bi-list'"
        class="text-xl transition-all duration-300"
        aria-hidden="true"
    ></i>
</button>

<style>
/* Custom animation for smoother transitions */
.mobile-menu-btn {
    --tw-ring-color: theme('colors.indigo.500');
}

.mobile-menu-btn:focus {
    --tw-ring-opacity: 0.5;
}

/* Ensure proper stacking for mobile overlay */
@media (max-width: 1023px) {
    .alaga-sidebar {
        z-index: 50;
    }
    
    .mobile-menu-overlay {
        z-index: 40;
    }
}
</style>