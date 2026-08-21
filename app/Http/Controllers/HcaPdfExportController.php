<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Participant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Spatie\LaravelPdf\Facades\Pdf;
use Spatie\LaravelPdf\PdfBuilder;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class HcaPdfExportController extends Controller
{
    /**
     * Download HCA Report PDF as a physical file attachment
     */
    public function download(Request $request, Participant $participant): PdfBuilder|SymfonyResponse
    {
        $this->authorizeAccess($participant);

        $pdf = $this->buildPdf($participant);
        $fileName = $this->generateFileName($participant);

        return $pdf->download($fileName);
    }

    /**
     * Preview HCA Report PDF inline in the browser tab
     */
    public function preview(Request $request, Participant $participant): PdfBuilder|SymfonyResponse
    {
        $this->authorizeAccess($participant);

        $pdf = $this->buildPdf($participant);
        $fileName = $this->generateFileName($participant);

        return $pdf->inline($fileName);
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

        return Pdf::view('pdf.hca.report', [
            'participant' => $participant,
        ])
            ->format('a4')
            ->portrait()
            ->footerView('pdf.hca.footer')
            ->margins(8, 8, 12, 8, 'mm')
            ->withBrowsershot(function ($browsershot) {
                $chromePath = config('laravel-pdf.browsershot.chrome_path');
                if ($chromePath && file_exists($chromePath)) {
                    $browsershot->setChromePath($chromePath);
                }
                $browsershot
                    ->noSandbox()
                    ->showBackground()
                    ->setDelay(1500)
                    ->addChromiumArguments([
                        '--headless=new',
                        '--disable-gpu',
                        '--disable-dev-shm-usage',
                        '--no-sandbox',
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
