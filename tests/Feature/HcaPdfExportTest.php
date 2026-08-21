<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Institution;
use App\Models\Participant;
use App\Models\User;
use Tests\TestCase;

class HcaPdfExportTest extends TestCase
{
    /**
     * Test guest cannot download protected HCA report PDF
     */
    public function test_guest_cannot_download_protected_pdf(): void
    {
        $participant = Participant::query()->first() ?? Participant::factory()->create();

        $response = $this->get(route('hca-report.download-pdf', ['participant' => $participant->id]));

        $response->assertRedirect(route('login'));
    }

    /**
     * Test guest cannot preview protected HCA report PDF
     */
    public function test_guest_cannot_preview_protected_pdf(): void
    {
        $participant = Participant::query()->first() ?? Participant::factory()->create();

        $response = $this->get(route('hca-report.preview-pdf', ['participant' => $participant->id]));

        $response->assertRedirect(route('login'));
    }

    /**
     * Test user cannot download PDF from a different institution
     */
    public function test_user_cannot_download_pdf_of_different_institution(): void
    {
        $inst1 = Institution::factory()->create();
        $inst2 = Institution::factory()->create();

        /** @var User $user */
        $user = User::factory()->create([
            'institution_id' => $inst1->id,
        ]);

        $participant = Participant::factory()->create([
            'institution_id' => $inst2->id,
        ]);

        $response = $this->actingAs($user)->get(route('hca-report.download-pdf', ['participant' => $participant->id]));

        $response->assertNotFound();
    }

    /**
     * Test authorized user can access PDF download route
     */
    public function test_authorized_user_can_access_pdf_download_route(): void
    {
        $institution = Institution::query()->first() ?? Institution::factory()->create();

        /** @var User $user */
        $user = User::factory()->create([
            'institution_id' => $institution->id,
        ]);

        $participant = Participant::query()->where('institution_id', $institution->id)->first()
            ?? Participant::factory()->create(['institution_id' => $institution->id]);

        $response = $this->actingAs($user)->get(route('hca-report.download-pdf', ['participant' => $participant->id]));

        $response->assertStatus(200);
        $this->assertStringContainsString('pdf', (string) $response->headers->get('content-type', ''));
    }

    /**
     * Test demo download route in local/test environment
     */
    public function test_demo_download_route_responds_with_pdf(): void
    {
        $participant = Participant::query()->first() ?? Participant::factory()->create();

        $response = $this->get(route('hca-report.download-demo', ['participant' => $participant->id]));

        $response->assertStatus(200);
        $this->assertStringContainsString('pdf', (string) $response->headers->get('content-type', ''));
    }
}
