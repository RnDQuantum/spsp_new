<div>
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Final Report</h1>
        <p class="text-gray-600 dark:text-gray-400 mt-2">
            Complete assessment report for test number: {{ $testNumber }}
        </p>
    </div>
    {{-- General Matching Section --}}
    <div class="mb-8">
        <livewire:pages.individual-report.general-matching :eventCode="$eventCode" :testNumber="$testNumber"
            :isStandalone="false" :key="'general-matching-' . $eventCode . '-' . $testNumber" />
    </div>
</div>