<div class="w-full max-w-4xl mx-auto bg-white border border-warm-border rounded-xl p-8 md:p-12 print:border-none">
    
    <!-- Section Header -->
    <div class="border-b border-warm-border pb-6 mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <span class="text-[11px] font-bold uppercase tracking-widest text-slate-400 block mb-1">Highlight Karakter & Kelebihan Utama</span>
            <h2 class="font-display text-2xl md:text-3xl text-primary-ink font-semibold">
                Kekuatan <span class="text-accent-amber italic">Psikologis</span>
            </h2>
        </div>
        <!-- Indicator -->
        <div class="flex items-center gap-2">
            <span class="text-xs font-semibold text-slate-500">Kategori Profil:</span>
            <span class="text-xs font-bold text-accent-amber bg-accent-amber/10 border border-accent-amber/20 px-3 py-1 rounded-md uppercase tracking-wider">
                Leadership Ready
            </span>
        </div>
    </div>

    <!-- Intro Text -->
    <p class="text-xs text-slate-500 leading-relaxed mb-8">
        Berikut adalah rangkuman area kekuatan psikologis yang paling menonjol dari Budi Santoso berdasarkan hasil integrasi tes kognitif, inventory kepribadian (Big Five & DISC), serta wawancara kompetensi. Karakteristik ini menjadi modal dasar utama dalam mengemban tanggung jawab baru yang lebih tinggi.
    </p>

    <!-- Cards Grid (2 columns on large screen, 1 column on mobile) -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @foreach ($strengths as $strength)
            <div class="bg-white border border-warm-border rounded-xl p-6 hover:border-accent-amber/30 hover:scale-[1.01] transition-all duration-200 flex flex-col justify-between shadow-sm">
                <div>
                    <!-- Card Top Header -->
                    <div class="flex items-center justify-between gap-4 mb-3">
                        <div class="w-8 h-8 rounded-lg bg-accent-amber/10 text-accent-amber border border-accent-amber/20 flex items-center justify-center shrink-0">
                            <i class="fas {{ $strength['icon'] }} text-xs"></i>
                        </div>
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500 bg-warm-ivory px-2 py-0.5 rounded border border-warm-border">
                            {{ $strength['tag'] }}
                        </span>
                    </div>

                    <!-- Title -->
                    <h3 class="font-display font-semibold text-primary-ink text-sm mb-2 leading-snug">
                        {{ $strength['title'] }}
                    </h3>

                    <!-- Description -->
                    <p class="text-xs text-slate-600 leading-relaxed">
                        {{ $strength['desc'] }}
                    </p>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Section Bottom Info (Ethics and validation) -->
    <div class="mt-8 bg-warm-ivory border border-warm-border rounded-xl p-6 text-xs text-slate-500 leading-relaxed flex items-start gap-3">
        <i class="fas fa-user-shield text-accent-amber text-sm mt-0.5"></i>
        <span>
            <strong>Pernyataan Kepatuhan Etis:</strong> Profil kekuatan ini didasarkan pada asesmen psikologi formal dan terstandarisasi. Hasil interpretasi ditujukan untuk penggunaan pengembangan talenta internal secara objektif dan rahasia.
        </span>
    </div>

</div>
