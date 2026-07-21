<div class="interpretation-section font-sans">
    @if ($showHeader && $isStandalone)
        <div class="mb-4 pb-4 border-b border-warm-border dark:border-[#25211e]">
            <span class="font-mono-data text-accent-amber font-bold uppercase tracking-widest text-xs block mb-1">
                INDIVIDUAL REPORT / INTERPRETASI
            </span>
            <h2 class="font-display text-xl md:text-2xl font-bold tracking-tight text-primary-ink dark:text-neutral-100">
                Interpretasi Hasil Assessment
            </h2>
            <p class="text-xs font-mono-data text-primary-ink/75 dark:text-neutral-400 mt-1">
                Participant: {{ $participant?->name }} | Event: {{ $eventCode }} | Test: {{ $testNumber }}
            </p>
        </div>
    @endif

    @if (!$participant)
        <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
            <p class="text-yellow-800 dark:text-yellow-200">Data participant tidak ditemukan.</p>
        </div>
    @else
        {{-- Potensi Interpretation Section --}}
        @if ($showPotensi && $potensiInterpretation)
            <div class="mb-8">
                <div
                    class="bg-white dark:bg-[#171412] rounded-md border border-warm-border dark:border-[#25211e] overflow-hidden shadow-xs">
                    <div class="bg-warm-ivory dark:bg-[#1f1b18] px-6 py-4 border-b border-warm-border dark:border-[#25211e]">
                        <h3 class="font-display text-lg font-bold text-primary-ink dark:text-neutral-100">1. Interpretasi Potensi</h3>
                    </div>

                    <div class="p-6">
                        <div class="prose prose-sm max-w-none dark:prose-invert">
                            @foreach (explode("\n\n", $potensiInterpretation) as $paragraph)
                                @if (trim($paragraph))
                                    <p class="mb-4 text-primary-ink/85 dark:text-neutral-300 leading-relaxed text-justify">
                                        {{ trim($paragraph) }}
                                    </p>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- Kompetensi Interpretation Section --}}
        @if ($showKompetensi && $kompetensiInterpretation)
            <div class="mb-8">
                <div
                    class="bg-white dark:bg-[#171412] rounded-md border border-warm-border dark:border-[#25211e] overflow-hidden shadow-xs">
                    <div class="bg-warm-ivory dark:bg-[#1f1b18] px-6 py-4 border-b border-warm-border dark:border-[#25211e]">
                        <h3 class="font-display text-lg font-bold text-primary-ink dark:text-neutral-100">2. Interpretasi Kompetensi</h3>
                    </div>

                    <div class="p-6">
                        <div class="prose prose-sm max-w-none dark:prose-invert">
                            @foreach (explode("\n\n", $kompetensiInterpretation) as $paragraph)
                                @if (trim($paragraph))
                                    <p class="mb-4 text-primary-ink/85 dark:text-neutral-300 leading-relaxed text-justify">
                                        {{ trim($paragraph) }}
                                    </p>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- Empty State --}}
        @if ((!$showPotensi || !$potensiInterpretation) && (!$showKompetensi || !$kompetensiInterpretation))
            <div
                class="bg-warm-ivory dark:bg-[#1f1b18] border border-warm-border dark:border-[#25211e] rounded-md p-8 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <p class="mt-4 text-gray-600 dark:text-gray-400">Interpretasi belum tersedia.</p>
            </div>
        @endif

        {{-- Admin Actions (for testing/development) --}}
        {{-- @if ($isStandalone && config('app.debug'))
    <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
        <button wire:click="regenerate" wire:loading.attr="disabled"
            class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
            <span wire:loading.remove wire:target="regenerate">Regenerate Interpretations</span>
            <span wire:loading wire:target="regenerate">Regenerating...</span>
        </button>
        <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
            Debug mode only - This will regenerate interpretations using current templates
        </p>
    </div>
    @endif --}}
    @endif
</div>
