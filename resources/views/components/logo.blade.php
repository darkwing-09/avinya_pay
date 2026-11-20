@props(['dark' => false])
<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-2.5']) }}>
    <span class="grid place-items-center w-8 h-8 rounded-lg bg-[#38bdf8] text-[#041419] shadow-sm shadow-[#38bdf8]/40">
        <svg viewBox="0 0 24 24" class="w-[18px] h-[18px]" fill="currentColor"><path d="M13.5 2 4 14h6.5L9.5 22 19 10h-6.5z"/></svg>
    </span>
    <span class="font-bold tracking-tight text-[17px] text-[#fafafc]">{{ config('app.name') }}</span>
</span>
