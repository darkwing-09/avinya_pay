{{-- GoCrazy Fullscreen 3D Neon Tubes Background (Runs across 100% of the screen) --}}
<div aria-hidden="true" class="pointer-events-none fixed inset-0 -z-10 w-screen h-screen overflow-hidden bg-[#07070a]">
    {{-- Static Ambient Radial Glow --}}
    <div class="absolute inset-0 pointer-events-none" style="background: radial-gradient(70% 60% at 50% 30%, rgba(56, 189, 248, 0.22), transparent 75%);"></div>
    
    {{-- Secondary bottom-corner ambient glow --}}
    <div class="absolute inset-0 pointer-events-none" style="background: radial-gradient(50% 50% at 85% 85%, rgba(56, 189, 248, 0.12), transparent 70%);"></div>

    {{-- Interactive 3D WebGL Neon Tubes Canvas --}}
    <canvas id="gocrazy-tubes-canvas" class="absolute inset-0 block h-full w-full pointer-events-none transition-opacity duration-700 ease-out" style="opacity: 0.9; touch-action: none;"></canvas>
</div>
