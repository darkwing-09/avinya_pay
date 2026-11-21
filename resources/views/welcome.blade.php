@extends('layouts.base')
@section('title', 'AvinyaPay · India\'s First Custom UPI Payment Gateway for Developers')

@section('body')
<div class="min-h-full gocrazy-bg text-[#fafafc]">
    @include('layouts.public-nav')

    {{-- Hero Section --}}
    <section class="relative overflow-hidden bg-transparent pt-12 pb-20 lg:pt-20 lg:pb-32">
        <div class="absolute inset-0 pointer-events-none -z-10" style="background: radial-gradient(800px circle at 50% -10%, rgba(56, 189, 248, 0.18), transparent 70%);"></div>

        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid lg:grid-cols-12 gap-12 lg:gap-8 items-center">
                
                {{-- Left Column: Copy & Value Proposition --}}
                <div class="lg:col-span-7 flex flex-col items-start">
                    
                    {{-- Status Pill --}}
                    <div class="inline-flex items-center gap-2 rounded-full bg-[#101016]/90 border border-white/10 px-3.5 py-1.5 text-xs font-semibold text-slate-300 shadow-sm backdrop-blur-md mb-8">
                        <span class="flex h-2 w-2 rounded-full bg-[#38bdf8] animate-pulse"></span>
                        <span class="text-[#38bdf8]">India's First</span> · Custom UPI Gateway for Developers
                        <x-icon name="arrow-right" class="w-3.5 h-3.5 text-slate-400" />
                    </div>

                    {{-- Main Headline --}}
                    <h1 class="text-4xl sm:text-5xl lg:text-[3.75rem] font-extrabold tracking-tight text-white leading-[1.08]">
                        Build your own
                        <span class="relative whitespace-nowrap">
                            <span class="bg-gradient-to-r from-[#38bdf8] via-[#7dd3fc] to-[#38bdf8] bg-clip-text text-transparent">custom UPI gateway</span>
                            <svg class="absolute -bottom-1.5 left-0 w-full" viewBox="0 0 200 8" fill="none" preserveAspectRatio="none">
                                <path d="M0 6 Q50 0 100 4 Q150 8 200 2" stroke="url(#hero-grad)" stroke-width="2.5" stroke-linecap="round" fill="none"/>
                                <defs>
                                    <linearGradient id="hero-grad" x1="0" y1="0" x2="200" y2="0" gradientUnits="userSpaceOnUse">
                                        <stop stop-color="#38bdf8"/>
                                        <stop offset="1" stop-color="#0284c7"/>
                                    </linearGradient>
                                </defs>
                            </svg>
                        </span>
                        in minutes.
                    </h1>

                    {{-- Subtitle --}}
                    <p class="mt-6 text-base sm:text-lg text-slate-400 max-w-2xl leading-relaxed">
                        Create dynamic UPI orders with a single API call. Accept direct payments straight into your bank account with <strong class="text-white font-semibold">0% transaction fees</strong> and real-time bank webhook confirmation in seconds. No aggregators, no rolling reserves.
                    </p>

                    {{-- CTA Action Buttons --}}
                    <div class="mt-9 flex flex-wrap items-center gap-4 w-full sm:w-auto">
                        @if (config('auth.allow_registration', true))
                            <a href="{{ route('register') }}" class="btn btn-primary btn-lg shadow-lg shadow-[#38bdf8]/20 hover:shadow-[#38bdf8]/35 transition-all text-sm sm:text-base font-semibold px-6 py-3">
                                Start Building Free <x-icon name="arrow-right" class="w-4 h-4 ml-1" />
                            </a>
                        @endif
                        <a href="{{ route('docs') }}" class="btn btn-secondary btn-lg border border-white/10 bg-white/5 hover:bg-white/10 text-white text-sm sm:text-base font-semibold px-6 py-3 backdrop-blur-sm">
                            <x-icon name="code" class="w-4 h-4 mr-1 text-[#38bdf8]" /> Explore API Docs
                        </a>
                    </div>

                    {{-- Checklist badges --}}
                    <div class="mt-10 flex flex-wrap items-center gap-x-6 gap-y-3 text-xs sm:text-sm text-slate-400 font-medium">
                        <span class="flex items-center gap-1.5">
                            <x-icon name="check-circle" class="w-4 h-4 text-emerald-400 shrink-0" /> 0% Transaction Fees
                        </span>
                        <span class="flex items-center gap-1.5">
                            <x-icon name="check-circle" class="w-4 h-4 text-emerald-400 shrink-0" /> Instant Bank Settlement
                        </span>
                        <span class="flex items-center gap-1.5">
                            <x-icon name="check-circle" class="w-4 h-4 text-emerald-400 shrink-0" /> All UPI Apps Compatible
                        </span>
                        <span class="flex items-center gap-1.5">
                            <x-icon name="check-circle" class="w-4 h-4 text-emerald-400 shrink-0" /> Signed Webhooks
                        </span>
                    </div>

                </div>

                {{-- Right Column: Interactive Developer Studio & Mockup --}}
                <div class="lg:col-span-5 relative mt-4 lg:mt-0">
                    <div class="absolute -inset-4 bg-gradient-to-tr from-[#38bdf8]/20 via-[#0284c7]/10 to-transparent blur-3xl rounded-[2.5rem] pointer-events-none"></div>

                    {{-- Bento Showcase Container --}}
                    <div class="relative rounded-2xl border border-white/15 bg-[#0d0d14]/90 backdrop-blur-2xl shadow-2xl shadow-black/80 overflow-hidden">
                        
                        {{-- Terminal / Window Header --}}
                        <div class="flex items-center justify-between px-4 py-3 border-b border-white/10 bg-[#07070a]/80">
                            <div class="flex items-center gap-2">
                                <span class="w-3 h-3 rounded-full bg-red-500/80"></span>
                                <span class="w-3 h-3 rounded-full bg-amber-500/80"></span>
                                <span class="w-3 h-3 rounded-full bg-emerald-500/80"></span>
                                <span class="ml-2 font-mono text-xs text-slate-400 font-medium">AvinyaPay · Checkout Simulator</span>
                            </div>
                            <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full bg-emerald-500/10 border border-emerald-500/20 text-[11px] font-mono font-semibold text-emerald-400">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-ping"></span> Live Gateway
                            </span>
                        </div>

                        {{-- Card Body: Real-time Checkout Mockup --}}
                        <div class="p-6">
                            
                            {{-- Merchant Header --}}
                            <div class="p-4 rounded-xl bg-white/5 border border-white/10 flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-lg bg-gradient-to-tr from-[#38bdf8] to-[#0284c7] flex items-center justify-center font-bold text-[#041419] text-base shadow-sm">
                                        V
                                    </div>
                                    <div>
                                        <div class="text-[11px] uppercase tracking-wider text-slate-400 font-semibold">Paying To</div>
                                        <div class="font-bold text-white text-sm">Varun Dev Mishra</div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-[11px] uppercase tracking-wider text-slate-400 font-semibold">Amount</div>
                                    <div class="font-extrabold text-white text-lg font-mono">₹499.00</div>
                                </div>
                            </div>

                            {{-- Dynamic QR Stage --}}
                            <div class="my-6 flex flex-col items-center">
                                <div class="relative p-3.5 rounded-2xl bg-white ring-4 ring-[#38bdf8]/30 shadow-xl shadow-[#38bdf8]/10 group">
                                    <svg viewBox="0 0 29 29" class="w-44 h-44" shape-rendering="crispEdges">
                                        @php
                                            mt_srand(42);
                                            $finder = fn ($x, $y) => ($x < 7 && $y < 7) || ($x > 21 && $y < 7) || ($x < 7 && $y > 21);
                                        @endphp
                                        @foreach ([[0, 0], [22, 0], [0, 22]] as [$fx, $fy])
                                            <rect x="{{ $fx }}" y="{{ $fy }}" width="7" height="7" fill="#07070a"/>
                                            <rect x="{{ $fx + 1 }}" y="{{ $fy + 1 }}" width="5" height="5" fill="white"/>
                                            <rect x="{{ $fx + 2 }}" y="{{ $fy + 2 }}" width="3" height="3" fill="#07070a"/>
                                        @endforeach
                                        @for ($y = 0; $y < 29; $y++)
                                            @for ($x = 0; $x < 29; $x++)
                                                @if (! $finder($x, $y) && mt_rand(0, 100) < 48)
                                                    <rect x="{{ $x }}" y="{{ $y }}" width="1" height="1" fill="#07070a"/>
                                                @endif
                                            @endfor
                                        @endfor
                                    </svg>
                                    
                                    {{-- Center Icon Badge --}}
                                    <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                                        <span class="w-9 h-9 rounded-lg bg-white text-black flex items-center justify-center shadow-lg shadow-white/10">
                                            <svg viewBox="0 0 24 24" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M12 12c-2-2.67-4-4-6-4a4 4 0 1 0 0 8c2 0 4-1.33 6-4Zm0 0c2 2.67 4 4 6 4a4 4 0 0 0 0-8c-2 0-4 1.33-6 4Z"/>
                                            </svg>
                                        </span>
                                    </div>
                                </div>

                                <div class="mt-3 flex items-center gap-1.5 text-xs text-slate-400 font-mono">
                                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                                    Scan with GPay, PhonePe, Paytm, BHIM or CRED
                                </div>

                                {{-- UPI ID Badge --}}
                                <div class="mt-3">
                                    <span class="inline-block font-mono text-[11px] font-semibold text-[#38bdf8] bg-[#38bdf8]/10 border border-[#38bdf8]/20 px-3 py-1 rounded-full select-all">
                                        Yourupi@upi
                                    </span>
                                </div>
                            </div>

                            {{-- Real-time Webhook Notification Simulation --}}
                            <div class="p-3.5 rounded-xl bg-[#07070a] border border-emerald-500/30 flex items-center justify-between shadow-lg">
                                <div class="flex items-center gap-3">
                                    <span class="w-8 h-8 rounded-lg bg-emerald-500/20 text-emerald-400 flex items-center justify-center">
                                        <x-icon name="check" class="w-4 h-4 text-emerald-400" />
                                    </span>
                                    <div>
                                        <div class="text-xs font-bold text-white flex items-center gap-1.5">
                                            Webhook Dispatched <span class="text-[10px] text-emerald-400 font-mono">200 OK</span>
                                        </div>
                                        <div class="text-[11px] text-slate-400 font-mono">order.paid · verified in 1.8s</div>
                                    </div>
                                </div>
                                <span class="font-mono text-xs font-semibold text-emerald-400">₹499.00</span>
                            </div>

                        </div>

                        {{-- Supported Apps Strip --}}
                        <div class="px-6 py-3 border-t border-white/10 bg-[#07070a]/60 flex items-center justify-between text-xs text-slate-400">
                            <span class="text-[11px] font-medium text-slate-500">Supported Apps:</span>
                            <div class="flex items-center gap-3 font-semibold text-slate-300">
                                <span class="hover:text-[#38bdf8] transition-colors">GPay</span>
                                <span class="hover:text-[#38bdf8] transition-colors">PhonePe</span>
                                <span class="hover:text-[#38bdf8] transition-colors">Paytm</span>
                                <span class="hover:text-[#38bdf8] transition-colors">BHIM</span>
                                <span class="hover:text-[#38bdf8] transition-colors">CRED</span>
                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </section>

    {{-- Stats Banner --}}
    <section class="border-y border-white/10 bg-[#0a0a0f]/80 backdrop-blur-xl py-10">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-8 text-center divide-y lg:divide-y-0 lg:divide-x divide-white/10">
                <div class="pt-4 lg:pt-0">
                    <div class="text-3xl sm:text-4xl font-extrabold text-emerald-400 font-mono">₹0</div>
                    <div class="mt-1 text-xs sm:text-sm font-medium text-slate-400">Transaction Fee Forever</div>
                </div>
                <div class="pt-4 lg:pt-0">
                    <div class="text-3xl sm:text-4xl font-extrabold text-[#38bdf8] font-mono">&lt; 3s</div>
                    <div class="mt-1 text-xs sm:text-sm font-medium text-slate-400">Direct Bank Signal Verification</div>
                </div>
                <div class="pt-4 lg:pt-0">
                    <div class="text-3xl sm:text-4xl font-extrabold text-sky-400 font-mono">100%</div>
                    <div class="mt-1 text-xs sm:text-sm font-medium text-slate-400">Direct Bank Settlement (0 Hold)</div>
                </div>
                <div class="pt-4 lg:pt-0">
                    <div class="text-3xl sm:text-4xl font-extrabold text-amber-400 font-mono">99.9%</div>
                    <div class="mt-1 text-xs sm:text-sm font-medium text-slate-400">Webhook Delivery SLA</div>
                </div>
            </div>
        </div>
    </section>

    {{-- Interactive Developer Code Studio --}}
    <section id="developers" class="py-20 lg:py-28 bg-transparent">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto mb-16">
                <span class="text-xs font-bold uppercase tracking-widest text-[#38bdf8]">Developer Experience</span>
                <h2 class="mt-3 text-3xl sm:text-4xl font-extrabold tracking-tight text-white">Integrate checkout in less than 5 minutes</h2>
                <p class="mt-4 text-slate-400 text-sm sm:text-base leading-relaxed">
                    Simple, predictable REST APIs. Call one endpoint to generate a dynamic order, point your customer to the hosted payment page, and let our verified bank webhook confirm it automatically.
                </p>
            </div>

            {{-- Interactive Code Switcher --}}
            <div class="max-w-4xl mx-auto rounded-2xl border border-white/10 bg-[#0d0d14]/90 backdrop-blur-2xl shadow-2xl overflow-hidden">
                
                {{-- Language Tabs --}}
                <div class="flex items-center justify-between border-b border-white/10 bg-[#07070a]/90 px-4 py-3">
                    <div class="flex items-center gap-2">
                        <button type="button" class="code-tab-btn active px-3 py-1.5 rounded-lg text-xs font-mono font-semibold transition-all bg-[#38bdf8]/15 text-[#38bdf8] border border-[#38bdf8]/30" data-lang="curl">cURL</button>
                        <button type="button" class="code-tab-btn px-3 py-1.5 rounded-lg text-xs font-mono font-semibold transition-all text-slate-400 hover:text-white" data-lang="nodejs">Node.js</button>
                        <button type="button" class="code-tab-btn px-3 py-1.5 rounded-lg text-xs font-mono font-semibold transition-all text-slate-400 hover:text-white" data-lang="python">Python</button>
                        <button type="button" class="code-tab-btn px-3 py-1.5 rounded-lg text-xs font-mono font-semibold transition-all text-slate-400 hover:text-white" data-lang="webhook">Webhook (JSON)</button>
                    </div>
                    <button type="button" id="copy-code-btn" class="inline-flex items-center gap-1.5 text-xs text-slate-400 hover:text-white font-mono transition-colors">
                        <x-icon name="copy" class="w-3.5 h-3.5" /> <span>Copy</span>
                    </button>
                </div>

                {{-- Code Panes --}}
                <div class="p-6 font-mono text-[13px] leading-relaxed overflow-x-auto text-slate-200">
                    
                    {{-- cURL --}}
                    <div id="code-curl" class="code-pane">
