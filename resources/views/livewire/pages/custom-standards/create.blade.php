<div>
    <div class="max-w-[1400px] mx-auto p-3 md:p-4 font-sans text-primary-ink dark:text-neutral-100">
        <div class="bg-white dark:bg-[#171412] p-4 md:p-6 rounded-xl border border-warm-border dark:border-[#25211e] shadow-xs">
            
            {{-- Header Editorial Executive Journal --}}
            <div class="mb-6 pb-4 border-b border-warm-border dark:border-[#25211e]">
                <span class="font-mono-data text-amber-700 dark:text-amber-500 font-bold uppercase tracking-widest text-xs block mb-1">
                    ADMINISTRATION / BUAT STANDAR KHUSUS
                </span>
                <h1 class="font-display text-xl md:text-2xl font-bold tracking-tight text-primary-ink dark:text-neutral-100">
                    Buat Standar Penilaian Kustom
                </h1>
            </div>

            <!-- Form -->
            <form wire:submit="save" class="space-y-6">
                @if (session('error'))
                    <div class="bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-rose-700 dark:text-rose-300 px-4 py-3 rounded-lg text-sm mb-6">
                        {{ session('error') }}
                    </div>
                @endif

                <x-custom-standard-form :code="$code" :name="$name" :description="$description"
                    :categoryWeights="$categoryWeights" :aspectConfigs="$aspectConfigs"
                    :subAspectConfigs="$subAspectConfigs" :potensiAspects="$potensiAspects"
                    :kompetensiAspects="$kompetensiAspects" :showTemplate="true" :templateId="$templateId"
                    :templates="$templates" />

                <!-- Actions -->
                <div class="border-t border-warm-border dark:border-[#25211e] pt-6 flex justify-end gap-3">
                    <x-mary-button label="Batal" link="{{ route('custom-standards.index') }}" class="btn-ghost" />
                    <x-mary-button label="Simpan" type="submit" class="btn-primary bg-[#171412] dark:bg-[#25211e] text-white hover:bg-black border-none" spinner="save"
                        :disabled="!$templateId || !$this->validationResult['valid']" />
                </div>
            </form>
        </div>
    </div>
</div>