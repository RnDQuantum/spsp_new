<?php

declare(strict_types=1);

namespace Database\Seeders\Support;

use App\Models\AssessmentEvent;
use App\Models\Participant;
use App\Models\PositionFormation;

/**
 * CareerHistoryGenerator - Generator Riwayat Histori Karier Kronologis Realistis
 *
 * Bertanggung jawab menghasilkan 3-4 tahapan perjalanan karier realistis
 * per peserta yang selaras dengan jabatan formasi, institusi, dan rekam jejak.
 */
class CareerHistoryGenerator
{
    /**
     * Template peran dan capaian realistis berdasarkan rumpun jabatan.
     *
     * @var array<string, array<int, array<string, mixed>>>
     */
    private static array $careerTracks = [
        'manajemen' => [
            [
                'title_suffix' => 'Head / Manager',
                'achievements' => [
                    'Memimpin transformasi tata kelola unit kerja dengan peningkatan efisiensi operasional sebesar 28%.',
                    'Mengorkestrasi penyusunan roadmap strategis tahunan yang diadopsi di tingkat korporat/kementerian.',
                    'Mempertahankan indeks kepuasan stakeholder internal di atas 92% secara konsisten.',
                ],
            ],
            [
                'title_suffix' => 'Assistant Manager / Sub-Koordinator',
                'achievements' => [
                    'Mengawal eksekusi program prioritas organisasi dengan tingkat ketercapaian target 100%.',
                    'Membangun sistem monitoring kinerja berbasis dashboard digital untuk tim lintas fungsi.',
                    'Menyusun SOP baru yang memangkas waktu birokrasi layanan hingga 30%.',
                ],
            ],
            [
                'title_suffix' => 'Senior Officer / Analis Madya',
                'achievements' => [
                    'Merumuskan analisis kebijakan dan telaah staf komprehensif bagi pimpinan tinggi.',
                    'Mengelola anggaran program tahunan sebesar Rp 15 Miliar dengan realisasi akuntabel 98.5%.',
                ],
            ],
            [
                'title_suffix' => 'Junior Analyst / Staf Pelaksana',
                'achievements' => [
                    'Mengumpulkan dan mengolah dataset operasional bulanan untuk pelaporan manajemen.',
                    'Berpartisipasi aktif dalam gugus tugas implementasi standar mutu ISO organisasi.',
                ],
            ],
        ],
        'teknis' => [
            [
                'title_suffix' => 'Lead Specialist / Arsitek Sistem',
                'achievements' => [
                    'Merancang arsitektur sistem inti terintegrasi yang melayani jutaan transaksi data.',
                    'Mengurangi insiden teknis operasional sebesar 45% melalui perbaikan arsitektur berkala.',
                    'Membimbing 10+ tenaga spesialis muda dalam implementasi best-practice industri.',
                ],
            ],
            [
                'title_suffix' => 'Senior Technical Specialist',
                'achievements' => [
                    'Mengembangkan modul otomatisasi proses bisnis yang menghemat ratusan jam kerja manual per bulan.',
                    'Melakukan audit keamanan data dan mitigasi kerentanan sistem secara berkala.',
                ],
            ],
            [
                'title_suffix' => 'Technical Officer / Analis Sistem',
                'achievements' => [
                    'Mengimplementasikan integrasi API multi-platform dengan standar keamanan tinggi.',
                    'Menyusun dokumentasi teknis dan panduan operasional pengguna secara menyeluruh.',
                ],
            ],
            [
                'title_suffix' => 'Junior Technical Officer',
                'achievements' => [
                    'Melakukan pemeliharaan rutin dan penanganan tiket teknis dengan SLA di atas 95%.',
                    'Mendukung proses migrasi data dari sistem warisan ke infrastruktur baru.',
                ],
            ],
        ],
        'pelayanan' => [
            [
                'title_suffix' => 'Koordinator Layanan Terpadu',
                'achievements' => [
                    'Meningkatkan Indeks Kepuasan Masyarakat (IKM) unit layanan ke kategori Sangat Baik (A).',
                    'Menginisiasi loket layanan digital responsif yang mempercepat proses verifikasi berkas.',
                    'Menerima penghargaan unit layanan percontohan di tingkat regional/nasional.',
                ],
            ],
            [
                'title_suffix' => 'Supervisor Mutu Pelayanan',
                'achievements' => [
                    'Menstandarkan alur penanganan keluhan pengguna dengan resolusi tuntas di bawah 24 jam.',
                    'Memberikan pelatihan budaya pelayanan prima (service excellence) kepada garda terdepan.',
                ],
            ],
            [
                'title_suffix' => 'Senior Customer Care / Petugas Pelayanan Madya',
                'achievements' => [
                    'Menangani eskalasi kasus kompleks dari stakeholder dengan kepuasan penyelesaian 98%.',
                    'Merekapitulasi umpan balik harian untuk bahan rekomendasi perbaikan berkala.',
                ],
            ],
            [
                'title_suffix' => 'Frontline Officer / Petugas Layanan',
                'achievements' => [
                    'Melayani ratusan permohonan harian dengan tingkat akurasi verifikasi data 99.8%.',
                    'Meraih predikat petugas layanan terfavorit berbasis survei kepuasan pelanggan berkala.',
                ],
            ],
        ],
    ];

