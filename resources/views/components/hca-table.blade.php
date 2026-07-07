@props([
    'headers' => []
])

<div class="overflow-x-auto border-t border-warm-border">
    <table {{ $attributes->merge(['class' => 'w-full border-collapse text-left text-sm']) }}>
        <thead>
            <tr class="bg-warm-ivory border-b border-warm-border text-slate-400 font-bold uppercase tracking-wider text-[10px] md:text-xs">
                @foreach($headers as $header)
                    <th 
                        class="py-3 px-4 {{ $header['class'] ?? '' }}"
                        @isset($header['style']) style="{{ $header['style'] }}" @endisset
                    >
                        {{ $header['label'] }}
                    </th>
                @endforeach
            </tr>
        </thead>
        <tbody class="divide-y divide-warm-border font-medium text-slate-700">
            {{ $slot }}
        </tbody>
    </table>
</div>
