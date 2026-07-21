<div class="w-full">
    <div class="flex items-center gap-3">
        @if ($showLabel)
            <label class="text-sm font-bold font-mono-data uppercase tracking-wider text-primary-ink/80 dark:text-neutral-300 whitespace-nowrap">
                Pilih Proyek :
            </label>
        @endif

        <div class="flex-1">
            <x-mary-choices-offline wire:model.live="eventCode" :options="$availableEvents" option-value="code" option-label="name"
                placeholder="Cari event/kegiatan..." single searchable
                class="border-warm-border dark:border-[#25211e] bg-white dark:bg-[#171412] text-primary-ink dark:text-neutral-100 text-sm">
                {{-- Custom item slot untuk list dropdown --}}
                @scope('item', $event)
                    <div
                        @click="if(window.showLoadingOverlay) window.showLoadingOverlay()"
                        class="p-2.5 font-mono-data text-sm hover:bg-warm-ivory dark:hover:bg-[#1f1b18] cursor-pointer transition-colors
                        {{ $this->eventCode == $event['code'] ? 'bg-warm-ivory dark:bg-[#1f1b18] font-bold text-accent-amber border-l-2 border-accent-amber' : 'text-primary-ink dark:text-neutral-100' }}">
                        {{ $event['name'] }}
                    </div>
                @endscope

                {{-- Custom selection slot --}}
                @scope('selection', $event)
                    <span class="font-bold font-mono-data text-primary-ink dark:text-neutral-100 text-sm">{{ $event['name'] }}</span>
                @endscope
            </x-mary-choices-offline>
        </div>
    </div>
</div>
