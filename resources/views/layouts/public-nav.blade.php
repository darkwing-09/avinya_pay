<header class="sticky top-0 z-30 bg-[#07070a]/85 backdrop-blur-md border-b border-white/10 transition-shadow" id="site-header">
    <div class="mx-auto max-w-6xl px-4 sm:px-6 h-16 flex items-center justify-between">
        <a href="{{ route('home') }}"><x-logo /></a>
        <nav class="hidden md:flex items-center gap-8 text-sm font-medium text-slate-300">
            <a href="{{ route('home') }}#features" class="hover:text-[#38bdf8] transition-colors">Features</a>
            <a href="{{ route('home') }}#how" class="hover:text-[#38bdf8] transition-colors">How it works</a>
            <a href="{{ route('home') }}#pricing" class="hover:text-[#38bdf8] transition-colors">Pricing</a>
            <a href="{{ route('docs') }}" class="hover:text-[#38bdf8] transition-colors">Developers</a>
        </nav>
        <div class="flex items-center gap-3">
            @auth
                <a href="{{ route('dashboard') }}" class="btn btn-primary">Dashboard <x-icon name="arrow-right" class="w-4 h-4" /></a>
            @else
                <a href="{{ route('login') }}" class="btn btn-ghost hidden sm:inline-flex text-slate-300 hover:text-white">Log in</a>
                @if (config('auth.allow_registration', false))
                    <a href="{{ route('register') }}" class="btn btn-primary">Get started <x-icon name="arrow-right" class="w-4 h-4" /></a>
                @endif
            @endauth
        </div>
    </div>
</header>
<script nonce="{{ $csp_nonce }}">
    const hdr = document.getElementById('site-header');
    window.addEventListener('scroll', () => hdr.classList.toggle('shadow-lg', window.scrollY > 10), { passive: true });
</script>