    /**
     * Generate daftar riwayat karier kronologis untuk 1 peserta.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function generateForParticipant(
        Participant $participant,
        AssessmentEvent $event,
        ?PositionFormation $position = null
    ): array {
        $positionName = $position?->name ?? $participant->current_position ?? 'Pejabat Fungsional/Struktural';
        $institutionName = $event->institution?->name ?? 'Instansi Pemerintahan / Korporasi';

        // Deteksi cluster peran berdasarkan kata kunci jabatan
        $lowerPos = strtolower($positionName);
        $trackKey = match (true) {
            str_contains($lowerPos, 'komputer') || str_contains($lowerPos, 'it') || str_contains($lowerPos, 'sistem') || str_contains($lowerPos, 'teknis') || str_contains($lowerPos, 'arsitek') => 'teknis',
            str_contains($lowerPos, 'layanan') || str_contains($lowerPos, 'pelayanan') || str_contains($lowerPos, 'humas') || str_contains($lowerPos, 'konsumen') => 'pelayanan',
            default => 'manajemen',
        };

        $track = self::$careerTracks[$trackKey];
        $assessmentYear = $participant->assessment_date
            ? (int) $participant->assessment_date->format('Y')
            : (int) date('Y');

        $histories = [];
        $stepsCount = 4;
        $yearInterval = 3;

        for ($index = 0; $index < $stepsCount; $index++) {
            $isCurrent = ($index === 0);
            $startYear = $assessmentYear - (($index + 1) * $yearInterval) + ($isCurrent ? $yearInterval - 1 : 0);
            $endYear = $isCurrent ? null : ($startYear + $yearInterval);

            $stepConfig = $track[$index] ?? $track[0];

            $roleTitle = $isCurrent
                ? $positionName
                : ($stepConfig['title_suffix'].' — '.$positionName);

            $unitName = match ($index) {
                0 => 'Kantor Pusat / Unit Strategis, '.$institutionName,
                1 => 'Divisi Operasional & Pengembangan, '.$institutionName,
                2 => 'Sub-Direktorat Regional / Balai Kerja',
                default => 'Kantor Wilayah / Satuan Kerja Pratama',
            };

            $histories[] = [
                'participant_id' => $participant->id,
                'position_title' => $roleTitle,
                'company_or_institution' => $unitName,
                'start_year' => $startYear,
                'end_year' => $endYear,
                'is_current' => $isCurrent,
                'achievements' => json_encode($stepConfig['achievements']),
                'order_index' => $index,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        return $histories;
    }
}
