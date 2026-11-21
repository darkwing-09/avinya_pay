@props(['dark' => false])
<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-2.5']) }}>
    <span class="grid place-items-center w-8 h-8 rounded-lg bg-white text-black shadow-sm shadow-white/20">
        <svg viewBox="0 0 24 24" class="w-5 h-5 text-black" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round">
            <path d="M12 12c-2-2.67-4-4-6-4a4 4 0 1 0 0 8c2 0 4-1.33 6-4Zm0 0c2 2.67 4 4 6 4a4 4 0 0 0 0-8c-2 0-4 1.33-6 4Z"/>
        </svg>
    </span>
    <span class="font-bold tracking-tight text-[17px] text-[#fafafc]">Avinya<span class="text-[#38bdf8]">Pay</span></span>
</span>
