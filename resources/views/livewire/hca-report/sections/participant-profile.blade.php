<div class="w-full max-w-5xl mx-auto bg-white border border-warm-border rounded-xl p-8 md:p-12 print:border-none">
    
    <!-- Section Header -->
    <div class="border-b border-warm-border pb-6 mb-10 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <span class="text-[11px] font-bold uppercase tracking-widest text-slate-400 block mb-1">Data Administratif & Faktual</span>
            <h2 class="font-display text-2xl md:text-3xl text-primary-ink font-semibold">
                Identitas <span class="text-accent-amber italic">Peserta</span>
            </h2>
        </div>
        <!-- Document Code Callout -->
        <div class="flex items-center gap-2">
            <span class="text-xs font-semibold text-slate-500">Kode Laporan:</span>
            <span class="text-xs font-mono font-bold text-primary-ink bg-warm-ivory border border-warm-border px-3 py-1 rounded-md">
                HCA-EMP-2026-04
            </span>
        </div>
    </div>

    <!-- Main Content Layout (Split Column: Photo + Biodata) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        
        <!-- Left: Photo Column (3 cols) -->
        <div class="lg:col-span-3 flex flex-col items-center">
            <div class="w-40 h-40 rounded-xl bg-warm-ivory border border-warm-border p-2 shadow-sm shrink-0 flex items-center justify-center overflow-hidden">
                <!-- Initial Avatar Placeholder -->
                <div class="w-full h-full bg-[#171412] text-white flex items-center justify-center font-display font-bold text-4xl rounded-lg">
                    BS
                </div>
            </div>
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mt-4">Kandidat Asesmen</span>
            <span class="text-xs font-semibold text-primary-ink mt-1">Budi Santoso, M.B.A.</span>
        </div>

        <!-- Right: Biodata Key-Value Grid (9 cols) -->
        <div class="lg:col-span-9">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-4 border-t border-b border-warm-border py-6">
                @foreach ($biodata as $item)
                    <div class="py-2 border-b border-warm-border/30 last:border-0 sm:even:border-b-0">
                        <span class="text-[11px] font-bold uppercase tracking-wide text-slate-400 block mb-0.5">
                            {{ $item['label'] }}
                        </span>
                        <span class="text-xs font-semibold text-primary-ink leading-relaxed">
                            {{ $item['value'] }}
                        </span>
                    </div>
                @endforeach
            </div>
            
            <p class="text-[11px] text-slate-400 mt-6 leading-relaxed flex items-start gap-2">
                <i class="fas fa-lock text-slate-300 mt-0.5"></i>
                <span>
                    Seluruh informasi di atas diverifikasi langsung oleh Divisi Human Capital SPSP dan dilindungi di bawah kepatuhan kerahasiaan data karyawan tingkat tinggi.
                </span>
            </p>
        </div>

    </div>
</div>
