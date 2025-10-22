<!-- Sidebar -->
<div x-data="{ mobileOpen: false, minimized: true, individualOpen: false, generalOpen: false }" x-init="$dispatch('sidebar-toggled', { minimized: minimized })" @sidebar-toggled.window="minimized = $event.detail.minimized">

    <!-- Mobile Toggle Button -->
    <button @click="mobileOpen = !mobileOpen"
        class="fixed top-4 left-4 z-50 p-2 bg-white dark:bg-gray-900 text-gray-900 dark:text-white rounded-lg shadow-md md:hidden">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
        </svg>
    </button>

    <!-- Overlay untuk Mobile -->
    <div x-show="mobileOpen" @click="mobileOpen = false"
        class="fixed inset-0 bg-black bg-opacity-50 dark:bg-black dark:bg-opacity-50 z-30 md:hidden">
    </div>

    <!-- Sidebar -->
    <aside
        :class="[
            mobileOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0',
            minimized ? 'w-20' : 'w-64 sm:w-full md:w-64'
        ]"
        class="fixed top-0 left-0 z-40 h-screen bg-white dark:bg-gray-800 transition-all duration-300">

        <!-- Toggle Button Desktop -->
        <button
            @click="minimized = !minimized; if (minimized) { individualOpen = false; generalOpen = false }; $dispatch('sidebar-toggled', { minimized: minimized })"
            class="hidden md:block absolute -right-3 top-6 bg-white dark:bg-gray-900 text-gray-900 dark:text-white rounded-full p-1 border-2 border-gray-300 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800 shadow-md">
            <svg :class="minimized ? 'rotate-180' : ''" class="w-4 h-4 transition-transform" fill="none"
                stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
        </button>

        <div class="h-full px-4 py-6 overflow-y-auto">
            <!-- Logo/Brand -->
            <div class="flex justify-center mb-6">
                <!-- Logo BESAR saat MAXIMIZED -->
                <img x-show="!minimized" x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                    src="{{ asset('images/thumb-qhrmi-hd.jpg') }}" alt="SPSP Dashboard" class="h-12 w-auto">

                <!-- Logo KECIL saat MINIMIZED -->
                <img x-show="minimized" x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                    src="{{ asset('images/thumb-qhrmi.png') }}" alt="SPSP Logo" class="h-8 w-auto">
            </div>

            <!-- Menu -->
            <nav class="space-y-2">
                <!-- Dashboard -->
                <a wire:navigate href="/dashboard" :class="minimized ? 'justify-center' : ''"
                    class="flex items-center px-4 py-3 text-gray-900 dark:text-white rounded hover:bg-gray-100 dark:hover:bg-gray-800"
                    title="Dashboard">
                    <i :class="minimized ? '' : 'mr-3'" class="fa-solid fa-house"></i>
                    <span x-show="!minimized">Dashboard</span>
                </a>

                <!-- Shortlist Peserta -->
                <a wire:navigate href="{{ route('shortlist') }}" :class="minimized ? 'justify-center' : ''"
                    class="flex items-center px-4 py-3 text-gray-900 dark:text-white rounded hover:bg-gray-100 dark:hover:bg-gray-800"
                    title="Shortlist Peserta">
                    <i :class="minimized ? '' : 'mr-3'" class="fa-solid fa-users"></i>
                    <span x-show="!minimized">Shortlist Peserta</span>
                </a>

                <!-- Individual Report dengan Sub Menu -->
                <div>
                    <button id="btn-individual"
                        @click="if (minimized) { minimized = false; $dispatch('sidebar-toggled', { minimized: minimized }); individualOpen = true } else { individualOpen = !individualOpen }"
                        :class="minimized ? 'justify-center' : 'justify-between'"
                        class="w-full flex items-center px-4 py-3 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded pointer-events-auto"
                        title="Individual Report" aria-haspopup="true" :aria-expanded="individualOpen"
                        aria-controls="submenu-individual">
                        <div class="flex items-center">
                            <svg :class="minimized ? '' : 'mr-3'" class="w-5 h-5" fill="currentColor"
                                viewBox="0 0 20 20">
                                <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"></path>
                                <path fill-rule="evenodd"
                                    d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z"
                                    clip-rule="evenodd"></path>
                            </svg>
                            <span x-show="!minimized">Individual Report</span>
                        </div>
                        <svg x-show="!minimized" :class="individualOpen ? 'rotate-180' : ''"
                            class="w-4 h-4 transition-transform" fill="currentColor" viewBox="0 0 20 20"
                            aria-hidden="true" focusable="false">
                            <path fill-rule="evenodd"
                                d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                clip-rule="evenodd"></path>
                        </svg>
                    </button>

                    <!-- Sub Menu Individual Report -->
                    <div x-show="individualOpen && !minimized" x-cloak id="submenu-individual"
                        class="ml-4 mt-2 space-y-1" role="menu" aria-labelledby="btn-individual">
                        <a wire:navigate href="{{ route('shortlist') }}" role="menuitem"
                            class="block px-4 py-2 text-sm text-gray-600 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-800 rounded">
                            General Mapping
                        </a>
                        <a wire:navigate href="{{ route('shortlist') }}" role="menuitem"
                            class="block px-4 py-2 text-sm text-gray-600 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-800 rounded">
                            General Potency Mapping
                        </a>
                        <a wire:navigate href="{{ route('shortlist') }}" role="menuitem"
                            class="block px-4 py-2 text-sm text-gray-600 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-800 rounded">
                            General Psychology Mapping
                        </a>
                        <a wire:navigate href="{{ route('shortlist') }}" role="menuitem"
                            class="block px-4 py-2 text-sm text-gray-600 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-800 rounded">
                            Managerial Potency Mapping
                        </a>
                        <a wire:navigate href="{{ route('shortlist') }}" role="menuitem"
                            class="block px-4 py-2 text-sm text-gray-600 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-800 rounded">
                            Ringkasan Managerial Potency Mapping
                        </a>
                        <a wire:navigate href="{{ route('shortlist') }}" role="menuitem"
                            class="block px-4 py-2 text-sm text-gray-600 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-800 rounded">
                            Gambaran Individu & Deskripsi Kompetensi
                        </a>
                        <a wire:navigate href="{{ route('shortlist') }}" role="menuitem"
                            class="block px-4 py-2 text-sm text-gray-600 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-800 rounded">
                            Ringkasan Hasil Assessment Individu
                        </a>
                        <a wire:navigate href="{{ route('shortlist') }}" role="menuitem"
                            class="block px-4 py-2 text-sm text-gray-600 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-800 rounded">
                            Hasil Emotional Quotient (EQ)
                        </a>
                    </div>
                </div>

                <!-- General Report dengan Sub Menu -->
                <div>
                    <button id="btn-general"
                        @click="if (minimized) { minimized = false; $dispatch('sidebar-toggled', { minimized: minimized }); generalOpen = true } else { generalOpen = !generalOpen }"
                        :class="minimized ? 'justify-center' : 'justify-between'"
                        class="w-full flex items-center px-4 py-3 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded pointer-events-auto"
                        title="General Report" aria-haspopup="true" :aria-expanded="generalOpen"
                        aria-controls="submenu-general">
                        <div class="flex items-center">
                            <svg :class="minimized ? '' : 'mr-3'" class="w-5 h-5" fill="currentColor"
                                viewBox="0 0 20 20">
                                <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"></path>
                                <path fill-rule="evenodd"
                                    d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm9.707 5.707a1 1 0 00-1.414-1.414L9 12.586l-1.293-1.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                    clip-rule="evenodd"></path>
                            </svg>
                            <span x-show="!minimized">General Report</span>
                        </div>
                        <svg x-show="!minimized" :class="generalOpen ? 'rotate-180' : ''"
                            class="w-4 h-4 transition-transform" fill="currentColor" viewBox="0 0 20 20"
                            aria-hidden="true" focusable="false">
                            <path fill-rule="evenodd"
                                d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                clip-rule="evenodd"></path>
                        </svg>
                    </button>

                    <!-- Sub Menu General Report -->
                    <div x-show="generalOpen && !minimized" x-cloak id="submenu-general" class="ml-4 mt-2 space-y-1"
                        role="menu" aria-labelledby="btn-general">
                        <a wire:navigate href="{{ route('ranking-psy-mapping') }}" role="menuitem"
                            class="block px-4 py-2 text-sm text-gray-600 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-800 rounded">
                            Ranking Psychology Mapping
                        </a>
                        <a wire:navigate href="{{ route('ranking-mc-mapping') }}" role="menuitem"
                            class="block px-4 py-2 text-sm text-gray-600 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-800 rounded">
                            Ranking Managerial Competency Mapping
                        </a>
                        <a wire:navigate href="{{ route('rekap-ranking-assessment') }}" role="menuitem"
                            class="block px-4 py-2 text-sm text-gray-600 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-800 rounded">
                            Rekap Ranking Assessment
                        </a>
                        <a wire:navigate href="{{ route('statistic') }}" role="menuitem"
                            class="block px-4 py-2 text-sm text-gray-600 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-800 rounded">
                            Statistik
                        </a>
                        <a wire:navigate href="{{ route('training-recommendation') }}" role="menuitem"
                            class="block px-4 py-2 text-sm text-gray-600 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-800 rounded">
                            Training Recommendation
                        </a>
                        <a wire:navigate href="{{ route('standard-mc') }}" role="menuitem"
                            class="block px-4 py-2 text-sm text-gray-600 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-800 rounded">
                            Standar Managerial Competency Mapping
                        </a>
                        <a wire:navigate href="{{ route('standard-psikometrik') }}" role="menuitem"
                            class="block px-4 py-2 text-sm text-gray-600 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-800 rounded">
                            Standar Potential Mapping
                        </a>
                        <a wire:navigate href="{{ route('general-report.mmpi') }}" role="menuitem"
                            class="block px-4 py-2 text-sm text-gray-600 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-800 rounded">
                            MMPI
                        </a>
                    </div>
                </div>
            </nav>
        </div>
    </aside>
</div>
