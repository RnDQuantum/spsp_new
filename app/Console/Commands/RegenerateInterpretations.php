<?php

namespace App\Console\Commands;

use App\Models\Participant;
use App\Services\InterpretationGeneratorService;
use Illuminate\Console\Command;

class RegenerateInterpretations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'interpretations:regenerate
                            {--event= : Event code to filter participants}
                            {--participant= : Specific participant ID}
                            {--force : Force regenerate even if interpretation exists}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Regenerate interpretations for participants';

    /**
     * Execute the console command.
     */
    public function handle(InterpretationGeneratorService $generator)
    {
        $this->info('Starting interpretation regeneration...');

        // Build query
        $query = Participant::with([
            'positionFormation.template.categoryTypes.aspects.subAspects',
        ]);

        // Filter by participant ID if specified
        if ($participantId = $this->option('participant')) {
            $query->where('id', $participantId);
        }

        // Filter by event code if specified
        if ($eventCode = $this->option('event')) {
            $query->whereHas('assessmentEvent', function ($q) use ($eventCode) {
                $q->where('code', $eventCode);
            });
        }

        $participants = $query->get();

        if ($participants->isEmpty()) {
            $this->error('No participants found with the given criteria.');

            return Command::FAILURE;
        }

        $this->info("Found {$participants->count()} participants to process.");

        $progressBar = $this->output->createProgressBar($participants->count());
        $progressBar->start();

        $successCount = 0;
        $errorCount = 0;

        foreach ($participants as $participant) {
            try {
                $generator->generateForParticipant($participant);
                $successCount++;
            } catch (\Exception $e) {
                $errorCount++;
                $this->newLine();
                $this->error("Failed for participant {$participant->id} ({$participant->name}): {$e->getMessage()}");
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        $this->info("✓ Successfully regenerated: {$successCount}");

        if ($errorCount > 0) {
            $this->warn("✗ Failed: {$errorCount}");
        }

        $this->info('Done!');

        return Command::SUCCESS;
    }
}
