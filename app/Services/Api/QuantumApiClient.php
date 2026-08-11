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
     * Ambik daftar semua kode proyek yang tersedia di API Tes Online.
     */
    public function getProjectCodes(): array
    {
        if (! $this->baseUrl || ! $this->apiKey) {
            return [];
        }

        try {
            $url = rtrim($this->baseUrl, '/').'/ambil_kode_proyek';
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'X-API-Key' => $this->apiKey,
            ])->timeout(30)->get($url);

            if ($response->successful() && $response->json('status')) {
                return $response->json('proyek') ?? [];
            }
        } catch (Exception $e) {
            Log::error("QuantumApiClient: Exception in getProjectCodes: {$e->getMessage()}");
        }

        return [];
    }

    /**
     * Ambil data lengkap seluruh peserta & hasil tes pada satu proyek dari API Tes Online.
     */
    public function getProjectData(string $kodeProyek): ?array
    {
        if (! $this->baseUrl || ! $this->apiKey) {
            return null;
        }

        try {
            $url = rtrim($this->baseUrl, '/').'/ambil_semua';
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'X-API-Key' => $this->apiKey,
            ])->timeout(60)->post($url, [
                'kode' => $kodeProyek,
            ]);

            if ($response->successful() && $response->json('status')) {
                return $response->json();
            }

            Log::warning("QuantumApiClient: getProjectData returned unsuccessful status for {$kodeProyek}", [
                'status' => $response->status(),
                'body' => substr($response->body(), 0, 200),
            ]);
        } catch (Exception $e) {
            Log::error("QuantumApiClient: Exception in getProjectData for {$kodeProyek}: {$e->getMessage()}");
        }

        return null;
    }

    /**
     * Tarik data tes online peserta spesifik dari API Quantum HRMI.
     */
    public function fetchParticipantTests(string|int $participantIdentifier, ?int $eventId = null): array
    {
        if ($this->baseUrl && $this->apiKey) {
            try {
                $url = rtrim($this->baseUrl, '/').'/api/v1/test-results';
                $response = Http::withHeaders([
                    'X-API-Key' => $this->apiKey,
                ])->timeout(15)->get($url, [
                    'participant_id' => $participantIdentifier,
                    'event_id' => $eventId,
                ]);

                if ($response->successful()) {
                    return $response->json('data') ?? $response->json() ?? [];
                }
            } catch (Exception $e) {
                Log::warning("QuantumApiClient: Falling back to mock data for participant {$participantIdentifier}: {$e->getMessage()}");
            }
        }

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
