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
            :isStandalone="false" :showHeader="false" :showInfoSection="false" :showPotensi="true"
            :showKompetensi="false" :key="'potensi-only-' . $eventCode . '-' . $testNumber" />
    </div>
    <div class="mb-8">
        <livewire:pages.individual-report.general-matching :eventCode="$eventCode" :testNumber="$testNumber"
            :isStandalone="false" :showHeader="false" :showInfoSection="false" :showPotensi="false"
            :showKompetensi="true" :key="'potensi-only-' . $eventCode . '-' . $testNumber" />
    </div>

    {{-- Interpretation Section --}}
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">Interpretasi Hasil Assessment</h2>
        <livewire:pages.individual-report.interpretation-section :eventCode="$eventCode" :testNumber="$testNumber"
            :isStandalone="false" :showHeader="false" :showPotensi="true" :showKompetensi="true"
            :key="'interpretation-' . $eventCode . '-' . $testNumber" />
    </div>
</div>