<pre><span class="text-slate-500"># 1. Create a dynamic UPI order via AvinyaPay</span>
<span class="text-sky-400">curl</span> -X POST {{ url('/api/v1/orders') }} \
  -H <span class="text-emerald-400">"X-Api-Key: key_live_hHUvU2o3nzoU7b7V6M3vEIhR"</span> \
  -H <span class="text-emerald-400">"X-Api-Secret: sec_live_arIFgtqDF92CnF2uV8IZYk4Z..."</span> \
  -H <span class="text-emerald-400">"Content-Type: application/json"</span> \
  -d <span class="text-amber-300">'{
    "amount": 499.00,
    "client_order_id": "ORDER_1042",
    "note": "Pro Subscription"
  }'</span>

<span class="text-slate-500"># 2. Instant Response with hosted payment URL and UPI Intent</span>
{
  <span class="text-sky-300">"order_id"</span>: <span class="text-emerald-400">"ORDXF64VIZMIHDS1BUL"</span>,
  <span class="text-sky-300">"amount"</span>: <span class="text-amber-300">"499.00"</span>,
  <span class="text-sky-300">"status"</span>: <span class="text-emerald-400">"pending"</span>,
  <span class="text-sky-300">"payment_url"</span>: <span class="text-emerald-400">"{{ url('/pay/ORDXF64VIZMIHDS1BUL') }}"</span>,
  <span class="text-sky-300">"upi_intent"</span>: <span class="text-emerald-400">"upi://pay?pa=paytmqr281005...&amp;pn=Varun&amp;am=499.00&amp;cu=INR"</span>
}</pre>
                    </div>

                    {{-- Node.js --}}
                    <div id="code-nodejs" class="code-pane hidden">
