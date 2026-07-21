<div>
    <div class="max-w-[1400px] mx-auto p-3 md:p-4 font-sans text-primary-ink dark:text-neutral-100">
        <div class="bg-white dark:bg-[#171412] p-4 md:p-6 rounded-xl border border-warm-border dark:border-[#25211e] shadow-xs">
            
            {{-- Header Editorial Executive Journal --}}
            <div class="mb-6 pb-4 border-b border-warm-border dark:border-[#25211e]">
                <span class="font-mono-data text-amber-700 dark:text-amber-500 font-bold uppercase tracking-widest text-xs block mb-1">
                    ADMINISTRATION / EDIT STANDAR KHUSUS
                </span>
                <h1 class="font-display text-xl md:text-2xl font-bold tracking-tight text-primary-ink dark:text-neutral-100">
                    Edit Standar Penilaian Kustom
                </h1>
                <p class="text-xs font-semibold text-primary-ink/70 dark:text-neutral-400 mt-1">
                    Template: <span class="text-primary-ink dark:text-neutral-200 font-bold">{{ $customStandard->template->name }}</span>
                </p>
            </div>

            <!-- Form -->
            <form wire:submit="save" class="space-y-6">
                <x-custom-standard-form :code="$code" :name="$name" :description="$description"
                    :categoryWeights="$categoryWeights" :aspectConfigs="$aspectConfigs"
                    :subAspectConfigs="$subAspectConfigs" :potensiAspects="$potensiAspects"
                    :kompetensiAspects="$kompetensiAspects" />

                <!-- Actions -->
                <div class="border-t border-warm-border dark:border-[#25211e] pt-6 flex justify-end gap-3">
                    <x-mary-button label="Batal" link="{{ route('custom-standards.index') }}" class="btn-ghost" />
                    <x-mary-button label="Simpan Perubahan" type="submit" class="btn-primary bg-[#171412] dark:bg-[#25211e] text-white hover:bg-black border-none" spinner="save"
                        :disabled="!$this->validationResult['valid']" />
                </div>
            </form>
        </div>
    </div>
</div>
