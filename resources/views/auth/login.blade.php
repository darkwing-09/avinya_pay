@extends('layouts.guest')
@section('title', 'Welcome back')
@section('subtitle', 'Log in to see your orders, payment links and API keys.')

@section('content')
<form id="login" method="POST" action="{{ route('login') }}" novalidate>
    @csrf
    <div class="mb-5">
        <label for="f-email" class="label">Email</label>
        <input id="f-email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="email" @class(['input py-2.5', 'ring-red-400 focus:ring-red-500' => $errors->has('email')]) placeholder="you@example.com">
    </div>
    <div class="mb-5">
        <label for="f-password" class="label">Password</label>
        <div class="relative">
            <input id="f-password" type="password" name="password" required autocomplete="current-password" @class(['input py-2.5 pr-14', 'ring-red-400 focus:ring-red-500' => $errors->has('password')]) placeholder="Enter your password">
            <button type="button" id="tg" class="absolute right-2 top-1/2 -translate-y-1/2 text-xs font-semibold text-[#38bdf8] hover:text-[#7dd3fc] px-2 py-1 rounded transition-colors" aria-controls="f-password" aria-pressed="false">Show</button>
        </div>
    </div>
    <div class="flex items-center justify-between text-sm mb-6">
        <label class="flex items-center gap-2 text-slate-400 hover:text-slate-300 cursor-pointer text-xs sm:text-sm">
            <input type="checkbox" name="remember" class="rounded border-white/20 bg-white/5 text-[#38bdf8] focus:ring-[#38bdf8]"> Keep me logged in
        </label>
    </div>
    <button class="btn btn-primary w-full py-2.5 text-sm font-semibold shadow-lg shadow-[#38bdf8]/20 hover:shadow-[#38bdf8]/30 transition-all">Log in</button>
</form>

<p class="text-sm text-center text-slate-400 mt-8">
    New to {{ config('app.name', 'AvinyaPay') }}? 
    <a href="{{ route('register') }}" class="font-semibold text-[#38bdf8] hover:text-[#7dd3fc] transition-colors">Create a free account</a>
</p>

@push('scripts')
<script>
    (function () {
        var pw = document.getElementById('f-password'), tg = document.getElementById('tg');
        if (pw && tg) {
            tg.addEventListener('click', function () {
                var show = pw.type === 'password';
                pw.type = show ? 'text' : 'password';
                tg.textContent = show ? 'Hide' : 'Show';
                tg.setAttribute('aria-pressed', String(show));
            });
        }
    })();
</script>
@endpush
@endsection
