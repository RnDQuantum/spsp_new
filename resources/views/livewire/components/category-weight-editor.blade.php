<div>
    {{-- Button to trigger modal --}}
    <x-mary-button wire:click="openModal" :class="$weights['isAdjusted'] 
        ? 'bg-amber-100 dark:bg-amber-900/30 border-amber-400 dark:border-amber-600 text-amber-700 dark:text-amber-300 hover:bg-amber-200 dark:hover:bg-amber-900/40' 
        : 'btn-outline'" spinner="openModal">
        <span class="flex items-center gap-2">
            📊 Bobot Standar: {{ $weights['name1'] }} {{ $weights['weight1'] }}% | {{ $weights['name2'] }} {{
            $weights['weight2'] }}%
            @if ($weights['isAdjusted'])
            <span class="text-xs">(disesuaikan)</span>
            @endif
        </span>
    </x-mary-button>

    {{-- Mary UI Modal with x-mary- prefix --}}
    <x-mary-modal wire:model="showModal" title="Edit Bobot Kategori" subtitle="Total bobot harus 100%" separator
        class="backdrop-blur">

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
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Bobot {{ $categoryName1 }}:
                </label>
                <x-mary-input type="number" x-model.number="weight1" min="0" max="100" step="1" suffix="%"
                    hint="Nilai standar: {{ $originalWeight1 }}%" placeholder="0" class="!outline-none" />
            </div>

            {{-- Category 2 Weight --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Bobot {{ $categoryName2 }}:
                </label>
                <x-mary-input type="number" x-model.number="weight2" min="0" max="100" step="1" suffix="%"
                    hint="Nilai standar: {{ $originalWeight2 }}%" placeholder="0" class="!outline-none" />
            </div>

            {{-- Quick Presets --}}
            <div class="pt-2">
                <p class="text-xs font-medium text-gray-700 dark:text-gray-300 mb-2">Preset Cepat:</p>
                <div class="flex gap-2">
                    <x-mary-button label="50/50" @click="weight1 = 50; weight2 = 50" class="btn btn-soft btn-primary" />
                    <x-mary-button label="60/40" @click="weight1 = 60; weight2 = 40" class="btn btn-soft btn-primary" />
                    <x-mary-button label="70/30" @click="weight1 = 70; weight2 = 30" class="btn btn-soft btn-primary" />
                    <x-mary-button label="Reset" @click="weight1 = originalWeight1; weight2 = originalWeight2"
                        class="btn btn-outline" />
                </div>
            </div>

            {{-- Real-time Total Display --}}
            <div class="p-4 rounded-lg transition-all duration-200"
                :class="isValid ? 'bg-green-100 dark:bg-green-900/30' : 'bg-amber-100 dark:bg-amber-900/30'">
                <div class="flex items-center justify-between">
                    <span class="font-semibold transition-colors"
                        :class="isValid ? 'text-green-800 dark:text-green-300' : 'text-amber-800 dark:text-amber-300'">
                        Total Bobot:
                    </span>
                    <div class="flex items-center gap-2">
                        <span class="font-bold text-xl transition-colors"
                            :class="isValid ? 'text-green-800 dark:text-green-300' : 'text-amber-800 dark:text-amber-300'"
                            x-text="total + '%'">
                        </span>
                        <svg x-show="isValid" class="w-5 h-5 text-green-600 dark:text-green-400" fill="currentColor"
                            viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                clip-rule="evenodd"></path>
                        </svg>
                    </div>
                </div>
                <div x-show="!isValid" class="mt-2 flex items-start gap-2">
                    <svg class="w-4 h-4 text-amber-600 dark:text-amber-400 mt-0.5 flex-shrink-0" fill="currentColor"
                        viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                            clip-rule="evenodd"></path>
                    </svg>
                    <p class="text-sm text-amber-700 dark:text-amber-400">
                        <span x-show="total < 100">Total masih kurang <strong x-text="100 - total"></strong>%</span>
                        <span x-show="total > 100">Total kelebihan <strong x-text="total - 100"></strong>%</span>
                    </p>
                </div>
            </div>
        </div>

        {{-- Actions Slot --}}
        <x-slot:actions>
            <x-mary-button label="Batal" @click="$wire.closeModal()" />
            <x-mary-button label="Simpan Perubahan" class="btn-primary" wire:click="saveWeights" spinner="saveWeights"
                x-bind:disabled="!$el.closest('[x-data]').__x.$data.isValid" />
        </x-slot:actions>

    </x-mary-modal>
</div>