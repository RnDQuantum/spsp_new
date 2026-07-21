<div class="max-w-7xl mx-auto p-4 md:p-6 font-sans text-primary-ink dark:text-neutral-100">
    <div class="bg-white dark:bg-[#171412] p-6 md:p-8 rounded-xl border border-warm-border dark:border-[#25211e] shadow-xs">
        
        {{-- Filter Section --}}
        <div class="mb-8 bg-warm-ivory dark:bg-[#1f1b18] p-5 rounded-xl border border-warm-border dark:border-[#25211e]">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Event Filter --}}
                <div class="p-3 bg-white dark:bg-[#171412] border border-warm-border dark:border-[#25211e] rounded-lg">
                    @livewire('components.event-selector', ['showLabel' => true])
                </div>

                {{-- Position Filter --}}
                <div class="p-3 bg-white dark:bg-[#171412] border border-warm-border dark:border-[#25211e] rounded-lg">
                    @livewire('components.position-selector', ['showLabel' => true])
                </div>
            </div>

            {{-- Template Info (Display Only) --}}
            @if ($selectedTemplate)
                <div class="mt-4 pt-4 border-t border-warm-border dark:border-[#25211e]">
                    <div class="flex flex-wrap items-center gap-6 text-sm font-mono-data text-primary-ink/80 dark:text-neutral-300">
                        <div class="flex items-center gap-2">
                            <span class="font-bold text-accent-amber">📄 Template:</span>
                            <span class="font-bold text-primary-ink dark:text-neutral-100">{{ $selectedTemplate->name }}</span>
                        </div>
                        @if ($selectedEvent)
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-accent-amber">🏢 Institusi:</span>
                                <span class="font-semibold text-primary-ink dark:text-neutral-100">{{ $selectedEvent->institution->name ?? 'N/A' }}</span>
                            </div>
                        @endif
                    </div>

                    {{-- PHASE 3: Custom Standard Selector --}}
                    @if (auth()->user()->institution_id)
                        <div class="mt-3">
                            <label for="customStandardSelect"
                                class="block text-xs font-bold font-mono-data uppercase tracking-wider text-primary-ink/80 dark:text-neutral-300 mb-1.5">
                                Standar Penilaian:
                            </label>
                            <select id="customStandardSelect" wire:model.live="selectedCustomStandardId"
                                wire:change="selectCustomStandard($event.target.value)"
                                class="w-full md:w-96 px-3 py-2 text-xs font-mono-data border border-warm-border dark:border-[#25211e] rounded-lg bg-white dark:bg-[#171412] text-primary-ink dark:text-neutral-100 focus:outline-none focus:border-accent-amber transition-colors">
                                <option value="">Quantum (Default)</option>
                                @foreach ($availableCustomStandards as $standard)
                                    <option value="{{ $standard['id'] }}">
                                        {{ $standard['name'] }} ({{ $standard['code'] }})
                                    </option>
                                @endforeach
                            </select>
                            @if ($selectedCustomStandardId)
                                <p class="mt-1 text-xs font-mono-data text-accent-amber">
                                    Menggunakan custom standard. Adjustment sementara akan direset.
                                </p>
                            @endif
                        </div>
                    @endif
                </div>

                {{-- PHASE 2C: Control Buttons --}}
                <div class="mt-5 flex flex-wrap items-center justify-between gap-4 pt-4 border-t border-warm-border dark:border-[#25211e]">
                    <div class="flex items-center gap-3">
                        {{-- Category Weight Editor Component --}}
                        @livewire('components.category-weight-editor', [
                            'templateId' => $selectedTemplate->id,
                            'categoryCode1' => 'potensi',
                            'categoryCode2' => 'kompetensi',
                        ])
                    </div>

                    <div class="flex items-center gap-3">
                        {{-- Button Pilih Aspek --}}
                        <button type="button"
                            x-data
                            @click="
                                $dispatch('open-selection-modal-instant');
                                $wire.openSelectionModal();
                            "
                            wire:loading.attr="disabled"
                            wire:target="openSelectionModal"
                            class="inline-flex items-center gap-2 px-4 py-2 text-xs font-bold uppercase tracking-wider rounded-lg bg-accent-amber text-white hover:bg-amber-700 active:scale-95 transition-all shadow-xs cursor-pointer disabled:opacity-50 disabled:cursor-wait">
                            <svg wire:loading wire:target="openSelectionModal" class="animate-spin h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span>🎯 Pilih Aspek & Sub-Aspek</span>
                        </button>

                        {{-- Reset Button --}}
                        <button type="button"
                            wire:click="resetAdjustments"
                            wire:loading.attr="disabled"
                            wire:target="resetAdjustments"
                            class="inline-flex items-center gap-2 px-4 py-2 text-xs font-bold uppercase tracking-wider rounded-lg border border-warm-border dark:border-[#25211e] bg-white dark:bg-[#171412] text-primary-ink dark:text-neutral-200 hover:bg-warm-ivory dark:hover:bg-[#1f1b18] transition-colors cursor-pointer disabled:opacity-50 disabled:cursor-wait">
                            <svg wire:loading wire:target="resetAdjustments" class="animate-spin h-3.5 w-3.5 text-primary-ink dark:text-neutral-200" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span>↻ Reset ke Default</span>
                        </button>
                    </div>

                    {{-- Adjustment Indicator --}}
                    <x-adjustment-indicator
                        :template-id="$selectedTemplate->id"
                        category-code="potensi"
                    />
                </div>
            @endif
        </div>

        {{-- Header Editorial Executive Journal --}}
        <div class="text-center mb-8">
            <span class="text-xs font-mono-data uppercase tracking-widest text-accent-amber font-bold block mb-1">Standar Penilaian Agregat</span>
            <h1 class="font-display text-2xl md:text-3xl font-bold tracking-tight text-primary-ink dark:text-neutral-100 uppercase mb-1">
                Standar Pemetaan Potensi Individu
            </h1>
            <p class="text-xs font-mono-data text-primary-ink/75 dark:text-neutral-400">
                Static Pribadi Spider Plot Analysis
            </p>
        </div>

        {{-- Empty State --}}
        @if (!$selectedEvent || !$selectedTemplate)
            <div class="text-center py-16 bg-warm-ivory/40 dark:bg-[#1f1b18]/40 border border-warm-border dark:border-[#25211e] rounded-xl">
                <div class="text-primary-ink/70 dark:text-neutral-300 text-base font-medium mb-1">Tidak ada data untuk ditampilkan</div>
                <div class="text-primary-ink/50 dark:text-neutral-400 text-xs font-mono-data">Silakan pilih kegiatan dan posisi terlebih dahulu.</div>
            </div>
        @else
            <div class="overflow-x-auto mb-6 rounded-lg border border-warm-border dark:border-[#25211e]">
                <table class="w-full text-sm text-primary-ink dark:text-neutral-100">
                    <tr class="border-b border-warm-border dark:border-[#25211e]">
                        <td class="px-4 py-3 bg-warm-ivory dark:bg-[#1f1b18] font-bold w-1/5 border-r border-warm-border dark:border-[#25211e]">
                            Perusahaan/Lembaga
                        </td>
                        <td class="px-4 py-3 font-semibold w-2/5 border-r border-warm-border dark:border-[#25211e]">
                            {{ strtoupper($selectedEvent->institution->name ?? 'N/A') }}
                        </td>
                        <td class="px-4 py-3 bg-warm-ivory dark:bg-[#1f1b18] font-bold w-1/6 border-r border-warm-border dark:border-[#25211e]">
                            Tahun Penilaian
                        </td>
                        <td class="px-4 py-3 font-mono-data text-center w-1/6">
                            {{ now()->year }}
                        </td>
                    </tr>
                    <tr>
                        <td class="px-4 py-3 bg-warm-ivory dark:bg-[#1f1b18] font-bold border-r border-warm-border dark:border-[#25211e]">
                            Standar Penilaian
                        </td>
                        <td class="px-4 py-3 font-semibold border-r border-warm-border dark:border-[#25211e]">
                            {{ $selectedTemplate->name }}
                        </td>
                        <td class="px-4 py-3 bg-warm-ivory dark:bg-[#1f1b18] font-bold border-r border-warm-border dark:border-[#25211e]">
                            Kode Standar:
                        </td>
                        <td class="px-4 py-3 font-mono-data font-bold text-center text-sm">
                            {{ $selectedTemplate->code }}
                        </td>
                    </tr>
                </table>
            </div>
        @endif

        {{-- Tabel Detail dengan Sub-Aspects --}}
        @if (count($categoryData) > 0)
            <div class="mt-6 mb-8 overflow-x-auto rounded-lg border border-warm-border dark:border-[#25211e]">
                <table class="w-full text-sm text-primary-ink dark:text-neutral-100 border-collapse">
                    <thead>
                        <tr class="bg-warm-ivory dark:bg-[#1f1b18] border-b border-warm-border dark:border-[#25211e]">
                            <th class="border-r border-warm-border dark:border-[#25211e] px-3 py-3 text-center font-bold text-xs uppercase tracking-wider w-12">No.</th>
                            <th class="border-r border-warm-border dark:border-[#25211e] px-4 py-3 text-left font-bold text-xs uppercase tracking-wider">ATRIBUT & SUB-ASPEK POTENSI</th>
                            <th class="border-r border-warm-border dark:border-[#25211e] px-4 py-3 text-center font-bold text-xs uppercase tracking-wider w-32">NILAI STANDAR</th>
                            <th class="border-r border-warm-border dark:border-[#25211e] px-4 py-3 text-center font-bold text-xs uppercase tracking-wider w-32">JUMLAH ATRIBUT</th>
                            <th class="border-r border-warm-border dark:border-[#25211e] px-4 py-3 text-center font-bold text-xs uppercase tracking-wider w-28">BOBOT (%)</th>
                            <th class="border-r border-warm-border dark:border-[#25211e] px-4 py-3 text-center font-bold text-xs uppercase tracking-wider w-36">RATING RATA-RATA</th>
                            <th class="px-4 py-3 text-center font-bold text-xs uppercase tracking-wider w-28">SKOR</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-warm-border dark:divide-[#25211e]">
                        @php $romanNumerals = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X']; @endphp

                        @foreach ($categoryData as $catIndex => $category)
                            {{-- Aspects within Potensi Category --}}
                            @foreach ($category['aspects'] as $aspectIndex => $aspect)
                                {{-- Aspect Row --}}
                                @if (count($aspect['sub_aspects']) > 0)
                                    <tr class="bg-warm-ivory/30 dark:bg-[#1f1b18]/40 font-bold">
                                        <td class="border-r border-warm-border dark:border-[#25211e] px-3 py-2.5 text-center font-mono-data text-sm">
                                            {{ $romanNumerals[$aspectIndex] ?? $aspectIndex + 1 }}
                                        </td>
                                        <td class="border-r border-warm-border dark:border-[#25211e] px-4 py-2.5 font-semibold text-sm">
                                            {{ $aspect['name'] }}
                                        </td>
                                        <td class="border-r border-warm-border dark:border-[#25211e] px-4 py-2.5 text-center font-mono-data text-sm"></td>
                                        <td class="border-r border-warm-border dark:border-[#25211e] px-4 py-2.5 text-center font-mono-data font-bold text-sm">
                                            {{ $aspect['sub_aspects_count'] }}
                                        </td>
                                        <td class="border-r border-warm-border dark:border-[#25211e] px-4 py-2.5 text-center font-mono-data font-bold text-sm {{ $aspect['is_weight_adjusted'] ?? false ? 'bg-amber-100 dark:bg-amber-900/40 text-amber-900 dark:text-amber-200' : '' }}"
                                            title="{{ $aspect['is_weight_adjusted'] ?? false ? 'Disesuaikan dari ' . $aspect['original_weight'] . '% - Edit via modal Pilih Aspek' : '' }}">
                                            {{ (int) $aspect['weight_percentage'] }}
                                        </td>
                                        <td class="border-r border-warm-border dark:border-[#25211e] px-4 py-2.5 text-center font-mono-data font-bold text-sm">
                                            {{ number_format($aspect['standard_rating'], 2) }}
                                        </td>
                                        <td class="px-4 py-2.5 text-center font-mono-data font-bold text-sm">
                                            {{ number_format($aspect['score'], 2) }}
                                        </td>
                                    </tr>

                                    {{-- Sub-Aspects --}}
                                    @foreach ($aspect['sub_aspects'] as $subIndex => $subAspect)
                                        <tr class="hover:bg-warm-ivory/40 dark:hover:bg-[#1f1b18]/40 transition-colors">
                                            <td class="border-r border-warm-border dark:border-[#25211e] px-3 py-2 text-center font-mono-data text-sm text-primary-ink/60 dark:text-neutral-400">
                                                {{ $subIndex + 1 }}
                                            </td>
                                            <td class="border-r border-warm-border dark:border-[#25211e] px-4 py-2 pl-8 text-sm text-primary-ink/90 dark:text-neutral-200">
                                                {{ $subAspect['name'] }}
                                            </td>
                                            {{-- Clickable Rating Cell --}}
                                            <td wire:click="openEditSubAspectRating('{{ $subAspect['code'] }}', {{ $subAspect['standard_rating'] }})"
                                                class="border-r border-warm-border dark:border-[#25211e] px-4 py-2 text-center font-mono-data text-sm cursor-pointer hover:bg-accent-amber/15 dark:hover:bg-amber-900/30 transition-colors {{ $subAspect['is_adjusted'] ?? false ? 'bg-amber-100 dark:bg-amber-900/40 font-bold text-amber-900 dark:text-amber-200' : '' }}"
                                                title="{{ $subAspect['is_adjusted'] ?? false ? 'Disesuaikan dari ' . $subAspect['original_rating'] . ' - Klik untuk edit' : 'Klik untuk edit rating' }}">
                                                <span class="font-bold">
                                                    {{ (int) $subAspect['standard_rating'] }}
                                                </span>
                                            </td>
                                            <td class="border-r border-warm-border dark:border-[#25211e] px-4 py-2"></td>
                                            <td class="border-r border-warm-border dark:border-[#25211e] px-4 py-2"></td>
                                            <td class="border-r border-warm-border dark:border-[#25211e] px-4 py-2"></td>
                                            <td class="px-4 py-2"></td>
                                        </tr>
                                    @endforeach
                                @else
                                    {{-- Aspect without sub-aspects --}}
                                    <tr class="hover:bg-warm-ivory/40 dark:hover:bg-[#1f1b18]/40 transition-colors">
                                        <td class="border-r border-warm-border dark:border-[#25211e] px-3 py-2 text-center font-mono-data text-sm">
                                            {{ $aspectIndex + 1 }}
                                        </td>
                                        <td class="border-r border-warm-border dark:border-[#25211e] px-4 py-2 font-semibold text-sm">
                                            {{ $aspect['name'] }}
                                        </td>
                                        <td class="border-r border-warm-border dark:border-[#25211e] px-4 py-2 text-center font-mono-data font-bold text-sm">
                                            {{ (int) $aspect['standard_rating'] }}
                                        </td>
                                        <td class="border-r border-warm-border dark:border-[#25211e] px-4 py-2"></td>
                                        <td class="border-r border-warm-border dark:border-[#25211e] px-4 py-2 text-center font-mono-data font-bold text-sm">
                                            {{ (int) $aspect['weight_percentage'] }}
                                        </td>
                                        <td class="border-r border-warm-border dark:border-[#25211e] px-4 py-2"></td>
                                        <td class="px-4 py-2"></td>
                                    </tr>
                                @endif
                            @endforeach
                        @endforeach

                        {{-- TOTAL --}}
                        <tr class="bg-warm-ivory dark:bg-[#1f1b18] text-primary-ink dark:text-neutral-100 font-bold border-t-2 border-warm-border dark:border-[#25211e] text-sm">
                            <td class="border-r border-warm-border dark:border-[#25211e] px-4 py-3 text-center uppercase tracking-wider font-display" colspan="2">
                                JUMLAH
                            </td>
                            <td class="border-r border-warm-border dark:border-[#25211e] px-4 py-3 text-center font-mono-data text-sm">
                                {{ (int) $totals['total_standard_rating_sum'] }}
                            </td>
                            <td class="border-r border-warm-border dark:border-[#25211e] px-4 py-3 text-center font-mono-data text-sm">
                                {{ $totals['total_aspects'] }}
                            </td>
                            <td class="border-r border-warm-border dark:border-[#25211e] px-4 py-3 text-center font-mono-data text-sm">
                                {{ (int) $totals['total_weight'] }}
                            </td>
                            <td class="border-r border-warm-border dark:border-[#25211e] px-4 py-3 text-center font-mono-data text-sm">
                                {{ number_format($totals['total_rating_sum'], 2) }}
                            </td>
                            <td class="px-4 py-3 text-center font-mono-data text-sm">
                                {{ number_format($totals['total_score'], 2) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        @endif

        {{-- CHART SATU - Rating --}}
        @if (count($chartData['labels']) > 0)
            <div class="border border-warm-border dark:border-[#25211e] mb-8 mt-8 p-6 rounded-xl bg-warm-ivory/30 dark:bg-[#1f1b18]/40 transition-all shadow-xs"
                wire:ignore id="chart-rating-{{ $chartId }}">
                <h3 class="text-center font-display text-sm md:text-base font-bold tracking-tight text-primary-ink dark:text-neutral-100 mb-4 uppercase">
                    Gambar Rating Standar Atribut Potensi Mapping Static Pribadi Spider Plot
                </h3>
                <div class="flex justify-center">
                    <canvas id="chartRating-{{ $chartId }}" style="max-width:580px; max-height:400px;"></canvas>
                </div>
            </div>

            {{-- Tabel Mapping Summary --}}
            <div class="mt-8 mb-6 overflow-x-auto rounded-lg border border-warm-border dark:border-[#25211e]">
                <div class="bg-warm-ivory dark:bg-[#1f1b18] text-primary-ink dark:text-neutral-100 border-b border-warm-border dark:border-[#25211e] px-4 py-3 text-center font-display font-bold text-sm uppercase tracking-wider">
                    ATRIBUT POTENSI MAPPING
                </div>
                <table class="w-full text-sm text-primary-ink dark:text-neutral-100 border-collapse">
                    <thead>
                        <tr class="bg-warm-ivory/50 dark:bg-[#1f1b18]/50 border-b border-warm-border dark:border-[#25211e]">
                            <th class="border-r border-warm-border dark:border-[#25211e] px-3 py-2.5 text-center font-bold text-xs uppercase tracking-wider w-12">No.</th>
                            <th class="border-r border-warm-border dark:border-[#25211e] px-4 py-2.5 text-left font-bold text-xs uppercase tracking-wider">ATRIBUT</th>
                            <th class="border-r border-warm-border dark:border-[#25211e] px-4 py-2.5 text-center font-bold text-xs uppercase tracking-wider w-32">JUMLAH ATRIBUT</th>
                            <th class="border-r border-warm-border dark:border-[#25211e] px-4 py-2.5 text-center font-bold text-xs uppercase tracking-wider w-28">BOBOT (%)</th>
                            <th class="border-r border-warm-border dark:border-[#25211e] px-4 py-2.5 text-center font-bold text-xs uppercase tracking-wider w-36">RATING RATA-RATA</th>
                            <th class="px-4 py-2.5 text-center font-bold text-xs uppercase tracking-wider w-28">SKOR</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-warm-border dark:divide-[#25211e]">
                        @foreach ($categoryData as $catIndex => $category)
                            @foreach ($category['aspects'] as $aspectIndex => $aspect)
                                <tr class="hover:bg-warm-ivory/40 dark:hover:bg-[#1f1b18]/40 transition-colors">
                                    <td class="border-r border-warm-border dark:border-[#25211e] px-3 py-2.5 text-center font-mono-data font-bold text-sm">
                                        {{ $romanNumerals[$aspectIndex] ?? $aspectIndex + 1 }}
                                    </td>
                                    <td class="border-r border-warm-border dark:border-[#25211e] px-4 py-2.5 font-semibold text-sm">
                                        {{ $aspect['name'] }}
                                    </td>
                                    <td class="border-r border-warm-border dark:border-[#25211e] px-4 py-2.5 text-center font-mono-data font-bold text-sm">
                                        {{ $aspect['sub_aspects_count'] }}
                                    </td>
                                    <td class="border-r border-warm-border dark:border-[#25211e] px-4 py-2.5 text-center font-mono-data font-bold text-sm">
                                        {{ (int) $aspect['weight_percentage'] }}
                                    </td>
                                    <td class="border-r border-warm-border dark:border-[#25211e] px-4 py-2.5 text-center font-mono-data font-bold text-sm">
                                        {{ (int) $aspect['standard_rating'] }}
                                    </td>
                                    <td class="px-4 py-2.5 text-center font-mono-data font-bold text-sm">
                                        {{ number_format($aspect['score'], 2) }}
                                    </td>
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="font-bold bg-warm-ivory dark:bg-[#1f1b18] text-primary-ink dark:text-neutral-100 border-t-2 border-warm-border dark:border-[#25211e] text-sm">
                            <td class="border-r border-warm-border dark:border-[#25211e] px-4 py-3 text-center font-display uppercase tracking-wider" colspan="2">
                                JUMLAH
                            </td>
                            <td class="border-r border-warm-border dark:border-[#25211e] px-4 py-3 text-center font-mono-data text-sm">
                                {{ $totals['total_aspects'] }}
                            </td>
                            <td class="border-r border-warm-border dark:border-[#25211e] px-4 py-3 text-center font-mono-data text-sm">
                                {{ (int) $totals['total_weight'] }}
                            </td>
                            <td class="border-r border-warm-border dark:border-[#25211e] px-4 py-3 text-center font-mono-data text-sm">
                                {{ number_format($totals['total_rating_sum'], 2) }}
                            </td>
                            <td class="px-4 py-3 text-center font-mono-data text-sm">
                                {{ number_format($totals['total_score'], 2) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            {{-- CHART DUA - Skor --}}
            <div class="border border-warm-border dark:border-[#25211e] mb-8 mt-8 p-6 rounded-xl bg-warm-ivory/30 dark:bg-[#1f1b18]/40 transition-all shadow-xs"
                wire:ignore id="chart-skor-{{ $chartId }}">
                <h3 class="text-center font-display text-sm md:text-base font-bold tracking-tight text-primary-ink dark:text-neutral-100 mb-4 uppercase">
                    Gambar Skor Standar Atribut Potensi Mapping Static Pribadi Spider Plot
                </h3>
                <div class="flex justify-center">
                    <canvas id="chartSkor-{{ $chartId }}" style="max-width:580px; max-height:400px;"></canvas>
                </div>
            </div>
        @endif

        {{-- Footer TTD dan Catatan --}}
        @if ($selectedEvent)
            <div class="mt-12 pt-8 border-t border-warm-border dark:border-[#25211e] grid grid-cols-1 md:grid-cols-2 gap-8 text-xs text-primary-ink dark:text-neutral-200">
                <div>
                    <div class="mb-1 font-medium">Menyetujui,</div>
                    <div class="font-bold font-display text-sm mb-16 text-primary-ink dark:text-neutral-100">{{ strtoupper($selectedEvent->institution->name ?? 'N/A') }}</div>
                    <div class="border-b border-warm-border dark:border-[#25211e] w-3/4 mb-1"></div>
                    <div class="w-3/4 font-mono-data text-primary-ink/60 dark:text-neutral-400">................................................</div>
                </div>
                <div class="text-right">
                    <div class="mb-1 font-mono-data">Surabaya, {{ now()->format('d F Y') }}</div>
                    <div class="mb-1 font-medium">Mengetahui,</div>
                    <div class="font-bold font-display text-sm mb-16 text-primary-ink dark:text-neutral-100">PT. QUANTUM HRM INTERNASIONAL</div>
                    <div class="font-bold underline text-sm mb-0.5">Prof. Dr. Pribadiyono, MS</div>
                    <div class="text-[11px] font-mono-data text-primary-ink/75 dark:text-neutral-400">Pemegang Hak Cipta Haki No. 027762 - 10 Maret 2004</div>
                </div>
            </div>
        @endif
    </div>

    {{-- PHASE 2C: Include SelectiveAspectsModal Component --}}
    @livewire('components.selective-aspects-modal')

    {{-- Edit Sub-Aspect Rating Modal --}}
    <div x-data="{ show: @entangle('showEditRatingModal') }" x-on:keydown.esc.window="$wire.closeModal()" x-cloak>
        <div x-show="show" x-transition.opacity.duration.200ms
            x-on:click.self="$wire.closeModal()"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm"
            role="dialog" aria-modal="true">
            <div x-show="show"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-100"
                x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                class="w-full max-w-md overflow-hidden rounded-xl border border-warm-border dark:border-[#25211e] bg-white dark:bg-[#171412] text-primary-ink dark:text-neutral-100 shadow-2xl font-sans"
                x-trap="show">

                {{-- Header --}}
                <div class="flex items-center justify-between border-b border-warm-border dark:border-[#25211e] bg-warm-ivory dark:bg-[#1f1b18] px-6 py-4">
                    <div>
                        <h3 class="text-lg font-bold font-display tracking-tight text-primary-ink dark:text-neutral-100">
                            Edit Rating Sub-Aspek
                        </h3>
                        <p class="text-xs font-mono-data text-primary-ink/75 dark:text-neutral-400">
                            Sesuaikan nilai rating sub-aspek potensi (1 - 5)
                        </p>
                    </div>
                    <button wire:click="closeModal" class="text-primary-ink/70 dark:text-neutral-400 hover:text-accent-amber transition-colors p-1">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                {{-- Body --}}
                <div class="p-6 space-y-4 bg-white dark:bg-[#171412]">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider font-mono-data text-primary-ink dark:text-neutral-200 mb-1.5">
                            Nilai Rating (1-5)
                        </label>
                        <input type="number" min="1" max="5" step="1" wire:model="editingValue"
                            class="w-full rounded-lg border border-warm-border dark:border-[#25211e] bg-warm-ivory/40 dark:bg-[#1f1b18] px-4 py-2.5 text-lg font-mono-data font-bold text-primary-ink dark:text-neutral-100 focus:outline-none focus:border-accent-amber transition-colors">
                    </div>
                    <p class="text-xs font-mono-data text-primary-ink/70 dark:text-neutral-400">
                        Nilai bawaan template: <span class="font-bold text-primary-ink dark:text-neutral-200">{{ $editingOriginalValue }}</span>
                    </p>
                </div>

                {{-- Footer --}}
                <div class="flex items-center justify-end gap-3 border-t border-warm-border dark:border-[#25211e] bg-warm-ivory dark:bg-[#1f1b18] px-6 py-3">
                    <button type="button" wire:click="closeModal"
                        class="rounded-lg border border-warm-border dark:border-[#25211e] bg-white dark:bg-[#171412] px-4 py-2 text-xs font-bold uppercase tracking-wider text-primary-ink dark:text-neutral-200 hover:bg-warm-ivory transition-colors cursor-pointer">
                        Batal
                    </button>
                    <button type="button" wire:click="saveSubAspectRating"
                        wire:loading.attr="disabled"
                        wire:target="saveSubAspectRating"
                        class="inline-flex items-center gap-2 rounded-lg bg-accent-amber px-5 py-2 text-xs font-bold uppercase tracking-wider text-white hover:bg-amber-700 transition-colors shadow-xs cursor-pointer disabled:opacity-50 disabled:cursor-wait">
                        <svg wire:loading wire:target="saveSubAspectRating" class="animate-spin h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span>Simpan</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    @if (count($chartData['labels']) > 0)
        <script>
            (function() {
                if (window['ratingChartSetup_{{ $chartId }}']) return;
                window['ratingChartSetup_{{ $chartId }}'] = true;

                function setupRatingChart() {
                    if (window.ratingChart_{{ $chartId }}) {
                        window.ratingChart_{{ $chartId }}.destroy();
                    }

                    let chartInstance = null;
                    let chartLabels = @js($chartData['labels']);
                    let chartRatings = @js($chartData['ratings']);
                    let templateName = @js($selectedTemplate?->name ?? 'Standard');

                    function getChartColors() {
                        const isDarkMode = document.documentElement.classList.contains('dark');
                        return {
                            borderColor: '#5db010',
                            backgroundColor: 'rgba(93, 176, 16, 0.25)',
                            pointBackgroundColor: '#5db010',
                            pointBorderColor: '#ffffff',
                            textColor: isDarkMode ? '#e5e5e5' : '#171412',
                            gridColor: isDarkMode ? 'rgba(255, 255, 255, 0.2)' : 'rgba(23, 20, 18, 0.15)',
                            angleColor: isDarkMode ? 'rgba(255, 255, 255, 0.2)' : 'rgba(23, 20, 18, 0.15)'
                        };
                    }

                    function updateChartColors() {
                        const colors = getChartColors();
                        if (window.ratingChart_{{ $chartId }}) {
                            const chart = window.ratingChart_{{ $chartId }};
                            chart.options.plugins.legend.labels.color = colors.textColor;
                            chart.options.scales.r.ticks.color = colors.textColor;
                            chart.options.scales.r.pointLabels.color = colors.textColor;
                            chart.options.scales.r.grid.color = colors.gridColor;
                            chart.options.scales.r.angleLines.color = colors.angleColor;
                            chart.update('active');
                        }
                    }

                    function initChart() {
                        const canvas = document.getElementById('chartRating-{{ $chartId }}');
                        if (!canvas) return;

                        const ctx = canvas.getContext('2d');
                        const colors = getChartColors();

                        chartInstance = new Chart(ctx, {
                            type: 'radar',
                            data: {
                                labels: chartLabels,
                                datasets: [{
                                    label: 'Rating Standar: ' + templateName,
                                    data: chartRatings,
                                    fill: true,
                                    borderColor: colors.borderColor,
                                    backgroundColor: colors.backgroundColor,
                                    pointBackgroundColor: colors.pointBackgroundColor,
                                    pointBorderColor: colors.pointBorderColor,
                                    pointRadius: 4,
                                    pointHoverRadius: 6,
                                    borderWidth: 2.5
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: true,
                                plugins: {
                                    legend: {
                                        display: true,
                                        position: 'bottom',
                                        labels: {
                                            usePointStyle: true,
                                            pointStyle: 'circle',
                                            padding: 16,
                                            font: {
                                                size: 12,
                                                family: "'Instrument Sans', sans-serif",
                                                weight: '600'
                                            },
                                            color: colors.textColor
                                        }
                                    }
                                },
                                scales: {
                                    r: {
                                        beginAtZero: true,
                                        min: 0,
                                        max: 5,
                                        ticks: {
                                            stepSize: 1,
                                            font: {
                                                size: 11,
                                                family: "'Instrument Sans', sans-serif",
                                                weight: '600'
                                            },
                                            color: colors.textColor,
                                            backdropColor: 'transparent',
                                            showLabelBackdrop: false
                                        },
                                        pointLabels: {
                                            font: {
                                                size: 12,
                                                family: "'Instrument Sans', sans-serif",
                                                weight: '600'
                                            },
                                            color: colors.textColor
                                        },
                                        grid: {
                                            color: colors.gridColor
                                        },
                                        angleLines: {
                                            color: colors.angleColor
                                        }
                                    }
                                }
                            }
                        });

                        window.ratingChart_{{ $chartId }} = chartInstance;
                    }

                    function waitForLivewire(callback) {
                        if (window.Livewire) callback();
                        else setTimeout(() => waitForLivewire(callback), 100);
                    }

                    waitForLivewire(function() {
                        initChart();

                        const observer = new MutationObserver(() => {
                            updateChartColors();
                        });
                        observer.observe(document.documentElement, {
                            attributes: true,
                            attributeFilter: ['class']
                        });

                        Livewire.on('chartDataUpdated', function(data) {
                            let chartData = Array.isArray(data) && data.length > 0 ? data[0] : data;
                            if (window.ratingChart_{{ $chartId }} && chartData) {
                                chartLabels = chartData.labels;
                                chartRatings = chartData.ratings;
                                templateName = chartData.templateName || 'Standard';

                                const chart = window.ratingChart_{{ $chartId }};
                                chart.data.labels = chartLabels;
                                chart.data.datasets[0].label = 'Rating Standar: ' + templateName;
                                chart.data.datasets[0].data = chartRatings;
                                updateChartColors();
                                chart.options.scales.r.ticks.backdropColor = 'transparent';
                                chart.options.scales.r.ticks.showLabelBackdrop = false;
                                chart.update('active');
                            }
                        });
                    });
                }

                setupRatingChart();
            })();
        </script>

        <script>
            (function() {
                if (window['skorChartSetup_{{ $chartId }}']) return;
                window['skorChartSetup_{{ $chartId }}'] = true;

                function setupSkorChart() {
                    if (window.skorChart_{{ $chartId }}) {
                        window.skorChart_{{ $chartId }}.destroy();
                    }

                    let chartInstance = null;
                    let chartLabels = @js($chartData['labels']);
                    let chartScores = @js($chartData['scores']);
                    let templateName = @js($selectedTemplate?->name ?? 'Standard');
                    let maxScore = @js($maxScore);

                    function getChartColors() {
                        const isDarkMode = document.documentElement.classList.contains('dark');
                        return {
                            borderColor: '#5db010',
                            backgroundColor: 'rgba(93, 176, 16, 0.25)',
                            pointBackgroundColor: '#5db010',
                            pointBorderColor: '#ffffff',
                            textColor: isDarkMode ? '#e5e5e5' : '#171412',
                            gridColor: isDarkMode ? 'rgba(255, 255, 255, 0.2)' : 'rgba(23, 20, 18, 0.15)',
                            angleColor: isDarkMode ? 'rgba(255, 255, 255, 0.2)' : 'rgba(23, 20, 18, 0.15)'
                        };
                    }

                    function updateChartColors() {
                        const colors = getChartColors();
                        if (window.skorChart_{{ $chartId }}) {
                            const chart = window.skorChart_{{ $chartId }};
                            chart.options.plugins.legend.labels.color = colors.textColor;
                            chart.options.scales.r.ticks.color = colors.textColor;
                            chart.options.scales.r.pointLabels.color = colors.textColor;
                            chart.options.scales.r.grid.color = colors.gridColor;
                            chart.options.scales.r.angleLines.color = colors.angleColor;
                            chart.update('active');
                        }
                    }

                    function initChart() {
                        const canvas = document.getElementById('chartSkor-{{ $chartId }}');
                        if (!canvas) return;

                        const ctx = canvas.getContext('2d');
                        const colors = getChartColors();

                        chartInstance = new Chart(ctx, {
                            type: 'radar',
                            data: {
                                labels: chartLabels,
                                datasets: [{
                                    label: 'Skor Standar: ' + templateName,
                                    data: chartScores,
                                    fill: true,
                                    borderColor: colors.borderColor,
                                    backgroundColor: colors.backgroundColor,
                                    pointBackgroundColor: colors.pointBackgroundColor,
                                    pointBorderColor: colors.pointBorderColor,
                                    pointRadius: 4,
                                    pointHoverRadius: 6,
                                    borderWidth: 2.5
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: true,
                                plugins: {
                                    legend: {
                                        display: true,
                                        position: 'bottom',
                                        labels: {
                                            usePointStyle: true,
                                            pointStyle: 'circle',
                                            padding: 16,
                                            font: {
                                                size: 12,
                                                family: "'Instrument Sans', sans-serif",
                                                weight: '600'
                                            },
                                            color: colors.textColor
                                        }
                                    }
                                },
                                scales: {
                                    r: {
                                        beginAtZero: true,
                                        min: 0,
                                        max: maxScore,
                                        ticks: {
                                            stepSize: maxScore > 50 ? 20 : 10,
                                            font: {
                                                size: 11,
                                                family: "'Instrument Sans', sans-serif",
                                                weight: '600'
                                            },
                                            color: colors.textColor,
                                            backdropColor: 'transparent',
                                            showLabelBackdrop: false
                                        },
                                        pointLabels: {
                                            font: {
                                                size: 12,
                                                family: "'Instrument Sans', sans-serif",
                                                weight: '600'
                                            },
                                            color: colors.textColor
                                        },
                                        grid: {
                                            color: colors.gridColor
                                        },
                                        angleLines: {
                                            color: colors.angleColor
                                        }
                                    }
                                }
                            }
                        });

                        window.skorChart_{{ $chartId }} = chartInstance;
                    }

                    function waitForLivewire(callback) {
                        if (window.Livewire) callback();
                        else setTimeout(() => waitForLivewire(callback), 100);
                    }

                    waitForLivewire(function() {
                        initChart();

                        const observer = new MutationObserver(() => {
                            updateChartColors();
                        });
                        observer.observe(document.documentElement, {
                            attributes: true,
                            attributeFilter: ['class']
                        });

                        Livewire.on('chartDataUpdated', function(data) {
                            let chartData = Array.isArray(data) && data.length > 0 ? data[0] : data;
                            if (window.skorChart_{{ $chartId }} && chartData) {
                                chartLabels = chartData.labels;
                                chartScores = chartData.scores;
                                templateName = chartData.templateName || 'Standard';
                                maxScore = chartData.maxScore || maxScore;

                                const chart = window.skorChart_{{ $chartId }};
                                chart.data.labels = chartLabels;
                                chart.data.datasets[0].label = 'Skor Standar: ' + templateName;
                                chart.data.datasets[0].data = chartScores;
                                chart.options.scales.r.max = maxScore;
                                updateChartColors();
                                chart.options.scales.r.ticks.backdropColor = 'transparent';
                                chart.options.scales.r.ticks.showLabelBackdrop = false;
                                chart.update('active');
                            }
                        });
                    });
                }

                setupSkorChart();
            })();
        </script>
    @endif
</div>
