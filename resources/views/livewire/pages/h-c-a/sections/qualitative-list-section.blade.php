<div class="w-full max-w-5xl mx-auto bg-white border border-warm-border rounded-xl p-8 md:p-12 print:border-none">
    
    <!-- Section Header -->
    <div class="border-b border-warm-border pb-6 mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <span class="text-[11px] font-bold uppercase tracking-widest text-slate-400 block mb-1">{{ $subtitle }}</span>
            <h2 class="font-display text-2xl md:text-3xl text-primary-ink font-semibold">
                {{ explode(' ', $title)[0] }} <span class="text-accent-amber italic">{{ count(explode(' ', $title)) > 1 ? implode(' ', array_slice(explode(' ', $title), 1)) : '' }}</span>
            </h2>
        </div>
        <!-- Indicator -->
        <div class="flex items-center gap-2">
            <span class="text-xs font-semibold text-slate-500">Kategori Profil:</span>
            <span class="text-[11px] font-bold text-accent-amber bg-accent-amber/10 border border-accent-amber/20 px-3 py-1 rounded-md uppercase tracking-wider">
                {{ $is_personal ? 'Pelengkap' : 'Asesmen' }}
            </span>
        </div>
    </div>

    <!-- Intro Text -->
    <p class="text-xs text-slate-500 leading-relaxed mb-8">
        {{ $desc }}
    </p>

    <!-- Cards Grid (2 columns on large screen, 1 column on mobile) -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @foreach ($items as $item)
            <div class="bg-white border border-warm-border rounded-xl p-6 hover:border-accent-amber/30 hover:scale-[1.01] transition-all duration-200 flex flex-col justify-between shadow-sm">
                <div>
                    <!-- Card Top Header -->
                    <div class="flex items-center justify-between gap-4 mb-3">
                        <div class="w-8 h-8 rounded-lg bg-accent-amber/10 text-accent-amber border border-accent-amber/20 flex items-center justify-center shrink-0">
                            <i class="fas {{ $item['icon'] }} text-xs"></i>
                        </div>
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500 bg-warm-ivory px-2 py-0.5 rounded border border-warm-border">
                            {{ $item['tag'] }}
                        </span>
                    </div>

                    <!-- Title -->
                    <h3 class="font-display font-semibold text-primary-ink text-sm mb-2 leading-snug">
                        {{ $item['title'] }}
                    </h3>

                    <!-- Description -->
                    <p class="text-xs text-slate-600 leading-relaxed">
                        {{ $item['desc'] }}
                    </p>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Section Bottom Info -->
    <div class="mt-8 bg-warm-ivory border border-warm-border rounded-xl p-6 text-xs text-slate-500 leading-relaxed flex items-start gap-3">
        @if ($is_personal)
            <i class="fas fa-circle-info text-accent-amber text-sm mt-0.5"></i>
            <span>
                <strong>Catatan Informasi:</strong> Informasi di atas dihimpun dari data pelengkap atau kuesioner pribadi kandidat. Data ini bersifat sekunder/informal dan ditujukan semata-mata sebagai konteks pelengkap kepribadian di luar penilaian inti asesmen profesional.
            </span>
        @else
            <i class="fas fa-user-shield text-accent-amber text-sm mt-0.5"></i>
            <span>
                <strong>Pernyataan Kepatuhan Etis:</strong> Profil kekuatan ini didasarkan pada asesmen psikologi formal dan terstandarisasi. Hasil interpretasi ditujukan untuk penggunaan pengembangan talenta internal secara objektif dan rahasia.
            </span>
        @endif
    </div>

</div>
