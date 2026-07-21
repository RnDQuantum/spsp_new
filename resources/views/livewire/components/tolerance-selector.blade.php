<div x-data="{ isOpen: false }" class="fixed bottom-6 right-6 z-50 font-sans print:hidden">
    
    <!-- 1. Kapsul Melayang (Collapsed State) -->
    <button x-show="!isOpen" 
            @click="isOpen = true" 
            x-transition:enter="transition motion-safe:ease-[cubic-bezier(0.16,1,0.3,1)] ease-out duration-200"
            x-transition:enter-start="opacity-0 motion-safe:scale-95 motion-safe:translate-y-2"
            x-transition:enter-end="opacity-100 motion-safe:scale-100 motion-safe:translate-y-0"
            x-transition:leave="transition motion-safe:ease-[cubic-bezier(0.3,0,0.66,1)] ease-in duration-150"
            x-transition:leave-start="opacity-100 motion-safe:scale-100 motion-safe:translate-y-0"
            x-transition:leave-end="opacity-0 motion-safe:scale-95 motion-safe:translate-y-2"
            class="absolute bottom-0 right-0 flex items-center gap-3 px-4 py-2.5 
                   bg-white/90 dark:bg-[#171412]/90 backdrop-blur-md 
                   border border-warm-border dark:border-[#25211e] 
                   rounded-full shadow-lg hover:shadow-xl transition-all duration-200 
                   group text-xs text-primary-ink dark:text-neutral-200 whitespace-nowrap font-mono-data">
        
        <!-- Ikon Slider dengan Aksen Amber -->
        <span class="text-accent-amber transition-colors flex items-center justify-center">
            <!-- Ikon Filter -->
            <svg wire:loading.remove wire:target="tolerancePercentage" xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
            </svg>
            <!-- Spinner Loader -->
            <svg wire:loading wire:target="tolerancePercentage" class="animate-spin w-4 h-4 text-accent-amber" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        </span>
        
        <!-- Status Toleransi Ringkas -->
        <span class="font-bold text-xs tracking-wide">
            Toleransi: <span class="text-accent-amber font-bold">{{ $tolerancePercentage }}%</span>
        </span>
        
        @if ($showSummary && $totalCount > 0)
            <span class="w-px h-3 bg-warm-border dark:bg-[#25211e]"></span>
            <span class="text-xs font-bold text-green-700 dark:text-green-400">
                {{ $passingCount }}/{{ $totalCount }} Aspek ({{ $this->passingPercentage }}%)
            </span>
        @endif
    </button>

    <!-- 2. Kartu Kontrol (Expanded State) -->
    <div x-show="isOpen" 
         @click.outside="isOpen = false" 
         x-transition:enter="transition motion-safe:ease-[cubic-bezier(0.16,1,0.3,1)] ease-out duration-200"
         x-transition:enter-start="opacity-0 motion-safe:scale-95 motion-safe:translate-y-2"
         x-transition:enter-end="opacity-100 motion-safe:scale-100 motion-safe:translate-y-0"
         x-transition:leave="transition motion-safe:ease-[cubic-bezier(0.3,0,0.66,1)] ease-in duration-150"
         x-transition:leave-start="opacity-100 motion-safe:scale-100 motion-safe:translate-y-0"
         x-transition:leave-end="opacity-0 motion-safe:scale-95 motion-safe:translate-y-2"
         class="absolute bottom-0 right-0 w-80 bg-white/95 dark:bg-[#171412]/95 backdrop-blur-md 
                border border-warm-border dark:border-[#25211e] 
                rounded-2xl shadow-2xl p-5 flex flex-col gap-3.5 font-sans"
         style="display: none;">
        
        <!-- Header Panel -->
        <div class="flex items-center justify-between pb-2.5 border-b border-warm-border dark:border-[#25211e]">
            <div class="flex items-center gap-2">
                <span class="text-accent-amber text-sm">🎛️</span>
                <h4 class="text-xs font-bold text-primary-ink dark:text-neutral-100 uppercase tracking-wider font-mono-data">
                    Toleransi Analisis
                </h4>
                <!-- Indikator Loading dalam Header -->
                <div wire:loading wire:target="tolerancePercentage" class="flex items-center">
                    <svg class="animate-spin h-3.5 w-3.5 text-accent-amber" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>
            </div>
            <button @click="isOpen = false" class="text-primary-ink/40 hover:text-accent-amber dark:text-neutral-400 dark:hover:text-accent-amber transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        <!-- Dropdown Pilihan -->
        <div class="flex flex-col gap-1.5">
            <label class="text-[11px] font-bold text-primary-ink/75 dark:text-neutral-400 uppercase tracking-wider font-mono-data">
                Pilih Batas Toleransi
            </label>
            <select wire:model.live="tolerancePercentage" 
                    @change="isOpen = false"
                class="w-full px-3 py-2 border border-warm-border dark:border-[#25211e] rounded-lg 
                       focus:outline-none focus:border-accent-amber 
                       bg-warm-ivory/40 dark:bg-[#1f1b18] 
                       text-xs font-mono-data font-bold text-primary-ink dark:text-neutral-100 cursor-pointer transition-colors">
                @foreach ($toleranceOptions as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <!-- Ringkasan Kelulusan (Summary) -->
        @if ($showSummary && $totalCount > 0)
            <div class="p-3 rounded-lg bg-warm-ivory/60 dark:bg-[#1f1b18] border border-warm-border dark:border-[#25211e]">
                <div class="text-xs font-mono-data text-primary-ink/80 dark:text-neutral-300 leading-relaxed">
                    Ringkasan kelulusan:<br>
                    <span class="font-bold text-green-700 dark:text-green-400">
                        {{ $passingCount }} dari {{ $totalCount }} aspek
                    </span> 
                    ({{ $this->passingPercentage }}%) memenuhi standar.
                </div>
            </div>
        @endif

        <div class="text-[10px] font-mono-data text-primary-ink/60 dark:text-neutral-400 leading-normal border-t border-warm-border dark:border-[#25211e] pt-2">
            <strong>Catatan:</strong> Toleransi tidak mengubah data asli pada database, hanya menyesuaikan ambang kelulusan visual pada laporan.
        </div>
    </div>
</div>
