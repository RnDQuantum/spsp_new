<div class="w-full">
    @if ($showLabel)
        <label class="block text-sm font-medium text-gray-700 mb-2">
            Jabatan
        </label>
    @endif

    <x-mary-choices-offline
        wire:model.live="positionFormationId"
        :options="$availablePositions"
        option-value="id"
        option-label="name"
        placeholder="Pilih jabatan..."
        single
        searchable
    />
</div>
