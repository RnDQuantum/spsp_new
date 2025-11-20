<div>
    <div class="bg-white dark:bg-gray-900 mx-auto my-8 shadow-lg dark:shadow-gray-800/50 overflow-hidden"
        style="max-width: 1200px;">
        <!-- Header -->
        <div class="border-b-4 border-black dark:border-gray-300 py-3 bg-gray-300 dark:bg-gray-600">
            <h1 class="text-center text-lg font-bold uppercase tracking-wide text-gray-900 dark:text-gray-100">
                Buat Custom Standard
            </h1>
        </div>

        <!-- Form -->
        <form wire:submit="save" class="p-6 space-y-6">
            @if (session('error'))
                <div
                    class="bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300 px-4 py-3 rounded">
                    {{ session('error') }}
                </div>
            @endif

            <x-custom-standard-form :code="$code" :name="$name" :description="$description"
                :categoryWeights="$categoryWeights" :aspectConfigs="$aspectConfigs"
                :subAspectConfigs="$subAspectConfigs" :potensiAspects="$potensiAspects"
                :kompetensiAspects="$kompetensiAspects" :showTemplate="true" :templateId="$templateId"
                :templates="$templates" />

            <!-- Actions -->
            <div class="border-t border-gray-200 dark:border-gray-700 pt-6 flex justify-end gap-3">
                <x-mary-button label="Batal" link="{{ route('custom-standards.index') }}" />
                <x-mary-button label="Simpan" type="submit" class="btn-primary" spinner="save"
                    :disabled="!$templateId || !$this->validationResult['valid']" />
            </div>
        </form>
    </div>
</div>