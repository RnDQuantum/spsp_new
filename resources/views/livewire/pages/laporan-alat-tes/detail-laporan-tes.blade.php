<div class="max-w-7xl mx-auto py-6 px-4 space-y-6">
    {{-- Header Navigation & Title --}}
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div class="flex items-center space-x-3">
            <a href="{{ route('laporan-alat-tes') }}" class="inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-lg
                  border border-gray-300 dark:border-gray-600
                  text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800
                  hover:bg-gray-50 dark:hover:bg-gray-700 transition shadow-sm">
                <i class="fa-solid fa-arrow-left mr-2 text-xs"></i>
                Kembali ke Daftar
            </a>
            <div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100">
                    Ringkasan Laporan Per Alat Tes Psikologis
                </h1>
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    Detail komponen hasil ujian instrumen psikometri peserta
                </p>
            </div>
        </div>
    </div>

    @if($participant)
        {{-- Card Biodata Peserta & Master Data --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 items-center">
                {{-- Foto Peserta --}}
                <div class="flex justify-center md:justify-start">
                    <div class="w-32 h-40 rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 flex items-center justify-center overflow-hidden shadow-inner">
                        @if($participant->photo_path)
                            <img src="{{ asset('storage/' . $participant->photo_path) }}" alt="{{ $participant->name }}" class="w-full h-full object-cover">
                        @else
                            <div class="text-center p-3">
                                <i class="fa-solid fa-user text-3xl text-gray-400 dark:text-gray-500 mb-1 block"></i>
                                <span class="text-[10px] text-gray-500 dark:text-gray-400">Foto Peserta</span>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Teks Biodata --}}
                <div class="md:col-span-3 space-y-3">
                    <div class="border-b border-gray-100 dark:border-gray-700 pb-3">
                        <h2 class="text-lg font-bold text-gray-900 dark:text-gray-100">
                            {{ $participant->name }}
                        </h2>
                        <div class="flex flex-wrap items-center gap-2 mt-1">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-mono font-semibold bg-blue-50 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300">
                                No. Tes: {{ $participant->test_number ?? '-' }}
                            </span>
                            @if($participant->skb_number && $participant->skb_number !== $participant->test_number)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-mono bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300">
                                    SKB: {{ $participant->skb_number }}
                                </span>
                            @endif
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium bg-purple-50 text-purple-700 dark:bg-purple-900/40 dark:text-purple-300">
                                {{ $participant->gender === 'P' ? 'Perempuan' : 'Laki-laki' }}
                            </span>
                        </div>
                    </div>

                    {{-- Informasi Instansi & Master Proyek --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 text-xs">
                        <div class="bg-gray-50 dark:bg-gray-700/50 p-2.5 rounded-lg border border-gray-100 dark:border-gray-700">
                            <span class="text-[10px] uppercase font-semibold text-gray-400 dark:text-gray-500 block">Klien / Instansi</span>
                            <span class="font-semibold text-gray-800 dark:text-gray-200">
                                {{ $participant->assessmentEvent?->institution?->name ?? 'Kejaksaan Agung RI' }}
                            </span>
                        </div>

                        <div class="bg-gray-50 dark:bg-gray-700/50 p-2.5 rounded-lg border border-gray-100 dark:border-gray-700">
                            <span class="text-[10px] uppercase font-semibold text-gray-400 dark:text-gray-500 block">Master Proyek</span>
                            <span class="font-semibold text-blue-600 dark:text-blue-400">
                                {{ $participant->assessmentEvent?->project?->code ?? '-' }}
                            </span>
                            <span class="text-gray-600 dark:text-gray-400 text-[11px] block truncate">
                                {{ $participant->assessmentEvent?->project?->name ?? '-' }}
                            </span>
                        </div>

                        <div class="bg-gray-50 dark:bg-gray-700/50 p-2.5 rounded-lg border border-gray-100 dark:border-gray-700">
                            <span class="text-[10px] uppercase font-semibold text-gray-400 dark:text-gray-500 block">Pelaksanaan (Event)</span>
                            <span class="font-semibold text-gray-800 dark:text-gray-200">
                                {{ $participant->assessmentEvent?->code ?? '-' }}
                            </span>
                            <span class="text-gray-600 dark:text-gray-400 text-[11px] block truncate">
                                {{ $participant->assessmentEvent?->name ?? '-' }}
                            </span>
                        </div>

                        <div class="bg-gray-50 dark:bg-gray-700/50 p-2.5 rounded-lg border border-gray-100 dark:border-gray-700">
                            <span class="text-[10px] uppercase font-semibold text-gray-400 dark:text-gray-500 block">Formasi Jabatan</span>
                            <span class="font-semibold text-gray-800 dark:text-gray-200">
                                {{ $participant->positionFormation?->name ?? '-' }}
                            </span>
                        </div>

                        <div class="bg-gray-50 dark:bg-gray-700/50 p-2.5 rounded-lg border border-gray-100 dark:border-gray-700">
                            <span class="text-[10px] uppercase font-semibold text-gray-400 dark:text-gray-500 block">Gelombang / Batch</span>
                            <span class="font-semibold text-gray-800 dark:text-gray-200">
                                {{ $participant->batch?->name ?? 'Gelombang 1' }}
                            </span>
                        </div>

                        <div class="bg-gray-50 dark:bg-gray-700/50 p-2.5 rounded-lg border border-gray-100 dark:border-gray-700">
                            <span class="text-[10px] uppercase font-semibold text-gray-400 dark:text-gray-500 block">Tanggal Asesmen</span>
                            <span class="font-semibold text-gray-800 dark:text-gray-200">
                                {{ $participant->assessment_date ? \Carbon\Carbon::parse($participant->assessment_date)->format('d M Y') : '-' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Daftar Hasil Komponen Per Alat Tes --}}
        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <h3 class="text-base font-bold text-gray-800 dark:text-gray-100">
                    Hasil Rinci per Instrumen Alat Tes ({{ count($testReports) }} Alat Tes)
                </h3>
            </div>

            @forelse($testReports as $code => $report)
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                    {{-- Header Card Alat Tes --}}
                    <div class="bg-gray-50 dark:bg-gray-700/60 px-6 py-3.5 border-b border-gray-100 dark:border-gray-700 flex flex-wrap items-center justify-between gap-2">
                        <div class="flex items-center space-x-3">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold font-mono bg-blue-600 text-white shadow-sm">
                                {{ $code }}
                            </span>
                            <div>
                                <h4 class="text-sm font-bold text-gray-800 dark:text-gray-100">
                                    {{ $report['test_name'] }}
                                </h4>
                                <span class="text-[11px] text-gray-500 dark:text-gray-400">
                                    Kategori: {{ $report['test_category'] }}
                                </span>
                            </div>
                        </div>

                        <div class="flex items-center space-x-2 text-xs">
                            <span class="px-2 py-0.5 rounded text-[10px] font-semibold uppercase bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-300">
                                Sumber: {{ $report['source'] ?? 'api' }}
                            </span>
                            <span class="px-2 py-0.5 rounded text-[10px] font-semibold uppercase bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300">
                                {{ $report['status'] }}
                            </span>
                        </div>
                    </div>

                    {{-- Content Body Card --}}
                    <div class="p-6">
                        @php $fmt = $report['formatted'] ?? []; @endphp

                        @if(in_array($code, ['A.1', 'A.2', 'A.5']))
                            {{-- Tampilan IST / CFIT --}}
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div class="bg-blue-50/50 dark:bg-blue-900/20 p-4 rounded-xl border border-blue-100 dark:border-blue-800 text-center flex flex-col justify-center">
                                    <span class="text-xs uppercase font-semibold text-blue-600 dark:text-blue-400">Skor Total IQ</span>
                                    <span class="text-4xl font-extrabold text-blue-700 dark:text-blue-300 my-1">
                                        {{ $fmt['iq'] ?? 100 }}
                                    </span>
                                    <span class="text-xs font-medium text-blue-800 dark:text-blue-200">
                                        Kategori: {{ $fmt['kategori'] ?? 'Rata-rata' }}
                                    </span>
                                </div>

                                <div class="md:col-span-2">
                                    <h5 class="text-xs font-bold text-gray-700 dark:text-gray-300 uppercase mb-2">Rincian Standard Score (SS) Subtest</h5>
                                    <div class="grid grid-cols-3 sm:grid-cols-3 md:grid-cols-5 gap-2 text-xs">
                                        @foreach($fmt['subtests'] ?? [] as $subName => $subVal)
                                            <div class="bg-gray-50 dark:bg-gray-700/50 p-2.5 rounded-lg border border-gray-100 dark:border-gray-700 text-center">
                                                <span class="font-bold text-gray-500 dark:text-gray-400 text-[10px] block uppercase">{{ $subName }}</span>
                                                <span class="text-sm font-bold text-gray-800 dark:text-gray-100">
                                                    {{ is_array($subVal) ? ($subVal['nilai'] ?? json_encode($subVal)) : $subVal }}
                                                </span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                        @elseif(in_array($code, ['B.1', 'D.1']))
                            {{-- Tampilan PAPI Kostik / Kompetensi Karakter --}}
                            <div class="space-y-4">
                                <h5 class="text-xs font-bold text-gray-700 dark:text-gray-300 uppercase">Faktor Kepribadian Kerja</h5>
                                <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-5 gap-2 text-xs">
                                    @foreach($fmt['factors'] ?? [] as $fKey => $fVal)
                                        @php $fName = str_replace('hasil_', '', $fKey); @endphp
                                        <div class="bg-gray-50 dark:bg-gray-700/50 p-2.5 rounded-lg border border-gray-100 dark:border-gray-700 text-center">
                                            <span class="font-bold text-gray-400 text-[10px] block uppercase">{{ $fmt['labels'][$fKey] ?? $fName }} ({{ $fName }})</span>
                                            <span class="text-base font-extrabold text-blue-600 dark:text-blue-400">{{ $fVal }}</span>
                                        </div>
                                    @endforeach
                                </div>

                                @if(!empty($fmt['narratives']))
                                    <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-700">
                                        <h5 class="text-xs font-bold text-gray-700 dark:text-gray-300 uppercase mb-2">Dinamika Perilaku & Karakter Kerja</h5>
                                        <div class="space-y-2 text-xs text-gray-600 dark:text-gray-300 bg-gray-50 dark:bg-gray-700/40 p-4 rounded-xl">
                                            @foreach($fmt['narratives'] as $nKey => $nText)
                                                <div class="flex items-start space-x-2">
                                                    <i class="fa-solid fa-circle-check text-blue-500 mt-0.5 text-[10px]"></i>
                                                    <span>{{ $nText }}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>

                        @elseif($code === 'B.2')
                            {{-- Tampilan 16PF --}}
                            <div class="space-y-4">
                                <div class="flex items-center justify-between">
                                    <h5 class="text-xs font-bold text-gray-700 dark:text-gray-300 uppercase">Sten Scores 16 Personality Factors</h5>
                                    <span class="text-xs font-semibold px-2.5 py-1 bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300 rounded-md">
                                        MD Score: {{ $fmt['md_score'] ?? 5 }}
                                    </span>
                                </div>
                                <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-8 gap-2 text-xs">
                                    @foreach($fmt['sten_scores'] ?? [] as $stKey => $stVal)
                                        <div class="bg-gray-50 dark:bg-gray-700/50 p-2.5 rounded-lg border border-gray-100 dark:border-gray-700 text-center">
                                            <span class="font-bold text-gray-400 text-[10px] block uppercase">Faktor {{ $stKey }}</span>
                                            <span class="text-base font-extrabold text-purple-600 dark:text-purple-400">{{ $stVal }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                        @elseif($code === 'D.2')
                            {{-- Tampilan Kraepelin --}}
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-center">
                                <div class="bg-gray-50 dark:bg-gray-700/50 p-4 rounded-xl border border-gray-100 dark:border-gray-700">
                                    <span class="text-xs font-semibold text-gray-400 block uppercase">Kecepatan (Pspeed)</span>
                                    <span class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ $fmt['pspeed'] }}</span>
                                </div>
                                <div class="bg-gray-50 dark:bg-gray-700/50 p-4 rounded-xl border border-gray-100 dark:border-gray-700">
                                    <span class="text-xs font-semibold text-gray-400 block uppercase">Ketelitian (Pacc)</span>
                                    <span class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ $fmt['pacc'] }}</span>
                                </div>
                                <div class="bg-gray-50 dark:bg-gray-700/50 p-4 rounded-xl border border-gray-100 dark:border-gray-700">
                                    <span class="text-xs font-semibold text-gray-400 block uppercase">Kestabilan (Pstab)</span>
                                    <span class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ $fmt['pstab'] }}</span>
                                </div>
                                <div class="bg-gray-50 dark:bg-gray-700/50 p-4 rounded-xl border border-gray-100 dark:border-gray-700">
                                    <span class="text-xs font-semibold text-gray-400 block uppercase">Ketahanan (Pstn)</span>
                                    <span class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ $fmt['pstn'] }}</span>
                                </div>
                            </div>

                        @else
                            {{-- Format Generic / Raw JSON --}}
                            <div class="bg-gray-50 dark:bg-gray-900 p-4 rounded-xl overflow-x-auto text-xs font-mono text-gray-700 dark:text-gray-300">
                                <pre>{{ json_encode($report['summary_data'] ?? $fmt, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-8 text-center text-gray-500 dark:text-gray-400">
                    <i class="fa-solid fa-clipboard-question text-3xl mb-2 text-gray-400 block"></i>
                    Belum ada data komponen alat tes tersimpan untuk peserta ini.
                </div>
            @endforelse
        </div>
    @else
        <div class="bg-white dark:bg-gray-800 rounded-xl p-8 text-center text-gray-500 dark:text-gray-400 border border-gray-100 dark:border-gray-700">
            Peserta tidak ditemukan.
        </div>
    @endif
</div>