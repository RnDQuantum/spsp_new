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
            <select wire:model.live="templateId"
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500">
                <option value="">Pilih Standar</option>
                @foreach ($templates as $template)
                    <option value="{{ $template->id }}">{{ $template->name }}</option>
                @endforeach
            </select>
            @error('templateId')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>
    @endif

    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
            Kode <span class="text-red-500">*</span>
        </label>
        <input type="text" wire:model="code" placeholder="STD-001"
            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500">
        @error('code')
            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
            Nama <span class="text-red-500">*</span>
        </label>
        <input type="text" wire:model="name" placeholder="Standar Institusi v1"
            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500">
        @error('name')
            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
        @enderror
    </div>

    <div @class([
        'md:col-span-2' => !$showTemplate,
    ])>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
            Deskripsi
        </label>
        <input type="text" wire:model="description" placeholder="Deskripsi singkat (opsional)"
            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500">
    </div>
</div>

@if ($showTemplate ? $templateId : true)
    <!-- Category Weights -->
    <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
        <h2 class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-4">Bobot Kategori</h2>
        @error('categoryWeights')
            <p class="text-red-500 text-sm mb-2">{{ $message }}</p>
        @enderror
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1">Potensi (%)</label>
                <input type="number" wire:model="categoryWeights.potensi" min="0" max="100"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100">
            </div>
            <div>
                <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1">Kompetensi (%)</label>
                <input type="number" wire:model="categoryWeights.kompetensi" min="0" max="100"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100">
            </div>
        </div>
        <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
            Total: {{ ($categoryWeights['potensi'] ?? 0) + ($categoryWeights['kompetensi'] ?? 0) }}%
            (harus 100%)
        </p>
    </div>

    <!-- Potensi Aspects -->
    @if (count($potensiAspects) > 0)
        <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
            <h2 class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-4">Aspek Potensi</h2>
            <div class="space-y-4">
                @foreach ($potensiAspects as $aspect)
                    <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center gap-3">
                                <input type="checkbox" wire:model="aspectConfigs.{{ $aspect['code'] }}.active"
                                    class="rounded border-gray-300 dark:border-gray-600">
                                <span class="font-medium text-gray-900 dark:text-gray-100">
                                    {{ $aspect['name'] }}
                                </span>
                            </div>
                            <div class="flex items-center gap-2">
                                <label class="text-sm text-gray-600 dark:text-gray-400">Bobot:</label>
                                <input type="number" wire:model="aspectConfigs.{{ $aspect['code'] }}.weight" min="0"
                                    max="100"
                                    class="w-16 px-2 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100">
                                <span class="text-sm text-gray-500">%</span>
                            </div>
                        </div>

                        @if (count($aspect['sub_aspects']) > 0)
                            <div class="ml-6 space-y-2">
                                @foreach ($aspect['sub_aspects'] as $subAspect)
                                    <div class="flex items-center justify-between py-1">
                                        <div class="flex items-center gap-2">
                                            <input type="checkbox"
                                                wire:model="subAspectConfigs.{{ $subAspect['code'] }}.active"
                                                class="rounded border-gray-300 dark:border-gray-600">
                                            <span class="text-sm text-gray-700 dark:text-gray-300">
                                                {{ $subAspect['name'] }}
                                            </span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <label class="text-xs text-gray-500">Rating:</label>
                                            <input type="number"
                                                wire:model="subAspectConfigs.{{ $subAspect['code'] }}.rating" min="1"
                                                max="5"
                                                class="w-14 px-2 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100">
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
            <h2 class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-4">Aspek Kompetensi</h2>
            <div class="space-y-3">
                @foreach ($kompetensiAspects as $aspect)
                    <div
                        class="flex items-center justify-between p-3 border border-gray-200 dark:border-gray-700 rounded-lg">
                        <div class="flex items-center gap-3">
                            <input type="checkbox" wire:model="aspectConfigs.{{ $aspect['code'] }}.active"
                                class="rounded border-gray-300 dark:border-gray-600">
                            <span class="font-medium text-gray-900 dark:text-gray-100">
                                {{ $aspect['name'] }}
                            </span>
                        </div>
                        <div class="flex items-center gap-4">
                            <div class="flex items-center gap-2">
                                <label class="text-sm text-gray-600 dark:text-gray-400">Bobot:</label>
                                <input type="number" wire:model="aspectConfigs.{{ $aspect['code'] }}.weight" min="0"
                                    max="100"
                                    class="w-16 px-2 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100">
                                <span class="text-sm text-gray-500">%</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <label class="text-sm text-gray-600 dark:text-gray-400">Rating:</label>
                                <input type="number" wire:model="aspectConfigs.{{ $aspect['code'] }}.rating" min="1"
                                    max="5" step="0.5"
                                    class="w-16 px-2 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100">
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
@endif
