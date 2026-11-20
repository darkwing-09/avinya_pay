@extends('layouts.base')

@section('body')
<div class="min-h-full grid lg:grid-cols-2 bg-[#07070a]">
    {{-- Brand panel --}}
    <div class="relative hidden lg:flex flex-col justify-between overflow-hidden bg-[#0a0a0f] p-12 text-white border-r border-white/10">
        <div class="absolute inset-0 opacity-40" style="background: radial-gradient(600px circle at 20% 20%, rgba(56, 189, 248, 0.25), transparent 60%), radial-gradient(500px circle at 90% 80%, rgba(56, 189, 248, 0.15), transparent 60%);"></div>
        <div class="absolute inset-0 opacity-[0.05]" style="background-image: linear-gradient(white 1px, transparent 1px), linear-gradient(90deg, white 1px, transparent 1px); background-size: 40px 40px;"></div>

        <a href="{{ route('home') }}" class="relative"><x-logo dark /></a>

        <div class="relative max-w-md">
            <h2 class="text-3xl font-extrabold tracking-tight leading-tight text-white">Accept UPI payments straight into your bank account.</h2>
            <p class="mt-4 text-slate-400 text-sm leading-relaxed">Dynamic QR for every order, instant automated confirmation and signed webhooks. Zero transaction fees.</p>
            <ul class="mt-8 space-y-3.5 text-xs text-slate-300">
                @foreach (['Money lands directly in your own UPI ID', 'Compatible with GPay, PhonePe, Paytm, BHIM and every UPI app', 'Tamper-proof HMAC-SHA256 signed webhooks'] as $point)
                    <li class="flex items-center gap-3"><span class="grid place-items-center w-5 h-5 rounded-full bg-[#38bdf8]/20 text-[#38bdf8]"><x-icon name="check" class="w-3.5 h-3.5" /></span>{{ $point }}</li>
                @endforeach
            </ul>
        </div>

        <div class="relative text-xs text-slate-500">© {{ date('Y') }} {{ config('app.name') }}</div>
    </div>

    {{-- Form Container --}}
    <div class="flex flex-col justify-center px-6 py-12 sm:px-12 bg-[#07070a]">
        <div class="w-full max-w-sm mx-auto">
            <a href="{{ route('home') }}" class="lg:hidden inline-block mb-10"><x-logo /></a>
            <h1 class="text-2xl font-bold tracking-tight text-white">@yield('title')</h1>
            @hasSection('subtitle')<p class="mt-2 text-xs text-slate-400">@yield('subtitle')</p>@endif

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
@endsection
