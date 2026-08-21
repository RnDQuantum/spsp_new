<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HCA Report — {{ $participant->name ?? 'Peserta' }}</title>

    @php
        $cssContent = '';
        $manifestPath = public_path('build/manifest.json');
        if (file_exists($manifestPath)) {
            $manifest = json_decode(file_get_contents($manifestPath), true) ?: [];
            if (!empty($manifest['resources/css/app.css']['file'])) {
                $file = public_path('build/' . $manifest['resources/css/app.css']['file']);
                if (file_exists($file)) {
                    $cssContent .= file_get_contents($file) . "\n";
                }
            }
            if (!empty($manifest['resources/js/app.js']['css'][0])) {
                $file = public_path('build/' . $manifest['resources/js/app.js']['css'][0]);
                if (file_exists($file)) {
                    $cssContent .= file_get_contents($file) . "\n";
                }
            }
        }
        $chartJsPath = base_path('node_modules/chart.js/dist/chart.umd.js');
        $chartJsContent = file_exists($chartJsPath) ? file_get_contents($chartJsPath) : '';
    @endphp

    @livewireStyles

    <!-- Google Fonts: Lora & Instrument Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400;500;600;700&family=Lora:ital,wght@0,400..700;1,400..700&display=swap" rel="stylesheet">

    <!-- FontAwesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />

    <!-- Inlined Chart.js -->
    @if ($chartJsContent)
        <script>{!! $chartJsContent !!}</script>
    @else
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    @endif

    <script>
        // Disable Chart.js animation for instant zero-latency canvas rendering in PDF
        if (typeof Chart !== 'undefined') {
            Chart.defaults.animation = false;
            Chart.defaults.animations = false;
            Chart.defaults.transitions = false;
        }
    </script>

    <style>
        {!! $cssContent !!}

        @page {
            size: A4 portrait;
            margin: 8mm 8mm 12mm 8mm;
        }

        body {
            background-color: #ffffff !important;
            color: #171412 !important;
            font-family: 'Instrument Sans', sans-serif;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        .page-break {
            page-break-after: always;
            break-after: page;
        }

        .no-break-inside {
            break-inside: avoid;
            page-break-inside: avoid;
        }

        * {
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
    </style>
</head>
<body class="bg-white text-[#171412] antialiased leading-relaxed">
    <div class="print-container bg-white p-0">
        <!-- 01 Cover -->
        <div class="page-break">
            <livewire:pages.h-c-a.sections.cover :participant-id="$participant->id" :key="'pdf_cover_'.$participant->id" />
        </div>

        <!-- 02 Ringkasan Eksekutif -->
        <div class="page-break p-6">
            <livewire:pages.h-c-a.sections.executive-summary :participant-id="$participant->id" :key="'pdf_exec_summary_'.$participant->id" />
        </div>

        <!-- 03 Identitas Peserta -->
        <div class="page-break p-6">
            <livewire:pages.h-c-a.sections.participant-profile :participant-id="$participant->id" :key="'pdf_profile_'.$participant->id" />
        </div>

        <!-- 04 HCI -->
        <div class="page-break p-6">
            <livewire:pages.h-c-a.sections.index-radar-section sectionCode="hci" :participant-id="$participant->id" :key="'pdf_hci_'.$participant->id" />
        </div>

        <!-- 05 Kompetensi -->
        <div class="page-break p-6">
            <livewire:pages.h-c-a.sections.score-list-section sectionCode="competency" :participant-id="$participant->id" :key="'pdf_competency_'.$participant->id" />
        </div>

        <!-- 06 Riwayat Karier -->
        <div class="page-break p-6">
            <livewire:pages.h-c-a.sections.timeline-section :participant-id="$participant->id" :key="'pdf_timeline_'.$participant->id" />
        </div>

        <!-- 07 Potensi -->
        <div class="page-break p-6">
            <livewire:pages.h-c-a.sections.index-radar-section sectionCode="potential" :participant-id="$participant->id" :key="'pdf_potential_'.$participant->id" />
        </div>

        <!-- 08 IQ & Profil Kognitif -->
        <div class="page-break p-6">
            <livewire:pages.h-c-a.sections.score-list-section sectionCode="cognitive" :participant-id="$participant->id" :key="'pdf_cognitive_'.$participant->id" />
        </div>

        <!-- 09 Big Five Personality -->
        <div class="page-break p-6">
            <livewire:pages.h-c-a.sections.score-list-section sectionCode="big_five" :participant-id="$participant->id" :key="'pdf_big_five_'.$participant->id" />
        </div>

        <!-- 10 DISC Profile -->
        <div class="page-break p-6">
            <livewire:pages.h-c-a.sections.disc-profile :participant-id="$participant->id" :key="'pdf_disc_'.$participant->id" />
        </div>

        <!-- 11 Learning Agility -->
        <div class="page-break p-6">
            <livewire:pages.h-c-a.sections.score-list-section sectionCode="learning_agility" :participant-id="$participant->id" :key="'pdf_learning_agility_'.$participant->id" />
        </div>

        <!-- 12 Leadership Potential -->
        <div class="page-break p-6">
            <livewire:pages.h-c-a.sections.score-list-section sectionCode="leadership_potential" :participant-id="$participant->id" :key="'pdf_leadership_potential_'.$participant->id" />
        </div>

        <!-- 13 Emotional Intelligence (EQ) -->
        <div class="page-break p-6">
            <livewire:pages.h-c-a.sections.index-radar-section sectionCode="eq" :participant-id="$participant->id" :key="'pdf_eq_'.$participant->id" />
        </div>

        <!-- 14 Values & Integrity -->
        <div class="page-break p-6">
            <livewire:pages.h-c-a.sections.score-list-section sectionCode="integrity" :participant-id="$participant->id" :key="'pdf_integrity_'.$participant->id" />
        </div>

        <!-- 15 Performance Dashboard -->
        <div class="page-break p-6">
            <livewire:pages.h-c-a.sections.performance-dashboard :participant-id="$participant->id" :key="'pdf_performance_'.$participant->id" />
        </div>

        <!-- 16 Talent 9-Box Matrix -->
        <div class="page-break p-6">
            <livewire:pages.h-c-a.sections.nine-box-matrix :participant-id="$participant->id" :key="'pdf_nine_box_'.$participant->id" />
        </div>

        <!-- 17 Succession Readiness -->
        <div class="page-break p-6">
            <livewire:pages.h-c-a.sections.succession-readiness :participant-id="$participant->id" :key="'pdf_succession_'.$participant->id" />
        </div>

        <!-- 18 Profil Personal -->
        <div class="page-break p-6">
            <livewire:pages.h-c-a.sections.qualitative-list-section sectionCode="personal_profile" :participant-id="$participant->id" :key="'pdf_personal_profile_'.$participant->id" />
        </div>

        <!-- 19 Kesehatan Jiwa -->
        <div class="page-break p-6">
            <livewire:pages.h-c-a.sections.mental-health-section :participant-id="$participant->id" :key="'pdf_mental_health_'.$participant->id" />
        </div>

        <!-- 20 Kekuatan Psikologis -->
        <div class="page-break p-6">
            <livewire:pages.h-c-a.sections.qualitative-list-section sectionCode="strengths" :participant-id="$participant->id" :key="'pdf_strengths_'.$participant->id" />
        </div>

        <!-- 21 Indikator Risiko -->
        <div class="page-break p-6">
            <livewire:pages.h-c-a.sections.risk-indicators :participant-id="$participant->id" :key="'pdf_risk_indicators_'.$participant->id" />
        </div>

        <!-- 22 Rekomendasi Pengembangan -->
        <div class="page-break p-6">
            <livewire:pages.h-c-a.sections.development-recommendation :participant-id="$participant->id" :key="'pdf_development_rec_'.$participant->id" />
        </div>

        <!-- 23 Rekomendasi Peran Berikutnya -->
        <div class="page-break p-6">
            <livewire:pages.h-c-a.sections.next-role-recommendation :participant-id="$participant->id" :key="'pdf_next_role_rec_'.$participant->id" />
        </div>

        <!-- 24 Laporan Hasil Alat Tes -->
        <div class="p-6">
            <livewire:pages.h-c-a.sections.test-instruments-appendix :participant-id="$participant->id" :key="'pdf_test_instruments_'.$participant->id" />
        </div>
    </div>
</body>
</html>
