@extends('layouts.app')

@section('title', 'Activity')

@section('content')
    <div class="tabs" data-panels="#activity-panels">
        <button class="tab-btn active" data-tab="transactions">Transactions</button>
        <button class="tab-btn" data-tab="orders">Orders</button>
    </div>

    <div id="activity-panels">

        <!-- Transactions -->
        <div class="tab-panel" id="transactions">
            <div class="card">
                @forelse($transactions as $tx)
                    @php
                        $isPositive = in_array(strtolower($tx->type), ['sell', 'distribution', 'deposit']);
                        $badgeClass = match(strtolower($tx->type)) {
                            'buy' => 'negative',
                            'sell' => 'positive',
                            'distribution' => 'positive',
                            'deposit' => 'neutral',
                            default => 'muted'
                        };
                    @endphp
                    <div class="list-row">
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <span class="badge {{ $badgeClass }}">{{ strtoupper($tx->type) }}</span>
                            <div>
                                <div class="primary">{{ $tx->asset->title ?? $tx->description ?? 'Transaction' }}</div>
                                <div class="secondary">
                                    {{ $tx->created_at->format('M j, Y') }}
                                    @if($tx->units)
                                        · {{ $tx->units }} units
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="value {{ $isPositive ? 'positive' : 'negative' }}">
                            {{ $isPositive ? '+' : '-' }}€{{ number_format(abs($tx->amount), 2) }}
                        </div>
                    </div>
                @empty
                    <div style="padding: 20px; text-align: center;" class="muted">No recent transactions found.</div>
                @endforelse

                @if($transactions->hasPages())
                    <div style="padding: 16px;">
                        {{ $transactions->links() }}
                    </div>
                @endif
            </div>
        </div>

        <!-- Orders -->
        <div class="tab-panel" id="orders">
            <div class="card">
                <table>
                    <thead>
                        <tr>
                            <th>Asset</th>
                            <th>Side</th>
                            <th>Units</th>
                            <th>Price</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orders as $order)
                            <tr>
                                <td style="font-weight: 700;">{{ $order->asset->title ?? 'N/A' }}</td>
                                <td>
                                    <span class="badge {{ strtolower($order->type) === 'buy' ? 'negative' : 'positive' }}">
                                        {{ strtoupper($order->type) }}
                                    </span>
                                </td>
                                <td>{{ $order->units }}</td>
                                <td>€{{ number_format($order->price_per_unit, 2) }}</td>
                                <td>
                                    <span class="badge {{ $order->status === 'active' ? 'positive' : 'muted' }}">
                                        {{ ucfirst($order->status) }}
                                    </span>
                                </td>
                                <td>
                                    @if($order->status === 'active')
                                        <form action="{{ route('orders.cancel', $order->id) }}" method="POST" style="display:inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger-outline btn-sm">Cancel</button>
                                        </form>
                                    @else
                                        <span class="muted" style="font-size: 0.82rem;">{{ $order->updated_at->format('M j') }}</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="muted" style="text-align: center; padding: 20px;">
                                    No active or past orders found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
@endsection