<!-- Sidebar -->
<div x-data="{ mobileOpen: false, minimized: true, individualOpen: false, generalOpen: false }"
    x-init="$dispatch('sidebar-toggled', { minimized: minimized })"
    @sidebar-toggled.window="minimized = $event.detail.minimized">
    <!-- Mobile Toggle Button -->
    <button @click="mobileOpen = !mobileOpen"
        class="fixed top-4 left-4 z-50 p-2 bg-gray-800 text-white rounded-lg md:hidden">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
        </svg>
    </button>

    <!-- Overlay untuk Mobile -->
    <div x-show="mobileOpen" @click="mobileOpen = false" class="fixed inset-0 bg-black bg-opacity-50 z-30 md:hidden">
    </div>

    <!-- Sidebar -->
    <aside :class="[
            mobileOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0',
            minimized ? 'w-20' : 'w-64 sm:w-full md:w-64'
        ]" class="fixed top-0 left-0 z-40 h-screen bg-gray-800 transition-all duration-300">
        <!-- Toggle Button Desktop -->
        <button
            @click="minimized = !minimized; if (minimized) { individualOpen = false; generalOpen = false }; $dispatch('sidebar-toggled', { minimized: minimized })"
            class="hidden md:block absolute -right-3 top-6 bg-gray-800 text-white rounded-full p-1 border-2 border-gray-600 hover:bg-gray-700">
            <svg :class="minimized ? 'rotate-180' : ''" class="w-4 h-4 transition-transform" fill="none"
                stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
        </button>

        <div class="h-full px-4 py-6 overflow-y-auto">
            <!-- Logo/Brand -->
            <h2 :class="minimized ? 'text-center' : ''" class="text-white text-xl font-bold mb-6"
                x-text="minimized ? 'MD' : 'My Dashboard'"></h2>

            <!-- Menu -->
            <nav class="space-y-2">
                <!-- Dashboard -->
                <a wire:navigate href="/dashboard" :class="minimized ? 'justify-center' : ''"
                    class="flex items-center px-4 py-3 text-white" title="Dashboard">
                    <i class="fa-solid fa-house mr-3"></i>
                    <span x-show="!minimized">Dashboard</span>
                </a>

                <!-- Shortlist Peserta -->
                <a wire:navigate href="{{ route('shortlist') }}" :class="minimized ? 'justify-center' : ''"
                    class="flex items-center px-4 py-3 text-white" title="Shortlist Peserta">
                    <i class="fa-solid fa-users mr-3"></i>
                    <span x-show="!minimized">Shortlist Peserta</span>
                </a>

                <!-- Individual Report dengan Sub Menu -->
                <div>
                    <button id="btn-individual"
                        @click="if (minimized) { minimized = false; $dispatch('sidebar-toggled', { minimized: minimized }); individualOpen = true } else { individualOpen = !individualOpen }"
                        :class="minimized ? 'justify-center' : 'justify-between'"
                        class="w-full flex items-center px-4 py-3 text-gray-300 hover:bg-gray-700 rounded pointer-events-auto"
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
                    <div x-show="individualOpen" x-cloak id="submenu-individual" class="ml-4 mt-2 space-y-1" role="menu"
                        aria-labelledby="btn-individual">
                        @if(!$this->canShowIndividualReports())
                        <div class="mx-4 my-3 p-3 bg-yellow-900/30 border border-yellow-600/50 rounded-lg">
                            <div class="flex items-start gap-3">
                                <div class="flex-1">
                                    <p class="text-sm font-semibold text-yellow-400 mb-1">Aksi Diperlukan</p>
                                    <p class="text-xs text-gray-300 leading-relaxed">
                                        Pilih Proyek dan Peserta terlebih dahulu pada halaman
                                        <a href="{{ route('dashboard') }}"
                                            class="text-yellow-400 hover:text-yellow-300 underline font-medium transition-colors">Dashboard</a>
                                        atau
                                        <a href="{{ route('shortlist') }}"
                                            class="text-yellow-400 hover:text-yellow-300 underline font-medium transition-colors">Shortlist
                                            Peserta</a>
                                    </p>
                                </div>
                            </div>
                        </div>
                        @endif

                        <a wire:navigate
                            href="{{ $this->canShowIndividualReports() ? route('general_matching', ['eventCode' => $eventCode, 'testNumber' => $testNumber]) : '#' }}"
                            role="menuitem" @class([ 'block px-4 py-2 text-sm rounded'
                            , 'text-gray-300 hover:bg-gray-700'=> $this->canShowIndividualReports(),
                            'text-gray-500 cursor-not-allowed' => !$this->canShowIndividualReports()
                            ])
                            @if(!$this->canShowIndividualReports())
                            title="Pilih event dan peserta terlebih dahulu"
                            @endif>
                            General Matching
                        </a>
                        <a wire:navigate
                            href="{{ $this->canShowIndividualReports() ? route('general_mapping', ['eventCode' => $eventCode, 'testNumber' => $testNumber]) : '#' }}"
                            role="menuitem" @class([ 'block px-4 py-2 text-sm rounded'
                            , 'text-gray-300 hover:bg-gray-700'=> $this->canShowIndividualReports(),
                            'text-gray-500 cursor-not-allowed' => !$this->canShowIndividualReports()
                            ])
                            @if(!$this->canShowIndividualReports())
                            title="Pilih event dan peserta terlebih dahulu"
                            @endif>
                            General Mapping
                        </a>

                        <a wire:navigate
                            href="{{ $this->canShowIndividualReports() ? route('general_mc_mapping', ['eventCode' => $eventCode, 'testNumber' => $testNumber]) : '#' }}"
                            role="menuitem" @class([ 'block px-4 py-2 text-sm rounded'
                            , 'text-gray-300 hover:bg-gray-700'=> $this->canShowIndividualReports(),
                            'text-gray-500 cursor-not-allowed' => !$this->canShowIndividualReports()
                            ])>
                            General Potency Mapping
                        </a>

                        <a wire:navigate
                            href="{{ $this->canShowIndividualReports() ? route('general_psy_mapping', ['eventCode' => $eventCode, 'testNumber' => $testNumber]) : '#' }}"
                            role="menuitem" @class([ 'block px-4 py-2 text-sm rounded'
                            , 'text-gray-300 hover:bg-gray-700'=> $this->canShowIndividualReports(),
                            'text-gray-500 cursor-not-allowed' => !$this->canShowIndividualReports()
                            ])>
                            General Psychology Mapping
                        </a>

                        <a wire:navigate
                            href="{{ $this->canShowIndividualReports() ? route('spider_plot', ['eventCode' => $eventCode, 'testNumber' => $testNumber]) : '#' }}"
                            role="menuitem" @class([ 'block px-4 py-2 text-sm rounded'
                            , 'text-gray-300 hover:bg-gray-700'=> $this->canShowIndividualReports(),
                            'text-gray-500 cursor-not-allowed' => !$this->canShowIndividualReports()
                            ])>
                            Managerial Potency Mapping
                        </a>

                        <a wire:navigate
                            href="{{ $this->canShowIndividualReports() ? route('ringkasan_mc_mapping', ['eventCode' => $eventCode, 'testNumber' => $testNumber]) : '#' }}"
                            role="menuitem" @class([ 'block px-4 py-2 text-sm rounded'
                            , 'text-gray-300 hover:bg-gray-700'=> $this->canShowIndividualReports(),
                            'text-gray-500 cursor-not-allowed' => !$this->canShowIndividualReports()
                            ])>
                            Ringkasan Managerial Potency Mapping
                        </a>

                        <a wire:navigate
                            href="{{ $this->canShowIndividualReports() ? route('general_matching', ['eventCode' => $eventCode, 'testNumber' => $testNumber]) : '#' }}"
                            role="menuitem" @class([ 'block px-4 py-2 text-sm rounded'
                            , 'text-gray-300 hover:bg-gray-700'=> $this->canShowIndividualReports(),
                            'text-gray-500 cursor-not-allowed' => !$this->canShowIndividualReports()
                            ])>
                            Gambaran Individu & Deskripsi Kompetensi
                        </a>

                        <a wire:navigate
                            href="{{ $this->canShowIndividualReports() ? route('ringkasan_assessment', ['eventCode' => $eventCode, 'testNumber' => $testNumber]) : '#' }}"
                            role="menuitem" @class([ 'block px-4 py-2 text-sm rounded'
                            , 'text-gray-300 hover:bg-gray-700'=> $this->canShowIndividualReports(),
                            'text-gray-500 cursor-not-allowed' => !$this->canShowIndividualReports()
                            ])>
                            Ringkasan Hasil Assessment Individu
                        </a>
                    </div>
                </div>

                <!-- General Report dengan Sub Menu -->
                <div>
                    <button id="btn-general"
                        @click="if (minimized) { minimized = false; $dispatch('sidebar-toggled', { minimized: minimized }); generalOpen = true } else { generalOpen = !generalOpen }"
                        :class="minimized ? 'justify-center' : 'justify-between'"
                        class="w-full flex items-center px-4 py-3 text-gray-300 hover:bg-gray-700 rounded pointer-events-auto"
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
                    <div x-show="generalOpen" x-cloak id="submenu-general" class="ml-4 mt-2 space-y-1" role="menu"
                        aria-labelledby="btn-general">
                        <a wire:navigate href="{{ route('ranking-psy-mapping') }}" role="menuitem"
                            class="block px-4 py-2 text-sm text-gray-300 hover:bg-gray-700 rounded">
                            Ranking Psychology Mapping
                        </a>
                        <a wire:navigate href="{{ route('ranking-mc-mapping') }}" role="menuitem"
                            class="block px-4 py-2 text-sm text-gray-300 hover:bg-gray-700 rounded">
                            Ranking Managerial Competency Mapping
                        </a>
                        <a wire:navigate href="{{ route('rekap-ranking-assessment') }}" role="menuitem"
                            class="block px-4 py-2 text-sm text-gray-300 hover:bg-gray-700 rounded">
                            Rekap Ranking Assessment
                        </a>
                        <a wire:navigate href="{{ route('statistic') }}" role="menuitem"
                            class="block px-4 py-2 text-sm text-gray-300 hover:bg-gray-700 rounded">
                            Statistik
                        </a>
                        <a wire:navigate href="{{ route('training-recommendation') }}" role="menuitem"
                            class="block px-4 py-2 text-sm text-gray-300 hover:bg-gray-700 rounded">
                            Training Recommendation
                        </a>
                        <a wire:navigate href="{{ route('standard-mc') }}" role="menuitem"
                            class="block px-4 py-2 text-sm text-gray-300 hover:bg-gray-700 rounded">
                            Standar Managerial Competency Mapping
                        </a>
                        <a wire:navigate href="{{ route('standard-psikometrik') }}" role="menuitem"
                            class="block px-4 py-2 text-sm text-gray-300 hover:bg-gray-700 rounded">
                            Standar Potential Mapping
                        </a>
                        <a wire:navigate href="{{ route('general-report.mmpi') }}" role="menuitem"
                            class="block px-4 py-2 text-sm text-gray-300 hover:bg-gray-700 rounded">
                            MMPI
                        </a>
                    </div>
                </div>
            </nav>
        </div>
    </aside>
</div>