<div
    class="max-w-7xl mx-auto mt-10 bg-white dark:bg-gray-900 p-6 rounded-lg shadow-lg text-gray-900 dark:text-gray-100">

    <!-- Tolerance Selector Component -->
    @if ($selectedEvent && $selectedAspect)
    @php
    $summary = $this->getPassingSummary();
    @endphp
    @livewire('components.tolerance-selector', [
    'passing' => $summary['passing'],
    'total' => $summary['total'],
    'showSummary' => false,
    ])
    @endif

    <!-- Event, Position, and Aspect Selection -->
    <div class="mb-6 bg-gray-50 dark:bg-gray-800 p-4 rounded-lg border border-gray-200 dark:border-gray-600">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Event Filter -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                    📅 Pilih Event Assessment
                </label>
                @livewire('components.event-selector', ['showLabel' => false])
            </div>

            <!-- Position Filter -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                    💼 Pilih Jabatan
                </label>
                @livewire('components.position-selector', ['showLabel' => false])
            </div>

            <!-- Aspect Filter -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                    🎯 Pilih Aspek untuk Analisis Training
                </label>
                @livewire('components.aspect-selector', ['showLabel' => false])
            </div>
        </div>
    </div>

    @if ($selectedEvent && $selectedAspect)
    <!-- SECTION 1: Summary -->
    <table class="w-full border border-black dark:border-gray-600 text-gray-900 dark:text-gray-100 text-sm">
        <tr>
            <td class="border border-black dark:border-gray-600 font-semibold px-4 py-2 bg-white dark:bg-gray-800"
                colspan="3">
                {{ $selectedEvent->name }}
            </td>
            <td class="border border-black dark:border-gray-600 px-4 py-2 bg-blue-50 dark:bg-blue-900 text-right">
                <span
                    class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-blue-100 dark:bg-blue-800 text-blue-800 dark:text-blue-200">
                    Rating Rata-rata: {{ number_format($averageRating, 2, ',', '.') }}
                </span>
            </td>
        </tr>
        <tr>
            <td
                class="border border-black dark:border-gray-600 px-4 py-2 bg-red-50 dark:bg-red-900 font-semibold text-red-800 dark:text-red-400">
                Recommended</td>
            <td
                class="border border-black dark:border-gray-600 px-4 py-2 bg-red-50 dark:bg-red-900 text-center w-20 font-bold text-red-900 dark:text-red-400">
                {{ $recommendedCount }}</td>
            <td
                class="border border-black dark:border-gray-600 px-4 py-2 bg-red-50 dark:bg-red-900 text-right w-24 font-bold text-red-900 dark:text-red-400">
                {{ number_format($this->recommendedPercentage, 2, ',', '.') }}%</td>
            <td class="border border-black dark:border-gray-600 px-4 py-2 bg-white dark:bg-gray-800" rowspan="2">
            </td>
        </tr>
        <tr>
            <td
                class="border border-black dark:border-gray-600 px-4 py-2 bg-green-50 dark:bg-green-900 font-semibold text-green-800 dark:text-green-400">
                Not Recommended</td>
            <td
                class="border border-black dark:border-gray-600 px-4 py-2 bg-green-50 dark:bg-green-900 text-center font-bold text-green-900 dark:text-green-400">
                {{ $notRecommendedCount }}</td>
            <td
                class="border border-black dark:border-gray-600 px-4 py-2 bg-green-50 dark:bg-green-900 text-right font-bold text-green-900 dark:text-green-400">
                {{ number_format($this->notRecommendedPercentage, 2, ',', '.') }}%</td>
        </tr>
    </table>

    <!-- SECTION 2: Selected Aspect Info -->
    <table class="w-full border border-black dark:border-gray-600 text-gray-900 dark:text-gray-100 text-sm mt-4">
        <tr>
            <td class="border border-black dark:border-gray-600 font-normal px-4 py-2 align-middle bg-white dark:bg-gray-800"
                colspan="2">
                Training recommended : <span class="font-bold underline ml-2">{{ $selectedAspect->name }}</span>
            </td>
            <td
                class="border border-black dark:border-gray-600 px-4 py-2 text-right bg-yellow-50 dark:bg-yellow-900 align-middle">
                <div class="inline-flex items-center gap-2">
                    <span
                        class="px-3 py-1 rounded-full text-sm font-semibold bg-yellow-200 dark:bg-yellow-800 text-yellow-900 dark:text-yellow-200">
                        Std. Rating: {{ number_format($originalStandardRating, 2, ',', '.') }}
                    </span>
                    <span x-show="$wire.tolerancePercentage > 0" class="text-xs text-gray-600 dark:text-gray-400">
                        → <span class="font-bold text-yellow-800 dark:text-yellow-400">{{ number_format($standardRating,
                            2, ',', '.') }}</span>
                        <span class="text-xs italic">(toleransi <span
                                x-text="$wire.tolerancePercentage"></span>%)</span>
                    </span>
                </div>
            </td>
        </tr>
    </table>

    <div class="mt-4 flex flex-wrap items-center justify-between gap-3 text-sm">
        <div class="flex items-center gap-2 mb-3">
            <span class="text-gray-700 dark:text-gray-300">Show:</span>
            <select wire:model.live="perPage"
                class="px-2 py-1 text-sm border rounded dark:bg-gray-800 dark:border-gray-600">
                <option value="10">10</option>
                <option value="25">25</option>
                <option value="50">50</option>
                <option value="100">100</option>
                <option value="all">All</option>
            </select>
        </div>
    </div>

    <!-- TABLE DATA: Participants -->
    <div class="overflow-x-auto mt-4 relative">
        <!-- Loading Indicator - Inside Container -->
        <div wire:loading wire:target="eventCode,aspectId,handleToleranceUpdate"
            class="absolute inset-0 bg-white dark:bg-gray-900 bg-opacity-75 flex items-center justify-center z-10 rounded-lg">
            <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow-lg border border-gray-200 dark:border-gray-600">
                <div class="flex items-center space-x-3">
                    <svg class="animate-spin h-6 w-6 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                        </circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>
                    </svg>
                    <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">Memuat data...</span>
                </div>
            </div>
        </div>

        <table
            class="w-full border border-black dark:border-gray-600 text-sm text-gray-900 dark:text-gray-100 bg-white dark:bg-gray-800">
            <thead>
                <tr class="bg-gray-200 dark:bg-gray-700">
                    <th class="border border-black dark:border-gray-600 px-3 py-2 text-center">Priority</th>
                    <th class="border border-black dark:border-gray-600 px-3 py-2 text-center">No. Test</th>
                    <th class="border border-black dark:border-gray-600 px-3 py-2 text-center">Nama</th>
                    <th class="border border-black dark:border-gray-600 px-3 py-2 text-center">Jabatan</th>
                    <th class="border border-black dark:border-gray-600 px-3 py-2 text-center">Rating</th>
                    <th class="border border-black dark:border-gray-600 px-3 py-2 text-center">Statement</th>
                </tr>
            </thead>
            <tbody>
                @if ($participants && count($participants) > 0)
                @foreach ($participants as $participant)
                <tr class="bg-white dark:bg-gray-800">
                    <td class="border border-black dark:border-gray-600 px-3 py-2 text-center">
                        {{ $participant['priority'] }}
                    </td>
                    <td class="border border-black dark:border-gray-600 px-3 py-2 text-center">
                        {{ $participant['test_number'] }}
                    </td>
                    <td class="border border-black dark:border-gray-600 px-3 py-2">
                        {{ $participant['name'] }}</td>
                    <td class="border border-black dark:border-gray-600 px-3 py-2">
                        {{ $participant['position'] }}</td>
                    <td
                        class="border border-black dark:border-gray-600 px-3 py-2 text-center font-semibold {{ $participant['is_recommended'] ? 'text-red-600 dark:text-red-400' : '' }}">
                        {{ number_format($participant['rating'], 2, ',', '.') }}
                    </td>
                    <td
                        class="border border-black dark:border-gray-600 px-3 py-2 text-center {{ $participant['is_recommended'] ? 'bg-red-700 dark:bg-red-800 text-red-100 dark:text-red-300 font-semibold' : 'bg-green-700 dark:bg-green-800 font-semibold text-green-100 dark:text-green-300' }}">
                        {{ $participant['statement'] }}
                    </td>
                </tr>
                @endforeach
                @else
                <tr>
                    <td colspan="7"
                        class="border border-black dark:border-gray-600 px-3 py-4 text-center text-gray-500 dark:text-gray-400">
                        Tidak ada data peserta untuk aspek ini
                    </td>
                </tr>
                @endif
            </tbody>
        </table>

        <!-- Pagination -->
        @if ($participants && $participants->hasPages())
        <div class="mt-4">
            {{ $participants->links(data: ['scrollTo' => false]) }}
        </div>
        @endif
    </div>

    <!-- Legend -->
    <div class="mt-4 p-4 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-600">
        <h4 class="font-semibold text-gray-700 dark:text-gray-300 mb-2">Keterangan:</h4>
        <ul class="list-disc list-inside text-sm text-gray-600 dark:text-gray-400 space-y-1">
            <li><strong>Statement:</strong>
                <ul class="ml-6 mt-1 space-y-1">
                    <li><span
                            class="px-2 py-1 rounded bg-red-100 dark:bg-red-800 text-red-700 dark:text-red-300 font-semibold">Recommended</span>:
                        Peserta dengan rating di bawah standar, direkomendasikan untuk mengikuti
                        pelatihan</li>
                    <li><span
                            class="px-2 py-1 rounded bg-green-100 dark:bg-green-800 text-green-700 dark:text-green-300 font-semibold">Not
                            Recommended</span>:
                        Peserta dengan rating memenuhi atau melebihi standar</li>
                </ul>
            </li>
            <li><strong>Priority:</strong> Urutan berdasarkan rating terendah (prioritas tertinggi)</li>
        </ul>
    </div>
    @else
    <!-- Empty State -->
    <div class="text-center py-12">
        <div class="text-6xl mb-4">📊</div>
        <h3 class="text-xl font-semibold text-gray-700 dark:text-gray-300 mb-2">Training Recommendation Analysis
        </h3>
        <p class="text-gray-500 dark:text-gray-400">Silakan pilih Event dan Aspek untuk melihat rekomendasi training
        </p>
    </div>
    @endif

    @if ($selectedEvent && $aspectPriorities && $aspectPriorities->isNotEmpty())
    <div class="max-w-7xl mx-auto mt-10 bg-white dark:bg-gray-900 p-6 rounded-lg shadow-lg">
        <!-- Header Section -->
        <div class="border-b-4 border-blue-800 dark:border-blue-600 pb-3 mb-6">
            <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100 uppercase">
                PRIORITAS PERBAIKAN ATRIBUT MAPPING
            </h1>
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ $selectedEvent->name }}
                ({{ $selectedEvent->year }})</p>
        </div>

        <!-- Table Section -->
        <div class="overflow-x-auto">
            <table
                class="w-full border-2 border-gray-900 dark:border-gray-600 text-sm text-gray-900 dark:text-gray-100">
                <thead>
                    <tr class="bg-cyan-100 dark:bg-cyan-900">
                        <th class="border-2 border-gray-900 dark:border-gray-600 px-4 py-3 text-center font-bold">
                            PRIORITAS</th>
                        <th class="border-2 border-gray-900 dark:border-gray-600 px-4 py-3 text-center font-bold">
                            ATRIBUT</th>
                        <th class="border-2 border-gray-900 dark:border-gray-600 px-4 py-3 text-center font-bold">
                            STD RATING</th>
                        <th class="border-2 border-gray-900 dark:border-gray-600 px-4 py-3 text-center font-bold">
                            RATA-2 RATING</th>
                        <th class="border-2 border-gray-900 dark:border-gray-600 px-4 py-3 text-center font-bold">
                            GAP</th>
                        <th class="border-2 border-gray-900 dark:border-gray-600 px-4 py-3 text-center font-bold">
                            TINDAK LANJUT</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800">
                    @foreach ($aspectPriorities as $priority)
                    <tr class="bg-white dark:bg-gray-800">
                        <td class="border-2 border-gray-900 dark:border-gray-600 px-4 py-2 text-center font-semibold">
                            {{ $priority['priority'] }}
                        </td>
                        <td class="border-2 border-gray-900 dark:border-gray-600 px-4 py-2">
                            {{ $priority['aspect_name'] }}</td>
                        <td class="border-2 border-gray-900 dark:border-gray-600 px-4 py-2 text-center">
                            {{ number_format($priority['adjusted_standard_rating'], 2, ',', '.') }}
                        </td>
                        <td class="border-2 border-gray-900 dark:border-gray-600 px-4 py-2 text-center">
                            {{ number_format($priority['average_rating'], 2, ',', '.') }}
                        </td>
                        <td
                            class="border-2 border-gray-900 dark:border-gray-600 px-4 py-2 text-center font-bold {{ $priority['gap'] < 0 ? 'text-red-700 dark:text-red-400' : 'text-green-700 dark:text-green-400' }}">
                            {{ number_format($priority['gap'], 2, ',', '.') }}
                        </td>
                        <td
                            class="border-2 border-gray-900 dark:border-gray-600 px-4 py-2 text-center {{ $priority['action'] === 'Pelatihan' ? 'bg-red-700 dark:bg-red-800 text-red-100 dark:text-red-300 font-semibold' : 'bg-green-700 dark:bg-green-800 text-green-100 dark:text-green-300 font-semibold' }}">
                            {{ $priority['action'] }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Summary Info -->
        <div class="mt-4 p-4 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-600">
            <h4 class="font-semibold text-gray-700 dark:text-gray-300 mb-2">Keterangan:</h4>
            <ul class="list-disc list-inside text-sm text-gray-600 dark:text-gray-400 space-y-1">
                <li><strong>Gap:</strong> Selisih antara rata-rata rating dengan standar rating (Rata-rata -
                    Standar)
                </li>
                <li><strong>Tindak Lanjut:</strong>
                    <ul class="ml-6 mt-1 space-y-1">
                        <li><span
                                class="px-2 py-1 rounded bg-red-100 dark:bg-red-800 text-red-700 dark:text-red-300 font-semibold">Pelatihan</span>:
                            Gap negatif, perlu pelatihan untuk meningkatkan kompetensi</li>
                        <li><span
                                class="px-2 py-1 rounded bg-green-100 dark:bg-green-800 text-green-700 dark:text-green-300 font-semibold">Dipertahankan</span>:
                            Gap positif atau nol, kompetensi sudah memenuhi atau melebihi standar</li>
                    </ul>
                </li>
                <li><strong>Prioritas:</strong> Diurutkan berdasarkan gap terkecil (paling negatif) sebagai
                    prioritas
                    tertinggi</li>
            </ul>
        </div>
    </div>
    @endif

</div>