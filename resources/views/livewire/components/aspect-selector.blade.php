<div class="w-full">
    <div class="flex items-center gap-3">
        @if ($showLabel)
            <label class="text-sm font-bold font-mono-data uppercase tracking-wider text-primary-ink/80 dark:text-neutral-300 whitespace-nowrap">
                Pilih Aspek :
            </label>
        @endif

        <div class="flex-1">
            <x-mary-choices-offline wire:model.live="aspectId" :options="$availableAspects" option-value="id"
                placeholder="Cari aspek..." single searchable
                class="border-warm-border dark:border-[#25211e] bg-white dark:bg-[#171412] text-primary-ink dark:text-neutral-100 text-sm">
                {{-- Custom item slot untuk list dropdown dengan badge kategori --}}
                @scope('item', $aspect)
                    <div class="flex items-center gap-2.5 p-2.5 font-mono-data text-sm hover:bg-warm-ivory dark:hover:bg-[#1f1b18] cursor-pointer transition-colors text-primary-ink dark:text-neutral-100">
                        <span class="px-2 py-0.5 rounded text-xs font-bold border border-warm-border dark:border-[#25211e] bg-warm-ivory dark:bg-[#1f1b18] text-accent-amber">
                            {{ strtoupper($aspect['category']) }}
                        </span>
                        <span class="font-semibold text-primary-ink dark:text-neutral-100 text-sm">{{ $aspect['name'] }}</span>
                    </div>
                @endscope

                {{-- Custom selection slot --}}
                @scope('selection', $aspect)
                    <div class="flex items-center gap-2 font-mono-data">
                        <span class="px-2 py-0.5 rounded text-xs font-bold border border-warm-border dark:border-[#25211e] bg-warm-ivory dark:bg-[#1f1b18] text-accent-amber">
                            {{ strtoupper($aspect['category']) }}
                        </span>
                        <span class="font-bold text-primary-ink dark:text-neutral-100 text-sm">{{ $aspect['name'] }}</span>
                    </div>
                @endscope
            </x-mary-choices-offline>
        </div>
    </div>
</div>
