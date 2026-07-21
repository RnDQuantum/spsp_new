<div>
    {{-- Button to trigger modal --}}
    <button type="button" wire:click="openModal"
        wire:loading.attr="disabled"
        wire:target="openModal"
        class="inline-flex items-center gap-2 px-3.5 py-2 text-xs font-bold font-mono-data rounded-lg border transition-all cursor-pointer shadow-xs disabled:opacity-50 {{ $weights['isAdjusted'] 
        ? 'bg-amber-100 dark:bg-amber-900/40 border-amber-400 dark:border-amber-600 text-amber-900 dark:text-amber-200 hover:bg-amber-200 dark:hover:bg-amber-900/60' 
        : 'border-warm-border dark:border-[#25211e] bg-white dark:bg-[#171412] text-primary-ink dark:text-neutral-200 hover:bg-warm-ivory dark:hover:bg-[#1f1b18]' }}">
        <svg wire:loading wire:target="openModal" class="animate-spin h-3.5 w-3.5 text-accent-amber" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
        <span class="flex items-center gap-2">
            📊 Bobot Standar: {{ $weights['name1'] }} {{ $weights['weight1'] }}% | {{ $weights['name2'] }} {{ $weights['weight2'] }}%
            @if ($weights['isAdjusted'])
            <span class="text-[10px] text-amber-700 dark:text-amber-300 font-bold uppercase tracking-wider">(disesuaikan)</span>
            @endif
        </span>
    </button>

    {{-- Mary UI Modal --}}
    <x-mary-modal wire:model="showModal" title="Edit Bobot Kategori" subtitle="Total bobot kombinasi harus 100%" separator
        class="backdrop-blur" box-class="w-full max-w-lg rounded-xl border border-warm-border dark:border-[#25211e] bg-white dark:bg-[#171412] text-primary-ink dark:text-neutral-100 shadow-2xl font-sans">

        <div x-data="{
            weight1: @entangle('editingWeight1'),
            weight2: @entangle('editingWeight2'),
            originalWeight1: @entangle('originalWeight1'),
            originalWeight2: @entangle('originalWeight2'),
            get total() { return Number(this.weight1) + Number(this.weight2); },
            get isValid() { return this.total === 100; }
        }" class="space-y-4">

            {{-- Category 1 Weight --}}
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider font-mono-data text-primary-ink/80 dark:text-neutral-300 mb-1.5">
                    Bobot {{ $categoryName1 }}:
                </label>
                <div class="relative">
                    <input type="number" x-model.number="weight1" min="0" max="100" step="1" placeholder="0"
                        class="w-full rounded-lg border border-warm-border dark:border-[#25211e] bg-warm-ivory/40 dark:bg-[#1f1b18] px-4 py-2 text-sm font-mono-data font-bold text-primary-ink dark:text-neutral-100 focus:outline-none focus:border-accent-amber transition-colors">
                    <span class="absolute right-4 top-2 text-sm font-mono-data font-bold text-primary-ink/60 dark:text-neutral-400">%</span>
                </div>
                <p class="mt-1 text-xs font-mono-data text-primary-ink/60 dark:text-neutral-400">Nilai bawaan: {{ $originalWeight1 }}%</p>
            </div>

            {{-- Category 2 Weight --}}
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider font-mono-data text-primary-ink/80 dark:text-neutral-300 mb-1.5">
                    Bobot {{ $categoryName2 }}:
                </label>
                <div class="relative">
                    <input type="number" x-model.number="weight2" min="0" max="100" step="1" placeholder="0"
                        class="w-full rounded-lg border border-warm-border dark:border-[#25211e] bg-warm-ivory/40 dark:bg-[#1f1b18] px-4 py-2 text-sm font-mono-data font-bold text-primary-ink dark:text-neutral-100 focus:outline-none focus:border-accent-amber transition-colors">
                    <span class="absolute right-4 top-2 text-sm font-mono-data font-bold text-primary-ink/60 dark:text-neutral-400">%</span>
                </div>
                <p class="mt-1 text-xs font-mono-data text-primary-ink/60 dark:text-neutral-400">Nilai bawaan: {{ $originalWeight2 }}%</p>
            </div>

            {{-- Quick Presets --}}
            <div class="pt-2">
                <p class="text-xs font-bold uppercase tracking-wider font-mono-data text-primary-ink/80 dark:text-neutral-300 mb-2">Preset Cepat:</p>
                <div class="flex flex-wrap gap-2">
                    <button type="button" @click="weight1 = 50; weight2 = 50" class="px-3 py-1 rounded-md text-xs font-mono-data font-bold bg-warm-ivory dark:bg-[#1f1b18] border border-warm-border dark:border-[#25211e] text-primary-ink dark:text-neutral-100 hover:bg-accent-amber hover:text-white transition-colors cursor-pointer">50/50</button>
                    <button type="button" @click="weight1 = 60; weight2 = 40" class="px-3 py-1 rounded-md text-xs font-mono-data font-bold bg-warm-ivory dark:bg-[#1f1b18] border border-warm-border dark:border-[#25211e] text-primary-ink dark:text-neutral-100 hover:bg-accent-amber hover:text-white transition-colors cursor-pointer">60/40</button>
                    <button type="button" @click="weight1 = 70; weight2 = 30" class="px-3 py-1 rounded-md text-xs font-mono-data font-bold bg-warm-ivory dark:bg-[#1f1b18] border border-warm-border dark:border-[#25211e] text-primary-ink dark:text-neutral-100 hover:bg-accent-amber hover:text-white transition-colors cursor-pointer">70/30</button>
                    <button type="button" @click="weight1 = originalWeight1; weight2 = originalWeight2"
                        class="px-3 py-1 rounded-md text-xs font-mono-data font-bold bg-white dark:bg-[#171412] border border-warm-border dark:border-[#25211e] text-primary-ink dark:text-neutral-300 hover:bg-warm-ivory transition-colors cursor-pointer">Reset</button>
                </div>
            </div>

            {{-- Real-time Total Display --}}
            <div class="p-4 rounded-lg border border-warm-border dark:border-[#25211e] bg-warm-ivory/60 dark:bg-[#1f1b18]">
                <div class="flex items-center justify-between font-mono-data">
                    <span class="font-bold text-xs uppercase tracking-wider text-primary-ink dark:text-neutral-200">
                        Total Bobot:
                    </span>
                    <div class="flex items-center gap-2">
                        <span class="font-bold text-base text-primary-ink dark:text-neutral-100"
                            x-text="total + '%'">
                        </span>
                        <svg x-show="isValid" class="w-4 h-4 text-green-600 dark:text-green-400" fill="currentColor"
                            viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                clip-rule="evenodd"></path>
                        </svg>
                    </div>
                </div>
                <div x-show="!isValid" class="mt-2 flex items-start gap-2 font-mono-data">
                    <svg class="w-4 h-4 text-amber-600 dark:text-amber-400 mt-0.5 flex-shrink-0" fill="currentColor"
                        viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                            clip-rule="evenodd"></path>
                    </svg>
                    <p class="text-xs text-amber-700 dark:text-amber-400">
                        <span x-show="total < 100">Total kurang <strong x-text="100 - total"></strong>%</span>
                        <span x-show="total > 100">Total kelebihan <strong x-text="total - 100"></strong>%</span>
                    </p>
                </div>
            </div>
        </div>

        {{-- Actions Slot --}}
        <x-slot:actions>
            <button type="button" @click="$wire.closeModal()"
                class="rounded-lg border border-warm-border dark:border-[#25211e] bg-white dark:bg-[#171412] px-4 py-2 text-xs font-bold uppercase tracking-wider text-primary-ink dark:text-neutral-200 hover:bg-warm-ivory transition-colors cursor-pointer">
                Batal
            </button>
            <button type="button" wire:click="saveWeights"
                wire:loading.attr="disabled"
                wire:target="saveWeights"
                x-bind:disabled="!$el.closest('[x-data]').__x.$data.isValid"
                class="inline-flex items-center gap-2 rounded-lg bg-accent-amber px-5 py-2 text-xs font-bold uppercase tracking-wider text-white hover:bg-amber-700 transition-colors shadow-xs disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer">
                <svg wire:loading wire:target="saveWeights" class="animate-spin h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                <span>Simpan Perubahan</span>
            </button>
        </x-slot:actions>

    </x-mary-modal>
</div>