<nav x-cloak
    class="fixed left-0 top-0 z-40 flex h-svh shrink-0 flex-col border-r border-neutral-200/60 bg-white/95 backdrop-blur-md p-4 transition-all duration-300 ease-in-out shadow-sm dark:border-neutral-800/60 dark:bg-neutral-950/95"
    x-bind:class="[
        getSidebarWidthClass(),
        getSidebarTransformClass()
    ]"
    aria-label="sidebar navigation">

    <!-- Brand/Logo -->
    <a href="{{ route('dashboard') }}" wire:navigate class="flex items-center justify-center mb-8 group">
        <span class="sr-only">homepage</span>
        <div class="flex items-center gap-3 overflow-hidden">
            <div
                class="shrink-0 w-10 h-10 bg-white rounded-xl flex items-center justify-center shadow-lg group-hover:shadow-xl transition-all duration-300 group-hover:scale-105 p-1">
                <img src="{{ asset('images/thumb-qhrmi.webp') }}" class="w-8 h-8 object-contain" alt="Logo">
            </div>
            <div x-show="!sidebarIsMini" x-transition class="flex flex-col">
                <span class="text-lg font-bold text-neutral-900 dark:text-white leading-none">SPSP</span>
                <span class="text-[10px] text-neutral-500 dark:text-neutral-400 leading-tight">Quantum HRM</span>
            </div>
        </div>
    </a>

    <!-- Menu dengan Scroll -->
    <div class="flex flex-col gap-1.5 pb-6 flex-1 overflow-y-auto overflow-x-hidden scrollbar-hidden sidebar-scroll-container" wire:navigate:scroll
        x-data="{
            init() {
                let scrollPos = localStorage.getItem('sidebarScroll');
                if (scrollPos) {
                    this.$el.scrollTop = parseInt(scrollPos);
                }
            },
            saveScroll() {
                localStorage.setItem('sidebarScroll', this.$el.scrollTop);
            }
        }"
        @scroll.debounce.100ms="saveScroll">

        @if (auth()->user()->hasRole('admin'))
            <!-- Dashboard Admin -->
            <a href="{{ route('dashboard-admin') }}" class="group flex items-center rounded-xl gap-3 px-3 py-2.5 text-sm font-medium transition-all duration-200 relative overflow-hidden shrink-0"
                x-bind:class="[
                    sidebarIsMini ? 'justify-center px-2.5 py-3' : '',
                    isActive('{{ route('dashboard-admin') }}')
                        ? 'text-red-600 bg-red-50/70 dark:text-red-400 dark:bg-red-950/50'
                        : 'text-neutral-700 hover:text-red-600 hover:bg-red-50/50 dark:text-neutral-300 dark:hover:text-red-400 dark:hover:bg-red-950/50'
                ]" 
                x-bind:title="'Beranda Admin'" 
                x-bind:aria-current="isActive('{{ route('dashboard-admin') }}') ? 'page' : 'false'" 
                wire:navigate>
                <div class="shrink-0 w-5 h-5 flex items-center justify-center">
                    <i class="fa-solid fa-house-laptop text-base group-hover:scale-110 transition-transform duration-200"></i>
                </div>
                <span x-show="!sidebarIsMini" x-transition class="truncate">Beranda Admin</span>
                <div class="absolute inset-x-0 bottom-0 h-0.5 bg-linear-to-r from-red-500 to-orange-600 transform scale-x-0 group-hover:scale-x-100 transition-transform duration-300"
                    x-bind:class="isActive('{{ route('dashboard-admin') }}') ? 'scale-x-100' : ''">
                </div>
            </a>
        @endif

        <!-- Beranda (Dashboard) -->
        <a href="{{ route('dashboard') }}" class="group flex items-center rounded-xl gap-3 px-3 py-2.5 text-sm font-medium transition-all duration-200 relative overflow-hidden shrink-0"
            x-bind:class="[
                sidebarIsMini ? 'justify-center px-2.5 py-3' : '',
                isActive('{{ route('dashboard') }}')
                    ? 'text-red-600 bg-red-50/70 dark:text-red-400 dark:bg-red-950/50'
                    : 'text-neutral-700 hover:text-red-600 hover:bg-red-50/50 dark:text-neutral-300 dark:hover:text-red-400 dark:hover:bg-red-950/50'
            ]" 
            x-bind:title="'Beranda'" 
            x-bind:aria-current="isActive('{{ route('dashboard') }}') ? 'page' : 'false'" 
            wire:navigate>
            <div class="shrink-0 w-5 h-5 flex items-center justify-center">
                <i class="fa-solid fa-house text-base group-hover:scale-110 transition-transform duration-200"></i>
            </div>
            <span x-show="!sidebarIsMini" x-transition class="truncate">Beranda</span>
            <div class="absolute inset-x-0 bottom-0 h-0.5 bg-linear-to-r from-red-500 to-orange-600 transform scale-x-0 group-hover:scale-x-100 transition-transform duration-300"
                x-bind:class="isActive('{{ route('dashboard') }}') ? 'scale-x-100' : ''">
            </div>
        </a>

        <!-- Daftar Peserta (Shortlist) -->
        <a href="{{ route('shortlist') }}" class="group flex items-center rounded-xl gap-3 px-3 py-2.5 text-sm font-medium transition-all duration-200 relative overflow-hidden shrink-0"
            x-bind:class="[
                sidebarIsMini ? 'justify-center px-2.5 py-3' : '',
                isActive('{{ route('shortlist') }}')
                    ? 'text-red-600 bg-red-50/70 dark:text-red-400 dark:bg-red-950/50'
                    : 'text-neutral-700 hover:text-red-600 hover:bg-red-50/50 dark:text-neutral-300 dark:hover:text-red-400 dark:hover:bg-red-950/50'
            ]" 
            x-bind:title="'Daftar Peserta'" 
            x-bind:aria-current="isActive('{{ route('shortlist') }}') ? 'page' : 'false'" 
            wire:navigate>
            <div class="shrink-0 w-5 h-5 flex items-center justify-center">
                <i class="fa-solid fa-users text-base group-hover:scale-110 transition-transform duration-200"></i>
            </div>
            <span x-show="!sidebarIsMini" x-transition class="truncate">Daftar Peserta</span>
            <div class="absolute inset-x-0 bottom-0 h-0.5 bg-linear-to-r from-red-500 to-orange-600 transform scale-x-0 group-hover:scale-x-100 transition-transform duration-300"
                x-bind:class="isActive('{{ route('shortlist') }}') ? 'scale-x-100' : ''">
            </div>
        </a>

        <!-- Tambah Standar (custom-standards.index) -->
        <a href="{{ route('custom-standards.index') }}" class="group flex items-center rounded-xl gap-3 px-3 py-2.5 text-sm font-medium transition-all duration-200 relative overflow-hidden shrink-0"
            x-bind:class="[
                sidebarIsMini ? 'justify-center px-2.5 py-3' : '',
                isActive('{{ route('custom-standards.index') }}')
                    ? 'text-red-600 bg-red-50/70 dark:text-red-400 dark:bg-red-950/50'
                    : 'text-neutral-700 hover:text-red-600 hover:bg-red-50/50 dark:text-neutral-300 dark:hover:text-red-400 dark:hover:bg-red-950/50'
            ]" 
            x-bind:title="'Tambah Standar'" 
            x-bind:aria-current="isActive('{{ route('custom-standards.index') }}') ? 'page' : 'false'" 
            wire:navigate>
            <div class="shrink-0 w-5 h-5 flex items-center justify-center">
                <i class="fa-solid fa-chart-line text-base group-hover:scale-110 transition-transform duration-200"></i>
            </div>
            <span x-show="!sidebarIsMini" x-transition class="truncate">Tambah Standar</span>
            <div class="absolute inset-x-0 bottom-0 h-0.5 bg-linear-to-r from-red-500 to-orange-600 transform scale-x-0 group-hover:scale-x-100 transition-transform duration-300"
                x-bind:class="isActive('{{ route('custom-standards.index') }}') ? 'scale-x-100' : ''">
            </div>
        </a>

        <!-- Divider -->
        <div class="h-px bg-neutral-200 dark:bg-neutral-800 my-3 shrink-0" x-show="!sidebarIsMini" x-transition></div>

        <!-- Individual Report Dropdown -->
        <div x-data="{
            isExpanded: localStorage.getItem('sidebar-dropdown-individual') === 'true',
            init() {
                this.$watch('isExpanded', value => localStorage.setItem('sidebar-dropdown-individual', value));
                
                const checkChildren = () => {
                    const current = window.location.pathname;
                    if (current.includes('/general-matching/') || 
                        current.includes('/general-mapping/') ||
                        current.includes('/general-psy-mapping/') ||
                        current.includes('/general-mc-mapping/') ||
                        current.includes('/spider-plot/') ||
                        current.includes('/ringkasan-mc-mapping/') ||
                        current.includes('/ringkasan-assessment/') ||
                        current.includes('/final-report/')) {
                        this.isExpanded = true;
                    }
                };
                checkChildren();
                window.addEventListener('livewire:navigated', checkChildren);
            }
        }" x-on:close-all-dropdowns.window="isExpanded = false" class="flex flex-col shrink-0">
            <button type="button" x-on:click="isExpanded = !isExpanded" id="individual-btn"
                aria-controls="individual-submenu" x-bind:aria-expanded="isExpanded ? 'true' : 'false'"
                class="group flex items-center justify-between rounded-xl gap-3 px-3 py-2.5 text-sm font-medium transition-all duration-200 relative overflow-hidden"
                x-bind:class="{
                    'justify-center px-2.5 py-3': sidebarIsMini,
                    'text-red-600 bg-red-50/70 dark:text-red-400 dark:bg-red-950/50': isExpanded,
                    'text-neutral-700 hover:text-red-600 hover:bg-red-50/50 dark:text-neutral-300 dark:hover:text-red-400 dark:hover:bg-red-950/50':
                        !isExpanded
                }"
                x-bind:title="sidebarIsMini ? 'Individual Report' : ''">

                <div class="flex items-center gap-3">
                    <div class="shrink-0 w-5 h-5 flex items-center justify-center">
                        <i class="fa-solid fa-file-invoice text-base group-hover:scale-110 transition-transform duration-200"></i>
                    </div>
                    <span x-show="!sidebarIsMini" x-transition class="truncate">
                        Individual Report
                    </span>
                </div>

                <svg x-show="!sidebarIsMini" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                    class="w-4 h-4 text-neutral-400 group-hover:text-red-600 transition-all duration-200 shrink-0"
                    x-bind:class="isExpanded ? 'rotate-180 text-red-600 dark:text-red-400' : 'rotate-0'" aria-hidden="true">
                    <path fill-rule="evenodd"
                        d="M5.22 8.22a.75.75 0 0 1 1.06 0L10 11.94l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 9.28a.75.75 0 0 1 0-1.06Z"
                        clip-rule="evenodd" />
                </svg>

                <div
                    class="absolute inset-x-0 bottom-0 h-0.5 bg-linear-to-r from-red-500 to-orange-600 transform scale-x-0 group-hover:scale-x-100 transition-transform duration-300">
                </div>
            </button>

            <ul x-cloak x-show="isExpanded" x-collapse id="individual-submenu" x-bind:class="sidebarIsMini ? 'hidden' : ''"
                class="mt-1 mb-1 ml-2 pl-4 border-l-2 border-neutral-200 dark:border-neutral-700 space-y-0.5">
                
                @if (!$this->canShowIndividualReports())
                    <li class="mx-2 my-2 p-2.5 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-600/50 rounded-lg">
                        <div class="flex items-start gap-2">
                            <i class="fa-solid fa-circle-exclamation text-yellow-600 dark:text-yellow-400 text-sm mt-0.5"></i>
                            <div class="flex-1">
                                <p class="text-xs font-semibold text-yellow-800 dark:text-yellow-300 mb-1">Pilih Peserta</p>
                                <p class="text-[10px] text-yellow-700 dark:text-yellow-400/90 leading-normal">
                                    Pilih Proyek & Peserta di <a href="{{ route('dashboard') }}" class="underline font-medium">Beranda</a> atau di <a href="{{ route('shortlist') }}" class="underline font-medium">Daftar Peserta</a>.
                                </p>
                            </div>
                        </div>
                    </li>
                @endif

                <!-- General Matching -->
                <li>
                    @if ($this->canShowIndividualReports())
                        <a href="{{ route('general_matching', ['eventCode' => $eventCode, 'testNumber' => $testNumber]) }}" wire:navigate
                            class="group flex items-center rounded-lg gap-2 px-3 py-1.5 text-xs transition-all duration-200"
                            x-bind:class="isActive('{{ route('general_matching', ['eventCode' => $eventCode, 'testNumber' => $testNumber]) }}')
                                ? 'text-red-600 bg-red-50/30 dark:text-red-400 dark:bg-red-950/30 font-medium'
                                : 'text-neutral-600 hover:text-red-600 hover:bg-red-50/30 dark:text-neutral-400 dark:hover:text-red-400 dark:hover:bg-red-950/30'">
                            <span class="w-1.5 h-1.5 rounded-full bg-neutral-300 group-hover:bg-red-500 transition-colors duration-200 dark:bg-neutral-600"
                                x-bind:class="isActive('{{ route('general_matching', ['eventCode' => $eventCode, 'testNumber' => $testNumber]) }}') ? 'bg-red-500' : ''"></span>
                            General Matching
                        </a>
                    @else
                        <span class="group flex items-center rounded-lg gap-2 px-3 py-1.5 text-xs text-neutral-400 dark:text-neutral-600 cursor-not-allowed">
                            <span class="w-1.5 h-1.5 rounded-full bg-neutral-200 dark:bg-neutral-800"></span>
                            General Matching
                        </span>
                    @endif
                </li>

                <!-- General Mapping -->
                <li>
                    @if ($this->canShowIndividualReports())
                        <a href="{{ route('general_mapping', ['eventCode' => $eventCode, 'testNumber' => $testNumber]) }}" wire:navigate
                            class="group flex items-center rounded-lg gap-2 px-3 py-1.5 text-xs transition-all duration-200"
                            x-bind:class="isActive('{{ route('general_mapping', ['eventCode' => $eventCode, 'testNumber' => $testNumber]) }}')
                                ? 'text-red-600 bg-red-50/30 dark:text-red-400 dark:bg-red-950/30 font-medium'
                                : 'text-neutral-600 hover:text-red-600 hover:bg-red-50/30 dark:text-neutral-400 dark:hover:text-red-400 dark:hover:bg-red-950/30'">
                            <span class="w-1.5 h-1.5 rounded-full bg-neutral-300 group-hover:bg-red-500 transition-colors duration-200 dark:bg-neutral-600"
                                x-bind:class="isActive('{{ route('general_mapping', ['eventCode' => $eventCode, 'testNumber' => $testNumber]) }}') ? 'bg-red-500' : ''"></span>
                            General Mapping
                        </a>
                    @else
                        <span class="group flex items-center rounded-lg gap-2 px-3 py-1.5 text-xs text-neutral-400 dark:text-neutral-600 cursor-not-allowed">
                            <span class="w-1.5 h-1.5 rounded-full bg-neutral-200 dark:bg-neutral-800"></span>
                            General Mapping
                        </span>
                    @endif
                </li>

                <!-- General Psychology Mapping -->
                <li>
                    @if ($this->canShowIndividualReports())
                        <a href="{{ route('general_psy_mapping', ['eventCode' => $eventCode, 'testNumber' => $testNumber]) }}" wire:navigate
                            class="group flex items-center rounded-lg gap-2 px-3 py-1.5 text-xs transition-all duration-200"
                            x-bind:class="isActive('{{ route('general_psy_mapping', ['eventCode' => $eventCode, 'testNumber' => $testNumber]) }}')
                                ? 'text-red-600 bg-red-50/30 dark:text-red-400 dark:bg-red-950/30 font-medium'
                                : 'text-neutral-600 hover:text-red-600 hover:bg-red-50/30 dark:text-neutral-400 dark:hover:text-red-400 dark:hover:bg-red-950/30'">
                            <span class="w-1.5 h-1.5 rounded-full bg-neutral-300 group-hover:bg-red-500 transition-colors duration-200 dark:bg-neutral-600"
                                x-bind:class="isActive('{{ route('general_psy_mapping', ['eventCode' => $eventCode, 'testNumber' => $testNumber]) }}') ? 'bg-red-500' : ''"></span>
                            General Psy Mapping
                        </a>
                    @else
                        <span class="group flex items-center rounded-lg gap-2 px-3 py-1.5 text-xs text-neutral-400 dark:text-neutral-600 cursor-not-allowed">
                            <span class="w-1.5 h-1.5 rounded-full bg-neutral-200 dark:bg-neutral-800"></span>
                            General Psy Mapping
                        </span>
                    @endif
                </li>

                <!-- General Managerial Competency Mapping -->
                <li>
                    @if ($this->canShowIndividualReports())
                        <a href="{{ route('general_mc_mapping', ['eventCode' => $eventCode, 'testNumber' => $testNumber]) }}" wire:navigate
                            class="group flex items-center rounded-lg gap-2 px-3 py-1.5 text-xs transition-all duration-200"
                            x-bind:class="isActive('{{ route('general_mc_mapping', ['eventCode' => $eventCode, 'testNumber' => $testNumber]) }}')
                                ? 'text-red-600 bg-red-50/30 dark:text-red-400 dark:bg-red-950/30 font-medium'
                                : 'text-neutral-600 hover:text-red-600 hover:bg-red-50/30 dark:text-neutral-400 dark:hover:text-red-400 dark:hover:bg-red-950/30'">
                            <span class="w-1.5 h-1.5 rounded-full bg-neutral-300 group-hover:bg-red-500 transition-colors duration-200 dark:bg-neutral-600"
                                x-bind:class="isActive('{{ route('general_mc_mapping', ['eventCode' => $eventCode, 'testNumber' => $testNumber]) }}') ? 'bg-red-500' : ''"></span>
                            General MC Mapping
                        </a>
                    @else
                        <span class="group flex items-center rounded-lg gap-2 px-3 py-1.5 text-xs text-neutral-400 dark:text-neutral-600 cursor-not-allowed">
                            <span class="w-1.5 h-1.5 rounded-full bg-neutral-200 dark:bg-neutral-800"></span>
                            General MC Mapping
                        </span>
                    @endif
                </li>

                <!-- Spider Plot -->
                <li>
                    @if ($this->canShowIndividualReports())
                        <a href="{{ route('spider_plot', ['eventCode' => $eventCode, 'testNumber' => $testNumber]) }}" wire:navigate
                            class="group flex items-center rounded-lg gap-2 px-3 py-1.5 text-xs transition-all duration-200"
                            x-bind:class="isActive('{{ route('spider_plot', ['eventCode' => $eventCode, 'testNumber' => $testNumber]) }}')
                                ? 'text-red-600 bg-red-50/30 dark:text-red-400 dark:bg-red-950/30 font-medium'
                                : 'text-neutral-600 hover:text-red-600 hover:bg-red-50/30 dark:text-neutral-400 dark:hover:text-red-400 dark:hover:bg-red-950/30'">
                            <span class="w-1.5 h-1.5 rounded-full bg-neutral-300 group-hover:bg-red-500 transition-colors duration-200 dark:bg-neutral-600"
                                x-bind:class="isActive('{{ route('spider_plot', ['eventCode' => $eventCode, 'testNumber' => $testNumber]) }}') ? 'bg-red-500' : ''"></span>
                            Spider Plot
                        </a>
                    @else
                        <span class="group flex items-center rounded-lg gap-2 px-3 py-1.5 text-xs text-neutral-400 dark:text-neutral-600 cursor-not-allowed">
                            <span class="w-1.5 h-1.5 rounded-full bg-neutral-200 dark:bg-neutral-800"></span>
                            Spider Plot
                        </span>
                    @endif
                </li>

                <!-- Ringkasan MC Mapping -->
                <li>
                    @if ($this->canShowIndividualReports())
                        <a href="{{ route('ringkasan_mc_mapping', ['eventCode' => $eventCode, 'testNumber' => $testNumber]) }}" wire:navigate
                            class="group flex items-center rounded-lg gap-2 px-3 py-1.5 text-xs transition-all duration-200"
                            x-bind:class="isActive('{{ route('ringkasan_mc_mapping', ['eventCode' => $eventCode, 'testNumber' => $testNumber]) }}')
                                ? 'text-red-600 bg-red-50/30 dark:text-red-400 dark:bg-red-950/30 font-medium'
                                : 'text-neutral-600 hover:text-red-600 hover:bg-red-50/30 dark:text-neutral-400 dark:hover:text-red-400 dark:hover:bg-red-950/30'">
                            <span class="w-1.5 h-1.5 rounded-full bg-neutral-300 group-hover:bg-red-500 transition-colors duration-200 dark:bg-neutral-600"
                                x-bind:class="isActive('{{ route('ringkasan_mc_mapping', ['eventCode' => $eventCode, 'testNumber' => $testNumber]) }}') ? 'bg-red-500' : ''"></span>
                            Ringkasan MC Mapping
                        </a>
                    @else
                        <span class="group flex items-center rounded-lg gap-2 px-3 py-1.5 text-xs text-neutral-400 dark:text-neutral-600 cursor-not-allowed">
                            <span class="w-1.5 h-1.5 rounded-full bg-neutral-200 dark:bg-neutral-800"></span>
                            Ringkasan MC Mapping
                        </span>
                    @endif
                </li>

                <!-- Ringkasan Assessment -->
                <li>
                    @if ($this->canShowIndividualReports())
                        <a href="{{ route('ringkasan_assessment', ['eventCode' => $eventCode, 'testNumber' => $testNumber]) }}" wire:navigate
                            class="group flex items-center rounded-lg gap-2 px-3 py-1.5 text-xs transition-all duration-200"
                            x-bind:class="isActive('{{ route('ringkasan_assessment', ['eventCode' => $eventCode, 'testNumber' => $testNumber]) }}')
                                ? 'text-red-600 bg-red-50/30 dark:text-red-400 dark:bg-red-950/30 font-medium'
                                : 'text-neutral-600 hover:text-red-600 hover:bg-red-50/30 dark:text-neutral-400 dark:hover:text-red-400 dark:hover:bg-red-950/30'">
                            <span class="w-1.5 h-1.5 rounded-full bg-neutral-300 group-hover:bg-red-500 transition-colors duration-200 dark:bg-neutral-600"
                                x-bind:class="isActive('{{ route('ringkasan_assessment', ['eventCode' => $eventCode, 'testNumber' => $testNumber]) }}') ? 'bg-red-500' : ''"></span>
                            Ringkasan Asesmen
                        </a>
                    @else
                        <span class="group flex items-center rounded-lg gap-2 px-3 py-1.5 text-xs text-neutral-400 dark:text-neutral-600 cursor-not-allowed">
                            <span class="w-1.5 h-1.5 rounded-full bg-neutral-200 dark:bg-neutral-800"></span>
                            Ringkasan Asesmen
                        </span>
                    @endif
                </li>

                <!-- Laporan Individu -->
                <li>
                    @if ($this->canShowIndividualReports())
                        <a href="{{ route('final_report', ['eventCode' => $eventCode, 'testNumber' => $testNumber]) }}" wire:navigate
                            class="group flex items-center rounded-lg gap-2 px-3 py-1.5 text-xs transition-all duration-200"
                            x-bind:class="isActive('{{ route('final_report', ['eventCode' => $eventCode, 'testNumber' => $testNumber]) }}')
                                ? 'text-red-600 bg-red-50/30 dark:text-red-400 dark:bg-red-950/30 font-medium'
                                : 'text-neutral-600 hover:text-red-600 hover:bg-red-50/30 dark:text-neutral-400 dark:hover:text-red-400 dark:hover:bg-red-950/30'">
                            <span class="w-1.5 h-1.5 rounded-full bg-neutral-300 group-hover:bg-red-500 transition-colors duration-200 dark:bg-neutral-600"
                                x-bind:class="isActive('{{ route('final_report', ['eventCode' => $eventCode, 'testNumber' => $testNumber]) }}') ? 'bg-red-500' : ''"></span>
                            Laporan Individu
                        </a>
                    @else
                        <span class="group flex items-center rounded-lg gap-2 px-3 py-1.5 text-xs text-neutral-400 dark:text-neutral-600 cursor-not-allowed">
                            <span class="w-1.5 h-1.5 rounded-full bg-neutral-200 dark:bg-neutral-800"></span>
                            Laporan Individu
                        </span>
                    @endif
                </li>
            </ul>
        </div>

        <!-- General Report Dropdown -->
        <div x-data="{
            isExpanded: localStorage.getItem('sidebar-dropdown-general') === 'true',
            init() {
                this.$watch('isExpanded', value => localStorage.setItem('sidebar-dropdown-general', value));
                
                const checkChildren = () => {
                    const current = window.location.pathname;
                    if (current.includes('/ranking-psy-mapping') || 
                        current.includes('/ranking-mc-mapping') ||
                        current.includes('/rekap-ranking-assessment') ||
                        current.includes('/statistic') ||
                        current.includes('/training-recommendation') ||
                        current.includes('/standard-mc') ||
                        current.includes('/standard-psikometrik') ||
                        current.includes('/general-report/mmpi')) {
                        this.isExpanded = true;
                    }
                };
                checkChildren();
                window.addEventListener('livewire:navigated', checkChildren);
            }
        }" x-on:close-all-dropdowns.window="isExpanded = false" class="flex flex-col shrink-0">
            <button type="button" x-on:click="isExpanded = !isExpanded" id="general-btn"
                aria-controls="general-submenu" x-bind:aria-expanded="isExpanded ? 'true' : 'false'"
                class="group flex items-center justify-between rounded-xl gap-3 px-3 py-2.5 text-sm font-medium transition-all duration-200 relative overflow-hidden"
                x-bind:class="{
                    'justify-center px-2.5 py-3': sidebarIsMini,
                    'text-red-600 bg-red-50/70 dark:text-red-400 dark:bg-red-950/50': isExpanded,
                    'text-neutral-700 hover:text-red-600 hover:bg-red-50/50 dark:text-neutral-300 dark:hover:text-red-400 dark:hover:bg-red-950/50':
                        !isExpanded
                }"
                x-bind:title="sidebarIsMini ? 'General Report' : ''">

                <div class="flex items-center gap-3">
                    <div class="shrink-0 w-5 h-5 flex items-center justify-center">
                        <i class="fa-solid fa-chart-pie text-base group-hover:scale-110 transition-transform duration-200"></i>
                    </div>
                    <span x-show="!sidebarIsMini" x-transition class="truncate">
                        General Report
                    </span>
                </div>

                <svg x-show="!sidebarIsMini" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                    class="w-4 h-4 text-neutral-400 group-hover:text-red-600 transition-all duration-200 shrink-0"
                    x-bind:class="isExpanded ? 'rotate-180 text-red-600 dark:text-red-400' : 'rotate-0'" aria-hidden="true">
                    <path fill-rule="evenodd"
                        d="M5.22 8.22a.75.75 0 0 1 1.06 0L10 11.94l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 9.28a.75.75 0 0 1 0-1.06Z"
                        clip-rule="evenodd" />
                </svg>

                <div
                    class="absolute inset-x-0 bottom-0 h-0.5 bg-linear-to-r from-red-500 to-orange-600 transform scale-x-0 group-hover:scale-x-100 transition-transform duration-300">
                </div>
            </button>

            <ul x-cloak x-show="isExpanded" x-collapse id="general-submenu" x-bind:class="sidebarIsMini ? 'hidden' : ''"
                class="mt-1 mb-1 ml-2 pl-4 border-l-2 border-neutral-200 dark:border-neutral-700 space-y-0.5">
                
                <!-- Ranking Psychology Mapping -->
                <li>
                    <a href="{{ route('ranking-psy-mapping') }}" wire:navigate
                        class="group flex items-center rounded-lg gap-2 px-3 py-1.5 text-xs transition-all duration-200"
                        x-bind:class="isActive('{{ route('ranking-psy-mapping') }}')
                            ? 'text-red-600 bg-red-50/30 dark:text-red-400 dark:bg-red-950/30 font-medium'
                            : 'text-neutral-600 hover:text-red-600 hover:bg-red-50/30 dark:text-neutral-400 dark:hover:text-red-400 dark:hover:bg-red-950/30'">
                        <span class="w-1.5 h-1.5 rounded-full bg-neutral-300 group-hover:bg-red-500 transition-colors duration-200 dark:bg-neutral-600"
                            x-bind:class="isActive('{{ route('ranking-psy-mapping') }}') ? 'bg-red-500' : ''"></span>
                        Ranking Psy Mapping
                    </a>
                </li>

                <!-- Ranking Managerial Competency Mapping -->
                <li>
                    <a href="{{ route('ranking-mc-mapping') }}" wire:navigate
                        class="group flex items-center rounded-lg gap-2 px-3 py-1.5 text-xs transition-all duration-200"
                        x-bind:class="isActive('{{ route('ranking-mc-mapping') }}')
                            ? 'text-red-600 bg-red-50/30 dark:text-red-400 dark:bg-red-950/30 font-medium'
                            : 'text-neutral-600 hover:text-red-600 hover:bg-red-50/30 dark:text-neutral-400 dark:hover:text-red-400 dark:hover:bg-red-950/30'">
                        <span class="w-1.5 h-1.5 rounded-full bg-neutral-300 group-hover:bg-red-500 transition-colors duration-200 dark:bg-neutral-600"
                            x-bind:class="isActive('{{ route('ranking-mc-mapping') }}') ? 'bg-red-500' : ''"></span>
                        Ranking MC Mapping
                    </a>
                </li>

                <!-- Rekap Ranking Assessment -->
                <li>
                    <a href="{{ route('rekap-ranking-assessment') }}" wire:navigate
                        class="group flex items-center rounded-lg gap-2 px-3 py-1.5 text-xs transition-all duration-200"
                        x-bind:class="isActive('{{ route('rekap-ranking-assessment') }}')
                            ? 'text-red-600 bg-red-50/30 dark:text-red-400 dark:bg-red-950/30 font-medium'
                            : 'text-neutral-600 hover:text-red-600 hover:bg-red-50/30 dark:text-neutral-400 dark:hover:text-red-400 dark:hover:bg-red-950/30'">
                        <span class="w-1.5 h-1.5 rounded-full bg-neutral-300 group-hover:bg-red-500 transition-colors duration-200 dark:bg-neutral-600"
                            x-bind:class="isActive('{{ route('rekap-ranking-assessment') }}') ? 'bg-red-500' : ''"></span>
                        Ranking Ringkasan Asesmen
                    </a>
                </li>

                <!-- Statistik -->
                <li>
                    <a href="{{ route('statistic') }}" wire:navigate
                        class="group flex items-center rounded-lg gap-2 px-3 py-1.5 text-xs transition-all duration-200"
                        x-bind:class="isActive('{{ route('statistic') }}')
                            ? 'text-red-600 bg-red-50/30 dark:text-red-400 dark:bg-red-950/30 font-medium'
                            : 'text-neutral-600 hover:text-red-600 hover:bg-red-50/30 dark:text-neutral-400 dark:hover:text-red-400 dark:hover:bg-red-950/30'">
                        <span class="w-1.5 h-1.5 rounded-full bg-neutral-300 group-hover:bg-red-500 transition-colors duration-200 dark:bg-neutral-600"
                            x-bind:class="isActive('{{ route('statistic') }}') ? 'bg-red-500' : ''"></span>
                        Statistik
                    </a>
                </li>

                <!-- Training Recommendation -->
                <li>
                    <a href="{{ route('training-recommendation') }}" wire:navigate
                        class="group flex items-center rounded-lg gap-2 px-3 py-1.5 text-xs transition-all duration-200"
                        x-bind:class="isActive('{{ route('training-recommendation') }}')
                            ? 'text-red-600 bg-red-50/30 dark:text-red-400 dark:bg-red-950/30 font-medium'
                            : 'text-neutral-600 hover:text-red-600 hover:bg-red-50/30 dark:text-neutral-400 dark:hover:text-red-400 dark:hover:bg-red-950/30'">
                        <span class="w-1.5 h-1.5 rounded-full bg-neutral-300 group-hover:bg-red-500 transition-colors duration-200 dark:bg-neutral-600"
                            x-bind:class="isActive('{{ route('training-recommendation') }}') ? 'bg-red-500' : ''"></span>
                        Training Recommendation
                    </a>
                </li>

                <!-- Standar MC -->
                <li>
                    <a href="{{ route('standard-mc') }}" wire:navigate
                        class="group flex items-center rounded-lg gap-2 px-3 py-1.5 text-xs transition-all duration-200"
                        x-bind:class="isActive('{{ route('standard-mc') }}')
                            ? 'text-red-600 bg-red-50/30 dark:text-red-400 dark:bg-red-950/30 font-medium'
                            : 'text-neutral-600 hover:text-red-600 hover:bg-red-50/30 dark:text-neutral-400 dark:hover:text-red-400 dark:hover:bg-red-950/30'">
                        <span class="w-1.5 h-1.5 rounded-full bg-neutral-300 group-hover:bg-red-500 transition-colors duration-200 dark:bg-neutral-600"
                            x-bind:class="isActive('{{ route('standard-mc') }}') ? 'bg-red-500' : ''"></span>
                        Standar MC Mapping
                    </a>
                </li>

                <!-- Standar Potential Mapping -->
                <li>
                    <a href="{{ route('standard-psikometrik') }}" wire:navigate
                        class="group flex items-center rounded-lg gap-2 px-3 py-1.5 text-xs transition-all duration-200"
                        x-bind:class="isActive('{{ route('standard-psikometrik') }}')
                            ? 'text-red-600 bg-red-50/30 dark:text-red-400 dark:bg-red-950/30 font-medium'
                            : 'text-neutral-600 hover:text-red-600 hover:bg-red-50/30 dark:text-neutral-400 dark:hover:text-red-400 dark:hover:bg-red-950/30'">
                        <span class="w-1.5 h-1.5 rounded-full bg-neutral-300 group-hover:bg-red-500 transition-colors duration-200 dark:bg-neutral-600"
                            x-bind:class="isActive('{{ route('standard-psikometrik') }}') ? 'bg-red-500' : ''"></span>
                        Standar Potential Mapping
                    </a>
                </li>

                <!-- MMPI -->
                <li>
                    <a href="{{ route('general-report.mmpi') }}" wire:navigate
                        class="group flex items-center rounded-lg gap-2 px-3 py-1.5 text-xs transition-all duration-200"
                        x-bind:class="isActive('{{ route('general-report.mmpi') }}')
                            ? 'text-red-600 bg-red-50/30 dark:text-red-400 dark:bg-red-950/30 font-medium'
                            : 'text-neutral-600 hover:text-red-600 hover:bg-red-50/30 dark:text-neutral-400 dark:hover:text-red-400 dark:hover:bg-red-950/30'">
                        <span class="w-1.5 h-1.5 rounded-full bg-neutral-300 group-hover:bg-red-500 transition-colors duration-200 dark:bg-neutral-600"
                            x-bind:class="isActive('{{ route('general-report.mmpi') }}') ? 'bg-red-500' : ''"></span>
                        MMPI
                    </a>
                </li>
            </ul>
        </div>

        <!-- Laporan Alat Tes -->
        <a href="{{ route('laporan-alat-tes') }}" class="group flex items-center rounded-xl gap-3 px-3 py-2.5 text-sm font-medium transition-all duration-200 relative overflow-hidden shrink-0"
            x-bind:class="[
                sidebarIsMini ? 'justify-center px-2.5 py-3' : '',
                isActive('{{ route('laporan-alat-tes') }}')
                    ? 'text-red-600 bg-red-50/70 dark:text-red-400 dark:bg-red-950/50'
                    : 'text-neutral-700 hover:text-red-600 hover:bg-red-50/50 dark:text-neutral-300 dark:hover:text-red-400 dark:hover:bg-red-950/50'
            ]" 
            x-bind:title="'Laporan Alat Tes'" 
            x-bind:aria-current="isActive('{{ route('laporan-alat-tes') }}') ? 'page' : 'false'" 
            wire:navigate>
            <div class="shrink-0 w-5 h-5 flex items-center justify-center">
                <i class="fa-solid fa-square-poll-vertical text-base group-hover:scale-110 transition-transform duration-200"></i>
            </div>
            <span x-show="!sidebarIsMini" x-transition class="truncate">Laporan Alat Tes</span>
            <div class="absolute inset-x-0 bottom-0 h-0.5 bg-linear-to-r from-red-500 to-orange-600 transform scale-x-0 group-hover:scale-x-100 transition-transform duration-300"
                x-bind:class="isActive('{{ route('laporan-alat-tes') }}') ? 'scale-x-100' : ''">
            </div>
        </a>

        <!-- Talent Pool Management -->
        <a href="{{ route('talentpool') }}" class="group flex items-center rounded-xl gap-3 px-3 py-2.5 text-sm font-medium transition-all duration-200 relative overflow-hidden shrink-0"
            x-bind:class="[
                sidebarIsMini ? 'justify-center px-2.5 py-3' : '',
                isActive('{{ route('talentpool') }}')
                    ? 'text-red-600 bg-red-50/70 dark:text-red-400 dark:bg-red-950/50'
                    : 'text-neutral-700 hover:text-red-600 hover:bg-red-50/50 dark:text-neutral-300 dark:hover:text-red-400 dark:hover:bg-red-950/50'
            ]" 
            x-bind:title="'Talent Pool Management'" 
            x-bind:aria-current="isActive('{{ route('talentpool') }}') ? 'page' : 'false'" 
            wire:navigate>
            <div class="shrink-0 w-5 h-5 flex items-center justify-center">
                <i class="fa-solid fa-briefcase text-base group-hover:scale-110 transition-transform duration-200"></i>
            </div>
            <span x-show="!sidebarIsMini" x-transition class="truncate">Talent Pool Management</span>
            <div class="absolute inset-x-0 bottom-0 h-0.5 bg-linear-to-r from-red-500 to-orange-600 transform scale-x-0 group-hover:scale-x-100 transition-transform duration-300"
                x-bind:class="isActive('{{ route('talentpool') }}') ? 'scale-x-100' : ''">
            </div>
        </a>

    </div>

    <!-- Close All Menus Button -->
    <div class="pt-4 border-t border-neutral-200/60 dark:border-neutral-800/60">
        <div x-data="{
            closeAllMenus() {
                localStorage.setItem('sidebar-dropdown-individual', 'false');
                localStorage.setItem('sidebar-dropdown-general', 'false');
                window.dispatchEvent(new CustomEvent('close-all-dropdowns'));
            }
        }">
            <button 
                type="button" 
                @click="closeAllMenus()" 
                aria-label="Tutup Semua Menu"
                class="w-full group flex items-center justify-center gap-2 rounded-xl px-3 py-2.5 text-sm font-medium transition-all duration-200 relative overflow-hidden text-neutral-700 hover:text-red-600 hover:bg-red-50/50 dark:text-neutral-300 dark:hover:text-red-400 dark:hover:bg-red-950/50 border border-neutral-200 dark:border-neutral-700 hover:border-red-300 dark:hover:border-red-800"
                x-bind:class="sidebarIsMini ? 'px-2.5 py-3' : ''"
                x-bind:title="sidebarIsMini ? 'Tutup Semua Menu' : ''"
            >
                <div class="flex items-center gap-2">
                    <div class="shrink-0 w-5 h-5 flex items-center justify-center">
                        <i class="fa-solid fa-xmark text-base group-hover:scale-110 group-hover:rotate-180 transition-transform duration-300"></i>
                    </div>
                    
                    <span x-show="!sidebarIsMini" x-transition class="truncate">
                        Tutup Semua Menu
                    </span>
                </div>
            </button>
        </div>
    </div>
</nav>
