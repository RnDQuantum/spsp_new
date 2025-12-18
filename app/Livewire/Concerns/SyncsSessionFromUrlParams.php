<?php

namespace App\Livewire\Concerns;

use App\Models\AssessmentEvent;
use App\Models\Participant;

trait SyncsSessionFromUrlParams
{
    /**
     * Sync session from URL parameters
     *
     * This method should be called in the mount() method of Livewire components
     * to ensure session filters are synchronized when users directly access URLs.
     *
     * @param  string|null  $eventCode  The event code from URL
     * @param  string|null  $testNumber  The participant's test number from URL
     * @param  int|null  $participantId  Direct participant ID (if available)
     * @param  int|null  $positionFormationId  Direct position formation ID (if available)
     */
    protected function syncSessionFromUrl(
        ?string $eventCode = null,
        ?string $testNumber = null,
        ?int $participantId = null,
        ?int $positionFormationId = null
    ): void {
        // Sync event code to session
        if ($eventCode !== null) {
            session(['filter.event_code' => $eventCode]);
        }

        // Sync position formation ID to session
        if ($positionFormationId !== null) {
            session(['filter.position_formation_id' => $positionFormationId]);
        }

        // Sync participant ID to session
        // Either from explicit ID or resolve from testNumber + eventCode
        if ($participantId !== null) {
            session(['filter.participant_id' => $participantId]);
        } elseif ($testNumber !== null && $eventCode !== null) {
            // Resolve participant from eventCode + testNumber
            $participant = Participant::query()
                ->whereHas('assessmentEvent', function ($query) use ($eventCode) {
                    $query->where('code', $eventCode);
                })
                ->where('test_number', $testNumber)
                ->first();

            if ($participant) {
                session([
                    'filter.participant_id' => $participant->id,
                    'filter.position_formation_id' => $participant->position_formation_id,
                ]);
            }
        }
    }

    /**
     * Sync session from Participant model
     *
     * This is a convenient method when you already have a loaded Participant model
     * with its relationships (assessmentEvent).
     *
     * @param  \App\Models\Participant  $participant  The participant model with assessmentEvent loaded
     */
    protected function syncSessionFromParticipant(Participant $participant): void
    {
        session()->put([
            'filter.event_code' => $participant->assessmentEvent->code,
            'filter.position_formation_id' => $participant->position_formation_id,
            'filter.participant_id' => $participant->id,
        ]);
    }

    /**
     * Sync session from Event model
     *
     * This is useful for pages that only have event context without participant.
     *
     * @param  \App\Models\AssessmentEvent  $event  The assessment event model
     * @param  int|null  $positionFormationId  Optional position formation ID
     */
    protected function syncSessionFromEvent(AssessmentEvent $event, ?int $positionFormationId = null): void
    {
        session(['filter.event_code' => $event->code]);

        if ($positionFormationId !== null) {
            session(['filter.position_formation_id' => $positionFormationId]);
        }
    }
}