<pre><span class="text-slate-500">// Modern fetch in Node.js / Next.js / Express</span>
<span class="text-purple-400">const</span> response = <span class="text-purple-400">await</span> fetch(<span class="text-emerald-400">"{{ url('/api/v1/orders') }}"</span>, {
  method: <span class="text-emerald-400">"POST"</span>,
  headers: {
    <span class="text-sky-300">"X-Api-Key"</span>: process.env.AVINYA_API_KEY,
    <span class="text-sky-300">"X-Api-Secret"</span>: process.env.AVINYA_API_SECRET,
    <span class="text-sky-300">"Content-Type"</span>: <span class="text-emerald-400">"application/json"</span>
  },
  body: JSON.stringify({
    amount: <span class="text-amber-300">499.00</span>,
    client_order_id: <span class="text-emerald-400">"ORDER_1042"</span>,
    note: <span class="text-emerald-400">"Pro Subscription"</span>
  })
});

<span class="text-purple-400">const</span> { order_id, payment_url, upi_intent } = <span class="text-purple-400">await</span> response.json();
<span class="text-slate-500">// Redirect user to payment_url or display QR code inline</span></pre>
                    </div>

                    {{-- Python --}}
                    <div id="code-python" class="code-pane hidden">
<pre><span class="text-slate-500"># Python using httpx / requests</span>
<span class="text-purple-400">import</span> httpx, os

