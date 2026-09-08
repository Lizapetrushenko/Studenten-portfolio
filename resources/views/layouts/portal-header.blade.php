<header class="portal-header-wrapper bg-white shadow" x-data="{ showDeleteModal: false, formToSubmit: null }">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
        <div class="flex justify-between items-center">
            <!-- Logo Section -->
            <div class="shrink-0 flex items-center">
                <img src="/images/deltionlogo.webp" alt="Deltion Logo" class="h-10 w-auto object-contain">
            </div>

            <!-- Title Section -->
            <div class="flex flex-col items-center">
                <h1 class="text-xl font-bold text-gray-900 ">{{ $headerTitle ?? 'Portfolio aanmaken' }}</h1>
            </div>

            <!-- Right Section: Links and Dropdown -->
            <div class="flex items-center gap-6">
                <a href="#" class="text-gray-600 hover:text-gray-900 flex items-center gap-2">
                    <a href="https://xerte.deltion.nl/play.php?template_id=11228#page1" target="_blank" class=" text-xs text-blue-600 hover:text-blue-800">Meer over portfolio </a>
                    
                </a>

                <!-- User Dropdown -->
                <div class="hidden sm:flex sm:items-center">
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center px-3 py-2 border border-transparent text-lg leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                                <div>{{ Auth::user()->name }}</div>
                                <div class="ms-1">
                                    <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <x-dropdown-link :href="route('profile.edit')">
                                Profiel
                            </x-dropdown-link>
                            <x-dropdown-link :href="route('mfa.setup')">MFA instellen</x-dropdown-link>

                            @if(Auth::user()->portfolios()->exists())
                                <form method="POST" action="{{ route('portfolio.destroy', Auth::user()->portfolios()->first()) }}" id="deletePortfolioFormHeader">
                                    @csrf
                                    @method('DELETE')
                                        <button type="button"
                                            @click.stop="showDeleteModal = true; formToSubmit = $el.closest('form')"
                                            class="block w-full text-start px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition">
                                        Portfolio verwijderen
                                    </button>
                                </form>
                            @endif

                            <!-- Authentication -->
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-dropdown-link :href="route('logout')"
                                        onclick="event.preventDefault();
                                                    this.closest('form').submit();">
                                    Uitloggen
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Portfolio Confirmation Modal -->
    <div x-cloak x-show="showDeleteModal" style="display: none;"
         @keydown.escape="showDeleteModal = false"
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm transition-opacity"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="showDeleteModal = false">
        
        <div @click.stop class="bg-white rounded-2xl shadow-2xl max-w-md w-full mx-4 overflow-hidden transform transition-all"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="scale-90 opacity-0 -translate-y-4"
             x-transition:enter-end="scale-100 opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="scale-100 opacity-100 translate-y-0"
             x-transition:leave-end="scale-90 opacity-0 -translate-y-4">
            
            <!-- Modal Header -->
            <div class="bg-gradient-to-br from-red-50 to-red-100/50 px-6 py-6 border-b border-red-200">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-red-100 rounded-lg">
                        <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4v2m0 0H8m4 0h4m-2-2h2m0 0V5a2 2 0 00-2-2h-2a2 2 0 00-2 2v14a2 2 0 002 2h2a2 2 0 002-2z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900">Portfolio verwijderen?</h3>
                </div>
            </div>

            <!-- Modal Content -->
            <div class="p-6">
                <p class="text-gray-600 mb-4">
                    Weet je zeker dat je je portfolio wilt verwijderen?
                </p>
                <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                    <p class="text-red-800 text-sm font-medium">
                        ⚠️ Dit kan niet ongedaan gemaakt worden. Je kunt dan wel een nieuw portfolio aanmaken.
                    </p>
                </div>

                <!-- Buttons -->
                <div class="flex gap-3">
                    <button @click="showDeleteModal = false"
                            type="button"
                            class="flex-1 px-4 py-3 bg-gray-100 text-gray-800 rounded-lg hover:bg-gray-200 font-semibold transition-all duration-200 active:scale-95">
                        Annuleren
                    </button>
                    <button @click="formToSubmit && formToSubmit.submit(); showDeleteModal = false"
                            type="button"
                            class="flex-1 px-4 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 font-semibold transition-all duration-200 active:scale-95 shadow-lg hover:shadow-xl">
                        Verwijderen
                    </button>
                </div>
            </div>
        </div>
    </div>
</header>
