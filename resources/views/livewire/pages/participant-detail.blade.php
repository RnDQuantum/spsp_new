<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header dengan tombol kembali - DARK MODE READY -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <a href="{{ route('shortlist') }}"
                    class="inline-flex items-center px-3 py-2 text-sm font-medium 
                           text-gray-700 dark:text-gray-300 
                           bg-white dark:bg-[#171412] border border-warm-border dark:border-[#25211e] rounded-md hover:bg-warm-ivory dark:hover:bg-[#1f1b18] 
                           focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 
                           dark:focus:ring-blue-400">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7">
                        </path>
                    </svg>
                    Kembali ke Shortlist
                </a>
                <h1 class="font-display text-2xl font-bold tracking-tight text-primary-ink dark:text-neutral-100">Detail Peserta Assessment</h1>
            </div>
        </div>
    </div>

    @if ($participant)
        <!-- Biodata Singkat - DARK MODE READY -->
        <div class="bg-white dark:bg-[#171412] rounded-md border border-warm-border dark:border-[#25211e] shadow-xs overflow-hidden mb-8">
            <div class="px-6 py-4 bg-warm-ivory dark:bg-[#1f1b18] border-b border-warm-border dark:border-[#25211e]">
                <h2 class="font-display text-lg font-bold text-primary-ink dark:text-neutral-100">Biodata Peserta</h2>
            </div>

            <div class="px-6 py-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <!-- Informasi Personal -->
                    <div class="space-y-4">
                        <h3
                            class="font-display text-base font-bold text-primary-ink dark:text-neutral-100 border-b border-warm-border dark:border-[#25211e] pb-2">
                            Informasi Personal</h3>

                        <div>
                            <label class="block text-xs font-semibold text-primary-ink/75 dark:text-neutral-400">Nama
                                Lengkap</label>
                            <p class="mt-1 text-sm font-medium text-primary-ink dark:text-neutral-100">{{ $participant->name }}</p>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-primary-ink/75 dark:text-neutral-400">NIP/SKB
                                Number</label>
                            <p class="mt-1 text-sm font-medium text-primary-ink dark:text-neutral-100">
                                {{ $participant->skb_number ?? '-' }}</p>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-primary-ink/75 dark:text-neutral-400">Email</label>
                            <p class="mt-1 text-sm font-medium text-primary-ink dark:text-neutral-100">{{ $participant->email ?? '-' }}
                            </p>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-primary-ink/75 dark:text-neutral-400">Telepon</label>
                            <p class="mt-1 text-sm font-medium text-primary-ink dark:text-neutral-100">{{ $participant->phone ?? '-' }}
                            </p>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-primary-ink/75 dark:text-neutral-400">Jenis
                                Kelamin</label>
                            <p class="mt-1 text-sm font-medium text-primary-ink dark:text-neutral-100">{{ $participant->gender ?? '-' }}
                            </p>
                        </div>
                    </div>

                    <!-- Informasi Assessment -->
                    <div class="space-y-4">
                        <h3
                            class="font-display text-base font-bold text-primary-ink dark:text-neutral-100 border-b border-warm-border dark:border-[#25211e] pb-2">
                            Informasi Assessment</h3>

                        <div>
                            <label class="block text-xs font-semibold text-primary-ink/75 dark:text-neutral-400">Kode
                                Proyek</label>
                            <p class="mt-1 text-sm font-medium text-primary-ink dark:text-neutral-100">
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                      bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300">
                                    {{ $participant->assessmentEvent->code ?? '-' }}
                                </span>
                            </p>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-primary-ink/75 dark:text-neutral-400">Nama
                                Proyek</label>
                            <p class="mt-1 text-sm font-medium text-primary-ink dark:text-neutral-100">
                                {{ $participant->assessmentEvent->name ?? '-' }}</p>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-primary-ink/75 dark:text-neutral-400">Batch</label>
                            <p class="mt-1 text-sm font-medium text-primary-ink dark:text-neutral-100">
                                {{ $participant->batch->name ?? '-' }}</p>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-primary-ink/75 dark:text-neutral-400">Posisi</label>
                            <p class="mt-1 text-sm font-medium text-primary-ink dark:text-neutral-100">
                                {{ $participant->positionFormation->name ?? '-' }}</p>
                            @if ($participant->positionFormation?->code)
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Kode:
                                    {{ $participant->positionFormation->code }}</p>
                            @endif
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-primary-ink/75 dark:text-neutral-400">No. Test</label>
                            <p class="mt-1 text-sm font-medium text-primary-ink dark:text-neutral-100">
                                {{ $participant->test_number ?? '-' }}</p>
                        </div>
                    </div>

                    <!-- Informasi Tanggal -->
                    <div class="space-y-4">
                        <h3
                            class="font-display text-base font-bold text-primary-ink dark:text-neutral-100 border-b border-warm-border dark:border-[#25211e] pb-2">
                            Informasi Tanggal</h3>

                        <div>
                            <label class="block text-xs font-semibold text-primary-ink/75 dark:text-neutral-400">Tanggal
                                Assessment</label>
                            <p class="mt-1 text-sm font-medium text-primary-ink dark:text-neutral-100">
                                {{ $participant->assessment_date?->format('d M Y') ?? '-' }}
                            </p>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-primary-ink/75 dark:text-neutral-400">Tanggal
                                Dibuat</label>
                            <p class="mt-1 text-sm font-medium text-primary-ink dark:text-neutral-100">
                                {{ $participant->created_at?->format('d M Y H:i') ?? '-' }}
                            </p>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-primary-ink/75 dark:text-neutral-400">Terakhir
                                Diupdate</label>
                            <p class="mt-1 text-sm font-medium text-primary-ink dark:text-neutral-100">
                                {{ $participant->updated_at?->format('d M Y H:i') ?? '-' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Menu Tabs: Individual Report & Data Pelengkap HCA - DARK MODE READY -->
        <div class="bg-white dark:bg-[#171412] rounded-md border border-warm-border dark:border-[#25211e] shadow-xs overflow-hidden">
            <div class="px-6 py-3 bg-warm-ivory dark:bg-[#1f1b18] border-b border-warm-border dark:border-[#25211e] flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center gap-2">
                    <button 
                        type="button" 
                        wire:click="setMainTab('reports')"
                        class="px-4 py-2 text-xs font-semibold rounded-md transition-all flex items-center gap-2 cursor-pointer {{ $mainTab === 'reports' ? 'bg-primary-ink text-white shadow-xs dark:bg-accent-amber' : 'text-primary-ink/80 dark:text-neutral-300 hover:bg-black/5 dark:hover:bg-white/5' }}"
                    >
                        <i class="fas fa-file-lines text-xs"></i>
                        <span>Individual Reports & HCA (9 Modul)</span>
                    </button>

                    <button 
                        type="button" 
                        wire:click="setMainTab('supplementary_data')"
                        class="px-4 py-2 text-xs font-semibold rounded-md transition-all flex items-center gap-2 cursor-pointer {{ $mainTab === 'supplementary_data' ? 'bg-primary-ink text-white shadow-xs dark:bg-accent-amber' : 'text-primary-ink/80 dark:text-neutral-300 hover:bg-black/5 dark:hover:bg-white/5' }}"
                    >
                        <i class="fas fa-sliders text-xs"></i>
                        <span>Data Pelengkap HCA</span>
                        <span class="text-[10px] px-1.5 py-0.2 rounded-full {{ $mainTab === 'supplementary_data' ? 'bg-white/20 text-white' : 'bg-accent-amber/20 text-accent-amber font-bold' }}">
                            {{ count($performanceRecords) + count($careerHistories) }} Record
                        </span>
                    </button>
                </div>

                @if($mainTab === 'supplementary_data')
                <div class="flex items-center gap-2">
                    <button 
                        type="button" 
                        wire:click="saveSupplementaryData"
                        wire:loading.attr="disabled"
                        class="px-3.5 py-1.5 bg-accent-amber hover:bg-accent-amber/90 text-white text-xs font-semibold rounded-md transition-all shadow-xs flex items-center gap-1.5 cursor-pointer disabled:opacity-50"
                    >
                        <span wire:loading.remove wire:target="saveSupplementaryData">
                            <i class="fas fa-floppy-disk text-xs"></i> Simpan Data Pelengkap
                        </span>
                        <span wire:loading wire:target="saveSupplementaryData">
                            <i class="fas fa-spinner fa-spin text-xs"></i> Menyimpan...
                        </span>
                    </button>
                </div>
                @endif
            </div>

            @if($successMessage)
            <div class="mx-6 mt-4 p-3 bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-800/40 rounded-lg text-forest-green dark:text-emerald-400 text-xs flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <i class="fas fa-circle-check text-sm"></i>
                    <span>{{ $successMessage }}</span>
                </div>
                <button type="button" wire:click="$set('successMessage', null)" class="text-slate-400 hover:text-slate-600">
                    <i class="fas fa-times text-xs"></i>
                </button>
            </div>
            @endif

            @if($mainTab === 'reports')
            <div class="px-6 py-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                    <!-- KARTU 1: General Matching -->
                    <div class="h-full">
                        <div
                            class="flex flex-col h-full border border-warm-border dark:border-[#25211e] rounded-md p-6 hover:border-accent-amber/50 dark:hover:border-amber-500/50 transition-all bg-warm-ivory/30 dark:bg-[#1f1b18]/50 shadow-xs">
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center space-x-3">
                                    <div class="flex-shrink-0">
                                        <div
                                            class="w-10 h-10 bg-warm-ivory dark:bg-[#25211e] border border-warm-border dark:border-[#25211e] text-accent-amber rounded-lg flex items-center justify-center">
                                            <svg class="w-6 h-6 text-accent-amber dark:text-amber-500" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                                                </path>
                                            </svg>
                                        </div>
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">General
                                            Matching</h3>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">Analisis kesesuaian aspek
                                            potensi dan kompetensi</p>
                                    </div>
                                </div>
                            </div>
                            <div class="flex-grow mb-4">
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    Laporan yang menampilkan analisis mendalam terhadap aspek potensi dan kompetensi
                                    peserta, termasuk perbandingan dengan standar yang ditetapkan.
                                </p>
                            </div>
                            <div class="mt-auto">
                                <a href="{{ route('general_matching', ['eventCode' => $participant->assessmentEvent->code, 'testNumber' => $participant->test_number]) }}"
                                    class="w-full inline-flex items-center justify-center px-4 py-2 bg-primary-ink hover:bg-[#2c2724] text-warm-ivory dark:bg-amber-600 dark:hover:bg-amber-700 dark:text-white text-xs font-semibold rounded-md transition-colors shadow-xs">
                                    Lihat General Matching
                                    <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- KARTU 2: General Mapping -->
                    <div class="h-full">
                        <div
                            class="flex flex-col h-full border border-warm-border dark:border-[#25211e] rounded-md p-6 hover:border-accent-amber/50 dark:hover:border-amber-500/50 transition-all bg-warm-ivory/30 dark:bg-[#1f1b18]/50 shadow-xs">
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center space-x-3">
                                    <div class="flex-shrink-0">
                                        <div
                                            class="w-10 h-10 bg-warm-ivory dark:bg-[#25211e] border border-warm-border dark:border-[#25211e] text-accent-amber rounded-lg flex items-center justify-center">
                                            <svg class="w-6 h-6 text-accent-amber dark:text-amber-500" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                        </div>
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">General Mapping
                                        </h3>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">Mapping komprehensif dengan
                                            visualisasi grafik</p>
                                    </div>
                                </div>
                            </div>
                            <div class="flex-grow mb-4">
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    Laporan yang menampilkan mapping komprehensif dengan visualisasi grafik, analisis
                                    gap, dan kesimpulan berdasarkan toleransi yang ditetapkan.
                                </p>
                            </div>
                            <div class="mt-auto">
                                <a href="{{ route('general_mapping', ['eventCode' => $participant->assessmentEvent->code, 'testNumber' => $participant->test_number]) }}"
                                    class="w-full inline-flex items-center justify-center px-4 py-2 bg-primary-ink hover:bg-[#2c2724] text-warm-ivory dark:bg-amber-600 dark:hover:bg-amber-700 dark:text-white text-xs font-semibold rounded-md transition-colors shadow-xs">
                                    Lihat General Mapping
                                    <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- KARTU 3: Managerial Competency Mapping -->
                    <div class="h-full">
                        <div
                            class="flex flex-col h-full border border-warm-border dark:border-[#25211e] rounded-md p-6 hover:border-accent-amber/50 dark:hover:border-amber-500/50 transition-all bg-warm-ivory/30 dark:bg-[#1f1b18]/50 shadow-xs">
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center space-x-3">
                                    <div class="flex-shrink-0">
                                        <div
                                            class="w-10 h-10 bg-warm-ivory dark:bg-[#25211e] border border-warm-border dark:border-[#25211e] text-accent-amber rounded-lg flex items-center justify-center">
                                            <svg class="w-6 h-6 text-accent-amber dark:text-amber-500" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z">
                                                </path>
                                            </svg>
                                        </div>
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Managerial
                                            Competency Mapping</h3>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">Mapping aspek kompetensi
                                            saja</p>
                                    </div>
                                </div>
                            </div>
                            <div class="flex-grow mb-4">
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    Laporan yang fokus pada aspek kompetensi (Managerial Competency) dengan visualisasi
                                    spider plot, analisis gap, dan tolerance selector.
                                </p>
                            </div>
                            <div class="mt-auto">
                                <a href="{{ route('general_mc_mapping', ['eventCode' => $participant->assessmentEvent->code, 'testNumber' => $participant->test_number]) }}"
                                    class="w-full inline-flex items-center justify-center px-4 py-2 bg-primary-ink hover:bg-[#2c2724] text-warm-ivory dark:bg-amber-600 dark:hover:bg-amber-700 dark:text-white text-xs font-semibold rounded-md transition-colors shadow-xs">
                                    Lihat MC Mapping
                                    <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- KARTU 4: Psychology Mapping -->
                    <div class="h-full">
                        <div
                            class="flex flex-col h-full border border-warm-border dark:border-[#25211e] rounded-md p-6 hover:border-accent-amber/50 dark:hover:border-amber-500/50 transition-all bg-warm-ivory/30 dark:bg-[#1f1b18]/50 shadow-xs">
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center space-x-3">
                                    <div class="flex-shrink-0">
                                        <div
                                            class="w-10 h-10 bg-warm-ivory dark:bg-[#25211e] border border-warm-border dark:border-[#25211e] text-accent-amber rounded-lg flex items-center justify-center">
                                            <svg class="w-6 h-6 text-accent-amber dark:text-amber-500" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z">
                                                </path>
                                            </svg>
                                        </div>
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Psychology
                                            Mapping</h3>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">Mapping aspek potensi saja
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="flex-grow mb-4">
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    Laporan yang fokus pada aspek potensi (Psychological Potential) dengan visualisasi
                                    spider plot, analisis gap, dan tolerance selector.
                                </p>
                            </div>
                            <div class="mt-auto">
                                <a href="{{ route('general_psy_mapping', ['eventCode' => $participant->assessmentEvent->code, 'testNumber' => $participant->test_number]) }}"
                                    class="w-full inline-flex items-center justify-center px-4 py-2 bg-primary-ink hover:bg-[#2c2724] text-warm-ivory dark:bg-amber-600 dark:hover:bg-amber-700 dark:text-white text-xs font-semibold rounded-md transition-colors shadow-xs">
                                    Lihat PSY Mapping
                                    <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- KARTU 5: Ringkasan MC Mapping -->
                    <div class="h-full">
                        <div
                            class="flex flex-col h-full border border-warm-border dark:border-[#25211e] rounded-md p-6 hover:border-accent-amber/50 dark:hover:border-amber-500/50 transition-all bg-warm-ivory/30 dark:bg-[#1f1b18]/50 shadow-xs">
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center space-x-3">
                                    <div class="flex-shrink-0">
                                        <div
                                            class="w-10 h-10 bg-warm-ivory dark:bg-[#25211e] border border-warm-border dark:border-[#25211e] text-accent-amber rounded-lg flex items-center justify-center">
                                            <svg class="w-6 h-6 text-accent-amber dark:text-amber-500" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01">
                                                </path>
                                            </svg>
                                        </div>
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Ringkasan MC
                                            Mapping</h3>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">Ringkasan kompetensi dengan
                                            kesimpulan</p>
                                    </div>
                                </div>
                            </div>
                            <div class="flex-grow mb-4">
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    Laporan ringkasan aspek kompetensi dengan individual rating, standard rating, dan
                                    kesimpulan untuk setiap aspek kompetensi.
                                </p>
                            </div>
                            <div class="mt-auto">
                                <a href="{{ route('ringkasan_mc_mapping', ['eventCode' => $participant->assessmentEvent->code, 'testNumber' => $participant->test_number]) }}"
                                    class="w-full inline-flex items-center justify-center px-4 py-2 bg-primary-ink hover:bg-[#2c2724] text-warm-ivory dark:bg-amber-600 dark:hover:bg-amber-700 dark:text-white text-xs font-semibold rounded-md transition-colors shadow-xs">
                                    Lihat Ringkasan MC
                                    <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- KARTU 6: Ringkasan Asesmen -->
                    <div class="h-full">
                        <div
                            class="flex flex-col h-full border border-warm-border dark:border-[#25211e] rounded-md p-6 hover:border-accent-amber/50 dark:hover:border-amber-500/50 transition-all bg-warm-ivory/30 dark:bg-[#1f1b18]/50 shadow-xs">
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center space-x-3">
                                    <div class="flex-shrink-0">
                                        <div
                                            class="w-10 h-10 bg-warm-ivory dark:bg-[#25211e] border border-warm-border dark:border-[#25211e] text-accent-amber rounded-lg flex items-center justify-center">
                                            <svg class="w-6 h-6 text-accent-amber dark:text-amber-500" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                                </path>
                                            </svg>
                                        </div>
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Ringkasan
                                            Asesmen</h3>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">Ringkasan hasil keseluruhan
                                            asesmen</p>
                                    </div>
                                </div>
                            </div>
                            <div class="flex-grow mb-4">
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    Laporan ringkasan hasil asesmen keseluruhan yang menampilkan skor potensi dan
                                    kompetensi, bobot penilaian, gap score, dan kesimpulan akhir peserta.
                                </p>
                            </div>
                            <div class="mt-auto">
                                <a href="{{ route('ringkasan_assessment', ['eventCode' => $participant->assessmentEvent->code, 'testNumber' => $participant->test_number]) }}"
                                    class="w-full inline-flex items-center justify-center px-4 py-2 bg-primary-ink hover:bg-[#2c2724] text-warm-ivory dark:bg-amber-600 dark:hover:bg-amber-700 dark:text-white text-xs font-semibold rounded-md transition-colors shadow-xs">
                                    Lihat Ringkasan Asesmen
                                    <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- KARTU 7: Spider Plot -->
                    <div class="h-full">
                        <div
                            class="flex flex-col h-full border border-warm-border dark:border-[#25211e] rounded-md p-6 hover:border-accent-amber/50 dark:hover:border-amber-500/50 transition-all bg-warm-ivory/30 dark:bg-[#1f1b18]/50 shadow-xs">
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center space-x-3">
                                    <div class="flex-shrink-0">
                                        <div
                                            class="w-10 h-10 bg-warm-ivory dark:bg-[#25211e] border border-warm-border dark:border-[#25211e] text-accent-amber rounded-lg flex items-center justify-center">
                                            <svg class="w-6 h-6 text-accent-amber dark:text-amber-500" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                                                </path>
                                            </svg>
                                        </div>
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Spider Plot
                                            Analysis</h3>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">Visualisasi radar chart
                                            dengan tolerance selector</p>
                                    </div>
                                </div>
                            </div>
                            <div class="flex-grow mb-4">
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    Analisis visual dengan spider plot untuk Potensi, Kompetensi, dan General Mapping
                                    dengan fitur tolerance selector yang dapat diubah secara real-time.
                                </p>
                            </div>
                            <div class="mt-auto">
                                <a href="{{ route('spider_plot', ['eventCode' => $participant->assessmentEvent->code, 'testNumber' => $participant->test_number]) }}"
                                    class="w-full inline-flex items-center justify-center px-4 py-2 bg-primary-ink hover:bg-[#2c2724] text-warm-ivory dark:bg-amber-600 dark:hover:bg-amber-700 dark:text-white text-xs font-semibold rounded-md transition-colors shadow-xs">
                                    Lihat Spider Plot
                                    <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- KARTU 8: Gambaran Ringkasan -->
                    <div class="h-full">
                        <div
                            class="flex flex-col h-full border border-warm-border dark:border-[#25211e] rounded-md p-6 hover:border-accent-amber/50 dark:hover:border-amber-500/50 transition-all bg-warm-ivory/30 dark:bg-[#1f1b18]/50 shadow-xs">
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center space-x-3">
                                    <div class="flex-shrink-0">
                                        <div
                                            class="w-10 h-10 bg-warm-ivory dark:bg-[#25211e] border border-warm-border dark:border-[#25211e] text-accent-amber rounded-lg flex items-center justify-center">
                                            <svg class="w-6 h-6 text-accent-amber dark:text-amber-500" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                                                </path>
                                            </svg>
                                        </div>
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Laporan
                                            Individu
                                        </h3>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">Visualisasi gambaran
                                            ringkasan untuk Potensi, Kompetensi, dan General Mapping</p>
                                    </div>
                                </div>
                            </div>
                            <div class="flex-grow mb-4">
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    Analisis visual dengan gambaran ringkasan untuk Potensi, Kompetensi, dan General
                                    Mapping
                                </p>
                            </div>
                            <div class="mt-auto">
                                <a href="{{ route('final_report', ['eventCode' => $participant->assessmentEvent->code, 'testNumber' => $participant->test_number]) }}"
                                    class="w-full inline-flex items-center justify-center px-4 py-2 bg-primary-ink hover:bg-[#2c2724] text-warm-ivory dark:bg-amber-600 dark:hover:bg-amber-700 dark:text-white text-xs font-semibold rounded-md transition-colors shadow-xs">
                                    Lihat Gambaran Ringkasan
                                    <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- KARTU 9: Executive HCA Report -->
                    <div class="h-full">
                        <div
                            class="flex flex-col h-full border-2 border-accent-amber/40 dark:border-amber-500/40 rounded-md p-6 hover:border-accent-amber dark:hover:border-amber-500 transition-all bg-accent-amber/5 dark:bg-[#1f1b18] shadow-xs">
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center space-x-3">
                                    <div class="flex-shrink-0">
                                        <div
                                            class="w-10 h-10 bg-accent-amber text-white rounded-lg flex items-center justify-center shadow-xs">
                                            <i class="fa-solid fa-book-open text-base"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Executive HCA Report</h3>
                                            <span class="text-[10px] bg-accent-amber text-white font-bold px-2 py-0.5 rounded-full">NEW</span>
                                        </div>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">Executive Journal 24 Bab & Cetak PDF</p>
                                    </div>
                                </div>
                            </div>
                            <div class="flex-grow mb-4">
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    Laporan eksekutif komprehensif mengintegrasikan HCI, 9-Box Matrix, DISC, Big Five, Riwayat Karier, hingga Rekomendasi Suksesi.
                                </p>
                            </div>
                            <div class="mt-auto">
                                <a href="{{ route('hca-report', ['participant' => $participant->id]) }}"
                                    class="w-full inline-flex items-center justify-center px-4 py-2 bg-accent-amber hover:bg-accent-amber/90 text-white text-xs font-semibold rounded-md transition-colors shadow-xs">
                                    Buka Executive HCA Report
                                    <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            @endif

            @if($mainTab === 'supplementary_data')
            <div class="px-6 py-6 space-y-6">
                <!-- Subtab Navigation -->
                <div class="border-b border-warm-border dark:border-[#25211e] flex items-center gap-2 overflow-x-auto pb-1">
                    <button 
                        type="button"
                        wire:click="setSupplementarySubTab('performance')"
                        class="py-2.5 px-4 text-xs font-semibold border-b-2 transition-all flex items-center gap-2 cursor-pointer {{ $supplementarySubTab === 'performance' ? 'border-accent-amber text-accent-amber font-bold' : 'border-transparent text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white' }}"
                    >
                        <i class="fas fa-chart-line text-xs"></i>
                        <span>1. Rekam Kinerja & KPI Tahunan</span>
                        <span class="text-[10px] px-1.5 py-0.2 rounded-full {{ $supplementarySubTab === 'performance' ? 'bg-accent-amber text-white' : 'bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-300' }}">
                            {{ count($performanceRecords) }}
                        </span>
                    </button>

                    <button 
                        type="button"
                        wire:click="setSupplementarySubTab('career')"
                        class="py-2.5 px-4 text-xs font-semibold border-b-2 transition-all flex items-center gap-2 cursor-pointer {{ $supplementarySubTab === 'career' ? 'border-accent-amber text-accent-amber font-bold' : 'border-transparent text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white' }}"
                    >
                        <i class="fas fa-briefcase text-xs"></i>
                        <span>2. Riwayat Karier & Rekam Jejak</span>
                        <span class="text-[10px] px-1.5 py-0.2 rounded-full {{ $supplementarySubTab === 'career' ? 'bg-accent-amber text-white' : 'bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-300' }}">
                            {{ count($careerHistories) }}
                        </span>
                    </button>

                    <button 
                        type="button"
                        wire:click="setSupplementarySubTab('personal')"
                        class="py-2.5 px-4 text-xs font-semibold border-b-2 transition-all flex items-center gap-2 cursor-pointer {{ $supplementarySubTab === 'personal' ? 'border-accent-amber text-accent-amber font-bold' : 'border-transparent text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white' }}"
                    >
                        <i class="fas fa-id-card-clip text-xs"></i>
                        <span>3. Profil Personal Pelengkap</span>
                    </button>

                    <button 
                        type="button"
                        wire:click="setSupplementarySubTab('succession')"
                        class="py-2.5 px-4 text-xs font-semibold border-b-2 transition-all flex items-center gap-2 cursor-pointer {{ $supplementarySubTab === 'succession' ? 'border-accent-amber text-accent-amber font-bold' : 'border-transparent text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white' }}"
                    >
                        <i class="fas fa-chess-king text-xs"></i>
                        <span>4. Kurasi Suksesi & Peran</span>
                        @if($successionTargetRole || $readinessHorizon)
                            <span class="w-1.5 h-1.5 rounded-full bg-accent-amber"></span>
                        @endif
                    </button>
                </div>

                <!-- SUBTAB 1: KINERJA & KPI -->
                @if($supplementarySubTab === 'performance')
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="font-display font-bold text-sm text-primary-ink dark:text-white">Rekam Kinerja & KPI Tahunan</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                Data ini disinkronkan ke <strong class="text-slate-700 dark:text-slate-300">Dashboard Kinerja (Section 15)</strong> dan menjadi <strong class="text-slate-700 dark:text-slate-300">Sumbu Kinerja Matriks 9-Box (Section 16)</strong> pada HCA Report.
                            </p>
                        </div>
                        <button 
                            type="button" 
                            wire:click="addPerformanceRow"
                            class="px-3 py-1.5 rounded-md bg-accent-amber/10 hover:bg-accent-amber/20 text-accent-amber border border-accent-amber/30 text-xs font-semibold transition-colors flex items-center gap-1.5 cursor-pointer"
                        >
                            <i class="fas fa-plus text-[10px]"></i>
                            Tambah Tahun
                        </button>
                    </div>

                    <div class="space-y-3">
                        @forelse($performanceRecords as $index => $record)
                        <div class="p-4 rounded-lg border border-warm-border dark:border-[#25211e] bg-warm-ivory/30 dark:bg-[#1f1b18]/40 space-y-3">
                            <div class="flex items-center justify-between border-b border-warm-border/60 dark:border-[#25211e] pb-2.5">
                                <span class="text-xs font-bold text-primary-ink dark:text-white flex items-center gap-2">
                                    <span class="w-5 h-5 rounded-full bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300 flex items-center justify-center text-[10px]">
                                        {{ $index + 1 }}
                                    </span>
                                    Tahun Evaluasi: {{ $record['year'] }}
                                </span>
                                <button 
                                    type="button" 
                                    wire:click="removePerformanceRow({{ $index }})"
                                    class="text-xs text-rose-500 hover:text-rose-700 hover:bg-rose-50 dark:hover:bg-rose-950/30 px-2 py-1 rounded transition-colors cursor-pointer"
                                    title="Hapus baris tahun ini"
                                >
                                    <i class="fas fa-trash-can mr-1"></i> Hapus
                                </button>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3">
                                <div>
                                    <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-400 mb-1">Tahun</label>
                                    <input 
                                        type="number" 
                                        wire:model="performanceRecords.{{ $index }}.year"
                                        class="w-full text-xs px-3 py-2 rounded-md border border-warm-border dark:border-[#25211e] bg-white dark:bg-[#171412] text-primary-ink dark:text-white focus:outline-none focus:ring-1 focus:ring-accent-amber"
                                        placeholder="YYYY"
                                    />
                                </div>
                                <div>
                                    <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-400 mb-1">Capaian KPI (%)</label>
                                    <input 
                                        type="number" 
                                        step="0.01"
                                        wire:model="performanceRecords.{{ $index }}.kpi_score"
                                        class="w-full text-xs px-3 py-2 rounded-md border border-warm-border dark:border-[#25211e] bg-white dark:bg-[#171412] text-primary-ink dark:text-white focus:outline-none focus:ring-1 focus:ring-accent-amber"
                                        placeholder="95.50"
                                    />
                                </div>
                                <div>
                                    <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-400 mb-1">Target (%)</label>
                                    <input 
                                        type="number" 
                                        step="0.01"
                                        wire:model="performanceRecords.{{ $index }}.target_score"
                                        class="w-full text-xs px-3 py-2 rounded-md border border-warm-border dark:border-[#25211e] bg-white dark:bg-[#171412] text-primary-ink dark:text-white focus:outline-none focus:ring-1 focus:ring-accent-amber"
                                        placeholder="100.00"
                                    />
                                </div>
                                <div>
                                    <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-400 mb-1">Predikat / Rating</label>
                                    <select 
                                        wire:model="performanceRecords.{{ $index }}.performance_rating"
                                        class="w-full text-xs px-3 py-2 rounded-md border border-warm-border dark:border-[#25211e] bg-white dark:bg-[#171412] text-primary-ink dark:text-white focus:outline-none focus:ring-1 focus:ring-accent-amber"
                                    >
                                        <option value="Istimewa">Istimewa (Exceeded)</option>
                                        <option value="Sangat Baik">Sangat Baik</option>
                                        <option value="Baik">Baik (Achieved)</option>
                                        <option value="Cukup">Cukup (Needs Improvement)</option>
                                        <option value="Kurang">Kurang (Underperformed)</option>
                                    </select>
                                </div>
                            </div>

                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-400 mb-1">
                                    Pencapaian Kunci / Metrik Strategis (1 poin per baris)
                                </label>
                                <textarea 
                                    rows="2"
                                    wire:model="performanceRecords.{{ $index }}.achievements_text"
                                    class="w-full text-xs px-3 py-2 rounded-md border border-warm-border dark:border-[#25211e] bg-white dark:bg-[#171412] text-primary-ink dark:text-white focus:outline-none focus:ring-1 focus:ring-accent-amber"
                                    placeholder="Contoh: Realisasi efisiensi anggaran sebesar 12%&#10;Implementasi sistem digitalisasi operasional"
                                ></textarea>
                            </div>
                        </div>
                        @empty
                        <div class="text-center py-8 border-2 border-dashed border-warm-border dark:border-[#25211e] rounded-lg">
                            <p class="text-xs text-slate-500">Belum ada rekam kinerja tahunan.</p>
                            <button type="button" wire:click="addPerformanceRow" class="mt-2 text-xs text-accent-amber font-semibold">
                                + Tambah Tahun Pertama
                            </button>
                        </div>
                        @endforelse
                    </div>
                </div>
                @endif

                <!-- SUBTAB 2: RIWAYAT KARIER -->
                @if($supplementarySubTab === 'career')
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="font-display font-bold text-sm text-primary-ink dark:text-white">Riwayat Jabatan & Rekam Jejak Penugasan</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                Data ini disinkronkan ke <strong class="text-slate-700 dark:text-slate-300">Riwayat Karier (Section 06)</strong> dan perhitungan total masa kerja efektif (*tenure*) HCA Report.
                            </p>
                        </div>
                        <button 
                            type="button" 
                            wire:click="addCareerRow"
                            class="px-3 py-1.5 rounded-md bg-accent-amber/10 hover:bg-accent-amber/20 text-accent-amber border border-accent-amber/30 text-xs font-semibold transition-colors flex items-center gap-1.5 cursor-pointer"
                        >
                            <i class="fas fa-plus text-[10px]"></i>
                            Tambah Jabatan
                        </button>
                    </div>

                    <div class="space-y-3">
                        @forelse($careerHistories as $index => $career)
                        <div class="p-4 rounded-lg border border-warm-border dark:border-[#25211e] bg-warm-ivory/30 dark:bg-[#1f1b18]/40 space-y-3">
                            <div class="flex items-center justify-between border-b border-warm-border/60 dark:border-[#25211e] pb-2.5">
                                <span class="text-xs font-bold text-primary-ink dark:text-white flex items-center gap-2">
                                    <span class="w-5 h-5 rounded-full bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300 flex items-center justify-center text-[10px]">
                                        {{ $index + 1 }}
                                    </span>
                                    {{ $career['position_title'] ?: 'Posisi Baru' }}
                                    @if(!empty($career['is_current']))
                                        <span class="text-[10px] px-2 py-0.5 rounded bg-emerald-100 text-forest-green font-bold">Posisi Aktif</span>
                                    @endif
                                </span>
                                <button 
                                    type="button" 
                                    wire:click="removeCareerRow({{ $index }})"
                                    class="text-xs text-rose-500 hover:text-rose-700 hover:bg-rose-50 dark:hover:bg-rose-950/30 px-2 py-1 rounded transition-colors cursor-pointer"
                                    title="Hapus baris jabatan ini"
                                >
                                    <i class="fas fa-trash-can mr-1"></i> Hapus
                                </button>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-400 mb-1">Nama Jabatan / Posisi</label>
                                    <input 
                                        type="text" 
                                        wire:model="careerHistories.{{ $index }}.position_title"
                                        class="w-full text-xs px-3 py-2 rounded-md border border-warm-border dark:border-[#25211e] bg-white dark:bg-[#171412] text-primary-ink dark:text-white focus:outline-none focus:ring-1 focus:ring-accent-amber"
                                        placeholder="Contoh: Manager Operasional"
                                    />
                                </div>
                                <div>
                                    <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-400 mb-1">Instansi / Perusahaan / Unit Kerja</label>
                                    <input 
                                        type="text" 
                                        wire:model="careerHistories.{{ $index }}.company_or_institution"
                                        class="w-full text-xs px-3 py-2 rounded-md border border-warm-border dark:border-[#25211e] bg-white dark:bg-[#171412] text-primary-ink dark:text-white focus:outline-none focus:ring-1 focus:ring-accent-amber"
                                        placeholder="Contoh: Direktorat Jenderal Pajak"
                                    />
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 items-center">
                                <div>
                                    <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-400 mb-1">Tahun Mulai</label>
                                    <input 
                                        type="number" 
                                        wire:model="careerHistories.{{ $index }}.start_year"
                                        class="w-full text-xs px-3 py-2 rounded-md border border-warm-border dark:border-[#25211e] bg-white dark:bg-[#171412] text-primary-ink dark:text-white focus:outline-none focus:ring-1 focus:ring-accent-amber"
                                        placeholder="YYYY"
                                    />
                                </div>
                                <div>
                                    <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-400 mb-1">Tahun Selesai</label>
                                    <input 
                                        type="number" 
                                        wire:model="careerHistories.{{ $index }}.end_year"
                                        :disabled="{{ !empty($career['is_current']) ? 'true' : 'false' }}"
                                        class="w-full text-xs px-3 py-2 rounded-md border border-warm-border dark:border-[#25211e] bg-white dark:bg-[#171412] text-primary-ink dark:text-white focus:outline-none focus:ring-1 focus:ring-accent-amber disabled:opacity-50 disabled:bg-slate-100"
                                        placeholder="{{ !empty($career['is_current']) ? 'Sekarang' : 'YYYY' }}"
                                    />
                                </div>
                                <div class="pt-4 flex items-center">
                                    <label class="inline-flex items-center gap-2 cursor-pointer text-xs font-semibold text-primary-ink dark:text-white">
                                        <input 
                                            type="checkbox" 
                                            wire:click="toggleCurrentCareer({{ $index }})"
                                            {{ !empty($career['is_current']) ? 'checked' : '' }}
                                            class="rounded text-accent-amber focus:ring-accent-amber w-4 h-4"
                                        />
                                        <span>Jabatan Saat Ini (Aktif)</span>
                                    </label>
                                </div>
                            </div>

                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-400 mb-1">
                                    Pencapaian & Tanggung Jawab Utama (1 poin per baris)
                                </label>
                                <textarea 
                                    rows="2"
                                    wire:model="careerHistories.{{ $index }}.achievements_text"
                                    class="w-full text-xs px-3 py-2 rounded-md border border-warm-border dark:border-[#25211e] bg-white dark:bg-[#171412] text-primary-ink dark:text-white focus:outline-none focus:ring-1 focus:ring-accent-amber"
                                    placeholder="Contoh: Memimpin tim dalam transformasi tata kelola layanan&#10;Meningkatkan kepatuhan SOP divisi hingga 99%"
                                ></textarea>
                            </div>
                        </div>
                        @empty
                        <div class="text-center py-8 border-2 border-dashed border-warm-border dark:border-[#25211e] rounded-lg">
                            <p class="text-xs text-slate-500">Belum ada riwayat jabatan.</p>
                            <button type="button" wire:click="addCareerRow" class="mt-2 text-xs text-accent-amber font-semibold">
                                + Tambah Riwayat Jabatan
                            </button>
                        </div>
                        @endforelse
                    </div>
                </div>
                @endif

                <!-- SUBTAB 3: PROFIL PERSONAL -->
                @if($supplementarySubTab === 'personal')
                <div class="space-y-4">
                    <div>
                        <h3 class="font-display font-bold text-sm text-primary-ink dark:text-white">Profil Personal & Informasi Gaya Hidup</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                            Data ini disinkronkan ke <strong class="text-slate-700 dark:text-slate-300">Profil Personal Pelengkap (Section 18)</strong> untuk memberikan konteks personal yang humanis bagi pimpinan C-Level.
                        </p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-400 mb-1">Golongan Darah</label>
                            <select 
                                wire:model="bloodType"
                                class="w-full text-xs px-3 py-2 rounded-md border border-warm-border dark:border-[#25211e] bg-white dark:bg-[#171412] text-primary-ink dark:text-white focus:outline-none focus:ring-1 focus:ring-accent-amber"
                            >
                                <option value="A+">A+</option>
                                <option value="A-">A-</option>
                                <option value="B+">B+</option>
                                <option value="B-">B-</option>
                                <option value="AB+">AB+</option>
                                <option value="AB-">AB-</option>
                                <option value="O+">O+</option>
                                <option value="O-">O-</option>
                                <option value="-">- (Belum Diketahui)</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-400 mb-1">Hobi & Kegemaran</label>
                            <input 
                                type="text" 
                                wire:model="hobbies"
                                class="w-full text-xs px-3 py-2 rounded-md border border-warm-border dark:border-[#25211e] bg-white dark:bg-[#171412] text-primary-ink dark:text-white focus:outline-none focus:ring-1 focus:ring-accent-amber"
                                placeholder="Membaca, Riset Kebijakan, Musik"
                            />
                        </div>

                        <div>
                            <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-400 mb-1">Olahraga Favorit</label>
                            <input 
                                type="text" 
                                wire:model="sports"
                                class="w-full text-xs px-3 py-2 rounded-md border border-warm-border dark:border-[#25211e] bg-white dark:bg-[#171412] text-primary-ink dark:text-white focus:outline-none focus:ring-1 focus:ring-accent-amber"
                                placeholder="Jogging, Bulu Tangkis, Bersepeda"
                            />
                        </div>
                    </div>

                    <div class="space-y-3 pt-2">
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-400 mb-1">Catatan Medis & Kebugaran Fisik</label>
                            <textarea 
                                rows="2"
                                wire:model="medicalNotes"
                                class="w-full text-xs px-3 py-2 rounded-md border border-warm-border dark:border-[#25211e] bg-white dark:bg-[#171412] text-primary-ink dark:text-white focus:outline-none focus:ring-1 focus:ring-accent-amber"
                                placeholder="Kondisi kesehatan umum prima, siap ditugaskan untuk mobilitas tinggi..."
                            ></textarea>
                        </div>

                        <div>
                            <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-400 mb-1">Catatan Budaya & Karakteristik Sosial</label>
                            <textarea 
                                rows="2"
                                wire:model="culturalNotes"
                                class="w-full text-xs px-3 py-2 rounded-md border border-warm-border dark:border-[#25211e] bg-white dark:bg-[#171412] text-primary-ink dark:text-white focus:outline-none focus:ring-1 focus:ring-accent-amber"
                                placeholder="Menjunjung tinggi nilai kejujuran, integritas, dan budaya gotong royong..."
                            ></textarea>
                        </div>

                        <div>
                            <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-400 mb-1">Moto Hidup / Nilai Utama (Core Values)</label>
                            <input 
                                type="text" 
                                wire:model="mottoOrValues"
                                class="w-full text-xs px-3 py-2 rounded-md border border-warm-border dark:border-[#25211e] bg-white dark:bg-[#171412] text-primary-ink dark:text-white focus:outline-none focus:ring-1 focus:ring-accent-amber"
                                placeholder="Integritas dalam Bekerja, Keunggulan dalam Melayani"
                            />
                        </div>
                    </div>
                </div>
                @endif

                <!-- SUBTAB 4: KURASI SUKSESI & REKOMENDASI PERAN -->
                @if($supplementarySubTab === 'succession')
                <div class="space-y-4">
                    <div class="p-3.5 rounded-lg bg-amber-500/10 border border-accent-amber/20 text-xs text-slate-700 dark:text-slate-300 flex items-start gap-3">
                        <i class="fas fa-circle-info text-accent-amber text-sm mt-0.5 shrink-0"></i>
                        <div>
                            <p class="font-semibold text-primary-ink dark:text-white">Smart Default with Human-in-the-Loop Override</p>
                            <p class="text-[11px] text-slate-600 dark:text-slate-400 mt-0.5 leading-relaxed">
                                Jika kolom di bawah dikosongkan, sistem secara otomatis menghitung proyeksi suksesi berbasis algoritma 9-box (Section 17 & 23). Isi kolom berikut jika Asesor atau Dewan Suksesi ingin menetapkan keputusan definitif kustom.
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-400 mb-1">
                                Target Posisi Suksesi Definitif
                            </label>
                            <input 
                                type="text" 
                                wire:model="successionTargetRole"
                                class="w-full text-xs px-3 py-2 rounded-md border border-warm-border dark:border-[#25211e] bg-white dark:bg-[#171412] text-primary-ink dark:text-white focus:outline-none focus:ring-1 focus:ring-accent-amber"
                                placeholder="Contoh: Direktur Operasi & Transformasi Bisnis"
                            />
                            <p class="text-[10px] text-slate-500 mt-1">Kosongkan untuk menggunakan target peran otomatis sistem.</p>
                        </div>

                        <div>
                            <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-400 mb-1">
                                Horizon Kesiapan Suksesi Definitif
                            </label>
                            <select 
                                wire:model="readinessHorizon"
                                class="w-full text-xs px-3 py-2 rounded-md border border-warm-border dark:border-[#25211e] bg-white dark:bg-[#171412] text-primary-ink dark:text-white focus:outline-none focus:ring-1 focus:ring-accent-amber"
                            >
                                <option value="">-- Otomatis (Sesuai Algoritma 9-Box) --</option>
                                <option value="ready_now">Siap Sekarang (Ready Now &bull; 0–6 Bulan)</option>
                                <option value="1_year">Kesiapan 1 Tahun (Ready in 12 Months)</option>
                                <option value="2_year">Kesiapan 2 Tahun (Ready in 24 Months)</option>
                            </select>
                            <p class="text-[10px] text-slate-500 mt-1">Menentukan segmen kesiapan suksesi di Section 17 HCA Report.</p>
                        </div>
                    </div>

                    <div>
                        <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-400 mb-1">
                            Tingkat Keyakinan Kesiapan / Confidence Index (0–100%)
                        </label>
                        <input 
                            type="number" 
                            min="0"
                            max="100"
                            wire:model="readinessPercentage"
                            class="w-full sm:w-1/2 text-xs px-3 py-2 rounded-md border border-warm-border dark:border-[#25211e] bg-white dark:bg-[#171412] text-primary-ink dark:text-white focus:outline-none focus:ring-1 focus:ring-accent-amber"
                            placeholder="Contoh: 92 (Kosongkan jika otomatis)"
                        />
                    </div>

                    <div>
                        <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-400 mb-1">
                            Catatan Strategis Dewan Suksesi & Asesor (Strategic Succession Verdict)
                        </label>
                        <textarea 
                            rows="3"
                            wire:model="successionNotes"
                            class="w-full text-xs px-3 py-2 rounded-md border border-warm-border dark:border-[#25211e] bg-white dark:bg-[#171412] text-primary-ink dark:text-white focus:outline-none focus:ring-1 focus:ring-accent-amber"
                            placeholder="Contoh: Kandidat diproyeksikan untuk suksesi Direktur Operasi dengan syarat mengikuti rotasi unit regional dan sertifikasi kepemimpinan eksekutif dalam 6 bulan pertama..."
                        ></textarea>
                    </div>
                </div>
                @endif

                <!-- Bottom Save Button Bar -->
                <div class="pt-4 border-t border-warm-border dark:border-[#25211e] flex items-center justify-between">
                    <p class="text-xs text-slate-500">
                        Pastikan data yang diinput sudah diverifikasi sebelum disimpan.
                    </p>
                    <button 
                        type="button" 
                        wire:click="saveSupplementaryData"
                        wire:loading.attr="disabled"
                        class="px-5 py-2.5 bg-primary-ink dark:bg-amber-600 hover:bg-[#2c2724] dark:hover:bg-amber-700 text-white text-xs font-semibold rounded-md transition-all shadow-xs flex items-center gap-2 cursor-pointer disabled:opacity-50"
                    >
                        <span wire:loading.remove wire:target="saveSupplementaryData">
                            <i class="fas fa-floppy-disk text-accent-amber dark:text-white mr-1"></i> Simpan Seluruh Data Pelengkap
                        </span>
                        <span wire:loading wire:target="saveSupplementaryData">
                            <i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan Data...
                        </span>
                    </button>
                </div>
            </div>
            @endif
        </div>
    @else
        <!-- Peserta tidak ditemukan - DARK MODE READY -->
        <div class="bg-white dark:bg-[#171412] rounded-md border border-warm-border dark:border-[#25211e] shadow-xs overflow-hidden">
            <div class="px-6 py-8 text-center">
                <div class="flex flex-col items-center">
                    <svg class="h-12 w-12 text-gray-400 dark:text-gray-500 mb-4" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.464 0L4.35 16.5c-.77.833.192 2.5 1.732 2.5z">
                        </path>
                    </svg>
                    <p class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">Peserta tidak ditemukan</p>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Data peserta dengan kode proyek dan nomor test
                        tersebut tidak ditemukan.</p>
                </div>
            </div>
        </div>
    @endif
</div>
