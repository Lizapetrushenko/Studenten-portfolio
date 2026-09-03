<header class="portal-header-wrapper bg-white shadow">
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
</header>
