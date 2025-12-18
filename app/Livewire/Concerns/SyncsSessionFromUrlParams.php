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
     * SMART SAVE: Only updates session if values have changed to reduce overhead.
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
        $updates = [];

        // Only update event code if changed
        if ($eventCode !== null && session('filter.event_code') !== $eventCode) {
            $updates['filter.event_code'] = $eventCode;
        }

        // Only update position formation ID if changed
        if ($positionFormationId !== null && session('filter.position_formation_id') !== $positionFormationId) {
            $updates['filter.position_formation_id'] = $positionFormationId;
        }

        // Sync participant ID to session
        // Either from explicit ID or resolve from testNumber + eventCode
        if ($participantId !== null && session('filter.participant_id') !== $participantId) {
            $updates['filter.participant_id'] = $participantId;
        } elseif ($testNumber !== null && $eventCode !== null) {
            // Resolve participant from eventCode + testNumber (only if session doesn't match)
            $participant = Participant::query()
                ->whereHas('assessmentEvent', function ($query) use ($eventCode) {
                    $query->where('code', $eventCode);
                })
                ->where('test_number', $testNumber)
                ->first();

            if ($participant) {
                if (session('filter.participant_id') !== $participant->id) {
                    $updates['filter.participant_id'] = $participant->id;
                }
                if (session('filter.position_formation_id') !== $participant->position_formation_id) {
                    $updates['filter.position_formation_id'] = $participant->position_formation_id;
                }
            }
        }

        // Batch update session only if there are changes
        if (! empty($updates)) {
            session($updates);
        }
    }

    /**
     * Sync session from Participant model
     *
     * This is a convenient method when you already have a loaded Participant model
     * with its relationships (assessmentEvent).
     *
     * SMART SAVE: Only updates session if values have changed to reduce overhead.
     *
     * @param  \App\Models\Participant  $participant  The participant model with assessmentEvent loaded
     */
    protected function syncSessionFromParticipant(Participant $participant): void
    {
        $updates = [];

        // Only update if values have changed
        if (session('filter.event_code') !== $participant->assessmentEvent->code) {
            $updates['filter.event_code'] = $participant->assessmentEvent->code;
        }

        if (session('filter.position_formation_id') !== $participant->position_formation_id) {
            $updates['filter.position_formation_id'] = $participant->position_formation_id;
        }

        if (session('filter.participant_id') !== $participant->id) {
            $updates['filter.participant_id'] = $participant->id;
        }

        // Batch update session only if there are changes
        if (! empty($updates)) {
            session()->put($updates);
        }
    }

    /**
     * Sync session from Event model
     *
     * This is useful for pages that only have event context without participant.
     *
     * SMART SAVE: Only updates session if values have changed to reduce overhead.
     *
     * @param  \App\Models\AssessmentEvent  $event  The assessment event model
     * @param  int|null  $positionFormationId  Optional position formation ID
     */
    protected function syncSessionFromEvent(AssessmentEvent $event, ?int $positionFormationId = null): void
    {
        $updates = [];

        // Only update event code if changed
        if (session('filter.event_code') !== $event->code) {
            $updates['filter.event_code'] = $event->code;
        }

        // Only update position formation ID if provided and changed
        if ($positionFormationId !== null && session('filter.position_formation_id') !== $positionFormationId) {
            $updates['filter.position_formation_id'] = $positionFormationId;
        }

        // Batch update session only if there are changes
        if (! empty($updates)) {
            session($updates);
        }
    }
}
