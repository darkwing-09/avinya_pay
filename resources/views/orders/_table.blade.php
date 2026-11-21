@if ($orders->isEmpty())
    <x-empty icon="receipt" title="No orders yet" text="Create a payment link or call the API to start accepting UPI payments.">
        <a href="{{ route('orders.create') }}" class="btn btn-primary"><x-icon name="plus" class="w-4 h-4" /> Create payment link</a>
    </x-empty>
@else
<div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-white/10">
        <thead class="bg-[#17171f]/95 border-b border-white/10">
            <tr>
                <th class="th text-slate-300 font-bold">Order</th>
                @isset($showMerchant)<th class="th text-slate-300 font-bold">Merchant</th>@endisset
                <th class="th text-slate-300 font-bold">Customer</th>
                <th class="th text-right text-slate-300 font-bold">Amount</th>
                <th class="th text-slate-300 font-bold">Status</th>
                <th class="th text-slate-300 font-bold">UTR</th>
                <th class="th text-slate-300 font-bold">Date</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-white/5 bg-transparent">
            @foreach ($orders as $order)
                <tr class="hover:bg-white/5 cursor-pointer transition-colors" data-href="{{ route('orders.show', $order) }}">
                    <td class="td">
                        <a href="{{ route('orders.show', $order) }}" class="font-mono text-[13px] font-bold text-white hover:text-[#38bdf8] transition-colors">{{ $order->order_no }}</a>
                        @if ($order->client_order_id !== $order->order_no)
                            <div class="text-xs text-slate-400 mt-0.5 font-mono">Ref: {{ $order->client_order_id }}</div>
                        @endif
                    </td>
                    @isset($showMerchant)<td class="td text-slate-200 font-medium">{{ $order->user->displayName() }}</td>@endisset
                    <td class="td text-slate-200 font-medium">{{ $order->customer_name ?: ($order->customer_mobile ?: '—') }}</td>
                    <td class="td text-right font-bold text-white tabular-nums text-sm">₹{{ number_format($order->amount, 2) }}</td>
                    <td class="td"><x-status :status="$order->status" /></td>
                    <td class="td font-mono text-xs text-slate-300">
                        @if ($order->status === 'review')
                            ••••••••{{ substr($order->customer_utr, -4) }}<div class="font-sans text-[11px] text-[#38bdf8]">submitted by customer</div>
                        @else
                            {{ $order->utr ?? '—' }}
                        @endif
                    </td>
                    <td class="td text-slate-300 whitespace-nowrap">
                        <div class="text-white font-medium">{{ $order->created_at->format('d M Y') }}</div>
                        <div class="text-xs text-slate-400">{{ $order->created_at->format('h:i A') }}</div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif
