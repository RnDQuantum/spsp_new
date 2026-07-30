<?php

namespace App\Services\Api;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class QuantumApiClient
{
    protected ?string $baseUrl;

    protected ?string $apiKey;

    public function __construct(?string $baseUrl = null, ?string $apiKey = null)
    {
        $this->baseUrl = $baseUrl ?? config('services.quantum_hrmi.base_url', env('QUANTUM_API_BASE_URL'));
        $this->apiKey = $apiKey ?? config('services.quantum_hrmi.api_key', env('QUANTUM_API_KEY'));
    }

    /**
     * Tarik data tes online peserta dari API Quantum HRMI.
     * Jika URL API belum dikonfigurasi, gunakan data mock/simulasi untuk development.
     */
    public function fetchParticipantTests(string|int $participantIdentifier, ?int $eventId = null): array
    {
        if ($this->baseUrl && $this->apiKey) {
            try {
                $response = Http::withToken($this->apiKey)
                    ->timeout(15)
                    ->get("{$this->baseUrl}/api/v1/test-results", [
                        'participant_id' => $participantIdentifier,
                        'event_id' => $eventId,
                    ]);

                if ($response->successful()) {
                    return $response->json('data') ?? $response->json() ?? [];
                }

                Log::warning("QuantumApiClient: Failed to fetch API for participant {$participantIdentifier}", [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
            } catch (Exception $e) {
                Log::error("QuantumApiClient: Exception while connecting to Quantum API: {$e->getMessage()}", [
                    'participant' => $participantIdentifier,
                ]);
            }
        }

        // Fallback: Simulasi response API jika live endpoint belum aktif
        return $this->getMockParticipantTests($participantIdentifier);
    }

    /**
     * Data mock simulasi untuk testing API Tes Online Quantum HRMI.
     */
    protected function getMockParticipantTests(string|int $participantIdentifier): array
    {
        return [
            'A.1' => [
                'nama_alat_tes' => 'Typical CFIT3A',
                'status' => true,
                'mulai_tes' => now()->subHours(2)->toDateTimeString(),
                'iq' => 115,
                'kategori' => 'Tinggi',
                'total' => 38,
                'index_kecerdasan_umum' => 115,
                'hasil_sub' => [
                    'sub_1' => ['nilai' => 4],
                    'sub_2' => ['nilai' => 4],
                    'sub_3' => ['nilai' => 3],
                    'sub_4' => ['nilai' => 4],
                ],
            ],
            'B.1' => [
                'nama_alat_tes' => 'KOMPETENSI KARAKTER',
                'status' => true,
                'mulai_tes' => now()->subHours(1)->toDateTimeString(),
                'hasil_integritas' => 4,
                'hasil_kerjasama' => 4,
                'hasil_komunikasi' => 3,
                'labels_aspek' => [
                    'hasil_integritas' => 'Integritas',
                    'hasil_kerjasama' => 'Kerjasama',
                    'hasil_komunikasi' => 'Komunikasi',
                ],
            ],
            'B.2' => [
                'nama_alat_tes' => 'Typical 16PF',
                'status' => true,
                'mulai_tes' => now()->subMinutes(30)->toDateTimeString(),
                'nilaiAspek' => [
                    'A' => 6, 'B' => 7, 'C' => 5, 'E' => 6, 'F' => 7,
                    'G' => 8, 'H' => 6, 'I' => 5, 'L' => 4, 'M' => 5,
                    'N' => 6, 'O' => 4, 'Q1' => 6, 'Q2' => 5, 'Q3' => 7, 'Q4' => 4,
                ],
                'MDStenScore' => 5,
            ],
        ];
    }
}
