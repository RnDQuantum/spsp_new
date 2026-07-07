<div class="w-full max-w-5xl mx-auto bg-white border border-warm-border rounded-xl p-8 md:p-12 print:border-none">
    
    <!-- Section Header -->
    <div class="border-b border-warm-border pb-6 mb-10 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <span class="text-[11px] font-bold uppercase tracking-widest text-slate-400 block mb-1">Talent Management Mapping</span>
            <h2 class="font-display text-2xl md:text-3xl text-primary-ink font-semibold">
                Talent <span class="text-accent-amber italic">9-Box Matrix</span>
            </h2>
        </div>
        <!-- Talent Quadrant Callout -->
        <div class="flex items-center gap-2">
            <span class="text-xs font-semibold text-slate-500">Talent Klasifikasi:</span>
            <span class="text-xs font-bold text-white bg-accent-amber px-3 py-1 rounded-md uppercase tracking-wider">
                Star Talent (Box 9)
            </span>
        </div>
    </div>

    <!-- Description Paragraph -->
    <p class="text-xs text-slate-500 leading-relaxed mb-8">
        Matriks 9-Box memetakan karyawan berdasarkan persilangan antara tingkat potensi pertumbuhan (sumbu vertikal) dan kinerja aktual (sumbu horizontal) sebagai dasar keputusan promosi dan suksesi jabatan.
    </p>

    <!-- Matrix Layout Grid -->
    <div class="grid grid-cols-12 gap-4 items-stretch mb-8">
        
        <!-- Y-Axis Potential Label (1 col) -->
        <div class="col-span-1 flex flex-col justify-around items-center text-center font-bold text-[10px] text-slate-400 uppercase tracking-widest py-8 border-r border-warm-border/30">
            <span class="rotate-[-90] origin-center translate-y-1 block shrink-0 whitespace-nowrap">Tinggi</span>
            <span class="rotate-[-90] origin-center block shrink-0 whitespace-nowrap">Sedang</span>
            <span class="rotate-[-90] origin-center -translate-y-1 block shrink-0 whitespace-nowrap">Rendah</span>
        </div>

        <!-- 9-Cells Grid (11 cols) -->
        <div class="col-span-11 grid grid-cols-3 gap-3">
            @php
                // We order rows from high potential (3) to low potential (1)
                $rows = [3, 2, 1];
                // We order columns from low performance (1) to high performance (3)
                $cols = [1, 2, 3];
            @endphp
            
            @foreach ($rows as $rowVal)
                @foreach ($cols as $colVal)
                    @php
                        // Find matching cell
                        $cell = collect($grid)->first(fn($item) => $item[0] === $rowVal && $item[1] === $colVal);
                        $isActive = ($activePotential === $rowVal && $activePerformance === $colVal);
                    @endphp
                    
                    @if ($isActive)
                        <!-- Active Talent Cell -->
                        <div class="bg-accent-amber border-2 border-accent-amber rounded-xl p-4 flex flex-col justify-between min-h-[100px] shadow-sm text-white transition-all duration-300 hover:scale-[1.02]">
                            <span class="text-[10px] font-bold tracking-wider opacity-85">BOX {{ ($rowVal-1)*3 + $colVal }}</span>
                            <div class="my-auto">
                                <h4 class="font-display font-extrabold text-sm leading-tight">{{ $cell[2] }}</h4>
                                <p class="text-[10px] opacity-90 mt-1 leading-normal hidden sm:block">{{ $cell[3] }}</p>
                            </div>
                            <span class="text-[9px] font-bold uppercase tracking-wider bg-white/20 px-1.5 py-0.5 rounded self-start">Kandidat</span>
                        </div>
                    @else
                        <!-- Inactive Cell -->
                        <div class="bg-white border border-warm-border rounded-xl p-4 flex flex-col justify-between min-h-[100px] transition-all duration-300 hover:border-slate-300">
                            <span class="text-[10px] font-bold text-slate-300">BOX {{ ($rowVal-1)*3 + $colVal }}</span>
                            <div class="my-auto">
                                <h4 class="font-display font-bold text-primary-ink text-xs leading-tight opacity-75">{{ $cell[2] }}</h4>
                                <p class="text-[9px] text-slate-400 mt-0.5 leading-normal hidden sm:block">{{ $cell[3] }}</p>
                            </div>
                            <div class="h-2"></div>
                        </div>
                    @endif
                @endforeach
            @endforeach
        </div>

        <!-- Spacer for Label Alignment -->
        <div class="col-span-1"></div>
        
        <!-- X-Axis Performance Label (11 cols) -->
        <div class="col-span-11 grid grid-cols-3 gap-3 text-center font-bold text-[10px] text-slate-400 uppercase tracking-widest pt-3 border-t border-warm-border/30">
            <span>Rendah</span>
            <span>Sedang</span>
            <span>Tinggi</span>
        </div>
    </div>

    <!-- Section Bottom Info -->
    <div class="bg-warm-ivory border border-warm-border rounded-xl p-6 text-xs text-slate-500 leading-relaxed flex items-start gap-3">
        <i class="fas fa-circle-info text-accent-amber text-sm mt-0.5"></i>
        <span>
            <strong>Talent Placement (Star Talent):</strong> Kandidat berada di kuadran berkinerja tinggi dengan kapasitas kepemimpinan potensial teratas. Direkomendasikan untuk promosi kepemimpinan akselerasi (*fast track*) dan pengembangan program suksesi kritis.
        </span>
    </div>
</div>
