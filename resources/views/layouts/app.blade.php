@extends('layouts.base')

@section('body')
@php
    $user = auth()->user();
    $reviewCount = $user->orders()->where('status', \App\Models\Order::REVIEW)->count();
    $sections = [
        'Payments' => [
            ['dashboard', 'Dashboard', 'home', 'dashboard'],
            ['orders.index', 'Orders', 'receipt', 'orders.*'],
            ['accounts.index', 'UPI Accounts', 'qr', 'accounts.*'],
        ],
        'Developers' => [
            ['settings', 'API & Webhooks', 'key', 'settings*'],
            ['docs', 'API Docs', 'book', 'docs'],
        ],
    ];
    if ($user->isAdmin()) {
        $sections['Admin'] = [
            ['admin.merchants', 'Merchants', 'users', 'admin.merchants*'],
            ['admin.orders', 'All Orders', 'chart', 'admin.orders'],
            ['admin.transactions.index', 'Transaction Debugger', 'search', 'admin.transactions.*'],
        ];
    }
@endphp
<div class="min-h-full bg-transparent text-[#fafafc]">
    {{-- Ambient Top Glow --}}
    <div class="fixed top-0 inset-x-0 h-96 pointer-events-none -z-10" style="background: radial-gradient(50% 80% at 50% 0%, rgba(56, 189, 248, 0.12), transparent 75%);"></div>

    {{-- Mobile top bar --}}
    <div class="lg:hidden sticky top-0 z-30 flex items-center justify-between bg-[#0a0a0f]/90 backdrop-blur-xl/90 backdrop-blur border-b border-white/10 px-4 h-14">
        <a href="{{ route('dashboard') }}"><x-logo /></a>
        <button type="button" data-sidebar-toggle class="btn btn-ghost p-2" aria-label="Open menu">
            <x-icon name="menu" class="w-6 h-6 text-slate-300" />
        </button>
    </div>
    <div id="backdrop" data-sidebar-close class="hidden lg:hidden fixed inset-0 z-30 bg-black/60 backdrop-blur-sm"></div>

    {{-- Sidebar --}}
    <aside id="sidebar" class="fixed inset-y-0 left-0 z-40 w-64 -translate-x-full lg:translate-x-0 transition-transform bg-[#0a0a0f]/90 backdrop-blur-xl border-r border-white/10 flex flex-col">
        <div class="h-16 flex items-center px-5 border-b border-white/10">
            <a href="{{ route('dashboard') }}"><x-logo /></a>
        </div>

        <div class="px-3 pt-4">
            <a href="{{ route('orders.create') }}" class="btn btn-primary w-full shadow-lg shadow-[#38bdf8]/20"><x-icon name="plus" class="w-4 h-4" /> New payment link</a>
        </div>

        <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-6">
            @foreach ($sections as $heading => $items)
                <div>
                    <div class="px-3 mb-2 text-[10px] font-bold uppercase tracking-wider text-slate-500">{{ $heading }}</div>
                    <div class="space-y-1">
                        @foreach ($items as [$route, $label, $icon, $pattern])
                            @php $active = request()->routeIs($pattern); @endphp
                            <a href="{{ route($route) }}" @class([
                                'group flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition',
                                'bg-[#38bdf8]/10 text-[#38bdf8] border border-[#38bdf8]/30 font-semibold' => $active,
                                'text-slate-400 hover:bg-white/5 hover:text-white' => ! $active,
                            ])>
                                <x-icon :name="$icon" @class(['w-5 h-5', 'text-[#38bdf8]' => $active, 'text-slate-400 group-hover:text-white' => ! $active]) />
                                <span class="flex-1">{{ $label }}</span>
                                @if ($route === 'orders.index' && $reviewCount)
                                    <span class="rounded-full bg-[#38bdf8] px-1.5 text-[11px] font-bold text-[#041419] tabular-nums" title="Payments waiting for your review">{{ $reviewCount }}</span>
                                @endif
                            </a>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </nav>

        <div class="border-t border-white/10 p-3">
            <div class="flex items-center gap-3 rounded-lg px-2 py-2 bg-white/5 border border-white/5">
                <x-avatar :name="$user->displayName()" class="!bg-[#17171f] !text-[#38bdf8]" />
                <div class="min-w-0 flex-1">
                    <div class="text-sm font-semibold text-white truncate">{{ $user->displayName() }}</div>
                    <div class="text-xs text-slate-400 truncate">{{ $user->email }}</div>
                </div>
                <form method="POST" action="{{ route('logout') }}">@csrf
                    <button class="p-2 rounded-lg text-slate-400 hover:bg-white/10 hover:text-white transition-colors" title="Log out" aria-label="Log out"><x-icon name="logout" class="w-4 h-4" /></button>
                </form>
            </div>
        </div>
    </aside>

    {{-- Main Content --}}
    <div class="lg:pl-64">
        <main class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-10 py-6 lg:py-10">
            <div class="flex flex-wrap items-end justify-between gap-4 mb-8">
                <div class="min-w-0">
                    @hasSection('back')
                        <a href="@yield('back')" class="inline-flex items-center gap-1.5 text-xs text-slate-400 hover:text-[#38bdf8] mb-2 transition-colors"><x-icon name="arrow-left" class="w-3.5 h-3.5" /> Back</a>
                    @endif
                    <h1 class="text-2xl font-bold tracking-tight text-white truncate">@yield('title')</h1>
                    @hasSection('subtitle')<p class="mt-1.5 text-sm font-medium text-slate-300">@yield('subtitle')</p>@endif
                </div>
                <div class="flex w-full flex-wrap items-center gap-2 sm:w-auto">@yield('actions')</div>
            </div>

            @if (session('status'))
                <div class="mb-6 flex items-start gap-3 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 px-4 py-3 text-sm">
                    <x-icon name="check-circle" class="w-5 h-5 text-emerald-400 shrink-0" /><div>{{ session('status') }}</div>
                </div>
            @endif
            @if ($errors->any())
                <div class="mb-6 flex items-start gap-3 rounded-xl bg-red-500/10 border border-red-500/20 text-red-400 px-4 py-3 text-sm">
                    <x-icon name="alert" class="w-5 h-5 text-red-400 shrink-0" />
                    <div>@foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</div>
@endsection
