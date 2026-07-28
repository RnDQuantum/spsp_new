<div class="min-h-screen bg-warm-ivory font-sans text-primary-ink leading-relaxed">
    @if ($printMode)
        <!-- PRINT FLAT VIEW -->
        <div class="print-container bg-white p-0">
            <!-- 01 Cover -->
            <div class="page-break">
                <livewire:pages.h-c-a.sections.cover :participant-id="$participantId" :key="'cover_print_'.$participantId" />
            </div>

            <!-- 02 Ringkasan Eksekutif -->
            <div class="page-break p-8">
                <livewire:pages.h-c-a.sections.executive-summary />
            </div>

            <!-- 03 Identitas Peserta -->
            <div class="page-break p-8">
                <livewire:pages.h-c-a.sections.participant-profile />
            </div>

            <!-- 04 HCI -->
            <div class="page-break p-8">
                <livewire:pages.h-c-a.sections.index-radar-section sectionCode="hci" />
            </div>

            <!-- 05 Kompetensi -->
            <div class="page-break p-8">
                <livewire:pages.h-c-a.sections.score-list-section sectionCode="competency" />
            </div>

            <!-- 06 Riwayat Karier -->
            <div class="page-break p-8">
                <livewire:pages.h-c-a.sections.timeline-section />
            </div>

            <!-- 07 Potensi -->
            <div class="page-break p-8">
                <livewire:pages.h-c-a.sections.index-radar-section sectionCode="potential" />
            </div>

            <!-- 08 IQ -->
            <div class="page-break p-8">
                <livewire:pages.h-c-a.sections.score-list-section sectionCode="cognitive" />
            </div>

            <!-- 09 Big Five -->
            <div class="page-break p-8">
                <livewire:pages.h-c-a.sections.score-list-section sectionCode="big_five" />
            </div>

            <!-- 10 DISC -->
            <div class="page-break p-8">
                <livewire:pages.h-c-a.sections.disc-profile />
            </div>

            <!-- 11 Learning Agility -->
            <div class="page-break p-8">
                <livewire:pages.h-c-a.sections.score-list-section sectionCode="learning_agility" />
            </div>

            <!-- 12 Leadership Potential -->
            <div class="page-break p-8">
                <livewire:pages.h-c-a.sections.score-list-section sectionCode="leadership_potential" />
            </div>

            <!-- 13 EQ -->
            <div class="page-break p-8">
                <livewire:pages.h-c-a.sections.index-radar-section sectionCode="eq" />
            </div>

            <!-- 14 Values & Integrity -->
            <div class="page-break p-8">
                <livewire:pages.h-c-a.sections.score-list-section sectionCode="integrity" />
            </div>

            <!-- 15 Performance Dashboard -->
            <div class="page-break p-8">
                <livewire:pages.h-c-a.sections.performance-dashboard />
            </div>

            <!-- 16 9-Box Matrix -->
            <div class="page-break p-8">
                <livewire:pages.h-c-a.sections.nine-box-matrix />
            </div>

            <!-- 17 Succession Readiness -->
            <div class="page-break p-8">
                <livewire:pages.h-c-a.sections.succession-readiness />
            </div>

            <!-- 18 Profil Personal -->
            <div class="page-break p-8">
                <livewire:pages.h-c-a.sections.qualitative-list-section sectionCode="personal_profile" />
            </div>

            <!-- 19 Kesehatan Jiwa -->
            <div class="page-break p-8">
                <livewire:pages.h-c-a.sections.mental-health-section />
            </div>

            <!-- 20 Kekuatan Psikologis -->
            <div class="page-break p-8">
                <livewire:pages.h-c-a.sections.qualitative-list-section sectionCode="strengths" />
            </div>

            <!-- 21 Indikator Risiko -->
            <div class="page-break p-8">
                <livewire:pages.h-c-a.sections.risk-indicators />
            </div>

            <!-- 22 Rekomendasi Pengembangan -->
            <div class="page-break p-8">
                <livewire:pages.h-c-a.sections.development-recommendation />
            </div>

            <!-- 23 Rekomendasi Peran Berikutnya -->
            <div class="p-8">
                <livewire:pages.h-c-a.sections.next-role-recommendation />
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Auto trigger print when printMode is active
                setTimeout(() => {
                    window.print();
                }, 800);
            });

            // Restore normal view after printing
            window.onafterprint = function() {
                @this.togglePrintMode(false);
            };
        </script>
    @else
        <!-- WEB INTERACTIVE VIEW -->
        <div class="flex flex-col md:flex-row min-h-screen md:h-screen md:overflow-hidden">
            <!-- Left Sidebar (TOC) -->
            <aside class="w-full md:w-80 bg-primary-ink text-slate-200 flex flex-col border-r border-warm-border/10 shrink-0 md:h-full">
                <!-- Branded Header -->
                <div class="p-6 border-b border-warm-border/10 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-accent-amber flex items-center justify-center font-display font-bold text-white text-xl shadow-md">
                        HC
                    </div>
                    <div>
                        <h1 class="font-display font-semibold tracking-wide text-lg text-white">HCA Report</h1>
                        <p class="text-xs text-slate-400 font-medium uppercase tracking-widest">SPSP Assessment</p>
                    </div>
                </div>

                <!-- Participant Brief Profile -->
                @php
                    $currentTalent = $this->participant;
                    $talentInitials = $currentTalent ? $this->getInitials($currentTalent->name) : 'P';
                @endphp
                <div class="p-5 border-b border-warm-border/10 flex items-center justify-between gap-3 bg-[#241f1c]">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-10 h-10 rounded-full bg-slate-700 overflow-hidden border-2 border-accent-amber flex shrink-0 items-center justify-center font-display font-bold text-white text-xs">
                            @if ($currentTalent?->photo_path && file_exists(public_path('storage/' . $currentTalent->photo_path)))
                                <img src="{{ asset('storage/' . $currentTalent->photo_path) }}" alt="{{ $currentTalent->name }}" class="w-full h-full object-cover" />
                            @else
                                {{ $talentInitials }}
                            @endif
                        </div>
                        <div class="min-w-0 flex-1">
                            <h2 class="font-semibold text-white text-xs truncate" title="{{ $currentTalent?->name }}">
                                {{ $currentTalent?->name ?? 'Belum memilih peserta' }}
                            </h2>
                            <p class="text-[11px] text-slate-400 truncate" title="{{ $currentTalent?->positionFormation?->name }}">
                                {{ $currentTalent?->positionFormation?->name ?? '-' }}
                            </p>
                            <span class="inline-flex mt-1 items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-accent-amber/20 text-accent-amber border border-accent-amber/30">
                                Active Talent
                            </span>
                        </div>
                    </div>
                    <button 
                        wire:click="toggleTalentModal" 
                        class="p-2 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white transition-all text-xs shrink-0 flex items-center gap-1 border border-slate-700 cursor-pointer"
                        title="Ganti Peserta / Switch Talent"
                    >
                        <i class="fas fa-users-line text-accent-amber"></i>
                        <i class="fas fa-chevron-down text-[10px] text-slate-400"></i>
                    </button>
                </div>

                <!-- TOC Sections list -->
                <nav class="flex-1 overflow-y-auto p-4 space-y-6 scrollbar-hidden">
                    @foreach ($menuGroups as $group)
                        <div>
                            <span class="px-3 text-[11px] font-semibold tracking-wide text-slate-400 flex items-center gap-2 mb-2">
                                <i class="fas {{ $group['icon'] }} text-accent-amber text-xs w-4"></i>
                                {{ $group['title'] }}
                            </span>
                            <ul class="space-y-1">
                                @foreach ($group['sections'] as $section)
                                    <li>
                                        @if ($section['active'])
                                            <button 
                                                wire:click="setSection('{{ $section['code'] }}')"
                                                class="w-full text-left px-3 py-2 rounded-md text-xs font-medium transition-all duration-200 flex items-center justify-between {{ $activeSection === $section['code'] ? 'bg-accent-amber text-white shadow-sm' : 'text-slate-300 hover:bg-[#2c2724] hover:text-white' }}"
                                            >
                                                <span>{{ $section['label'] }}</span>
                                                 <i class="fas fa-chevron-right text-xs opacity-60"></i>
                                            </button>
                                        @else
                                            <div 
                                                class="w-full text-left px-3 py-2 rounded-md text-xs font-medium text-slate-500 cursor-not-allowed flex items-center justify-between group"
                                                title="Tersedia di fase berikutnya (Fase B/C)"
                                            >
                                                <span>{{ $section['label'] }}</span>
                                                <div class="flex items-center gap-1">
                                                    <span class="text-[11px] bg-slate-700 text-slate-400 px-1.5 py-0.5 rounded font-mono scale-90 group-hover:block hidden">DRAFT</span>
                                                    <i class="fas fa-lock text-xs opacity-40"></i>
                                                </div>
                                            </div>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endforeach
                </nav>

                <!-- Footer details -->
                <div class="p-4 border-t border-warm-border/10 text-center text-xs text-slate-500">
                    <p class="font-mono">CONFIDENTIAL &copy; {{ date('Y') }} SPSP</p>
                </div>
            </aside>

            <!-- Right Content Area -->
            <main class="flex-1 flex flex-col min-h-0 bg-warm-ivory md:h-full">
                <!-- Top Toolbar (Sticky) -->
                <header class="bg-white border-b border-warm-border px-8 py-4 flex items-center justify-between shrink-0 sticky top-0 z-30">
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-bold uppercase tracking-widest text-slate-400">Section Aktif</span>
                        <span class="text-xs font-semibold text-primary-ink/80 bg-warm-ivory px-2.5 py-1 rounded-md border border-warm-border">
                            @php
                                $activeLabel = '';
                                foreach ($menuGroups as $group) {
                                    foreach ($group['sections'] as $sec) {
                                        if ($sec['code'] === $activeSection) {
                                            $activeLabel = $sec['label'];
                                            break 2;
                                        }
                                    }
                                }
                            @endphp
                            {{ $activeLabel }}
                        </span>
                    </div>

                    <div class="flex items-center gap-3">
                        <a 
                            href="{{ route('dashboard') }}"
                            class="border border-warm-border hover:bg-warm-ivory text-primary-ink font-semibold text-xs px-4 py-2 rounded-md transition-all duration-200 flex items-center gap-2 cursor-pointer"
                        >
                            <i class="fas fa-arrow-left"></i>
                            Kembali ke SPSP
                        </a>
                        <button 
                            wire:click="togglePrintMode(true)"
                            class="bg-[#171412] hover:bg-[#2c2724] text-warm-ivory font-medium text-xs px-4 py-2 rounded-md shadow-sm transition-all duration-200 flex items-center gap-2 cursor-pointer"
                        >
                            <i class="fas fa-print"></i>
                            Cetak PDF
                        </button>
                    </div>
                </header>

                <!-- Scrollable Content Frame -->
                <div class="flex-1 overflow-y-auto p-8 md:p-12 scrollbar-hidden">
                    <div class="max-w-5xl mx-auto">
                        <!-- Transition wrapper -->
                        <div class="transition-all duration-300">
                            @switch($activeSection)
                                @case('cover')
                                    <livewire:pages.h-c-a.sections.cover :participant-id="$participantId" :key="'cover_'.$participantId" />
                                    @break
                                @case('exec_summary')
                                    <livewire:pages.h-c-a.sections.executive-summary />
                                    @break
                                @case('participant_id')
                                    <livewire:pages.h-c-a.sections.participant-profile />
                                    @break
                                @case('hci')
                                @case('potential')
                                @case('eq')
                                    <livewire:pages.h-c-a.sections.index-radar-section :sectionCode="$activeSection" :key="'radar_' . $activeSection" />
                                    @break
                                @case('competency')
                                @case('cognitive')
                                @case('big_five')
                                @case('learning_agility')
                                @case('leadership_potential')
                                @case('integrity')
                                    <livewire:pages.h-c-a.sections.score-list-section :sectionCode="$activeSection" :key="'scores_' . $activeSection" />
                                    @break
                                @case('career')
                                    <livewire:pages.h-c-a.sections.timeline-section />
                                    @break
                                @case('disc')
                                    <livewire:pages.h-c-a.sections.disc-profile />
                                    @break
                                @case('performance')
                                    <livewire:pages.h-c-a.sections.performance-dashboard />
                                    @break
                                @case('nine_box')
                                    <livewire:pages.h-c-a.sections.nine-box-matrix />
                                    @break
                                @case('succession')
                                    <livewire:pages.h-c-a.sections.succession-readiness />
                                    @break
                                @case('personal_profile')
                                @case('strengths')
                                    <livewire:pages.h-c-a.sections.qualitative-list-section :sectionCode="$activeSection" :key="'qualitative_' . $activeSection" />
                                    @break
                                @case('mental_health')
                                    <livewire:pages.h-c-a.sections.mental-health-section />
                                    @break
                                @case('risk_indicators')
                                    <livewire:pages.h-c-a.sections.risk-indicators />
                                    @break
                                @case('development_rec')
                                    <livewire:pages.h-c-a.sections.development-recommendation />
                                    @break
                                @case('next_role_rec')
                                    <livewire:pages.h-c-a.sections.next-role-recommendation />
                                    @break
                            @endswitch
                        </div>
                    </div>
                </div>

        <!-- Talent Selector Modal -->
        @if ($showTalentModal)
            <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
                <div class="bg-[#1e1b18] border border-warm-border/20 rounded-xl shadow-2xl w-full max-w-lg overflow-hidden flex flex-col max-h-[80vh]">
                    <!-- Modal Header -->
                    <div class="p-4 border-b border-warm-border/10 flex items-center justify-between bg-[#282320]">
                        <div class="flex items-center gap-2 text-white font-semibold text-sm">
                            <i class="fas fa-user-check text-accent-amber"></i>
                            <span>Pilih Active Talent (Peserta Asesmen)</span>
                        </div>
                        <button wire:click="toggleTalentModal" class="text-slate-400 hover:text-white text-sm p-1 cursor-pointer">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <!-- Search Bar -->
                    <div class="p-4 border-b border-warm-border/10 bg-[#221e1a]">
                        <div class="relative">
                            <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                            <input 
                                type="text" 
                                wire:model.live.debounce.250ms="searchParticipant"
                                placeholder="Cari nama, nomor tes, atau posisi..." 
                                class="w-full bg-[#181513] border border-warm-border/20 rounded-lg pl-9 pr-4 py-2 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-accent-amber"
                            />
                        </div>
                    </div>

                    <!-- Talent List -->
                    <div class="flex-1 overflow-y-auto p-3 space-y-1">
                        @forelse ($this->availableParticipants as $p)
                            <button 
                                wire:click="selectParticipant({{ $p->id }})"
                                class="w-full text-left p-3 rounded-lg flex items-center justify-between gap-3 transition-all cursor-pointer {{ $participantId === $p->id ? 'bg-accent-amber/20 border border-accent-amber/40 text-white' : 'hover:bg-[#2c2724] text-slate-300' }}"
                            >
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="w-9 h-9 rounded-full bg-slate-700 border border-slate-600 flex items-center justify-center text-xs font-bold text-white shrink-0">
                                        {{ $this->getInitials($p->name) }}
                                    </div>
                                    <div class="min-w-0">
                                        <div class="font-medium text-xs text-white truncate">{{ $p->name }}</div>
                                        <div class="text-[11px] text-slate-400 truncate font-mono">{{ $p->test_number }} &bull; {{ $p->positionFormation?->name ?? 'Posisi N/A' }}</div>
                                    </div>
                                </div>
                                @if ($participantId === $p->id)
                                    <span class="text-accent-amber text-xs font-bold shrink-0">
                                        <i class="fas fa-check-circle"></i> Active
                                    </span>
                                @endif
                            </button>
                        @empty
                            <div class="p-8 text-center text-slate-500 text-xs">
                                Tidak ada peserta yang cocok dengan pencarian "{{ $searchParticipant }}".
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        @endif

            </main>
        </div>
    @endif

    <style>
        /* Print Stylesheet */
        @media print {
            .page-break {
                page-break-after: always;
                break-after: page;
            }
            body {
                background: white !important;
                color: black !important;
            }
            main, aside, header, .no-print {
                display: none !important;
            }
            .print-container {
                display: block !important;
                width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
            }
        }
    </style>
</div>
