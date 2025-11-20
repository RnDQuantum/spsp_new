<div>
    <div class="bg-white dark:bg-gray-900 mx-auto my-8 shadow-lg dark:shadow-gray-800/50 overflow-hidden"
        style="max-width: 1200px;">
        <!-- Header -->
        <div class="border-b-4 border-black dark:border-gray-300 py-3 bg-gray-300 dark:bg-gray-600">
            <h1 class="text-center text-lg font-bold uppercase tracking-wide text-gray-900 dark:text-gray-100">
                Custom Standards
            </h1>
            <p class="text-center text-sm text-gray-700 dark:text-gray-300 mt-1">
                Kelola standar penilaian kustom institusi Anda
            </p>
        </div>

        <!-- Content -->
        <div class="p-6">
            <!-- Action Bar -->
            <div class="flex justify-between items-center mb-6">
                <div class="text-sm text-gray-600 dark:text-gray-400">
                    Total: {{ $customStandards->count() }} standar
                </div>
                <a href="{{ route('custom-standards.create') }}"
                    class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-md transition-colors">
                    + Buat Standar Baru
                </a>
            </div>

            @if ($customStandards->isEmpty())
            <!-- Empty State -->
            <div class="text-center py-12 bg-gray-50 dark:bg-gray-800 rounded-lg">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">Belum ada Custom Standard</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Mulai buat standar penilaian kustom untuk institusi Anda.
                </p>
                <div class="mt-6">
                    <a href="{{ route('custom-standards.create') }}"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-md transition-colors">
                        + Buat Standar Baru
                    </a>
                </div>
            </div>
            @else
            <!-- Standards List -->
            <div class="space-y-4">
                @foreach ($customStandards as $standard)
                <div
                    class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <div class="flex items-center gap-2">
                                <span
                                    class="text-xs font-mono bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 px-2 py-1 rounded">
                                    {{ $standard->code }}
                                </span>
                                <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">
                                    {{ $standard->name }}
                                </h3>
                            </div>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                Standar: {{ $standard->template->name }}
                            </p>
                            @if ($standard->description)
                            <p class="text-sm text-gray-600 dark:text-gray-300 mt-2">
                                {{ $standard->description }}
                            </p>
                            @endif
                            <p class="text-xs text-gray-400 dark:text-gray-500 mt-2">
                                Dibuat oleh {{ $standard->creator?->name ?? 'Unknown' }} •
                                {{ $standard->created_at->format('d M Y') }}
                            </p>
                        </div>
                        <div class="flex items-center gap-2 ml-4">
                            <a href="{{ route('custom-standards.edit', $standard->id) }}"
                                class="px-3 py-1.5 text-sm bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 rounded transition-colors">
                                Edit
                            </a>
                            @if ($deleteId === $standard->id)
                            <div class="flex items-center gap-1">
                                <button wire:click="delete"
                                    class="px-3 py-1.5 text-sm bg-red-600 hover:bg-red-700 text-white rounded transition-colors">
                                    Hapus
                                </button>
                                <button wire:click="cancelDelete"
                                    class="px-3 py-1.5 text-sm bg-gray-300 hover:bg-gray-400 dark:bg-gray-600 dark:hover:bg-gray-500 text-gray-700 dark:text-gray-200 rounded transition-colors">
                                    Batal
                                </button>
                            </div>
                            @else
                            <button wire:click="confirmDelete({{ $standard->id }})"
                                class="px-3 py-1.5 text-sm bg-red-100 hover:bg-red-200 dark:bg-red-900/30 dark:hover:bg-red-900/50 text-red-600 dark:text-red-400 rounded transition-colors">
                                Hapus
                            </button>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>
</div>