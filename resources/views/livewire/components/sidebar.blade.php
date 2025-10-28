<!-- Sidebar -->
<div x-data="{
        mobileOpen: false,
        minimized: $persist(true).as('sidebar_minimized'),
        individualOpen: $persist(false).as('sidebar_individual_open'),
        generalOpen: $persist(false).as('sidebar_general_open'),
        scrollPosition: 0,
        init() {
            // Restore scroll position after DOM is ready
            this.$nextTick(() => {
                const savedScroll = localStorage.getItem('sidebar_scroll_position');
                if (savedScroll && this.$refs.sidebarNav) {
                    this.$refs.sidebarNav.scrollTop = parseInt(savedScroll);
                }
            });
            // Dispatch initial state
            this.$dispatch('sidebar-toggled', { minimized: this.minimized });

            // Save scroll position before page unload
            window.addEventListener('beforeunload', () => {
                this.saveScrollPosition();
            });

            // Save scroll position on Livewire navigation
            document.addEventListener('livewire:navigating', () => {
                this.saveScrollPosition();
            });
        },
        saveScrollPosition() {
            if (this.$refs.sidebarNav) {
                localStorage.setItem('sidebar_scroll_position', this.$refs.sidebarNav.scrollTop);
            }
        }
    }" x-init="init()" @sidebar-toggled.window="minimized = $event.detail.minimized">
    <!-- Mobile Toggle Button -->
    <button @click="mobileOpen = !mobileOpen"
        class="fixed top-4 left-4 z-50 p-2 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 rounded-lg shadow-lg md:hidden hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
        </svg>
    </button>

    <!-- Overlay untuk Mobile -->
    <div x-show="mobileOpen" @click="mobileOpen = false"
        x-transition:enter="transition-opacity ease-linear duration-300" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="transition-opacity ease-linear duration-300"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-black bg-opacity-50 z-30 md:hidden" style="display: none;">
    </div>

    <!-- Sidebar -->
    <aside x-transition:enter="transition ease-in-out duration-300 transform"
        x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0"
        x-transition:leave="transition ease-in-out duration-300 transform" x-transition:leave-start="translate-x-0"
        x-transition:leave-end="-translate-x-full" :class="[
            mobileOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0',
            minimized ? 'w-20' : 'w-64'
        ]"
        class="fixed top-0 left-0 z-40 h-screen bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 transition-all duration-300 shadow-lg">
        <!-- Toggle Button Desktop -->
        <button
            @click="minimized = !minimized; if (minimized) { individualOpen = false; generalOpen = false }; $dispatch('sidebar-toggled', { minimized: minimized })"
            class="hidden md:block absolute -right-3 top-6 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 rounded-full p-1.5 border-2 border-gray-200 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700 hover:border-gray-300 dark:hover:border-gray-500 transition-all shadow-md">
            <svg :class="minimized ? 'rotate-180' : ''" class="w-4 h-4 transition-transform duration-300" fill="none"
                stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
        </button>

        <div class="h-full flex flex-col">
            <!-- Logo/Brand -->
            <div :class="minimized ? 'text-center' : ''"
                class="px-4 py-6 border-b border-gray-200 dark:border-gray-700">
                <img :src="minimized ? '{{ asset('images/thumb-qhrmi.png') }}' : '{{ asset('images/thumb-qhrmi-hd.jpg') }}'"
                    :alt="minimized ? 'MD' : 'My Dashboard'" :class="minimized ? 'h-10 mx-auto' : 'ml-2 h-12'"
                    class="object-contain transition-all duration-300">
            </div>

            <!-- Menu dengan Scroll -->
            <nav x-ref="sidebarNav" @scroll.debounce.500ms="saveScrollPosition()"
                class="flex-1 px-3 py-4 overflow-y-auto space-y-1">
                <!-- Dashboard -->
                <a href="/dashboard" x-tooltip.raw="minimized ? 'Dashboard' : null"
                    @class([ 'group flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-all duration-200'
                    , 'bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-400 border-l-4 border-blue-700 dark:border-blue-400'=>
                    $this->isActiveRoute('dashboard'),
                    'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700/50' =>
                    !$this->isActiveRoute('dashboard'),
                    ])
                    :class="minimized ? 'justify-center px-2' : ''">
                    <i class="fa-solid fa-house w-5 text-center" :class="!minimized && 'mr-3'"></i>
                    <span x-show="!minimized" x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">Dashboard</span>
                </a>

                <!-- Shortlist Peserta -->
                <a wire:navigate href="{{ route('shortlist') }}" x-tooltip.raw="minimized ? 'Shortlist Peserta' : null"
                    @class([ 'group flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-all duration-200'
                    , 'bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-400 border-l-4 border-blue-700 dark:border-blue-400'=>
                    $this->isActiveRoute('shortlist'),
                    'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700/50' =>
                    !$this->isActiveRoute('shortlist'),
                    ])
                    :class="minimized ? 'justify-center px-2' : ''">
                    <i class="fa-solid fa-users w-5 text-center" :class="!minimized && 'mr-3'"></i>
                    <span x-show="!minimized" x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">Shortlist
                        Peserta</span>
                </a>

                <!-- Individual Report dengan Sub Menu -->
                <div>
                    <button id="btn-individual"
                        @click="if (minimized) { minimized = false; $dispatch('sidebar-toggled', { minimized: minimized }); individualOpen = true } else { individualOpen = !individualOpen }"
                        x-tooltip.raw="minimized ? 'Individual Report' : null"
                        class="w-full group flex items-center px-3 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700/50 rounded-lg transition-all duration-200"
                        :class="minimized ? 'justify-center px-2' : 'justify-between'" aria-haspopup="true"
                        :aria-expanded="individualOpen" aria-controls="submenu-individual">
                        <div class="flex items-center">
                            <i class="fa-solid fa-file-lines w-5 text-center" :class="!minimized && 'mr-3'"></i>
                            <span x-show="!minimized" x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">Individual
                                Report</span>
                        </div>
                        <svg x-show="!minimized" :class="individualOpen ? 'rotate-180' : ''"
                            class="w-4 h-4 transition-transform duration-200" fill="currentColor" viewBox="0 0 20 20"
                            aria-hidden="true">
                            <path fill-rule="evenodd"
                                d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                clip-rule="evenodd"></path>
                        </svg>
                    </button>

                    <!-- Sub Menu Individual Report -->
                    <div x-show="individualOpen" x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 -translate-y-1"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100 translate-y-0"
                        x-transition:leave-end="opacity-0 -translate-y-1" id="submenu-individual"
                        class="ml-4 mt-1 space-y-1" role="menu" aria-labelledby="btn-individual" style="display: none;">
                        @if (!$this->canShowIndividualReports())
                        <div
                            class="mx-2 my-2 p-2.5 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-600/50 rounded-lg">
                            <div class="flex items-start gap-2">
                                <i
                                    class="fa-solid fa-circle-exclamation text-yellow-600 dark:text-yellow-400 text-sm mt-0.5"></i>
                                <div class="flex-1">
                                    <p class="text-xs font-semibold text-yellow-800 dark:text-yellow-300 mb-1">Pilih
                                        Data</p>
                                    <p class="text-xs text-yellow-700 dark:text-yellow-400/90 leading-relaxed">
                                        Pilih Proyek & Peserta di
                                        <a href="{{ route('dashboard') }}" wire:navigate
                                            class="underline hover:text-yellow-600 dark:hover:text-yellow-300 font-medium">Dashboard</a>
                                        atau pilih salah satu peserta di
                                        <a href="{{ route('shortlist') }}" wire:navigate
                                            class="underline hover:text-yellow-600 dark:hover:text-yellow-300 font-medium">Shortlist</a>
                                    </p>
                                </div>
                            </div>
                        </div>
                        @endif

                        <a wire:navigate
                            href="{{ $this->canShowIndividualReports() ? route('general_matching', ['eventCode' => $eventCode, 'testNumber' => $testNumber]) : '#' }}"
                            role="menuitem"
                            @class([ 'block px-3 py-2 text-xs rounded-lg transition-all duration-200'
                            , 'bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-400 font-medium'=>
                            $this->isActiveRoute('general_matching'),
                            'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700/50' =>
                            $this->canShowIndividualReports() && !$this->isActiveRoute('general_matching'),
                            'text-gray-400 dark:text-gray-600 cursor-not-allowed' =>
                            !$this->canShowIndividualReports(),
                            ])
                            @if (!$this->canShowIndividualReports()) title="Pilih data terlebih dahulu" @endif>
                            <i class="fa-solid fa-circle-dot mr-2 text-xs"></i>General Matching
                        </a>

                        <a wire:navigate
                            href="{{ $this->canShowIndividualReports() ? route('general_mapping', ['eventCode' => $eventCode, 'testNumber' => $testNumber]) : '#' }}"
                            role="menuitem"
                            @class([ 'block px-3 py-2 text-xs rounded-lg transition-all duration-200'
                            , 'bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-400 font-medium'=>
                            $this->isActiveRoute('general_mapping'),
                            'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700/50' =>
                            $this->canShowIndividualReports() && !$this->isActiveRoute('general_mapping'),
                            'text-gray-400 dark:text-gray-600 cursor-not-allowed' =>
                            !$this->canShowIndividualReports(),
                            ])
                            @if (!$this->canShowIndividualReports()) title="Pilih data terlebih dahulu" @endif>
                            <i class="fa-solid fa-circle-dot mr-2 text-xs"></i>General Mapping
                        </a>

                        <a wire:navigate
                            href="{{ $this->canShowIndividualReports() ? route('general_psy_mapping', ['eventCode' => $eventCode, 'testNumber' => $testNumber]) : '#' }}"
                            role="menuitem"
                            @class([ 'block px-3 py-2 text-xs rounded-lg transition-all duration-200'
                            , 'bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-400 font-medium'=>
                            $this->isActiveRoute('general_psy_mapping'),
                            'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700/50' =>
                            $this->canShowIndividualReports() && !$this->isActiveRoute('general_psy_mapping'),
                            'text-gray-400 dark:text-gray-600 cursor-not-allowed' =>
                            !$this->canShowIndividualReports(),
                            ])
                            @if (!$this->canShowIndividualReports()) title="Pilih data terlebih dahulu" @endif>
                            <i class="fa-solid fa-circle-dot mr-2 text-xs"></i>Psychology Mapping
                        </a>

                        <a wire:navigate
                            href="{{ $this->canShowIndividualReports() ? route('general_mc_mapping', ['eventCode' => $eventCode, 'testNumber' => $testNumber]) : '#' }}"
                            role="menuitem"
                            @class([ 'block px-3 py-2 text-xs rounded-lg transition-all duration-200'
                            , 'bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-400 font-medium'=>
                            $this->isActiveRoute('general_mc_mapping'),
                            'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700/50' =>
                            $this->canShowIndividualReports() && !$this->isActiveRoute('general_mc_mapping'),
                            'text-gray-400 dark:text-gray-600 cursor-not-allowed' =>
                            !$this->canShowIndividualReports(),
                            ])
                            @if (!$this->canShowIndividualReports()) title="Pilih data terlebih dahulu" @endif>
                            <i class="fa-solid fa-circle-dot mr-2 text-xs"></i>Managerial Competency Mapping
                        </a>

                        <a wire:navigate
                            href="{{ $this->canShowIndividualReports() ? route('spider_plot', ['eventCode' => $eventCode, 'testNumber' => $testNumber]) : '#' }}"
                            role="menuitem"
                            @class([ 'block px-3 py-2 text-xs rounded-lg transition-all duration-200'
                            , 'bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-400 font-medium'=>
                            $this->isActiveRoute('spider_plot'),
                            'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700/50' =>
                            $this->canShowIndividualReports() && !$this->isActiveRoute('spider_plot'),
                            'text-gray-400 dark:text-gray-600 cursor-not-allowed' =>
                            !$this->canShowIndividualReports(),
                            ])
                            @if (!$this->canShowIndividualReports()) title="Pilih data terlebih dahulu" @endif>
                            <i class="fa-solid fa-circle-dot mr-2 text-xs"></i>Spider Plot
                        </a>

                        <a wire:navigate
                            href="{{ $this->canShowIndividualReports() ? route('ringkasan_mc_mapping', ['eventCode' => $eventCode, 'testNumber' => $testNumber]) : '#' }}"
                            role="menuitem"
                            @class([ 'block px-3 py-2 text-xs rounded-lg transition-all duration-200'
                            , 'bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-400 font-medium'=>
                            $this->isActiveRoute('ringkasan_mc_mapping'),
                            'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700/50' =>
                            $this->canShowIndividualReports() && !$this->isActiveRoute('ringkasan_mc_mapping'),
                            'text-gray-400 dark:text-gray-600 cursor-not-allowed' =>
                            !$this->canShowIndividualReports(),
                            ])
                            @if (!$this->canShowIndividualReports()) title="Pilih data terlebih dahulu" @endif>
                            <i class="fa-solid fa-circle-dot mr-2 text-xs"></i>Ringkasan Managerial Potency Mapping
                        </a>

                        <a wire:navigate
                            href="{{ $this->canShowIndividualReports() ? route('ringkasan_assessment', ['eventCode' => $eventCode, 'testNumber' => $testNumber]) : '#' }}"
                            role="menuitem"
                            @class([ 'block px-3 py-2 text-xs rounded-lg transition-all duration-200'
                            , 'bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-400 font-medium'=>
                            $this->isActiveRoute('ringkasan_assessment'),
                            'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700/50' =>
                            $this->canShowIndividualReports() && !$this->isActiveRoute('ringkasan_assessment'),
                            'text-gray-400 dark:text-gray-600 cursor-not-allowed' =>
                            !$this->canShowIndividualReports(),
                            ])
                            @if (!$this->canShowIndividualReports()) title="Pilih data terlebih dahulu" @endif>
                            <i class="fa-solid fa-circle-dot mr-2 text-xs"></i>Ringkasan Hasil Assessment Individu
                        </a>

                        <a wire:navigate
                            href="{{ $this->canShowIndividualReports() ? route('final_report', ['eventCode' => $eventCode, 'testNumber' => $testNumber]) : '#' }}"
                            role="menuitem"
                            @class([ 'block px-3 py-2 text-xs rounded-lg transition-all duration-200'
                            , 'bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-400 font-medium'=>
                            $this->isActiveRoute('final_report'),
                            'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700/50' =>
                            $this->canShowIndividualReports() && !$this->isActiveRoute('final_report'),
                            'text-gray-400 dark:text-gray-600 cursor-not-allowed' =>
                            !$this->canShowIndividualReports(),
                            ])
                            @if (!$this->canShowIndividualReports()) title="Pilih data terlebih dahulu" @endif>
                            <i class="fa-solid fa-circle-dot mr-2 text-xs"></i>Laporan Individu
                        </a>
                    </div>
                </div>

                <!-- General Report dengan Sub Menu -->
                <div>
                    <button id="btn-general"
                        @click="if (minimized) { minimized = false; $dispatch('sidebar-toggled', { minimized: minimized }); generalOpen = true } else { generalOpen = !generalOpen }"
                        x-tooltip.raw="minimized ? 'General Report' : null"
                        class="w-full group flex items-center px-3 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700/50 rounded-lg transition-all duration-200"
                        :class="minimized ? 'justify-center px-2' : 'justify-between'" aria-haspopup="true"
                        :aria-expanded="generalOpen" aria-controls="submenu-general">
                        <div class="flex items-center">
                            <i class="fa-solid fa-chart-bar w-5 text-center" :class="!minimized && 'mr-3'"></i>
                            <span x-show="!minimized" x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">General
                                Report</span>
                        </div>
                        <svg x-show="!minimized" :class="generalOpen ? 'rotate-180' : ''"
                            class="w-4 h-4 transition-transform duration-200" fill="currentColor" viewBox="0 0 20 20"
                            aria-hidden="true">
                            <path fill-rule="evenodd"
                                d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                clip-rule="evenodd"></path>
                        </svg>
                    </button>

                    <!-- Sub Menu General Report -->
                    <div x-show="generalOpen" x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 -translate-y-1"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100 translate-y-0"
                        x-transition:leave-end="opacity-0 -translate-y-1" id="submenu-general"
                        class="ml-4 mt-1 space-y-1" role="menu" aria-labelledby="btn-general" style="display: none;">
                        <a wire:navigate href="{{ route('ranking-psy-mapping') }}" role="menuitem"
                            @class([ 'block px-3 py-2 text-xs rounded-lg transition-all duration-200'
                            , 'bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-400 font-medium'=>
                            $this->isActiveRoute('ranking-psy-mapping'),
                            'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700/50' =>
                            !$this->isActiveRoute('ranking-psy-mapping'),
                            ])>
                            <i class="fa-solid fa-circle-dot mr-2 text-xs"></i>Ranking Psychology
                        </a>
                        <a wire:navigate href="{{ route('ranking-mc-mapping') }}" role="menuitem"
                            @class([ 'block px-3 py-2 text-xs rounded-lg transition-all duration-200'
                            , 'bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-400 font-medium'=>
                            $this->isActiveRoute('ranking-mc-mapping'),
                            'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700/50' =>
                            !$this->isActiveRoute('ranking-mc-mapping'),
                            ])>
                            <i class="fa-solid fa-circle-dot mr-2 text-xs"></i>Ranking Managerial
                        </a>
                        <a wire:navigate href="{{ route('rekap-ranking-assessment') }}" role="menuitem"
                            @class([ 'block px-3 py-2 text-xs rounded-lg transition-all duration-200'
                            , 'bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-400 font-medium'=>
                            $this->isActiveRoute('rekap-ranking-assessment'),
                            'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700/50' =>
                            !$this->isActiveRoute('rekap-ranking-assessment'),
                            ])>
                            <i class="fa-solid fa-circle-dot mr-2 text-xs"></i>Ranking Assessment
                        </a>
                        <a wire:navigate href="{{ route('statistic') }}" role="menuitem"
                            @class([ 'block px-3 py-2 text-xs rounded-lg transition-all duration-200'
                            , 'bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-400 font-medium'=>
                            $this->isActiveRoute('statistic'),
                            'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700/50' =>
                            !$this->isActiveRoute('statistic'),
                            ])>
                            <i class="fa-solid fa-circle-dot mr-2 text-xs"></i>Statistik
                        </a>
                        <a wire:navigate href="{{ route('training-recommendation') }}" role="menuitem"
                            @class([ 'block px-3 py-2 text-xs rounded-lg transition-all duration-200'
                            , 'bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-400 font-medium'=>
                            $this->isActiveRoute('training-recommendation'),
                            'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700/50' =>
                            !$this->isActiveRoute('training-recommendation'),
                            ])>
                            <i class="fa-solid fa-circle-dot mr-2 text-xs"></i>Training Recommendation
                        </a>
                        <a wire:navigate href="{{ route('standard-mc') }}" role="menuitem"
                            @class([ 'block px-3 py-2 text-xs rounded-lg transition-all duration-200'
                            , 'bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-400 font-medium'=>
                            $this->isActiveRoute('standard-mc'),
                            'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700/50' =>
                            !$this->isActiveRoute('standard-mc'),
                            ])>
                            <i class="fa-solid fa-circle-dot mr-2 text-xs"></i>Standar Managerial
                        </a>
                        <a wire:navigate href="{{ route('standard-psikometrik') }}" role="menuitem"
                            @class([ 'block px-3 py-2 text-xs rounded-lg transition-all duration-200'
                            , 'bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-400 font-medium'=>
                            $this->isActiveRoute('standard-psikometrik'),
                            'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700/50' =>
                            !$this->isActiveRoute('standard-psikometrik'),
                            ])>
                            <i class="fa-solid fa-circle-dot mr-2 text-xs"></i>Standar Potential
                        </a>
                        <a wire:navigate href="{{ route('general-report.mmpi') }}" role="menuitem"
                            @class([ 'block px-3 py-2 text-xs rounded-lg transition-all duration-200'
                            , 'bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-400 font-medium'=>
                            $this->isActiveRoute('general-report.mmpi'),
                            'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700/50' =>
                            !$this->isActiveRoute('general-report.mmpi'),
                            ])>
                            <i class="fa-solid fa-circle-dot mr-2 text-xs"></i>MMPI
                        </a>
                    </div>
                </div>
            </nav>

            <!-- Footer - User Info (Optional) -->
            <div x-show="!minimized" x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                class="px-4 py-3 border-t border-gray-200 dark:border-gray-700">
                <div class="flex items-center gap-2">
                    <div
                        class="w-8 h-8 bg-blue-600 dark:bg-blue-500 rounded-full flex items-center justify-center text-white text-xs font-semibold">
                        {{ strtoupper(substr(auth()->user()?->name ?? 'U', 0, 2)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-medium text-gray-900 dark:text-gray-100 truncate">
                            {{ auth()->user()?->name ?? 'User' }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Online</p>
                    </div>
                </div>
            </div>
        </div>
    </aside>

    <!-- Loading Indicator -->
    <div wire:loading wire:target="handleEventSelected,handlePositionSelected,handleParticipantSelected"
        class="fixed top-20 left-1/2 transform -translate-x-1/2 z-50 bg-blue-600 text-white px-4 py-2 rounded-lg shadow-lg flex items-center gap-2">
        <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
            </circle>
            <path class="opacity-75" fill="currentColor"
                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
            </path>
        </svg>
        <span class="text-sm">Memuat...</span>
    </div>
</div>