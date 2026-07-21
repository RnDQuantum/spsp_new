<div>
    <div class="max-w-[1400px] mx-auto p-3 md:p-4 font-sans text-primary-ink dark:text-neutral-100">
        <div class="bg-white dark:bg-[#171412] p-4 md:p-6 rounded-xl border border-warm-border dark:border-[#25211e] shadow-xs">
            
            {{-- Header Editorial Executive Journal --}}
            <div class="mb-6 pb-4 border-b border-warm-border dark:border-[#25211e]">
                <span class="font-mono-data text-amber-700 dark:text-amber-500 font-bold uppercase tracking-widest text-xs block mb-1">
                    ADMINISTRATION / STANDAR KHUSUS
                </span>
                <h1 class="font-display text-xl md:text-2xl font-bold tracking-tight text-primary-ink dark:text-neutral-100">
                    Kelola Standar Penilaian Kustom Institusi
                </h1>
            </div>

            {{-- Action Bar --}}
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-3 p-4 bg-warm-ivory dark:bg-[#1f1b18] rounded-xl border border-warm-border dark:border-[#25211e]">
                <div class="text-xs font-semibold text-primary-ink/70 dark:text-neutral-400 uppercase tracking-wider">
                    Total: <span class="text-primary-ink dark:text-neutral-100 font-bold font-mono-data text-sm">{{ $customStandards->count() }}</span> standar
                </div>
                <a href="{{ route('custom-standards.create') }}"
                    class="inline-flex items-center px-4 py-2.5 bg-[#171412] dark:bg-[#25211e] text-white hover:bg-black dark:hover:bg-[#1f1b18] text-xs font-semibold rounded-lg border border-[#171412] dark:border-[#25211e] transition-colors shadow-xs">
                    <i class="fa-solid fa-plus mr-2 text-amber-500"></i>
                    Buat Standar Baru
                </a>
            </div>

            @if ($customStandards->isEmpty())
                <!-- Empty State -->
                <div class="text-center py-12 bg-warm-ivory/60 dark:bg-[#1f1b18]/60 rounded-xl border border-dashed border-warm-border dark:border-[#25211e]">
                    <i class="fa-solid fa-sliders text-3xl text-primary-ink/30 dark:text-neutral-500 mb-3 block"></i>
                    <h3 class="text-base font-bold text-primary-ink dark:text-neutral-100">Belum Ada Standar Khusus</h3>
                    <p class="mt-1 text-xs text-primary-ink/60 dark:text-neutral-400 max-w-sm mx-auto">
                        Mulai buat standar penilaian kustom untuk institusi Anda.
                    </p>
                    <div class="mt-5">
                        <a href="{{ route('custom-standards.create') }}"
                            class="inline-flex items-center px-4 py-2.5 bg-[#171412] dark:bg-[#25211e] text-white hover:bg-black dark:hover:bg-[#1f1b18] text-xs font-semibold rounded-lg transition-colors shadow-xs">
                            <i class="fa-solid fa-plus mr-2 text-amber-500"></i>
                            Buat Standar Baru
                        </a>
                    </div>
                </div>
            @else
                <!-- Standards List -->
                <div class="space-y-3">
                    @foreach ($customStandards as $standard)
                        <div class="bg-white dark:bg-[#171412] border border-warm-border dark:border-[#25211e] rounded-xl p-4 md:p-5 hover:bg-warm-ivory/40 dark:hover:bg-[#1f1b18]/40 transition-colors shadow-xs">
                            <div class="flex flex-col sm:flex-row justify-between items-start gap-4">
                                <div class="flex-1">
                                    <div class="flex flex-wrap items-center gap-2 mb-1.5">
                                        <span class="text-xs font-mono-data font-semibold bg-amber-50 text-amber-800 border border-amber-200 dark:bg-amber-950/50 dark:text-amber-300 dark:border-amber-800/50 px-2 py-0.5 rounded-md">
                                            {{ $standard->code }}
                                        </span>
                                        <h3 class="text-base font-bold text-primary-ink dark:text-neutral-100">
                                            {{ $standard->name }}
                                        </h3>
                                    </div>
                                    <p class="text-xs font-medium text-primary-ink/70 dark:text-neutral-400">
                                        Standar Template: <span class="font-semibold text-primary-ink dark:text-neutral-200">{{ $standard->template->name }}</span>
                                    </p>
                                    @if ($standard->description)
                                        <p class="text-xs text-primary-ink/80 dark:text-neutral-300 mt-2">
                                            {{ $standard->description }}
                                        </p>
                                    @endif
                                    <p class="text-[11px] font-mono-data text-primary-ink/50 dark:text-neutral-500 mt-2.5">
                                        Dibuat oleh {{ $standard->creator?->name ?? 'Unknown' }} • {{ $standard->created_at->format('d M Y') }}
                                    </p>
                                </div>
                                <div class="flex items-center gap-2 self-end sm:self-center shrink-0">
                                    <a href="{{ route('custom-standards.edit', $standard->id) }}"
                                        class="px-3 py-1.5 text-xs font-semibold bg-warm-ivory dark:bg-[#1f1b18] hover:bg-warm-border/50 dark:hover:bg-[#25211e] text-primary-ink dark:text-neutral-200 border border-warm-border dark:border-[#25211e] rounded-lg transition-colors">
                                        <i class="fa-solid fa-pen-to-square mr-1 text-amber-600"></i> Edit
                                    </a>
                                    @if ($deleteId === $standard->id)
                                        <div class="flex items-center gap-1.5">
                                            <button wire:click="delete"
                                                class="px-3 py-1.5 text-xs font-semibold bg-rose-600 hover:bg-rose-700 text-white rounded-lg transition-colors shadow-xs">
                                                Hapus
                                            </button>
                                            <button wire:click="cancelDelete"
                                                class="px-3 py-1.5 text-xs font-semibold bg-warm-border/60 hover:bg-warm-border dark:bg-[#25211e] dark:hover:bg-[#1f1b18] text-primary-ink dark:text-neutral-200 rounded-lg transition-colors">
                                                Batal
                                            </button>
                                        </div>
                                    @else
                                        <button wire:click="confirmDelete({{ $standard->id }})"
                                            class="px-3 py-1.5 text-xs font-semibold bg-rose-50 hover:bg-rose-100 dark:bg-rose-950/40 dark:hover:bg-rose-950/70 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-900/50 rounded-lg transition-colors">
                                            <i class="fa-solid fa-trash mr-1"></i> Hapus
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
