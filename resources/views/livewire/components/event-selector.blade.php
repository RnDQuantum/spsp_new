<div class="w-full">
    @if ($showLabel)
        <label class="block text-sm font-medium text-gray-700 mb-2">
            Event Assessment
        </label>
    @endif

    <x-mary-choices-offline
        wire:model.live="eventCode"
        :options="$availableEvents"
        option-value="code"
        option-label="name"
        placeholder="Cari event..."
        single
        searchable
    />
</div>
