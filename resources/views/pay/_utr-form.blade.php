@php $left = \App\Models\Order::MAX_UTR_ATTEMPTS - $order->utr_attempts; @endphp
<form method="POST" action="{{ route('pay.utr', $order) }}" class="rounded-xl bg-[#17171f] border border-white/10 p-4 text-left {{ $class ?? '' }}">
    @csrf
    @if ($order->review_note)
        <div class="mb-3 flex items-start gap-2 rounded-lg bg-red-500/10 border border-red-500/20 px-3 py-2 text-xs text-red-400">
            <x-icon name="alert" class="w-4 h-4 mt-px text-red-400" />
            <div><b>Couldn't verify your last UTR.</b> {{ $order->review_note }} Please re-check the 12-digit UTR in your UPI app.</div>
        </div>
    @endif
    <label for="utr" class="text-xs font-semibold text-white">Already paid? Enter 12-digit UTR</label>
    <p class="text-[11px] text-slate-400 mt-0.5 mb-2.5">Find the <b>UTR / UPI Ref. No.</b> in your transaction details.</p>
    <div class="flex gap-2">
        <input id="utr" name="utr" inputmode="numeric" autocomplete="off" maxlength="14" required value="{{ old('utr') }}"
            placeholder="e.g. 412345678901" @class(['w-full rounded-lg bg-[#07070a] border border-white/10 px-3 py-2 text-xs font-mono tracking-wider text-white placeholder:text-slate-500 focus:outline-none focus:border-[#38bdf8] focus:ring-1 focus:ring-[#38bdf8]', 'border-red-500 focus:border-red-500 focus:ring-red-500' => $errors->has('utr')])>
        <button type="submit" class="px-4 py-2 rounded-lg text-xs font-bold shrink-0 bg-[#38bdf8] text-[#07070a] hover:bg-[#7dd3fc] transition-all shadow-md shadow-[#38bdf8]/20">Confirm Payment</button>
    </div>
    @error('utr')<p class="mt-1.5 flex items-start gap-1.5 text-xs text-red-400"><x-icon name="alert" class="mt-px w-3.5 h-3.5" /> <span>{{ $message }}</span></p>@enderror
    <p class="mt-2 text-[10px] text-slate-500">{{ $left }} {{ Str::plural('attempt', $left) }} left.</p>
</form>
