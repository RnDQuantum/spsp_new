<div>
    <div class="bg-white dark:bg-gray-900 mx-auto my-8 shadow-lg dark:shadow-gray-800/50 overflow-hidden"
        style="max-width: 1200px;">
        <!-- Header -->
        <div class="border-b-4 border-black dark:border-gray-300 py-3 bg-gray-300 dark:bg-gray-600">
            <h1 class="text-center text-lg font-bold uppercase tracking-wide text-gray-900 dark:text-gray-100">
                Edit Custom Standard
            </h1>
            <p class="text-center text-sm text-gray-700 dark:text-gray-300 mt-1">
                Template: {{ $customStandard->template->name }}
            </p>
        </div>

        <!-- Form -->
        <form wire:submit="save" class="p-6 space-y-6">
            <x-custom-standard-form :code="$code" :name="$name" :description="$description"
                :categoryWeights="$categoryWeights" :aspectConfigs="$aspectConfigs"
                :subAspectConfigs="$subAspectConfigs" :potensiAspects="$potensiAspects"
                :kompetensiAspects="$kompetensiAspects" />

            <!-- Actions -->
            <div class="border-t border-gray-200 dark:border-gray-700 pt-6 flex justify-end gap-3">
                <a href="{{ route('custom-standards.index') }}"
                    class="px-4 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 rounded-md transition-colors">
                    Batal
                </a>
                <button type="submit"
                    class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md transition-colors">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