resp = httpx.post(
    <span class="text-emerald-400">"{{ url('/api/v1/orders') }}"</span>,
    headers={
        <span class="text-sky-300">"X-Api-Key"</span>: os.getenv(<span class="text-emerald-400">"AVINYA_KEY"</span>),
        <span class="text-sky-300">"X-Api-Secret"</span>: os.getenv(<span class="text-emerald-400">"AVINYA_SECRET"</span>),
    },
    json={
        <span class="text-sky-300">"amount"</span>: <span class="text-amber-300">499.00</span>,
        <span class="text-sky-300">"client_order_id"</span>: <span class="text-emerald-400">"ORDER_1042"</span>,
        <span class="text-sky-300">"note"</span>: <span class="text-emerald-400">"Pro Subscription"</span>,
    }
)

order = resp.json()
print(<span class="text-emerald-400">f"Checkout URL: {order['payment_url']}"</span>)</pre>
                    </div>

                    {{-- Webhook --}}
                    <div id="code-webhook" class="code-pane hidden">
<pre><span class="text-slate-500"># Webhook payload dispatched to your endpoint on payment confirmation</span>
<span class="text-slate-500"># Header: X-Avinya-Signature: hmac_sha256_e9c7a20f...</span>
{
  <span class="text-sky-300">"event"</span>: <span class="text-emerald-400">"order.paid"</span>,
  <span class="text-sky-300">"order_id"</span>: <span class="text-emerald-400">"ORDXF64VIZMIHDS1BUL"</span>,
  <span class="text-sky-300">"client_order_id"</span>: <span class="text-emerald-400">"ORDER_1042"</span>,
  <span class="text-sky-300">"amount"</span>: <span class="text-amber-300">"499.00"</span>,
  <span class="text-sky-300">"status"</span>: <span class="text-emerald-400">"paid"</span>,
  <span class="text-sky-300">"utr"</span>: <span class="text-emerald-400">"432689104521"</span>,
  <span class="text-sky-300">"paid_at"</span>: <span class="text-emerald-400">"2026-11-21T17:40:12Z"</span>,
  <span class="text-sky-300">"tamper_proof"</span>: <span class="text-purple-400">true</span>
}</pre>
                    </div>

                </div>
            </div>
        </div>
    </section>

    {{-- Bento Features Grid --}}
    <section id="features" class="py-20 lg:py-28 bg-[#0a0a0f]/60 border-t border-white/10">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto mb-16">
                <span class="text-xs font-bold uppercase tracking-widest text-[#38bdf8]">Architected for Scale</span>
                <h2 class="mt-3 text-3xl sm:text-4xl font-extrabold tracking-tight text-white">Why developers choose AvinyaPay</h2>
                <p class="mt-4 text-slate-400 text-sm sm:text-base leading-relaxed">
                    Designed from the ground up for Indian startups, solo founders, and modern development teams.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                
                {{-- Card 1 --}}
                <div class="p-8 rounded-2xl border border-white/10 bg-[#101016]/80 backdrop-blur-xl shadow-lg hover:border-[#38bdf8]/40 transition-all group">
                    <div class="w-12 h-12 rounded-xl bg-[#38bdf8]/10 border border-[#38bdf8]/20 text-[#38bdf8] flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                        <x-icon name="qr" class="w-6 h-6" />
                    </div>
                    <h3 class="text-lg font-bold text-white mb-2">Dynamic QR per Order</h3>
                    <p class="text-sm text-slate-400 leading-relaxed">
                        Every order generates an exact-amount QR code embedded with a unique transaction reference. Eliminates mispayments and mismatched UTRs forever.
                    </p>
                </div>

                {{-- Card 2 --}}
                <div class="p-8 rounded-2xl border border-white/10 bg-[#101016]/80 backdrop-blur-xl shadow-lg hover:border-emerald-500/40 transition-all group">
                    <div class="w-12 h-12 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                        <x-icon name="bolt" class="w-6 h-6" />
                    </div>
                    <h3 class="text-lg font-bold text-white mb-2">Automated Bank Signal Verification</h3>
                    <p class="text-sm text-slate-400 leading-relaxed">
                        Instant zero-touch confirmation. Ingests bank signals directly via IMAP and business webhooks, confirming customer payments within 2 to 4 seconds.
                    </p>
                </div>

                {{-- Card 3 --}}
                <div class="p-8 rounded-2xl border border-white/10 bg-[#101016]/80 backdrop-blur-xl shadow-lg hover:border-purple-500/40 transition-all group">
                    <div class="w-12 h-12 rounded-xl bg-purple-500/10 border border-purple-500/20 text-purple-400 flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                        <x-icon name="link" class="w-6 h-6" />
                    </div>
                    <h3 class="text-lg font-bold text-white mb-2">HMAC Signed Webhooks</h3>
                    <p class="text-sm text-slate-400 leading-relaxed">
                        Your server receives tamper-proof JSON webhooks signed with HMAC-SHA256. Includes automated exponential retries and failure alerts.
                    </p>
                </div>

                {{-- Card 4 --}}
                <div class="p-8 rounded-2xl border border-white/10 bg-[#101016]/80 backdrop-blur-xl shadow-lg hover:border-amber-500/40 transition-all group">
                    <div class="w-12 h-12 rounded-xl bg-amber-500/10 border border-amber-500/20 text-amber-400 flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                        <x-icon name="banknotes" class="w-6 h-6" />
                    </div>
                    <h3 class="text-lg font-bold text-white mb-2">0% Commission · 100% Yours</h3>
                    <p class="text-sm text-slate-400 leading-relaxed">
                        Stop paying 2% + GST to legacy payment aggregators. Funds land directly in your own bank account. We never hold or touch your money.
                    </p>
                </div>

                {{-- Card 5 --}}
                <div class="p-8 rounded-2xl border border-white/10 bg-[#101016]/80 backdrop-blur-xl shadow-lg hover:border-sky-500/40 transition-all group">
                    <div class="w-12 h-12 rounded-xl bg-sky-500/10 border border-sky-500/20 text-sky-400 flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                        <x-icon name="shield" class="w-6 h-6" />
                    </div>
                    <h3 class="text-lg font-bold text-white mb-2">Anti-Fraud & Underpayment Guard</h3>
                    <p class="text-sm text-slate-400 leading-relaxed">
                        Strict deterministic validation prevents fake UTR submissions, replay attacks, and partial amount exploits with bank-grade defense.
                    </p>
                </div>

                {{-- Card 6 --}}
                <div class="p-8 rounded-2xl border border-white/10 bg-[#101016]/80 backdrop-blur-xl shadow-lg hover:border-rose-500/40 transition-all group">
                    <div class="w-12 h-12 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-400 flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                        <x-icon name="users" class="w-6 h-6" />
                    </div>
                    <h3 class="text-lg font-bold text-white mb-2">Multi-Account Smart Rotation</h3>
                    <p class="text-sm text-slate-400 leading-relaxed">
                        Attach multiple UPI handles, set daily limits per account, and distribute traffic automatically to stay well within banking limits.
                    </p>
                </div>

            </div>
        </div>
    </section>

    {{-- Comparison Section --}}
    <section class="py-20 lg:py-28 bg-transparent border-t border-white/10">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-2xl mx-auto mb-16">
                <span class="text-xs font-bold uppercase tracking-widest text-[#38bdf8]">Fair Comparison</span>
                <h2 class="mt-3 text-3xl sm:text-4xl font-extrabold tracking-tight text-white">AvinyaPay vs Traditional Gateways</h2>
                <p class="mt-4 text-slate-400 text-sm sm:text-base">Why developers and bootstrapped founders prefer our architecture.</p>
            </div>

            <div class="rounded-2xl border border-white/10 bg-[#101016]/90 backdrop-blur-2xl shadow-xl overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="border-b border-white/10 bg-white/5 font-mono text-xs uppercase tracking-wider">
                            <tr>
                                <th class="p-5 text-slate-400">Feature</th>
                                <th class="p-5 text-[#38bdf8] font-bold">AvinyaPay</th>
                                <th class="p-5 text-slate-500">Razorpay / Cashfree</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5 text-slate-300">
                            <tr class="hover:bg-white/[0.02] transition-colors">
                                <td class="p-5 font-semibold text-white">Transaction Fee</td>
                                <td class="p-5 text-emerald-400 font-bold font-mono">0% Forever</td>
                                <td class="p-5 text-slate-400">2% + 18% GST</td>
                            </tr>
                            <tr class="hover:bg-white/[0.02] transition-colors">
                                <td class="p-5 font-semibold text-white">Settlement Timing</td>
                                <td class="p-5 text-emerald-400 font-bold font-mono">Instant (Direct to Bank)</td>
                                <td class="p-5 text-slate-400">T+2 Business Days</td>
                            </tr>
                            <tr class="hover:bg-white/[0.02] transition-colors">
                                <td class="p-5 font-semibold text-white">Funds Escrow / Holding</td>
                                <td class="p-5 text-emerald-400 font-bold">Zero (Never Touched)</td>
                                <td class="p-5 text-slate-400">Held in Nodal Accounts</td>
                            </tr>
                            <tr class="hover:bg-white/[0.02] transition-colors">
                                <td class="p-5 font-semibold text-white">Onboarding &amp; Paperwork</td>
                                <td class="p-5 text-emerald-400 font-bold">Zero KYC Delays (Self-Hosted)</td>
                                <td class="p-5 text-slate-400">Weeks of Business Verification</td>
                            </tr>
                            <tr class="hover:bg-white/[0.02] transition-colors">
                                <td class="p-5 font-semibold text-white">Dynamic Amount-Locked QR</td>
                                <td class="p-5 text-emerald-400"><x-icon name="check" class="w-5 h-5 text-emerald-400" /></td>
                                <td class="p-5 text-slate-400"><x-icon name="check" class="w-5 h-5 text-slate-400" /></td>
                            </tr>
                            <tr class="hover:bg-white/[0.02] transition-colors">
                                <td class="p-5 font-semibold text-white">Tamper-Proof Webhooks</td>
                                <td class="p-5 text-emerald-400"><x-icon name="check" class="w-5 h-5 text-emerald-400" /></td>
                                <td class="p-5 text-slate-400"><x-icon name="check" class="w-5 h-5 text-slate-400" /></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>

    {{-- How It Works --}}
    <section id="how" class="py-20 lg:py-28 bg-[#0a0a0f]/60 border-t border-white/10">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-2xl mx-auto mb-16">
                <span class="text-xs font-bold uppercase tracking-widest text-[#38bdf8]">Step by Step</span>
                <h2 class="mt-3 text-3xl sm:text-4xl font-extrabold tracking-tight text-white">How AvinyaPay Works</h2>
                <p class="mt-4 text-slate-400 text-sm sm:text-base">From checkout generation to bank confirmation in four seamless steps.</p>
            </div>

            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-8">
                <div class="p-6 rounded-2xl border border-white/10 bg-[#101016]/60 backdrop-blur-md relative">
                    <div class="w-9 h-9 rounded-full bg-[#38bdf8]/20 text-[#38bdf8] font-mono font-bold flex items-center justify-center text-sm mb-4 border border-[#38bdf8]/30">01</div>
                    <h3 class="text-base font-bold text-white mb-2">Create Order</h3>
                    <p class="text-xs text-slate-400 leading-relaxed">Your backend calls <code>/api/v1/orders</code> with the order amount and client reference.</p>
                </div>
                <div class="p-6 rounded-2xl border border-white/10 bg-[#101016]/60 backdrop-blur-md relative">
                    <div class="w-9 h-9 rounded-full bg-[#38bdf8]/20 text-[#38bdf8] font-mono font-bold flex items-center justify-center text-sm mb-4 border border-[#38bdf8]/30">02</div>
                    <h3 class="text-base font-bold text-white mb-2">Customer Scans</h3>
                    <p class="text-xs text-slate-400 leading-relaxed">Customer scans the dynamic QR with any UPI app. Amount and payee are locked.</p>
                </div>
                <div class="p-6 rounded-2xl border border-white/10 bg-[#101016]/60 backdrop-blur-md relative">
                    <div class="w-9 h-9 rounded-full bg-[#38bdf8]/20 text-[#38bdf8] font-mono font-bold flex items-center justify-center text-sm mb-4 border border-[#38bdf8]/30">03</div>
                    <h3 class="text-base font-bold text-white mb-2">Instant Signal</h3>
                    <p class="text-xs text-slate-400 leading-relaxed">The bank sends a credit notification; the agent validates the exact order match in seconds.</p>
                </div>
                <div class="p-6 rounded-2xl border border-white/10 bg-[#101016]/60 backdrop-blur-md relative">
                    <div class="w-9 h-9 rounded-full bg-[#38bdf8]/20 text-[#38bdf8] font-mono font-bold flex items-center justify-center text-sm mb-4 border border-[#38bdf8]/30">04</div>
                    <h3 class="text-base font-bold text-white mb-2">Webhook Sent</h3>
                    <p class="text-xs text-slate-400 leading-relaxed">Your backend receives an HMAC-signed webhook. The product or service unlocks immediately.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- Pricing Section --}}
    <section id="pricing" class="py-20 lg:py-28 bg-transparent border-t border-white/10">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-2xl mx-auto mb-16">
                <span class="text-xs font-bold uppercase tracking-widest text-[#38bdf8]">Pricing</span>
                <h2 class="mt-3 text-3xl sm:text-4xl font-extrabold tracking-tight text-white">Simple, Predictable Plans</h2>
                <p class="mt-4 text-slate-400 text-sm sm:text-base">0% transaction cuts on all plans. Keep 100% of your hard-earned revenue.</p>
            </div>

            <div class="grid md:grid-cols-3 gap-8 items-stretch max-w-6xl mx-auto">
                
                {{-- Starter --}}
                <div class="p-8 rounded-2xl border border-white/10 bg-[#101016]/80 backdrop-blur-xl flex flex-col justify-between hover:border-white/20 transition-all">
                    <div>
                        <div class="font-bold text-white text-lg">Starter</div>
                        <p class="text-xs text-slate-400 mt-1">For independent developers & side projects</p>
                        <div class="mt-6 flex items-baseline gap-1 font-mono">
                            <span class="text-4xl font-extrabold text-white">Free</span>
                            <span class="text-xs text-slate-400">/ forever</span>
                        </div>
                        <div class="text-xs font-semibold text-emerald-400 mt-1">0% transaction fees</div>

                        <ul class="mt-8 space-y-3.5 text-xs text-slate-300">
                            <li class="flex items-center gap-2.5"><x-icon name="check" class="w-4 h-4 text-emerald-400" /> 1 Connected UPI Account</li>
                            <li class="flex items-center gap-2.5"><x-icon name="check" class="w-4 h-4 text-emerald-400" /> Up to ₹50,000 / day volume</li>
                            <li class="flex items-center gap-2.5"><x-icon name="check" class="w-4 h-4 text-emerald-400" /> Dynamic QR &amp; Intent Links</li>
                            <li class="flex items-center gap-2.5"><x-icon name="check" class="w-4 h-4 text-emerald-400" /> Real-time status polling</li>
                            <li class="flex items-center gap-2.5"><x-icon name="check" class="w-4 h-4 text-emerald-400" /> Developer REST API</li>
                        </ul>
                    </div>

                    @if (config('auth.allow_registration', true))
                        <a href="{{ route('register') }}" class="btn btn-secondary w-full mt-8 py-2.5 text-xs font-semibold">Get Started Free</a>
                    @endif
                </div>

                {{-- Pro (Featured) --}}
                <div class="p-8 rounded-2xl border-2 border-[#38bdf8] bg-[#101016] shadow-xl shadow-[#38bdf8]/10 flex flex-col justify-between relative scale-105">
                    <span class="absolute -top-3 left-1/2 -translate-x-1/2 rounded-full bg-[#38bdf8] text-[#041419] px-3.5 py-0.5 text-xs font-bold uppercase tracking-wider">Most Popular</span>
                    <div>
                        <div class="font-bold text-white text-lg">Pro Merchant</div>
                        <p class="text-xs text-slate-400 mt-1">For growing startups & high-volume apps</p>
                        <div class="mt-6 flex items-baseline gap-1 font-mono">
                            <span class="text-4xl font-extrabold text-white">₹999</span>
                            <span class="text-xs text-slate-400">/ month</span>
                        </div>
                        <div class="text-xs font-semibold text-emerald-400 mt-1">0% transaction fees</div>

                        <ul class="mt-8 space-y-3.5 text-xs text-slate-300">
                            <li class="flex items-center gap-2.5"><x-icon name="check" class="w-4 h-4 text-[#38bdf8]" /> Up to 5 UPI accounts (Rotation)</li>
                            <li class="flex items-center gap-2.5"><x-icon name="check" class="w-4 h-4 text-[#38bdf8]" /> Up to ₹5,00,000 / day volume</li>
                            <li class="flex items-center gap-2.5"><x-icon name="check" class="w-4 h-4 text-[#38bdf8]" /> Automated Bank Signal Verification</li>
                            <li class="flex items-center gap-2.5"><x-icon name="check" class="w-4 h-4 text-[#38bdf8]" /> Signed Webhooks with Auto-Retries</li>
                            <li class="flex items-center gap-2.5"><x-icon name="check" class="w-4 h-4 text-[#38bdf8]" /> Anti-fraud &amp; Underpayment Defense</li>
                            <li class="flex items-center gap-2.5"><x-icon name="check" class="w-4 h-4 text-[#38bdf8]" /> Priority Developer Support</li>
                        </ul>
                    </div>

                    @if (config('auth.allow_registration', true))
                        <a href="{{ route('register') }}" class="btn btn-primary w-full mt-8 py-2.5 text-xs font-semibold shadow-md shadow-[#38bdf8]/20">Start 14-Day Free Trial</a>
                    @endif
                </div>

                {{-- Scale --}}
                <div class="p-8 rounded-2xl border border-white/10 bg-[#101016]/80 backdrop-blur-xl flex flex-col justify-between hover:border-white/20 transition-all">
                    <div>
                        <div class="font-bold text-white text-lg">Scale &amp; Enterprise</div>
                        <p class="text-xs text-slate-400 mt-1">For large volume platforms & agencies</p>
                        <div class="mt-6 flex items-baseline gap-1 font-mono">
                            <span class="text-4xl font-extrabold text-white">₹2,999</span>
                            <span class="text-xs text-slate-400">/ month</span>
                        </div>
                        <div class="text-xs font-semibold text-emerald-400 mt-1">0% transaction fees</div>

                        <ul class="mt-8 space-y-3.5 text-xs text-slate-300">
                            <li class="flex items-center gap-2.5"><x-icon name="check" class="w-4 h-4 text-emerald-400" /> Unlimited UPI Accounts</li>
                            <li class="flex items-center gap-2.5"><x-icon name="check" class="w-4 h-4 text-emerald-400" /> Unlimited Daily Processing Volume</li>
                            <li class="flex items-center gap-2.5"><x-icon name="check" class="w-4 h-4 text-emerald-400" /> Multi-Tenant Merchant System</li>
                            <li class="flex items-center gap-2.5"><x-icon name="check" class="w-4 h-4 text-emerald-400" /> White-Label Checkout Domain</li>
                            <li class="flex items-center gap-2.5"><x-icon name="check" class="w-4 h-4 text-emerald-400" /> Dedicated SLA &amp; Support Channel</li>
                        </ul>
                    </div>

                    @if (config('auth.allow_registration', true))
                        <a href="{{ route('register') }}" class="btn btn-secondary w-full mt-8 py-2.5 text-xs font-semibold">Contact Sales</a>
                    @endif
                </div>

            </div>
        </div>
    </section>

    {{-- Bottom Hero CTA --}}
    <section class="py-20 lg:py-28 bg-[#07070a] border-t border-white/10 relative overflow-hidden">
        <div class="absolute inset-0 pointer-events-none -z-10" style="background: radial-gradient(600px circle at 50% 100%, rgba(56, 189, 248, 0.15), transparent 70%);"></div>

        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8 text-center">
            <span class="w-12 h-12 rounded-2xl bg-white text-black inline-flex items-center justify-center shadow-lg shadow-white/20 mb-6">
                <svg viewBox="0 0 24 24" class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 12c-2-2.67-4-4-6-4a4 4 0 1 0 0 8c2 0 4-1.33 6-4Zm0 0c2 2.67 4 4 6 4a4 4 0 0 0 0-8c-2 0-4 1.33-6 4Z"/>
                </svg>
            </span>
            <h2 class="text-3xl sm:text-5xl font-extrabold text-white tracking-tight leading-tight">
                Ready to take 100% of your revenue?
            </h2>
            <p class="mt-4 text-slate-400 text-base sm:text-lg max-w-xl mx-auto">
                Set up your custom UPI payment gateway today. No aggregator delays, no rolling reserves.
            </p>

            <div class="mt-8 flex flex-wrap justify-center gap-4">
                @if (config('auth.allow_registration', true))
                    <a href="{{ route('register') }}" class="btn btn-primary btn-lg shadow-xl shadow-[#38bdf8]/20 px-8 py-3 text-base font-semibold">
                        Create Free Account <x-icon name="arrow-right" class="w-4 h-4 ml-1" />
                    </a>
                @endif
                <a href="{{ route('docs') }}" class="btn btn-secondary btn-lg px-8 py-3 text-base font-semibold">
                    View API Reference
                </a>
            </div>

            <div class="mt-12 text-xs text-slate-500">
                @built by <b class="text-white font-semibold">Varun</b> · Engineered for high performance &amp; zero-downtime payments
            </div>
        </div>
    </section>

    {{-- Footer --}}
    <footer class="border-t border-white/10 bg-[#07070a]/90 backdrop-blur-md py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-slate-500">
            <x-logo dark />
            <div>
                © {{ date('Y') }} AvinyaPay. All rights reserved.
            </div>
        </div>
    </footer>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Code tab switcher
        const tabBtns = document.querySelectorAll('.code-tab-btn');
        const codePanes = document.querySelectorAll('.code-pane');
        const copyBtn = document.getElementById('copy-code-btn');

        tabBtns.forEach(btn => {
            btn.addEventListener('click', function () {
                const targetLang = this.getAttribute('data-lang');
                
                tabBtns.forEach(b => {
                    b.classList.remove('active', 'bg-[#38bdf8]/15', 'text-[#38bdf8]', 'border', 'border-[#38bdf8]/30');
                    b.classList.add('text-slate-400');
                });
                this.classList.add('active', 'bg-[#38bdf8]/15', 'text-[#38bdf8]', 'border', 'border-[#38bdf8]/30');
                this.classList.remove('text-slate-400');

                codePanes.forEach(pane => pane.classList.add('hidden'));
                const activePane = document.getElementById('code-' + targetLang);
                if (activePane) activePane.classList.remove('hidden');
            });
        });

        if (copyBtn) {
            copyBtn.addEventListener('click', function () {
                const activePane = document.querySelector('.code-pane:not(.hidden)');
                if (activePane) {
                    navigator.clipboard.writeText(activePane.innerText.trim()).then(() => {
                        const span = copyBtn.querySelector('span');
                        if (span) {
                            const orig = span.textContent;
                            span.textContent = 'Copied!';
                            setTimeout(() => { span.textContent = orig; }, 1800);
                        }
                    });
                }
            });
        }
    });
</script>
@endpush
@endsection
