@props([
    'code' => '',
    'name' => '',
    'description' => '',
    'categoryWeights' => [],
    'aspectConfigs' => [],
    'subAspectConfigs' => [],
    'potensiAspects' => [],
    'kompetensiAspects' => [],
    'showTemplate' => false,
    'templateId' => null,
    'templates' => [],
])

<!-- Basic Info -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    @if ($showTemplate)
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Standar <span class="text-red-500">*</span>
            </label>
            <x-mary-select wire:model.live="templateId" :options="$templates" placeholder="Pilih Standar"
                class="!outline-none" />
            @error('templateId')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>
    @endif

    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
            Kode <span class="text-red-500">*</span>
        </label>
        <x-mary-input wire:model="code" placeholder="STD-001" class="!outline-none" />
        @error('code')
            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
            Nama <span class="text-red-500">*</span>
        </label>
        <x-mary-input wire:model="name" placeholder="Standar Institusi v1" class="!outline-none" />
        @error('name')
            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
        @enderror
    </div>

    <div @class(['md:col-span-2' => !$showTemplate])>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
            Deskripsi
        </label>
        <x-mary-input wire:model="description" placeholder="Deskripsi singkat (opsional)" class="!outline-none" />
    </div>
</div>

@if ($showTemplate ? $templateId : true)
    <!-- Category Weights -->
    <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
        <h2 class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-4">Bobot Kategori</h2>

        <div x-data="{
            potensi: @entangle('categoryWeights.potensi'),
            kompetensi: @entangle('categoryWeights.kompetensi'),
            get total() { return Number(this.potensi || 0) + Number(this.kompetensi || 0); },
            get isValid() { return this.total === 100; }
        }" class="space-y-4">

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1">Potensi (%)</label>
                    <x-mary-input type="number" x-model.number="potensi" min="0" max="100" suffix="%"
                        class="!outline-none" />
                </div>
                <div>
                    <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1">Kompetensi (%)</label>
                    <x-mary-input type="number" x-model.number="kompetensi" min="0" max="100" suffix="%"
                        class="!outline-none" />
                </div>
            </div>

            <!-- Quick Presets -->
            <div class="flex gap-2">
                <x-mary-button label="50/50" @click="potensi = 50; kompetensi = 50"
                    class="btn-sm btn-outline btn-primary" />
                <x-mary-button label="60/40" @click="potensi = 60; kompetensi = 40"
                    class="btn-sm btn-outline btn-primary" />
                <x-mary-button label="70/30" @click="potensi = 70; kompetensi = 30"
                    class="btn-sm btn-outline btn-primary" />
            </div>

            <!-- Real-time Total Display -->
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
    </div>

    <!-- Potensi Aspects -->
    @if (count($potensiAspects) > 0)
        <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-base font-semibold text-gray-900 dark:text-gray-100">Aspek Potensi</h2>
                <div class="flex gap-2">
                    <x-mary-button label="✓ Pilih Semua" wire:click="selectAllPotensiAspects"
                        class="btn-sm bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300 hover:bg-blue-200" />
                    <x-mary-button label="✗ Hapus Semua" wire:click="deselectAllPotensiAspects"
                        class="btn-sm bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300 hover:bg-gray-200" />
                    <x-mary-button label="⚖ Distribusi Bobot" wire:click="autoDistributePotensiWeights"
                        class="btn-sm bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300 hover:bg-green-200" />
                </div>
            </div>

            <!-- Validation Summary for Potensi -->
            <div class="grid grid-cols-2 gap-3 mb-4">
                <div
                    class="flex items-center justify-between p-3 rounded-lg transition-all {{ $this->activePotensiAspectsCount >= 3 ? 'bg-green-50 dark:bg-green-900/20' : 'bg-amber-50 dark:bg-amber-900/20' }}">
                    <span
                        class="text-sm font-medium {{ $this->activePotensiAspectsCount >= 3 ? 'text-green-800 dark:text-green-300' : 'text-amber-800 dark:text-amber-300' }}">
                        Aspek Aktif:
                    </span>
                    <span
                        class="text-sm font-bold {{ $this->activePotensiAspectsCount >= 3 ? 'text-green-800 dark:text-green-300' : 'text-amber-800 dark:text-amber-300' }}">
                        {{ $this->activePotensiAspectsCount }}/{{ count($potensiAspects) }} (Min: 3)
                    </span>
                </div>
                <div
                    class="flex items-center justify-between p-3 rounded-lg transition-all {{ $this->potensiAspectsWeightTotal === 100 ? 'bg-green-100 dark:bg-green-900/30' : 'bg-amber-100 dark:bg-amber-900/30' }}">
                    <span
                        class="font-semibold text-sm {{ $this->potensiAspectsWeightTotal === 100 ? 'text-green-800 dark:text-green-300' : 'text-amber-800 dark:text-amber-300' }}">
                        Total Bobot:
                    </span>
                    <span
                        class="font-bold text-lg {{ $this->potensiAspectsWeightTotal === 100 ? 'text-green-800 dark:text-green-300' : 'text-amber-800 dark:text-amber-300' }}">
                        {{ $this->potensiAspectsWeightTotal }}%
                    </span>
                </div>
            </div>

            <div class="space-y-4">
                @foreach ($potensiAspects as $aspect)
                    <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4"
                        x-data="{ expanded: {{ count($aspect['sub_aspects']) > 0 ? 'true' : 'false' }} }">
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center gap-3 flex-1">
                                <x-mary-checkbox wire:model.live="aspectConfigs.{{ $aspect['code'] }}.active"
                                    class="checkbox-primary" />

                                @if (count($aspect['sub_aspects']) > 0)
                                    <button @click="expanded = !expanded" type="button"
                                        class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
                                        <svg class="w-5 h-5 transform transition-transform"
                                            :class="expanded ? 'rotate-90' : ''" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 5l7 7-7 7"></path>
                                        </svg>
                                    </button>
                                @endif

                                <span
                                    class="font-medium text-gray-900 dark:text-gray-100 {{ !($aspectConfigs[$aspect['code']]['active'] ?? false) ? 'line-through opacity-50' : '' }}">
                                    {{ $aspect['name'] }}
                                </span>
                                @if (count($aspect['sub_aspects']) > 0)
                                    <span class="text-xs text-gray-500">({{ count($aspect['sub_aspects']) }}
                                        sub-aspek)</span>
                                @endif
                            </div>
                            <div class="flex items-center gap-2">
                                <label class="text-sm text-gray-600 dark:text-gray-400">Bobot:</label>
                                <x-mary-input type="number" wire:model.blur="aspectConfigs.{{ $aspect['code'] }}.weight"
                                    min="0" max="100" suffix="%" class="!outline-none w-24"
                                    :disabled="!($aspectConfigs[$aspect['code']]['active'] ?? false)" />
                            </div>
                        </div>

                        @if (count($aspect['sub_aspects']) > 0)
                            <div x-show="expanded" x-transition class="ml-6 space-y-2 border-t border-gray-200 dark:border-gray-700 pt-3">
                                @foreach ($aspect['sub_aspects'] as $subAspect)
                                    <div class="flex items-center justify-between py-1">
                                        <div class="flex items-center gap-2">
                                            <x-mary-checkbox
                                                wire:model.live="subAspectConfigs.{{ $subAspect['code'] }}.active"
                                                class="checkbox-sm checkbox-primary"
                                                :disabled="!($aspectConfigs[$aspect['code']]['active'] ?? false)" />
                                            <span
                                                class="text-sm text-gray-700 dark:text-gray-300 {{ !($aspectConfigs[$aspect['code']]['active'] ?? false) || !($subAspectConfigs[$subAspect['code']]['active'] ?? false) ? 'line-through opacity-50' : '' }}">
                                                {{ $subAspect['name'] }}
                                            </span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <label class="text-xs text-gray-500">Rating:</label>
                                            <x-mary-input type="number"
                                                wire:model="subAspectConfigs.{{ $subAspect['code'] }}.rating" min="1"
                                                max="5" class="!outline-none w-16 text-sm" />
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Kompetensi Aspects -->
    @if (count($kompetensiAspects) > 0)
        <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-base font-semibold text-gray-900 dark:text-gray-100">Aspek Kompetensi</h2>
                <div class="flex gap-2">
                    <x-mary-button label="✓ Pilih Semua" wire:click="selectAllKompetensiAspects"
                        class="btn-sm bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300 hover:bg-blue-200" />
                    <x-mary-button label="✗ Hapus Semua" wire:click="deselectAllKompetensiAspects"
                        class="btn-sm bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300 hover:bg-gray-200" />
                    <x-mary-button label="⚖ Distribusi Bobot" wire:click="autoDistributeKompetensiWeights"
                        class="btn-sm bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300 hover:bg-green-200" />
                </div>
            </div>

            <!-- Validation Summary for Kompetensi -->
            <div class="grid grid-cols-2 gap-3 mb-4">
                <div
                    class="flex items-center justify-between p-3 rounded-lg transition-all {{ $this->activeKompetensiAspectsCount >= 3 ? 'bg-green-50 dark:bg-green-900/20' : 'bg-amber-50 dark:bg-amber-900/20' }}">
                    <span
                        class="text-sm font-medium {{ $this->activeKompetensiAspectsCount >= 3 ? 'text-green-800 dark:text-green-300' : 'text-amber-800 dark:text-amber-300' }}">
                        Aspek Aktif:
                    </span>
                    <span
                        class="text-sm font-bold {{ $this->activeKompetensiAspectsCount >= 3 ? 'text-green-800 dark:text-green-300' : 'text-amber-800 dark:text-amber-300' }}">
                        {{ $this->activeKompetensiAspectsCount }}/{{ count($kompetensiAspects) }} (Min: 3)
                    </span>
                </div>
                <div
                    class="flex items-center justify-between p-3 rounded-lg transition-all {{ $this->kompetensiAspectsWeightTotal === 100 ? 'bg-green-100 dark:bg-green-900/30' : 'bg-amber-100 dark:bg-amber-900/30' }}">
                    <span
                        class="font-semibold text-sm {{ $this->kompetensiAspectsWeightTotal === 100 ? 'text-green-800 dark:text-green-300' : 'text-amber-800 dark:text-amber-300' }}">
                        Total Bobot:
                    </span>
                    <span
                        class="font-bold text-lg {{ $this->kompetensiAspectsWeightTotal === 100 ? 'text-green-800 dark:text-green-300' : 'text-amber-800 dark:text-amber-300' }}">
                        {{ $this->kompetensiAspectsWeightTotal }}%
                    </span>
                </div>
            </div>

            <div class="space-y-3">
                @foreach ($kompetensiAspects as $aspect)
                    <div
                        class="flex items-center justify-between p-3 border border-gray-200 dark:border-gray-700 rounded-lg">
                        <div class="flex items-center gap-3 flex-1">
                            <x-mary-checkbox wire:model.live="aspectConfigs.{{ $aspect['code'] }}.active"
                                class="checkbox-primary" />
                            <span
                                class="font-medium text-gray-900 dark:text-gray-100 {{ !($aspectConfigs[$aspect['code']]['active'] ?? false) ? 'line-through opacity-50' : '' }}">
                                {{ $aspect['name'] }}
                            </span>
                        </div>
                        <div class="flex items-center gap-4">
                            <div class="flex items-center gap-2">
                                <label class="text-sm text-gray-600 dark:text-gray-400">Bobot:</label>
                                <x-mary-input type="number" wire:model.blur="aspectConfigs.{{ $aspect['code'] }}.weight"
                                    min="0" max="100" suffix="%" class="!outline-none w-24"
                                    :disabled="!($aspectConfigs[$aspect['code']]['active'] ?? false)" />
                            </div>
                            <div class="flex items-center gap-2">
                                <label class="text-sm text-gray-600 dark:text-gray-400">Rating:</label>
                                <x-mary-input type="number" wire:model="aspectConfigs.{{ $aspect['code'] }}.rating"
                                    min="1" max="5" step="0.5" class="!outline-none w-20" />
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Overall Validation Errors -->
    @if (!$this->validationResult['valid'])
        <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
            <div class="bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-lg p-4">
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-red-600 dark:text-red-400 mt-0.5 flex-shrink-0" fill="currentColor"
                        viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                            clip-rule="evenodd"></path>
                    </svg>
                    <div class="flex-1">
                        <h3 class="font-semibold text-red-800 dark:text-red-300 mb-2">Perbaiki error berikut:</h3>
                        <ul class="list-disc list-inside space-y-1 text-sm text-red-700 dark:text-red-400">
                            @foreach ($this->validationResult['errors'] as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endif
