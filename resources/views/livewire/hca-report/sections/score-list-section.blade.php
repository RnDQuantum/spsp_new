<div class="w-full max-w-5xl mx-auto bg-white border border-warm-border rounded-xl p-8 md:p-12 print:border-none">
    
    <!-- Section Header -->
    <div class="border-b border-warm-border pb-6 mb-10 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <span class="text-[11px] font-bold uppercase tracking-widest text-slate-400 block mb-1">
                {{ $subtitle }}
            </span>
            <h2 class="font-display text-2xl md:text-3xl text-primary-ink font-semibold">
                {{ explode(':', $title)[0] }} <span class="text-accent-amber italic">{{ count(explode(':', $title)) > 1 ? trim(explode(':', $title)[1]) : '' }}</span>
            </h2>
        </div>
        <!-- Average Index Callout -->
        <div class="flex items-center gap-3">
            <span class="text-xs font-semibold text-slate-500">Skor Rata-Rata:</span>
            <span class="text-sm font-bold text-primary-ink bg-warm-ivory border border-warm-border px-3.5 py-1.5 rounded-md">
                {{ number_format($average, $is_iq ? 0 : 2) }} <span class="text-xs font-normal text-slate-400">/ {{ number_format($max_score, 0) }}</span>
            </span>
        </div>
    </div>

    <!-- Description Paragraph -->
    <p class="text-xs text-slate-500 leading-relaxed mb-8 border-b border-warm-border/10 pb-6">
        {{ $desc }}
    </p>

    <!-- Score Rows List -->
    <div class="space-y-8">
        @foreach ($scores as $score)
            @php
                $percentage = ($score['value'] / $max_score) * 100;
            @endphp
            <div class="flex flex-col md:flex-row md:items-center gap-4 md:gap-8">
                <!-- Label & Description -->
                <div class="w-full md:w-1/3 shrink-0">
                    <h3 class="text-xs font-bold text-primary-ink flex items-center gap-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-accent-amber"></span>
                        {{ $score['label'] }}
                    </h3>
                    <p class="text-xs text-slate-500 mt-1 pl-3.5 leading-normal">
                        {{ $score['desc'] }}
                    </p>
                </div>

                <!-- Progress Track and Value -->
                <div class="flex-1 flex items-center gap-4">
                    <!-- Progress Bar Track -->
                    <div class="flex-1 h-3 bg-warm-ivory border border-warm-border rounded-full overflow-hidden">
                        <div 
                            class="h-full bg-accent-amber rounded-full transition-all duration-1000 ease-out" 
                            style="width: {{ $percentage }}%"
                        ></div>
                    </div>

                    <!-- Numeric Value -->
                    <div class="w-16 text-right font-mono font-bold text-xs text-primary-ink">
                        {{ number_format($score['value'], $is_iq ? 0 : 2) }}
                    </div>
                </div>
            </div>
            @if (!$loop->last)
                <hr class="border-t border-warm-border/50">
            @endif
        @endforeach
    </div>
</div>
