<div class="w-full max-w-5xl mx-auto bg-white border border-warm-border rounded-2xl p-8 md:p-12 print:border-none print:p-0 shadow-sm">
    
    <!-- Section Header -->
    <div class="border-b border-warm-border pb-6 mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <span class="text-[11px] font-bold uppercase tracking-widest text-slate-400 block mb-1">
                Data Administratif, Kepegawaian & Parameter Asesmen
            </span>
            <h2 class="font-display text-2xl md:text-3xl text-primary-ink font-semibold">
                Identitas <span class="text-accent-amber italic">Peserta</span>
            </h2>
        </div>
        <!-- Document Code Callout -->
        <div class="flex items-center gap-2">
            <span class="text-xs font-semibold text-slate-500">Kode Dokumen:</span>
            <span class="text-xs font-mono font-bold text-primary-ink bg-warm-ivory border border-warm-border px-3 py-1.5 rounded-lg shadow-2xs">
                HCA-{{ $participant?->test_number ?? 'EMP' }}-{{ date('Y') }}
            </span>
        </div>
    </div>

    @if ($participant)
        <!-- Top Profile Summary Card -->
        <div class="bg-warm-ivory/60 border border-warm-border rounded-xl p-6 mb-8 flex flex-col md:flex-row items-center md:items-start gap-6">
            <!-- Left: Avatar / Photo -->
            <div class="w-32 h-32 rounded-xl bg-white border-2 border-accent-amber/40 p-1.5 shadow-sm shrink-0 flex items-center justify-center overflow-hidden">
                @if ($participant->photo_path && file_exists(public_path('storage/' . $participant->photo_path)))
                    <img 
                        src="{{ asset('storage/' . $participant->photo_path) }}" 
                        alt="{{ $participant->name }}" 
                        class="w-full h-full object-cover rounded-lg"
                    />
                @else
                    <div class="w-full h-full bg-[#171412] text-white flex items-center justify-center font-display font-bold text-3xl rounded-lg">
                        {{ $initials }}
                    </div>
                @endif
            </div>

            <!-- Right: Identity Overview -->
            <div class="flex-1 text-center md:text-left">
                <div class="flex flex-wrap items-center justify-center md:justify-start gap-2 mb-2">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-accent-amber/15 text-accent-amber border border-accent-amber/30">
                        <i class="fas fa-check-circle text-[10px] mr-1.5"></i>
                        Kandidat Terverifikasi
                    </span>
                    @if ($participant->status_kepegawaian)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-slate-200/80 text-slate-700">
                            {{ $participant->status_kepegawaian }}
                        </span>
                    @endif
                    @if ($participant->golongan)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-100 text-emerald-800 border border-emerald-200">
                            Gol. {{ $participant->golongan }}
                        </span>
                    @endif
                </div>

                <h3 class="font-display text-xl md:text-2xl text-primary-ink font-bold leading-snug">
                    {{ $formattedName }}
                </h3>

                <p class="text-xs text-slate-500 mt-1 font-medium flex flex-wrap items-center justify-center md:justify-start gap-x-3 gap-y-1">
                    <span><i class="fas fa-bullseye text-accent-amber mr-1"></i> {{ $participant->positionFormation?->name ?? 'Formasi Belum Ditentukan' }}</span>
                    <span>&bull;</span>
                    <span><i class="fas fa-building text-slate-400 mr-1"></i> {{ $participant->unit_kerja ?? ($participant->institution?->name ?? 'Instansi Klien') }}</span>
                </p>

                <!-- Quick Meta Pills -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 mt-4 pt-4 border-t border-warm-border/60 text-left">
                    <div class="bg-white px-3 py-1.5 rounded-lg border border-warm-border/60">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">No. Tes</span>
                        <span class="text-xs font-mono font-bold text-primary-ink truncate block">{{ $participant->test_number ?: '-' }}</span>
                    </div>
                    <div class="bg-white px-3 py-1.5 rounded-lg border border-warm-border/60">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">NIK</span>
                        <span class="text-xs font-mono font-bold text-primary-ink truncate block">{{ $participant->nik ?: '-' }}</span>
                    </div>
                    <div class="bg-white px-3 py-1.5 rounded-lg border border-warm-border/60">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Pendidikan</span>
                        <span class="text-xs font-semibold text-primary-ink truncate block">{{ $participant->pendidikan ?: '-' }}</span>
                    </div>
                    <div class="bg-white px-3 py-1.5 rounded-lg border border-warm-border/60">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Pengalaman</span>
                        <span class="text-xs font-semibold text-primary-ink truncate block">{{ $participant->pengalaman_kerja ?: '-' }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3 Structured Detail Cards -->
        <div class="space-y-6">

            <!-- Card 1: Informasi Pribadi & Kontak -->
            <div class="border border-warm-border rounded-xl p-6 bg-white shadow-2xs">
                <div class="flex items-center gap-2 pb-3 mb-4 border-b border-warm-border">
                    <div class="w-7 h-7 rounded-lg bg-amber-50 text-accent-amber flex items-center justify-center text-xs">
                        <i class="fas fa-id-card"></i>
                    </div>
                    <h4 class="font-display font-semibold text-sm text-primary-ink">
                        1. Informasi Pribadi & Kependudukan
                    </h4>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-x-6 gap-y-4">
                    @foreach ($personalBiodata as $item)
                        <div class="py-1">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 flex items-center gap-1.5 mb-0.5">
                                <i class="fas {{ $item['icon'] }} text-slate-400 text-[10px]"></i>
                                {{ $item['label'] }}
                            </span>
                            <span class="text-xs font-semibold text-primary-ink leading-relaxed block break-words">
                                {{ $item['value'] }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Card 2: Profil Kepegawaian & Posisi Saat Ini -->
            <div class="border border-warm-border rounded-xl p-6 bg-white shadow-2xs">
                <div class="flex items-center gap-2 pb-3 mb-4 border-b border-warm-border">
                    <div class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-700 flex items-center justify-center text-xs">
                        <i class="fas fa-briefcase"></i>
                    </div>
                    <h4 class="font-display font-semibold text-sm text-primary-ink">
                        2. Profil Kepegawaian & Posisi Saat Ini
                    </h4>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-x-6 gap-y-4">
                    @foreach ($employmentBiodata as $item)
                        <div class="py-1">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 flex items-center gap-1.5 mb-0.5">
                                <i class="fas {{ $item['icon'] }} text-slate-400 text-[10px]"></i>
                                {{ $item['label'] }}
                            </span>
                            <span class="text-xs font-semibold text-primary-ink leading-relaxed block break-words">
                                {{ $item['value'] }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Card 3: Konteks Asesmen & Formasi Target -->
            <div class="border border-warm-border rounded-xl p-6 bg-white shadow-2xs">
                <div class="flex items-center gap-2 pb-3 mb-4 border-b border-warm-border">
                    <div class="w-7 h-7 rounded-lg bg-blue-50 text-blue-700 flex items-center justify-center text-xs">
                        <i class="fas fa-bullseye"></i>
                    </div>
                    <h4 class="font-display font-semibold text-sm text-primary-ink">
                        3. Konteks Asesmen & Formasi Target
                    </h4>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-x-6 gap-y-4">
                    @foreach ($assessmentBiodata as $item)
                        <div class="py-1">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 flex items-center gap-1.5 mb-0.5">
                                <i class="fas {{ $item['icon'] }} text-slate-400 text-[10px]"></i>
                                {{ $item['label'] }}
                            </span>
                            <span class="text-xs font-semibold text-primary-ink leading-relaxed block break-words">
                                {{ $item['value'] }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>

        </div>

        <!-- Privacy & Verification Footer -->
        <div class="mt-8 pt-4 border-t border-warm-border flex items-center gap-3 text-slate-400 text-[11px]">
            <i class="fas fa-shield-halved text-slate-400 text-sm shrink-0"></i>
            <p class="leading-relaxed">
                Seluruh data administratif dan riwayat kepegawaian di atas diperoleh dari sinkronisasi resmi basis data SPSP serta dokumen pendaftaran asesmen, dan dilindungi di bawah prinsip kerahasiaan evaluasi SDM.
            </p>
        </div>
    @else
        <div class="p-12 text-center text-slate-500 font-medium text-sm">
            <i class="fas fa-user-slash text-2xl text-slate-300 mb-2 block"></i>
            Data peserta tidak ditemukan atau belum dipilih.
        </div>
    @endif
</div>
