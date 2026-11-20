@extends('layouts.base')
@section('title', 'Pay ₹'.number_format($order->amount, 2).' to '.$order->user->displayName())

@php
    $merchant = $order->user->displayName();
    $amount = number_format($order->amount, 2);
    $totalSeconds = max(1, $order->created_at->diffInSeconds($order->expires_at ?? now()->addMinutes(config('gateway.order_expiry_minutes', 15))));
    $apps = [
        ['GPay',    'tez://upi/pay',   'hover:border-[#38bdf8]/60 text-white', 'images/upi/gpay.png'],
        ['PhonePe', 'phonepe://pay',   'hover:border-[#38bdf8]/60 text-white', 'images/upi/phonepe.png'],
        ['Paytm',   'paytmmp://pay',   'hover:border-[#38bdf8]/60 text-white', 'images/upi/paytm.png'],
        ['BHIM',    'upi://pay',       'hover:border-[#38bdf8]/60 text-white', 'images/upi/bhim.png'],
    ];
@endphp

@push('head')
<meta name="robots" content="noindex">
<link rel="stylesheet" href="{{ asset('css/pay.css') }}">
@endpush

@section('body')
<div class="min-h-full gocrazy-bg flex flex-col justify-between selection:bg-[#38bdf8]/20 selection:text-white">

    {{-- Ambient Top Glow (GoCrazy signature) --}}
    <div class="fixed top-0 inset-x-0 h-96 pointer-events-none -z-10" style="background: radial-gradient(55% 100% at 50% 0%, rgba(56, 189, 248, 0.15), transparent 70%);"></div>

    {{-- Main Checkout Container with GoCrazy 3D Tilt & Spotlight Hover --}}
    <div class="flex-1 flex items-center justify-center p-4 sm:p-6 lg:p-8">
        <div data-tilt data-spot class="tilt spot w-full max-w-3xl gocrazy-glass rounded-2xl overflow-hidden grid md:grid-cols-5 ring-1 ring-white/10 shadow-2xl">

            {{-- Summary Sidebar (Left column) --}}
            <div class="md:col-span-2 relative bg-[#0a0a0f] text-[#fafafc] p-6 sm:p-8 flex flex-col justify-between border-b md:border-b-0 md:border-r border-white/10 overflow-hidden">
                <div class="hero-gradient absolute inset-0 opacity-40"></div>

                <div>
                    {{-- Merchant Brand Header --}}
                    <div class="relative flex items-center gap-3">
                        <x-avatar :name="$merchant" size="w-11 h-11 text-base" class="!bg-[#17171f] !text-[#38bdf8] ring-1 ring-white/10" />
                        <div class="min-w-0">
                            <div class="text-[11px] font-medium tracking-wide uppercase text-slate-400">Paying to</div>
                            <div class="font-bold text-white truncate text-base">{{ $merchant }}</div>
                        </div>
                    </div>

                    {{-- Amount --}}
                    <div class="relative mt-8">
                        <div class="text-xs uppercase tracking-wider font-semibold text-[#38bdf8]">Total Amount</div>
                        <div class="mt-1 text-4xl sm:text-5xl font-extrabold tracking-tight tabular-nums text-white flex items-baseline gap-1">
                            <span class="text-2xl text-[#38bdf8]">₹</span>{{ $amount }}
                        </div>
                        @if ($order->note)
                            <div class="mt-2 text-xs text-slate-300 bg-white/5 border border-white/10 rounded-lg px-3 py-1.5 inline-block">{{ $order->note }}</div>
                        @endif
                    </div>

                    {{-- Order Breakdown --}}
                    <dl class="relative mt-8 space-y-3 text-xs">
                        <div class="flex justify-between gap-4 py-1.5 border-b border-white/5">
                            <dt class="text-slate-400">Order ID</dt>
                            <dd class="font-mono text-slate-200 truncate">{{ $order->order_no }}</dd>
                        </div>
                        @if ($order->customer_name)
                            <div class="flex justify-between gap-4 py-1.5 border-b border-white/5">
                                <dt class="text-slate-400">Customer</dt>
                                <dd class="text-slate-200 truncate">{{ $order->customer_name }}</dd>
                            </div>
                        @endif
                        @if ($order->paymentAccount->upi_id)
                            <div class="flex justify-between gap-4 py-1.5 border-b border-white/5">
                                <dt class="text-slate-400">UPI ID</dt>
                                <dd class="font-mono text-[#38bdf8] truncate font-semibold">{{ $order->paymentAccount->upi_id }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>

                {{-- Timer Section --}}
                @if ($order->isPending())
                    <div class="relative mt-8 pt-6 border-t border-white/10 flex items-center gap-4">
                        <div class="relative w-12 h-12 shrink-0">
                            <svg viewBox="0 0 36 36" class="w-12 h-12 -rotate-90">
                                <circle cx="18" cy="18" r="16" fill="none" stroke="rgba(255, 255, 255, 0.08)" stroke-width="3" />
                                <circle id="ring" cx="18" cy="18" r="16" fill="none" stroke="#38bdf8" stroke-width="3" stroke-linecap="round" stroke-dasharray="100.5" stroke-dashoffset="0" class="ring-transition" />
                            </svg>
                            <div id="timer" class="absolute inset-0 grid place-items-center font-mono text-xs font-bold text-[#38bdf8]">--:--</div>
                        </div>
                        <div class="text-xs">
                            <div class="font-semibold text-white">Complete payment in time</div>
                            <div class="text-slate-400 text-[11px]">QR code expires automatically.</div>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Action Area (Right Column) --}}
            <div class="md:col-span-3 p-6 sm:p-8 flex flex-col justify-between bg-[#101016]">
                @if ($order->status === 'paid')
                    <div class="flex-1 flex flex-col items-center justify-center text-center py-8">
                        <div class="pop grid place-items-center w-20 h-20 rounded-full bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 shadow-lg shadow-emerald-500/20">
                            <svg viewBox="0 0 24 24" class="w-10 h-10" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path class="draw" d="m5 12.5 4.5 4.5L19 7.5" /></svg>
                        </div>
                        <h1 class="mt-6 text-2xl font-bold tracking-tight text-white">Payment Successful</h1>
                        <p class="mt-2 text-sm text-slate-300">₹{{ $amount }} paid directly to {{ $merchant }}</p>
                        
                        <dl class="mt-8 w-full max-w-xs rounded-xl bg-[#17171f] border border-white/10 divide-y divide-white/5 text-xs text-left">
                            @if ($order->utr)
                                <div class="flex justify-between px-4 py-3"><dt class="text-slate-400">UTR</dt><dd class="font-mono font-semibold text-[#38bdf8]">{{ $order->utr }}</dd></div>
                            @endif
                            <div class="flex justify-between px-4 py-3"><dt class="text-slate-400">Paid at</dt><dd class="font-medium text-slate-200">{{ $order->paid_at?->format('d M Y, h:i A') }}</dd></div>
                        </dl>
                        <p class="mt-6 text-xs text-slate-500">You can safely close this window.</p>
                    </div>

                @elseif ($order->status === 'review')
                    <div class="flex-1 flex flex-col items-center justify-center text-center py-8">
                        <div class="relative grid place-items-center w-20 h-20 rounded-full bg-[#38bdf8]/10 text-[#38bdf8] border border-[#38bdf8]/30">
                            <svg class="absolute inset-0 w-20 h-20 animate-spin [animation-duration:2.5s]" viewBox="0 0 80 80" fill="none"><circle cx="40" cy="40" r="38" stroke="currentColor" stroke-opacity=".15" stroke-width="3"/><path d="M78 40A38 38 0 0 0 40 2" stroke="currentColor" stroke-width="3" stroke-linecap="round"/></svg>
                            <x-icon name="shield" class="w-9 h-9" />
                        </div>
                        <h1 class="mt-6 text-2xl font-bold tracking-tight text-white">Verifying your payment</h1>
                        <p class="mt-2 text-xs text-slate-400 max-w-sm">{{ $merchant }} is confirming your transaction. This page automatically updates once verified.</p>
                        
                        <dl class="mt-8 w-full max-w-xs rounded-xl bg-[#17171f] border border-white/10 divide-y divide-white/5 text-xs text-left">
                            <div class="flex justify-between px-4 py-3"><dt class="text-slate-400">Your UTR</dt><dd class="font-mono font-medium text-[#38bdf8]">{{ $order->customer_utr }}</dd></div>
                            <div class="flex justify-between px-4 py-3"><dt class="text-slate-400">Submitted</dt><dd class="font-medium text-slate-200">{{ $order->utr_submitted_at?->format('d M, h:i A') }}</dd></div>
                        </dl>
                        <p class="mt-6 text-xs text-slate-500">Please do not pay again. You can close this page.</p>
                    </div>

                @elseif ($order->status === 'expired')
                    <div class="flex-1 flex flex-col items-center justify-center text-center py-8">
                        <div class="grid place-items-center w-20 h-20 rounded-full bg-white/5 border border-white/10 text-slate-400"><x-icon name="clock" class="w-10 h-10" /></div>
                        <h1 class="mt-6 text-2xl font-bold tracking-tight text-white">Payment link expired</h1>
                        @if ($order->canRecoverLatePayment())
                            <p class="mt-2 text-xs text-slate-400 max-w-sm">Already paid? Don't pay again. We check until {{ $order->expires_at->copy()->addHours((int) config('gateway.late_payment_grace_hours', 24))->format('d M, h:i A') }}.</p>
                        @endif
                        @if ($order->acceptsCustomerUtr())
                            <p class="mt-2 text-xs text-slate-400 max-w-sm">If money was debited, submit your 12-digit UTR below:</p>
                            @include('pay._utr-form', ['class' => 'mt-8 w-full max-w-sm'])
                        @else
                            <p class="mt-2 text-xs text-slate-400 max-w-xs">Please request a new payment link from {{ $merchant }}.</p>
                        @endif
                    </div>

                @else
                    {{-- Active Pending State --}}
                    <div class="text-center">
                        <h1 class="text-lg font-bold tracking-tight text-white">Scan &amp; Pay with Any UPI App</h1>
                        <p class="text-xs text-slate-400 mt-1">Open your UPI app, scan the dynamic QR, and approve payment.</p>
                    </div>

                    {{-- High Contrast QR Code with Neon Glowing Border --}}
                    <div class="relative mx-auto mt-5 p-3 rounded-2xl bg-white shadow-2xl border-2 border-[#38bdf8]/40 shadow-[#38bdf8]/20">
                        <span class="absolute -top-1 -left-1 w-6 h-6 border-t-2 border-l-2 border-[#38bdf8] rounded-tl-xl"></span>
                        <span class="absolute -top-1 -right-1 w-6 h-6 border-t-2 border-r-2 border-[#38bdf8] rounded-tr-xl"></span>
                        <span class="absolute -bottom-1 -left-1 w-6 h-6 border-b-2 border-l-2 border-[#38bdf8] rounded-bl-xl"></span>
                        <span class="absolute -bottom-1 -right-1 w-6 h-6 border-b-2 border-r-2 border-[#38bdf8] rounded-br-xl"></span>
                        <img src="{{ route('pay.qr', $order) }}" alt="UPI QR code for ₹{{ $amount }}" class="w-52 h-52 sm:w-56 sm:h-56">
                    </div>

                    {{-- UPI App Deep Links --}}
                    <div class="mt-5 flex flex-wrap justify-center gap-2">
                        @foreach ($apps as [$name, $scheme, $color, $logo])
                            <a href="{{ $order->upiIntentUrl($scheme) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-[#17171f] border border-white/10 hover:border-[#38bdf8] transition-all text-xs font-semibold text-white shadow-sm hover:shadow-[#38bdf8]/20">
                                <img src="{{ asset($logo) }}" class="w-4 h-4 rounded-sm" alt="{{ $name }}">{{ $name }}
                            </a>
                        @endforeach
                    </div>

                    {{-- Direct UPI ID Copy Row --}}
                    <div class="mt-4 flex items-center justify-between gap-3 rounded-xl bg-[#17171f] border border-white/10 px-4 py-2.5">
                        <div class="min-w-0">
                            <div class="text-[10px] uppercase font-bold tracking-wider text-slate-400">Direct VPA</div>
                            <div class="font-mono text-xs font-semibold text-[#38bdf8] truncate">{{ $order->paymentAccount->upi_id }}</div>
                        </div>
                        <x-copy :text="$order->paymentAccount->upi_id" />
                    </div>

                    {{-- Live Polling Status Indicator --}}
                    <div class="mt-5 pt-4 border-t border-white/10">
                        <div class="flex items-center justify-center gap-2.5 rounded-xl bg-[#17171f] border border-white/10 px-4 py-2.5 text-xs text-slate-300">
                            <span class="relative flex h-2 w-2">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-[#38bdf8] opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-[#38bdf8]"></span>
                            </span>
                            <span id="status-text">Awaiting automatic confirmation from Paytm / Bank…</span>
                        </div>
                        <p class="mt-2 text-center text-[11px] text-slate-500">Asynchronously verifies Order ID in real-time. Do not close.</p>
                    </div>

                    {{-- 5-Minute Delayed UTR Form --}}
                    @if ($order->acceptsCustomerUtr())
                        <div id="utr-section" @class(['mt-4', 'hidden' => !$order->customer_utr && $order->created_at->diffInSeconds(now()) < 300])>
                            @include('pay._utr-form')
                        </div>
                        @if (!$order->customer_utr && $order->created_at->diffInSeconds(now()) < 300)
                            <div id="utr-countdown-notice" class="mt-3 text-center">
                                <p class="text-[11px] text-slate-500">
                                    Checking Order ID in bank signals... 
                                    <span id="utr-timer-wrap">Manual UTR unlocks in <span id="utr-timer" class="font-mono text-[#38bdf8]">5:00</span></span>
                                </p>
                            </div>
                        @endif
                    @endif
                @endif
            </div>
        </div>
    </div>

    {{-- Clean Footer --}}
    <footer class="py-4 flex flex-col items-center gap-1.5 text-xs text-slate-500">
        <div class="flex items-center gap-1.5"><x-icon name="lock" class="w-3.5 h-3.5 text-[#38bdf8]" /> Secured by <span class="font-semibold text-slate-300">{{ config('app.name') }}</span></div>
        <div class="text-[11px]">Direct peer-to-peer UPI transfer to merchant bank account.</div>
    </footer>
</div>
@endsection

@if (in_array($order->status, ['pending', 'review']) || $order->canRecoverLatePayment())
@push('scripts')
<script nonce="{{ $csp_nonce }}">
(() => {
    const initialStatus = @json($order->status);
    const recoveryEndsAt = {{ $order->expires_at?->copy()->addHours((int) config('gateway.late_payment_grace_hours', 24))->getTimestampMs() ?? 0 }};
    const pollDelay = initialStatus === 'expired' ? 30000 : 4000;
    const expiresAt = {{ ($order->expires_at ?? now()->addMinutes(config('gateway.order_expiry_minutes', 15)))->getTimestampMs() }};
    const createdAt = {{ $order->created_at->getTimestampMs() }};
    const utrUnlockAt = createdAt + 300000;
    const total = {{ $totalSeconds }};
    const timer = document.getElementById('timer');
    const ring = document.getElementById('ring');
    const statusUrl = @json(route('pay.status', $order));

    const utrSection = document.getElementById('utr-section');
    const utrNotice = document.getElementById('utr-countdown-notice');
    const utrTimer = document.getElementById('utr-timer');

    const tick = () => {
        if (timer) {
            const left = Math.max(0, Math.floor((expiresAt - Date.now()) / 1000));
            const display = String(Math.floor(left / 60)).padStart(2, '0') + ':' + String(left % 60).padStart(2, '0');
            const offset = (100.5 * (1 - left / total)).toFixed(2);
            const stroke = left < 60 ? '#f87171' : '#38bdf8';
            timer.textContent = display;
            ring.style.strokeDashoffset = offset;
            ring.style.stroke = stroke;
            const timerPg = document.getElementById('timer-pg');
            const ringPg  = document.getElementById('ring-pg');
            if (timerPg) timerPg.textContent = display;
            if (ringPg)  { ringPg.style.strokeDashoffset = offset; ringPg.style.stroke = stroke; }
        }

        if (utrTimer && utrSection) {
            const remainingUtr = Math.max(0, Math.ceil((utrUnlockAt - Date.now()) / 1000));
            if (remainingUtr <= 0) {
                utrSection.classList.remove('hidden');
                if (utrNotice) utrNotice.classList.add('hidden');
            } else {
                const m = Math.floor(remainingUtr / 60);
                const s = String(remainingUtr % 60).padStart(2, '0');
                utrTimer.textContent = `${m}:${s}`;
            }
        }
    };
    tick();
    setInterval(tick, 1000);

    const poll = async () => {
        if (initialStatus === 'expired' && Date.now() > recoveryEndsAt) return;
        try {
            const res = await fetch(statusUrl, { headers: { Accept: 'application/json' } });
            if (res.ok) {
                const data = await res.json();
                if (data.status !== initialStatus) {
                    const text = document.getElementById('status-text');
                    if (text) text.textContent = data.status === 'paid' ? 'Payment received! Redirecting…' : 'Updating…';
                    window.location.href = data.redirect || window.location.href;
                    return;
                }
            }
        } catch (e) {}
        setTimeout(poll, pollDelay);
    };
    setTimeout(poll, pollDelay);
})();
</script>
@endpush
@endif
