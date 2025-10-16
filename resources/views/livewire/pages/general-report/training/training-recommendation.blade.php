<div class="max-w-7xl mx-auto mt-10 bg-white p-6 rounded-lg shadow-lg text-gray-900">

    <!-- Tolerance Selector Component -->
    @if ($selectedEvent && $selectedAspect)
        @php
            $summary = $this->getPassingSummary();
        @endphp
        @livewire('components.tolerance-selector', [
            'passing' => $summary['passing'],
            'total' => $summary['total'],
            'showSummary' => true,
        ])
    @endif

    <!-- Event and Aspect Selection -->
    <div class="mb-6 bg-gray-50 p-4 rounded-lg border border-gray-200">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Event Dropdown -->
            <div>
                <label for="eventSelect" class="block text-sm font-semibold text-gray-700 mb-2">
                    📅 Pilih Event Assessment
                </label>
                <select wire:model.live="eventCode" id="eventSelect"
                    class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-white text-gray-900 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    <option value="">-- Pilih Event --</option>
                    @foreach ($this->events as $event)
                        <option value="{{ $event->code }}">{{ $event->name }} ({{ $event->year }})</option>
                    @endforeach
                </select>
            </div>

            <!-- Aspect Dropdown -->
            <div>
                <label for="aspectSelect" class="block text-sm font-semibold text-gray-700 mb-2">
                    🎯 Pilih Aspek untuk Analisis Training
                </label>
                <select wire:model.live="aspectId" id="aspectSelect"
                    class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-white text-gray-900 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                    @if (!$selectedEvent) disabled @endif>
                    <option value="">-- Pilih Aspek --</option>
                    @foreach ($this->aspects as $aspect)
                        <option value="{{ $aspect->id }}">
                            {{ $aspect->categoryType->name }} - {{ $aspect->name }}
                        </option>
                    @endforeach
                </select>
                @if (!$selectedEvent)
                    <p class="text-xs text-gray-500 mt-1">Pilih event terlebih dahulu</p>
                @endif
            </div>
        </div>
    </div>

    @if ($selectedEvent && $selectedAspect)
        <!-- SECTION 1: Summary -->
        <table class="w-full border border-black text-gray-900 text-sm">
            <tr>
                <td class="border border-black font-bold text-center bg-gray-200 w-16 px-2 py-2" rowspan="3">1</td>
                <td class="border border-black font-semibold px-4 py-2 bg-white" colspan="3">
                    {{ $selectedEvent->name }}
                </td>
                <td class="border border-black px-4 py-2 bg-blue-50 text-right">
                    <span
                        class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-blue-100 text-blue-800">
                        Rating Rata-rata: {{ number_format($averageRating, 2, ',', '.') }}
                    </span>
                </td>
            </tr>
            <tr>
                <td class="border border-black px-4 py-2 bg-red-50 font-semibold text-red-800">Recommended</td>
                <td class="border border-black px-4 py-2 bg-red-50 text-center w-20 font-bold text-red-900">
                    {{ $recommendedCount }}</td>
                <td class="border border-black px-4 py-2 bg-red-50 text-right w-24 font-bold text-red-900">
                    {{ number_format($this->recommendedPercentage, 2, ',', '.') }}%</td>
                <td class="border border-black px-4 py-2 bg-white" rowspan="2"></td>
            </tr>
            <tr>
                <td class="border border-black px-4 py-2 bg-green-50 font-semibold text-green-800">Not Recommended</td>
                <td class="border border-black px-4 py-2 bg-green-50 text-center font-bold text-green-900">
                    {{ $notRecommendedCount }}</td>
                <td class="border border-black px-4 py-2 bg-green-50 text-right font-bold text-green-900">
                    {{ number_format($this->notRecommendedPercentage, 2, ',', '.') }}%</td>
            </tr>
        </table>

        <!-- SECTION 2: Selected Aspect Info -->
        <table class="w-full border border-black text-gray-900 text-sm mt-4">
            <tr>
                <td class="border border-black font-bold text-center bg-gray-200 w-16 px-2 py-2 align-middle">
                    2
                </td>
                <td class="border border-black font-normal px-4 py-2 align-middle bg-white" colspan="2">
                    Training recommended : <span class="font-bold underline ml-2">{{ $selectedAspect->name }}</span>
                </td>
                <td class="border border-black px-4 py-2 text-right bg-yellow-50 align-middle">
                    <div class="inline-flex items-center gap-2">
                        <span class="px-3 py-1 rounded-full text-sm font-semibold bg-yellow-200 text-yellow-900">
                            Std. Rating: {{ number_format($selectedAspect->standard_rating, 2, ',', '.') }}
                        </span>
                        <span x-show="$wire.tolerancePercentage > 0" class="text-xs text-gray-600">
                            → <span
                                class="font-bold text-yellow-800">{{ number_format($standardRating, 2, ',', '.') }}</span>
                            <span class="text-xs italic">(toleransi <span
                                    x-text="$wire.tolerancePercentage"></span>%)</span>
                        </span>
                    </div>
                </td>
            </tr>
        </table>

        <!-- TABLE DATA: Participants -->
        <div class="overflow-x-auto mt-4 relative">
            <!-- Loading Indicator - Inside Container -->
            <div wire:loading wire:target="eventCode,aspectId,handleToleranceUpdate"
                class="absolute inset-0 bg-white bg-opacity-75 flex items-center justify-center z-10 rounded-lg">
                <div class="bg-white rounded-lg p-4 shadow-lg border border-gray-200">
                    <div class="flex items-center space-x-3">
                        <svg class="animate-spin h-6 w-6 text-blue-600" xmlns="http://www.w3.org/2000/svg"
                            fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                        <span class="text-sm font-semibold text-gray-700">Memuat data...</span>
                    </div>
                </div>
            </div>

            <table class="w-full border border-black text-sm text-gray-900 bg-white">
                <thead>
                    <tr class="bg-gray-200">
                        <th class="border border-black px-3 py-2 text-center">Priority</th>
                        <th class="border border-black px-3 py-2 text-center">No. Test</th>
                        <th class="border border-black px-3 py-2 text-center">Nama</th>
                        <th class="border border-black px-3 py-2 text-center">Jabatan</th>
                        <th class="border border-black px-3 py-2 text-center">Rating</th>
                        <th class="border border-black px-3 py-2 text-center">Statement</th>
                    </tr>
                </thead>
                <tbody>
                    @if ($participants && count($participants) > 0)
                        @foreach ($participants as $participant)
                            <tr class="{{ $participant['is_recommended'] ? 'bg-red-50' : '' }}">
                                <td class="border border-black px-3 py-2 text-center">{{ $participant['priority'] }}
                                </td>
                                <td class="border border-black px-3 py-2 text-center">{{ $participant['test_number'] }}
                                </td>
                                <td class="border border-black px-3 py-2">{{ $participant['name'] }}</td>
                                <td class="border border-black px-3 py-2">{{ $participant['position'] }}</td>
                                <td
                                    class="border border-black px-3 py-2 text-center font-semibold {{ $participant['is_recommended'] ? 'text-red-600' : '' }}">
                                    {{ number_format($participant['rating'], 2, ',', '.') }}
                                </td>
                                <td class="border border-black px-3 py-2 text-center">
                                    <span
                                        class="px-2 py-1 rounded {{ $participant['is_recommended'] ? 'bg-red-100 text-red-700 font-semibold' : 'bg-green-100 font-semibold text-green-700' }}">
                                        {{ $participant['statement'] }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="7" class="border border-black px-3 py-4 text-center text-gray-500">
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
        <div class="mt-4 p-4 bg-gray-50 rounded-lg border border-gray-200">
            <h4 class="font-semibold text-gray-700 mb-2">Keterangan:</h4>
            <ul class="list-disc list-inside text-sm text-gray-600 space-y-1">
                <li><strong>Statement:</strong>
                    <ul class="ml-6 mt-1 space-y-1">
                        <li><span class="px-2 py-1 rounded bg-red-100 text-red-700 font-semibold">Recommended:</span>
                            Peserta dengan rating di bawah standar, direkomendasikan untuk mengikuti
                            pelatihan</li>
                        <li><span class="px-2 py-1 rounded bg-green-100 text-green-700 font-semibold">Not
                                Recommended:</span>
                            Recommended:</strong> Peserta dengan rating memenuhi atau melebihi standar</li>
                    </ul>
                </li>
                <li><strong>Priority:</strong> Urutan berdasarkan rating terendah (prioritas tertinggi)</li>
            </ul>
        </div>
    @else
        <!-- Empty State -->
        <div class="text-center py-12">
            <div class="text-6xl mb-4">📊</div>
            <h3 class="text-xl font-semibold text-gray-700 mb-2">Training Recommendation Analysis</h3>
            <p class="text-gray-500">Silakan pilih Event dan Aspek untuk melihat rekomendasi training</p>
        </div>
    @endif

</div>
