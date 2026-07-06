<div class="w-full max-w-4xl mx-auto bg-white border border-slate-200 shadow-xl rounded-2xl p-8 md:p-12 print:border-none print:shadow-none">
    
    <!-- Section Header -->
    <div class="border-b border-slate-100 pb-6 mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <span class="text-[10px] font-bold uppercase tracking-widest text-slate-400 block mb-1">Highlight Karakter & Kelebihan Utama</span>
            <h2 class="font-display text-2xl md:text-3xl text-slate-charcoal font-semibold">
                Kekuatan <span class="text-forest-green italic">Psikologis</span>
            </h2>
        </div>
        <!-- Indicator -->
        <div class="flex items-center gap-2">
            <span class="text-xs font-semibold text-slate-500">Kategori Profil:</span>
            <span class="text-xs font-bold text-forest-green bg-emerald-50 border border-emerald-100 px-3 py-1 rounded-md uppercase tracking-wider">
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
            <div class="bg-white border border-slate-200 rounded-xl p-6 shadow-sm border-l-4 border-l-forest-green hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 flex flex-col justify-between">
                <div>
                    <!-- Card Top Header -->
                    <div class="flex items-center justify-between gap-4 mb-3">
                        <div class="w-8 h-8 rounded-lg bg-emerald-50 text-forest-green border border-emerald-100 flex items-center justify-center shrink-0">
                            <i class="fas {{ $strength['icon'] }} text-xs"></i>
                        </div>
                        <span class="text-[9px] font-bold uppercase tracking-wider text-slate-400 bg-slate-100 px-2 py-0.5 rounded border border-slate-200">
                            {{ $strength['tag'] }}
                        </span>
                    </div>

                    <!-- Title -->
                    <h3 class="font-display font-semibold text-slate-charcoal text-sm mb-2 leading-snug">
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
    <div class="mt-8 bg-slate-50 border border-slate-100 rounded-xl p-6 text-xs text-slate-500 leading-relaxed flex items-start gap-3">
        <i class="fas fa-user-shield text-forest-green text-sm mt-0.5"></i>
        <span>
            <strong>Pernyataan Kepatuhan Etis:</strong> Profil kekuatan ini didasarkan pada asesmen psikologi formal dan terstandarisasi. Hasil interpretasi ditujukan untuk penggunaan pengembangan talenta internal secara objektif dan rahasia.
        </span>
    </div>

</div>
