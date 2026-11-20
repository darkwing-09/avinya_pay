{{-- GoCrazy 3D Neon Tube & Ambient Background (Matches GoCrazy's Background.tsx exactly) --}}
<div aria-hidden="true" class="pointer-events-none fixed inset-0 -z-10 overflow-hidden" style="mask-image: linear-gradient(to bottom, #000 60%, transparent 100%); -webkit-mask-image: linear-gradient(to bottom, #000 60%, transparent 100%);">
    {{-- Static Ambient Radial Glow --}}
    <div class="absolute inset-0" style="background: radial-gradient(60% 55% at 50% 30%, rgba(56, 189, 248, 0.18), transparent 70%);"></div>
    
    {{-- Secondary bottom-corner ambient glow --}}
    <div class="absolute inset-0" style="background: radial-gradient(40% 40% at 85% 85%, rgba(56, 189, 248, 0.08), transparent 70%);"></div>

    {{-- Interactive 3D WebGL Neon Tubes Canvas --}}
    <canvas id="gocrazy-tubes-canvas" class="absolute inset-0 block h-full w-full transition-opacity duration-1000 ease-out" style="opacity: 0; touch-action: none;"></canvas>

    {{-- Center readability layer (keeps text & checkout form legible over the brightest tubes) --}}
    <div class="absolute inset-0" style="background: radial-gradient(55% 45% at 50% 40%, rgba(7, 7, 10, 0.65), transparent 100%);"></div>
</div>
