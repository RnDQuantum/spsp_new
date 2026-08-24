<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Participant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Spatie\LaravelPdf\Facades\Pdf;
use Spatie\LaravelPdf\PdfBuilder;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class HcaPdfExportController extends Controller
{
    /**
     * Download HCA Report PDF as a physical file attachment
     */
    public function download(Request $request, Participant $participant): SymfonyResponse
    {
        $startTime = microtime(true);
        $timeStr = now()->format('Y-m-d H:i:s');
        Log::info("[{$timeStr}] [HCA PDF] 🚀 Memulai proses Download PDF | Peserta: {$participant->name} (ID: {$participant->id}, No Tes: {$participant->test_number})");

        $this->authorizeAccess($participant);

        $pdf = $this->buildPdf($participant);
        $fileName = $this->generateFileName($participant);

        // Eksekusi render Chromium secara langsung agar total durasi riil terukur
        $response = $pdf->download($fileName)->toResponse($request);

        $durationMs = round((microtime(true) - $startTime) * 1000, 2);
        $durationSec = round(microtime(true) - $startTime, 2);
        $fileSizeKb = round(strlen((string) $response->getContent()) / 1024, 2);

        Log::info("[{$timeStr}] [HCA PDF] ✅ Selesai render PDF (Download) | Peserta: {$participant->name} | Total Waktu Nyata: {$durationSec} detik ({$durationMs} ms) | Ukuran File: {$fileSizeKb} KB | File: {$fileName}");

        return $response;
    }

    /**
     * Preview HCA Report PDF inline in the browser tab
     */
    public function preview(Request $request, Participant $participant): SymfonyResponse
    {
        $startTime = microtime(true);
        $timeStr = now()->format('Y-m-d H:i:s');
        Log::info("[{$timeStr}] [HCA PDF] 🚀 Memulai proses Preview PDF | Peserta: {$participant->name} (ID: {$participant->id}, No Tes: {$participant->test_number})");

        $this->authorizeAccess($participant);

        $pdf = $this->buildPdf($participant);
        $fileName = $this->generateFileName($participant);

        // Eksekusi render Chromium secara langsung agar total durasi riil terukur
        $response = $pdf->inline($fileName)->toResponse($request);

        $durationMs = round((microtime(true) - $startTime) * 1000, 2);
        $durationSec = round(microtime(true) - $startTime, 2);
        $fileSizeKb = round(strlen((string) $response->getContent()) / 1024, 2);

        Log::info("[{$timeStr}] [HCA PDF] ✅ Selesai render PDF (Preview) | Peserta: {$participant->name} | Total Waktu Nyata: {$durationSec} detik ({$durationMs} ms) | Ukuran File: {$fileSizeKb} KB | File: {$fileName}");

        return $response;
    }

    /**
     * Build the Spatie PDF instance with standard configuration
     */
    protected function buildPdf(Participant $participant): PdfBuilder
    {
        $participant->loadMissing([
            'assessmentEvent.institution',
            'positionFormation.template',
            'batch',
            'finalAssessment',
            'mmpi',
            'institution',
            'personalProfile',
            'careerHistories',
            'performanceRecords',
        ]);

        $tolerancePercentage = (int) session('individual_report.tolerance', 0);
        $cacheKey = "hca_pdf_v3_{$participant->id}_tol_{$tolerancePercentage}_".($participant->updated_at?->timestamp ?? '0');

        return Pdf::view('pdf.hca.report', [
            'participant' => $participant,
            'tolerancePercentage' => $tolerancePercentage,
        ])
            ->format('a4')
            ->portrait()
            ->footerView('pdf.hca.footer')
            ->margins(8, 8, 12, 8, 'mm')
            ->cache(key: $cacheKey, ttl: 86400)
            ->withBrowsershot(function ($browsershot) {
                $chromePath = config('laravel-pdf.browsershot.chrome_path');
                if ($chromePath && file_exists($chromePath)) {
                    $browsershot->setChromePath($chromePath);
                }
                $browsershot
                    ->noSandbox()
                    ->showBackground()
                    ->setDelay(300)
                    ->addChromiumArguments([
                        '--headless=new',
                        '--disable-gpu',
                        '--disable-dev-shm-usage',
                        '--no-sandbox',
                        '--disable-extensions',
                        '--disable-background-networking',
                        '--disable-background-timer-throttling',
                        '--disable-backgrounding-occluded-windows',
                        '--disable-breakpad',
                        '--disable-component-extensions-with-background-pages',
                        '--disable-default-apps',
                        '--disable-features=TranslateUI,BlinkGenPropertyTrees',
                        '--disable-ipc-flooding-protection',
                        '--disable-renderer-backgrounding',
                        '--enable-features=NetworkService,NetworkServiceInProcess',
                        '--force-color-profile=srgb',
                        '--hide-scrollbars',
                        '--metrics-recording-only',
                        '--mute-audio',
                        '--no-first-run',
                    ]);
            });
    }

    /**
     * Authorize that the current authenticated user has access to the participant
     */
    protected function authorizeAccess(Participant $participant): void
    {
        // Allow demo route access when running in local/testing environment
        if (request()->routeIs('hca-report.download-demo') && ! app()->environment('production')) {
            return;
        }

        /** @var User|null $user */
        $user = auth()->user();

        if (! $user) {
            abort(401, 'Silakan login terlebih dahulu untuk mengunduh laporan.');
        }

        if ($user->institution_id && $participant->institution_id && (int) $user->institution_id !== (int) $participant->institution_id) {
            abort(403, 'Anda tidak memiliki otorisasi untuk mengakses laporan peserta dari institusi lain.');
        }
    }

    /**
     * Generate standard file name for the downloaded PDF
     */
    protected function generateFileName(Participant $participant): string
    {
        $cleanName = Str::slug($participant->name ?? 'Talenta', '_');
        $testNumber = Str::slug($participant->test_number ?? '000', '_');

        return "HCA_Report_{$testNumber}_{$cleanName}.pdf";
    }
}
