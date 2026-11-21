@extends('layouts.app')
@section('title', 'Orders')
@section('subtitle', $environment === 'sandbox' ? 'Simulated TEST payments, isolated from Live transactions.' : 'Every Live payment request created from the API or dashboard.')
@section('actions')
    <a href="{{ route('orders.create') }}" class="btn btn-primary"><x-icon name="plus" class="w-4 h-4" /> Payment link</a>
@endsection

@section('content')
<div class="mb-5 inline-flex rounded-lg bg-white/5 border border-white/10 p-1 text-sm font-medium">
    <a href="{{ route('orders.index', ['environment' => 'live']) }}" class="rounded-md px-4 py-2 {{ $environment === 'live' ? 'bg-[#38bdf8] text-slate-950 font-bold shadow-sm' : 'text-slate-400 hover:text-white' }}">Live</a>
    <a href="{{ route('orders.index', ['environment' => 'sandbox']) }}" class="rounded-md px-4 py-2 {{ $environment === 'sandbox' ? 'bg-amber-400 text-amber-950 font-bold shadow-sm' : 'text-slate-400 hover:text-white' }}">Sandbox · TEST</a>
</div>
@if ($environment === 'sandbox')
    <div class="mb-5 rounded-lg bg-amber-500/10 border border-amber-500/20 px-4 py-3 text-sm text-amber-300">Sandbox orders never affect Live balances, limits, or provider accounts.</div>
@endif
@php
    $current = request('status', '');
    $tabs = ['' => ['All', $counts->sum()], 'paid' => ['Paid', $counts['paid'] ?? 0], 'review' => ['Needs review', $counts['review'] ?? 0], 'pending' => ['Pending', $counts['pending'] ?? 0], 'failed' => ['Failed', $counts['failed'] ?? 0], 'expired' => ['Expired', $counts['expired'] ?? 0]];
@endphp
<div class="card overflow-hidden bg-[#101016]/95 border border-white/10 shadow-lg">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 px-5 py-3 border-b border-white/10">
        <nav class="flex gap-1 overflow-x-auto -mx-1">
            @foreach ($tabs as $value => [$label, $count])
                <a href="{{ route('orders.index', array_filter(['environment' => $environment, 'status' => $value, 'q' => request('q')])) }}" @class([
                    'inline-flex items-center gap-2 whitespace-nowrap rounded-lg px-3 py-1.5 text-sm font-medium transition',
                    'bg-[#38bdf8] text-slate-950 font-bold' => $current === $value,
                    'text-slate-300 hover:bg-white/5 hover:text-white' => $current !== $value,
                ])>
                    {{ $label }}
                    <span @class(['rounded-full px-1.5 text-xs tabular-nums', 'bg-black/20 text-slate-950 font-bold' => $current === $value, 'bg-white/10 text-slate-300' => $current !== $value])>{{ $count }}</span>
                </a>
            @endforeach
        </nav>
        <form class="relative md:w-80">
            <input type="hidden" name="environment" value="{{ $environment }}">
            @if ($current)<input type="hidden" name="status" value="{{ $current }}">@endif
            <x-icon name="search" class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" />
            <input name="q" value="{{ request('q') }}" placeholder="Search order ID, reference or UTR" class="input pl-9">
        </form>
    </div>

    @include('orders._table')

    @if ($orders->hasPages())
        <div class="px-5 py-3 border-t border-white/10">{{ $orders->links() }}</div>
    @endif
</div>
@endsection
