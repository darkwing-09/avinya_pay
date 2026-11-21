@extends('layouts.base')

@section('body')
<div class="min-h-full flex flex-col justify-between bg-transparent">
    <div class="flex-1 grid lg:grid-cols-2 bg-transparent">
        {{-- Brand story panel --}}
        <div class="relative hidden lg:flex flex-col justify-between overflow-hidden bg-[#0a0a0f]/85 backdrop-blur-xl p-12 text-white border-r border-white/10">
            <div class="absolute inset-0 opacity-40 pointer-events-none" style="background: radial-gradient(600px circle at 20% 20%, rgba(56, 189, 248, 0.25), transparent 60%), radial-gradient(500px circle at 90% 80%, rgba(56, 189, 248, 0.15), transparent 60%);"></div>
            <div class="absolute inset-0 opacity-[0.05] pointer-events-none" style="background-image: linear-gradient(white 1px, transparent 1px), linear-gradient(90deg, white 1px, transparent 1px); background-size: 40px 40px;"></div>

            <a href="{{ route('home') }}" class="relative"><x-logo dark /></a>

            <div class="relative max-w-lg my-auto py-8">
                <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight leading-tight text-white">Want to build your own custom payment gateway?</h1>
                <p class="mt-4 text-slate-300 text-sm sm:text-base leading-relaxed">India’s first custom payment gateway for developers and small businesses. Set up your own UPI checkout, take payments straight into your bank account and know the moment an order is paid.</p>
                
                <div class="mt-8 space-y-4 text-xs sm:text-sm text-slate-300">
                    <div class="pt-3 border-t border-white/10">
                        <b class="block text-white font-semibold mb-0.5">For developers</b>
                        Create an order with one API call and get a signed webhook, with automatic retries, when it is paid.
                    </div>
                    <div class="pt-3 border-t border-white/10">
                        <b class="block text-white font-semibold mb-0.5">For small businesses</b>
                        Share a payment link or QR code and track every payment from one dashboard. No technical setup needed.
                    </div>
                    <div class="pt-3 border-t border-white/10">
                        <b class="block text-white font-semibold mb-0.5">Fully yours</b>
                        Money goes directly to your own UPI account. You keep 100% of every payment, with 0% transaction fee.
                    </div>
                </div>

                {{-- Plate: UPI QR Card --}}
                <div class="mt-8 p-4 rounded-2xl bg-white/5 border border-white/10 backdrop-blur-md flex items-center gap-4 shadow-xl">
                    <div class="w-20 h-20 bg-white rounded-xl p-1.5 shrink-0 flex items-center justify-center overflow-hidden">
                        <svg viewBox="0 0 29 29" class="w-full h-full" shape-rendering="crispEdges">
                            @php
                                mt_srand(7);
                                $finder = fn ($x, $y) => ($x < 7 && $y < 7) || ($x > 21 && $y < 7) || ($x < 7 && $y > 21);
                            @endphp
                            @foreach ([[0, 0], [22, 0], [0, 22]] as [$fx, $fy])
                                <rect x="{{ $fx }}" y="{{ $fy }}" width="7" height="7" fill="#0f172a"/><rect x="{{ $fx + 1 }}" y="{{ $fy + 1 }}" width="5" height="5" fill="white"/><rect x="{{ $fx + 2 }}" y="{{ $fy + 2 }}" width="3" height="3" fill="#0f172a"/>
                            @endforeach
                            @for ($y = 0; $y < 29; $y++)
                                @for ($x = 0; $x < 29; $x++)
                                    @if (! $finder($x, $y) && mt_rand(0, 100) < 48)<rect x="{{ $x }}" y="{{ $y }}" width="1" height="1" fill="#0f172a"/>@endif
                                @endfor
                            @endfor
                        </svg>
                    </div>
                    <div>
                        <h2 class="font-bold text-white text-base">Pay Varun</h2>
                        <p class="text-xs text-slate-400 mt-0.5">Scan with GPay, PhonePe, Paytm, BHIM or any UPI app.</p>
                        <span class="inline-block mt-2 font-mono text-[11px] font-semibold text-[#38bdf8] bg-[#38bdf8]/10 border border-[#38bdf8]/20 px-2.5 py-0.5 rounded-full select-all">paytmqr281005050101cjehi3txn84r@paytm</span>
                    </div>
                </div>
            </div>

            <div class="relative text-xs text-slate-400">@built by <b class="text-white font-semibold">Varun</b></div>
        </div>

        {{-- Form Container --}}
        <div class="flex flex-col justify-center px-6 py-12 sm:px-12 bg-transparent">
            <div class="w-full max-w-sm mx-auto">
                <a href="{{ route('home') }}" class="lg:hidden inline-block mb-10"><x-logo /></a>
                <h2 class="text-2xl font-bold tracking-tight text-white">@yield('title')</h2>
                @hasSection('subtitle')<p class="mt-2 text-xs sm:text-sm text-slate-400">@yield('subtitle')</p>@endif

                <div class="mt-8">
                    @if ($errors->any() && ! request()->routeIs('register'))
                        <div class="mb-5 flex items-start gap-2 rounded-lg bg-red-500/10 border border-red-500/20 px-3 py-2.5 text-xs text-red-400">
                            <x-icon name="alert" class="w-4 h-4 shrink-0 text-red-400" /> {{ $errors->first() }}
                        </div>
                    @endif
                    @yield('content')
                </div>
            </div>
        </div>
    </div>

    {{-- Mobile footer mark --}}
    <footer class="w-full text-center py-3 border-t border-white/10 bg-[#07070a]/80 backdrop-blur text-xs text-slate-400 lg:hidden">
        @built by <b class="text-white font-semibold">Varun</b>
    </footer>
</div>
@endsection
