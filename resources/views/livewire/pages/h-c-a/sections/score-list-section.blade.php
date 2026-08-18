<div class="w-full max-w-5xl mx-auto bg-white border border-warm-border rounded-xl p-8 md:p-12 print:border-none shadow-sm">
    
    <!-- Section Header -->
    <div class="border-b border-warm-border pb-6 mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <span class="text-[11px] font-bold uppercase tracking-widest text-slate-400 block mb-1">
                {{ $subtitle }}
            </span>
            <h2 class="font-display text-2xl md:text-3xl text-primary-ink font-semibold">
                {{ explode(':', $title)[0] }} <span class="text-accent-amber italic">{{ count(explode(':', $title)) > 1 ? trim(explode(':', $title)[1]) : '' }}</span>
            </h2>
        </div>
        <!-- Average Index & IQ Callout -->
        <div class="flex flex-wrap items-center gap-3">
            @if (!empty($iqScore))
                <div class="flex items-center gap-2 bg-accent-amber/15 border border-accent-amber/30 px-3.5 py-1.5 rounded-lg shadow-2xs">
                    <span class="text-xs font-bold uppercase tracking-wider text-accent-amber">Skor IQ:</span>
                    <span class="text-base font-display font-bold text-primary-ink">{{ $iqScore }}</span>
                    @if (!empty($iqCategory))
                        <span class="text-[10px] font-bold uppercase tracking-wider bg-accent-amber text-white px-2 py-0.5 rounded-md">
                            {{ $iqCategory }}
                        </span>
                    @endif
                </div>
            @endif
            <div class="flex items-center gap-2 bg-warm-ivory border border-warm-border px-3.5 py-1.5 rounded-lg shadow-2xs">
                <span class="text-xs font-semibold text-slate-500">Skor Rata-Rata:</span>
                <span class="text-sm font-bold text-primary-ink">
                    {{ number_format($average, $is_iq ? 0 : 2) }} <span class="text-xs font-normal text-slate-400">/ {{ number_format($max_score, 0) }}</span>
                </span>
            </div>
        </div>
    </div>

    @if (!empty($iqScore))
        <!-- Executive IQ Spotlight Banner -->
        <div class="mb-8 p-5 bg-gradient-to-r from-warm-ivory via-white to-warm-ivory border border-warm-border rounded-xl flex flex-col sm:flex-row items-start sm:items-center justify-between gap-5 shadow-2xs">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-xl bg-accent-amber text-white flex flex-col items-center justify-center font-display shadow-sm shrink-0">
                    <span class="text-[10px] font-bold uppercase tracking-wider opacity-80 leading-none">IQ</span>
                    <span class="text-xl font-bold leading-tight">{{ $iqScore }}</span>
                </div>
                <div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <h3 class="font-display font-bold text-base text-primary-ink">
                            Kapasitas Inteligensi Umum
                        </h3>
                        @if (!empty($iqCategory))
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-accent-amber/20 text-accent-amber border border-accent-amber/30">
                                {{ $iqCategory }}
                            </span>
                        @endif
                    </div>
                    <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                        {{ $iqInterpretation ?? 'Kapasitas pemrosesan kognitif, ketajaman logika berpikir, daya analisa, dan kecepatan pemecahan masalah objektif.' }}
                    </p>
                </div>
            </div>

            @if (!empty($iqTestName))
                <div class="sm:text-right shrink-0 border-t sm:border-t-0 sm:border-l border-warm-border pt-3 sm:pt-0 sm:pl-5 w-full sm:w-auto">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Instrumen Tes</span>
                    <span class="text-xs font-semibold text-primary-ink block mt-0.5">{{ $iqTestName }}</span>
                </div>
            @endif
        </div>
    @endif

    <!-- Description Paragraph -->
    <p class="text-xs text-slate-500 leading-relaxed mb-8 border-b border-warm-border/10 pb-6">
        {{ $desc }}
    </p>

    <!-- Score Rows List -->
    <div class="space-y-8">
        @foreach ($scores as $score)
            @php
                $percentage = ($score['value'] / $max_score) * 100;
                $hasStandard = isset($score['standard']);
                $gap = $score['gap'] ?? null;
            @endphp
            <div class="flex flex-col md:flex-row md:items-center gap-4 md:gap-8">
                <!-- Label & Description -->
                <div class="w-full md:w-5/12 shrink-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <h3 class="text-sm font-bold text-primary-ink flex items-center gap-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-accent-amber"></span>
                            {{ $score['label'] }}
                        </h3>
                        @if (!empty($score['conclusion']))
                            @php
                                $concLower = strtolower($score['conclusion']);
                                $isAbove = str_contains($concLower, 'atas');
                                $isBelow = str_contains($concLower, 'bawah') || str_contains($concLower, 'kurang') || str_contains($concLower, 'perlu');
                            @endphp
                            @if ($isAbove)
                                <span class="text-[10px] uppercase font-bold tracking-wider px-2 py-0.5 rounded-md border bg-emerald-50 text-emerald-700 border-emerald-200">
                                    {{ $score['conclusion'] }}
                                </span>
                            @elseif ($isBelow)
                                <span class="text-[10px] uppercase font-bold tracking-wider px-2 py-0.5 rounded-md border bg-rose-50 text-rose-700 border-rose-200">
                                    {{ $score['conclusion'] }}
                                </span>
                            @else
                                <span class="text-[10px] uppercase font-bold tracking-wider px-2 py-0.5 rounded-md border bg-slate-100 text-slate-700 border-slate-200">
                                    {{ $score['conclusion'] }}
                                </span>
                            @endif
                        @endif
                    </div>
                    <p class="text-xs text-slate-500 mt-1 pl-3.5 leading-normal">
                        {{ $score['desc'] }}
                    </p>
                </div>

                <!-- Progress Track, Standard & Value -->
                <div class="flex-1 flex items-center gap-4">
                    <!-- Progress Bar Track Container with space for triangle handle -->
                    <div class="flex-1 relative py-1.5 flex items-center">
                        <!-- Progress Bar Track -->
                        <div class="w-full h-3 bg-warm-ivory border border-warm-border rounded-full overflow-hidden relative shadow-2xs">
                            <!-- Actual Score Bar -->
                            <div 
                                class="h-full bg-accent-amber rounded-full transition-all duration-700 ease-out" 
                                style="width: {{ min(100, max(0, $percentage)) }}%"
                            ></div>
                        </div>

                        @if ($hasStandard)
                            @php
                                $stdPercent = ($score['standard'] / $max_score) * 100;
                            @endphp
                            <!-- Neutral Dark Standard Marker with Triangle Handle & White Outline -->
                            <div 
                                class="absolute top-0 bottom-0 pointer-events-none z-10 flex flex-col items-center -translate-x-1/2" 
                                style="left: {{ min(100, max(0, $stdPercent)) }}%" 
                                title="Standar: {{ number_format($score['standard'], $is_iq ? 0 : 2) }}"
                            >
                                <!-- Triangle Handle (▲) protruding above top of bar -->
                                <svg class="w-2.5 h-2 text-[#171412] -mt-1 drop-shadow-[0_0_1px_rgba(255,255,255,1)] shrink-0" viewBox="0 0 10 8" fill="currentColor">
                                    <path d="M5 8L0 0H10L5 8Z" stroke="#ffffff" stroke-width="0.75" />
                                </svg>
                                <!-- Solid Dark Vertical Line with 1px White Outline -->
                                <div class="w-0.5 flex-1 bg-[#171412] shadow-[0_0_0_1px_#ffffff]"></div>
                            </div>
                        @endif
                    </div>

                    <!-- Metrics Group -->
                    <div class="flex items-center gap-2.5 shrink-0">
                        @if ($hasStandard)
                            <span class="text-xs font-mono font-medium text-slate-600 bg-slate-100 border border-slate-200/80 px-2 py-0.5 rounded-md" title="Standar Formasi Jabatan">
                                Std: {{ number_format($score['standard'], $is_iq ? 0 : 2) }}
                            </span>
                        @endif

                        @if ($gap !== null)
                            @php
                                $gapVal = (float) $gap;
                            @endphp
                            @if ($gapVal > 0.001)
                                <span class="text-xs font-mono font-bold px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-700 border border-emerald-200" title="Gap terhadap Standar (Di atas standar)">
                                    +{{ number_format($gapVal, $is_iq ? 0 : 2) }}
                                </span>
                            @elseif ($gapVal < -0.001)
                                <span class="text-xs font-mono font-bold px-2 py-0.5 rounded-md bg-rose-50 text-rose-700 border border-rose-200" title="Gap terhadap Standar (Di bawah standar)">
                                    {{ number_format($gapVal, $is_iq ? 0 : 2) }}
                                </span>
                            @else
                                <span class="text-xs font-mono font-bold px-2 py-0.5 rounded-md bg-slate-100 text-slate-600 border border-slate-200" title="Gap terhadap Standar (Sesuai standar)">
                                    0.00
                                </span>
                            @endif
                        @endif

                        <!-- Numeric Value -->
                        <div class="w-12 text-right font-mono font-bold text-xs text-primary-ink">
                            {{ number_format($score['value'], $is_iq ? 0 : 2) }}
                        </div>
                    </div>
                </div>
            </div>
            @if (!$loop->last)
                <hr class="border-t border-warm-border/50">
            @endif
        @endforeach
    </div>

    <!-- Bottom Card Legend -->
    <div class="mt-10 pt-6 border-t border-warm-border flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 text-xs text-slate-500">
        <div class="flex items-center gap-2">
            <i class="fas fa-info-circle text-accent-amber text-xs"></i>
            <span>Skala penilaian 1.00 – {{ number_format($max_score, $is_iq ? 0 : 2) }} terstandarisasi.</span>
        </div>
        <div class="flex items-center gap-6">
            <!-- Legend Item 1: Skor Aktual -->
            <div class="flex items-center gap-2">
                <span class="w-3.5 h-3 rounded-xs bg-accent-amber border border-amber-700/30 shrink-0"></span>
                <span class="text-slate-600 font-medium">Skor aktual</span>
            </div>
            <!-- Legend Item 2: Standar -->
            <div class="flex items-center gap-2">
                <div class="flex flex-col items-center justify-center h-4 w-2.5 shrink-0 relative">
                    <svg class="w-2 h-1.5 text-[#171412]" viewBox="0 0 10 8" fill="currentColor">
                        <path d="M5 8L0 0H10L5 8Z" />
                    </svg>
                    <span class="w-0.5 h-2.5 bg-[#171412]"></span>
                </div>
                <span class="text-slate-600 font-medium">Standar</span>
            </div>
        </div>
    </div>
</div>
