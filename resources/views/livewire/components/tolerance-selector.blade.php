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
                   bg-white/90 dark:bg-neutral-900/90 backdrop-blur-md 
                   border border-neutral-200 dark:border-neutral-800 
                   rounded-full shadow-lg hover:shadow-xl transition-[background-color,border-color,text-color,box-shadow] duration-200 
                   group text-sm text-neutral-800 dark:text-neutral-200 whitespace-nowrap">
        
        <!-- Ikon Slider dengan Aksen Amber Gold / Spinner saat Loading -->
        <span class="text-neutral-500 group-hover:text-amber-600 dark:group-hover:text-amber-500 transition-colors flex items-center justify-center">
            <!-- Ikon Filter (disembunyikan saat loading) -->
            <svg wire:loading.remove wire:target="tolerancePercentage" xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
            </svg>
            <!-- Spinner Loader (ditampilkan saat loading) -->
            <svg wire:loading wire:target="tolerancePercentage" class="animate-spin w-4 h-4 text-amber-600 dark:text-amber-500" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        </span>
        
        <!-- Status Toleransi Ringkas -->
        <span class="font-semibold text-xs tracking-wide">
            Toleransi: <span class="text-amber-700 dark:text-amber-500 font-bold">{{ $tolerancePercentage }}%</span>
        </span>
        
        @if ($showSummary && $totalCount > 0)
            <span class="w-px h-3 bg-neutral-300 dark:bg-neutral-700"></span>
            <span class="text-xs font-semibold text-emerald-600 dark:text-emerald-500">
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
         class="absolute bottom-0 right-0 w-80 bg-white/95 dark:bg-neutral-900/95 backdrop-blur-md 
                border border-neutral-200 dark:border-neutral-800 
                rounded-2xl shadow-2xl p-4 flex flex-col gap-3"
         style="display: none;">
        
        <!-- Header Panel -->
        <div class="flex items-center justify-between pb-2 border-b border-neutral-100 dark:border-neutral-800">
            <div class="flex items-center gap-2">
                <span class="text-amber-700 dark:text-amber-500 text-sm">🎛️</span>
                <h4 class="text-xs font-bold text-neutral-800 dark:text-neutral-200 uppercase tracking-wider">
                    Toleransi Analisis
                </h4>
                <!-- Indikator Loading dalam Header (Mencegah space lompat) -->
                <div wire:loading wire:target="tolerancePercentage" class="flex items-center">
                    <svg class="animate-spin h-3.5 w-3.5 text-amber-600 dark:text-amber-500" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>
            </div>
            <button @click="isOpen = false" class="text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-200 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        <!-- Dropdown Pilihan -->
        <div class="flex flex-col gap-1.5">
            <label class="text-[11px] font-bold text-neutral-400 dark:text-neutral-500 uppercase tracking-wider">
                Pilih Batas Toleransi
            </label>
            <select wire:model.live="tolerancePercentage" 
                    @change="isOpen = false"
                class="w-full px-3 py-2 border border-neutral-200 dark:border-neutral-800 rounded-lg 
                       focus:ring-2 focus:ring-amber-500 focus:outline-none 
                       bg-white dark:bg-neutral-800 
                       text-sm text-neutral-700 dark:text-neutral-200 font-medium cursor-pointer">
                @foreach ($toleranceOptions as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <!-- Ringkasan Kelulusan (Summary) -->
        @if ($showSummary && $totalCount > 0)
            <div class="p-2.5 rounded-lg bg-neutral-50 dark:bg-neutral-800/50 border border-neutral-100 dark:border-neutral-800/80">
                <div class="text-xs text-neutral-600 dark:text-neutral-400 leading-relaxed">
                    Ringkasan kelulusan:<br>
                    <span class="font-bold text-emerald-600 dark:text-emerald-500">
                        {{ $passingCount }} dari {{ $totalCount }} aspek
                    </span> 
                    ({{ $this->passingPercentage }}%) memenuhi standar.
                </div>
            </div>
        @endif

        <div class="text-[10px] text-neutral-400 dark:text-neutral-500 leading-normal border-t border-neutral-100 dark:border-neutral-800 pt-2">
            <strong>Catatan:</strong> Toleransi tidak mengubah data asli pada database, hanya menyesuaikan ambang kelulusan visual pada laporan.
        </div>
    </div>
</div>
