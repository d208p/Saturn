@extends('layouts.app')

@section('title', 'Activity')

@push('styles')
<style>
    .act { --line: rgba(255,255,255,0.07); }
    .act .tabs { border-bottom: 1px solid var(--line); gap: 28px; margin-bottom: 32px; }
    .act .tab-btn { padding: 12px 0; font-size: .88rem; letter-spacing: .01em; }
    .act .num { font-variant-numeric: tabular-nums; }
    .act .eyebrow { font-size: .64rem; text-transform: uppercase; letter-spacing: .12em; color: var(--slate); font-weight: 600; }

    .act-toolbar { display: grid; grid-template-columns: 1.6fr 1fr auto; gap: 12px; align-items: end; }
    .field label { display: block; font-size: .64rem; text-transform: uppercase; letter-spacing: .12em; color: var(--slate); margin-bottom: 6px; font-weight: 600; }
    .field input, .field select {
        width: 100%; background: transparent; border: 0; border-bottom: 1px solid var(--line);
        color: var(--ivory); font-family: inherit; font-size: .85rem; padding: 8px 2px; outline: none; border-radius: 0;
    }
    .field select option { background: var(--midnight); color: var(--ivory); }
    .field input:focus, .field select:focus { border-bottom-color: var(--gold); }
    .link-btn { background: none; border: 0; color: var(--gold); font-size: .8rem; font-weight: 600; cursor: pointer; padding: 8px 4px; white-space: nowrap; font-family: inherit; }
    .link-btn:disabled { color: var(--slate); opacity: .5; cursor: default; }
    .result-count { font-size: .78rem; color: var(--slate); margin: 18px 0 4px; }

    /* Transactions */
    .tx-row { display: grid; grid-template-columns: 96px minmax(0, 1fr) 150px 140px; gap: 20px; align-items: center; padding: 18px 0; border-top: 1px solid var(--line); }
    .tx-list > .tx-row:last-of-type { border-bottom: 1px solid var(--line); }
    .tx-date { font-size: .8rem; color: var(--slate); }
    .tx-title { font-weight: 700; font-size: .9rem; }
    .tx-sub { font-size: .76rem; color: var(--slate); margin-top: 2px; }
    .tx-amt { text-align: right; font-weight: 700; }

    /* Orders */
    .seg { display: inline-flex; gap: 22px; margin-bottom: 20px; }
    .seg button { background: none; border: 0; color: var(--slate); font-size: .82rem; font-weight: 600; padding: 6px 0; cursor: pointer; border-bottom: 2px solid transparent; font-family: inherit; }
    .seg button.active { color: var(--gold); border-bottom-color: var(--gold); }
    .ord-table { width: 100%; border-collapse: collapse; }
    .ord-table th { font-size: .64rem; padding: 12px 10px; border-bottom: 1px solid var(--line); }
    .ord-table td { padding: 16px 10px; border-bottom: 1px solid var(--line); font-size: .88rem; vertical-align: middle; }
    .ord-table .r { text-align: right; }
    .btn-cancel { background: transparent; color: var(--red); border: 1px solid rgba(248,113,113,.3); }
    .btn-cancel:hover { background: var(--red-dim); }

    .empty { text-align: center; padding: 56px 16px; color: var(--slate); font-size: .88rem; border-top: 1px solid var(--line); }
    .empty strong { display: block; color: var(--ivory); font-size: .95rem; margin-bottom: 6px; }
    .empty .link-btn { margin-top: 10px; }
    .note { font-size: .76rem; color: var(--slate); margin-top: 14px; }

    @media (max-width: 680px) {
        .act .tabs { gap: 20px; }
        .act-toolbar { grid-template-columns: 1fr 1fr; }
        .act-toolbar .field.search { grid-column: 1 / -1; }
        .tx-row { grid-template-columns: 1fr auto; gap: 4px 12px; padding: 16px 0; }
        .tx-row .tx-date { grid-column: 1; grid-row: 2; }
        .tx-row .tx-main { grid-column: 1; grid-row: 1; }
        .tx-row .tx-type { grid-column: 1; grid-row: 3; margin-top: 4px; }
        .tx-row .tx-amt { grid-column: 2; grid-row: 1 / span 3; align-self: center; }
        .ord-table thead { display: none; }
        .ord-table, .ord-table tbody, .ord-table tr, .ord-table td { display: block; width: 100%; }
        .ord-table tr { display: grid; grid-template-columns: 1fr auto; gap: 6px 12px; padding: 16px 0; border-bottom: 1px solid var(--line); }
        .ord-table td { border: 0; padding: 0; text-align: left !important; }
        .ord-table td.c-asset { font-weight: 700; }
        .ord-table td.c-status { text-align: right !important; }
        .ord-table td.c-side, .ord-table td.c-shares, .ord-table td.c-price { grid-column: 1 / -1; font-size: .78rem; color: var(--slate); }
        .ord-table td[data-label]::before { content: attr(data-label) "  "; font-size: .64rem; text-transform: uppercase; letter-spacing: .1em; margin-right: 6px; }
        .ord-table td.c-action { grid-column: 1 / -1; margin-top: 6px; }
        .ord-table td.c-action .btn { width: 100%; }
    }
</style>
@endpush

@section('content')
@php
    $inflowTypes  = ['deposit', 'distribution', 'sell', 'sell_equity', 'sell_secondary', 'income'];
    $outflowTypes = ['buy', 'buy_equity', 'buy_secondary', 'withdrawal'];

    $typeLabel = fn($t) => match($t) {
        'buy_equity' => 'Buy equity', 'buy_secondary' => 'Buy secondary',
        'sell_equity' => 'Sell equity', 'sell_secondary' => 'Sell secondary',
        'cancel_listing' => 'Cancel listing',
        default => ucfirst(str_replace('_', ' ', $t)),
    };

    $txTypes = $transactions->getCollection()->map(fn($t) => strtolower($t->type))->unique()->sort()->values();
    $ordersCol = collect($orders);
@endphp

<div class="act">
    <div class="tabs" data-panels="#activity-panels">
        <button class="tab-btn active" data-tab="transactions">Transactions</button>
        <button class="tab-btn" data-tab="orders">Orders</button>
    </div>

    <div id="activity-panels">

        {{-- ================= TRANSACTIONS ================= --}}
        <div class="tab-panel active" id="transactions">
            {{-- Hardcoded: filters apply to the current page only. Searching across all pages needs server-side filtering in the controller. --}}
            <div class="act-toolbar" id="txToolbar" role="search">
                <div class="field search"><label for="tx-q">Search</label><input id="tx-q" type="search" placeholder="Asset or description" autocomplete="off"></div>
                <div class="field"><label for="tx-type">Type</label>
                    <select id="tx-type">
                        <option value="">All types</option>
                        <option value="@in">Money in</option>
                        <option value="@out">Money out</option>
                        @foreach($txTypes as $t)<option value="{{ $t }}">{{ $typeLabel($t) }}</option>@endforeach
                    </select></div>
                <button type="button" class="link-btn" id="txReset" disabled>Reset</button>
            </div>
            <div class="result-count" id="txCount" aria-live="polite"></div>

            <div class="tx-list" id="txList">
                @foreach($transactions as $tx)
                    @php
                        $t = strtolower($tx->type);
                        $flow = in_array($t, $inflowTypes) ? 'in' : (in_array($t, $outflowTypes) ? 'out' : 'other');
                        $badge = $flow === 'in' ? 'positive' : ($flow === 'out' ? 'negative' : 'neutral');
                        $title = $tx->asset->title ?? $tx->description ?? $typeLabel($t);
                        $amount = abs($tx->amount);
                    @endphp
                    <div class="tx-row" data-search="{{ Str::lower($title . ' ' . ($tx->description ?? '')) }}" data-type="{{ $t }}" data-flow="{{ $flow }}">
                        <div class="tx-date num">{{ $tx->created_at->format('M j, Y') }}</div>
                        <div class="tx-main">
                            <div class="tx-title">{{ $title }}</div>
                            @if($tx->shares)<div class="tx-sub num">{{ number_format($tx->shares) }} {{ Str::plural('share', $tx->shares) }}</div>@endif
                        </div>
                        <div class="tx-type"><span class="badge {{ $badge }}">{{ $typeLabel($t) }}</span></div>
                        <div class="tx-amt num {{ $flow === 'in' ? 'positive' : ($flow === 'out' ? 'negative' : 'muted') }}">
                            @if($flow === 'other' && $amount == 0) — @else {{ $flow === 'in' ? '+' : ($flow === 'out' ? '−' : '') }}€{{ number_format($amount, 2) }} @endif
                        </div>
                    </div>
                @endforeach

                <div class="empty" id="txEmpty" @if($transactions->count()) style="display:none;" @endif>
                    @if($transactions->count())
                        <strong>No transactions match these filters</strong>Try widening your search.
                        <div><button type="button" class="link-btn" id="txReset2">Reset filters</button></div>
                    @else
                        <strong>No transactions yet</strong>Deposits, purchases, sales and payouts will be listed here.
                    @endif
                </div>
            </div>

            @if($transactions->hasPages())
                <div style="margin-top: 12px;">{{ $transactions->links() }}</div>
            @endif
        </div>

        {{-- ================= ORDERS ================= --}}
        <div class="tab-panel" id="orders">
            <div class="seg" id="orderStatus" role="tablist">
                <button type="button" class="active" data-status="">All ({{ $ordersCol->count() }})</button>
                <button type="button" data-status="active">Active ({{ $ordersCol->where('status', 'active')->count() }})</button>
                <button type="button" data-status="past">Past ({{ $ordersCol->where('status', '!=', 'active')->count() }})</button>
            </div>

            <table class="ord-table" id="orderTable" @if($ordersCol->isEmpty()) style="display:none;" @endif>
                <thead>
                    <tr><th>Asset</th><th>Side</th><th class="r">Shares</th><th class="r">Price / share</th><th>Status</th><th class="r">Action</th></tr>
                </thead>
                <tbody>
                    @foreach($ordersCol as $order)
                        <tr data-status="{{ $order->status === 'active' ? 'active' : 'past' }}">
                            <td class="c-asset" style="font-weight:700;">{{ $order->asset->title ?? 'N/A' }}</td>
                            {{-- Hardcoded: every order is a sell listing in the current data model. --}}
                            <td class="c-side" data-label="Side"><span class="badge positive">Sell</span></td>
                            <td class="c-shares r num" data-label="Shares">{{ number_format($order->shares) }}</td>
                            <td class="c-price r num" data-label="Price">€{{ number_format($order->price_per_share, 2) }}</td>
                            <td class="c-status"><span class="badge {{ $order->status === 'active' ? 'positive' : 'muted' }}">{{ ucfirst($order->status) }}</span></td>
                            <td class="c-action r">
                                @if($order->status === 'active')
                                    <form action="{{ route('listings.cancel', $order->id) }}" method="POST" style="display:inline;"
                                          onsubmit="return confirm('Cancel this order? Your shares will be returned to your portfolio.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-cancel">Cancel</button>
                                    </form>
                                @else
                                    <span class="muted num" style="font-size:.82rem;">{{ $order->updated_at->format('M j') }}</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="empty" id="orderEmpty" @if($ordersCol->isNotEmpty()) style="display:none;" @endif>
                @if($ordersCol->isEmpty())
                    <strong>No orders yet</strong>Orders you place on the secondary market will appear here.
                @endif
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    /* Transactions: search + type filter (current page). */
    const rows = Array.from(document.querySelectorAll('#txList .tx-row'));
    if (rows.length) {
        const q = document.getElementById('tx-q'), type = document.getElementById('tx-type');
        const empty = document.getElementById('txEmpty'), count = document.getElementById('txCount');
        const reset1 = document.getElementById('txReset'), reset2 = document.getElementById('txReset2');

        function apply() {
            const term = q.value.trim().toLowerCase(), t = type.value;
            let shown = 0;
            rows.forEach(r => {
                const typeOk = !t || (t === '@in' ? r.dataset.flow === 'in' : t === '@out' ? r.dataset.flow === 'out' : r.dataset.type === t);
                const ok = typeOk && (!term || r.dataset.search.includes(term));
                r.style.display = ok ? '' : 'none';
                if (ok) shown++;
            });
            empty.style.display = shown ? 'none' : 'block';
            empty.innerHTML = '<strong>No transactions match these filters</strong>Try widening your search.<div><button type="button" class="link-btn" id="txReset2">Reset filters</button></div>';
            document.getElementById('txReset2').addEventListener('click', reset);
            count.textContent = shown + ' of ' + rows.length + ' on this page';
            reset1.disabled = !(q.value || type.value);
        }
        function reset() { q.value = ''; type.value = ''; apply(); }
        q.addEventListener('input', apply);
        type.addEventListener('change', apply);
        reset1.addEventListener('click', reset);
        apply();
    }

    /* Orders: status filter. */
    const seg = document.getElementById('orderStatus'), table = document.getElementById('orderTable'), box = document.getElementById('orderEmpty');
    if (seg && table) {
        const orows = Array.from(table.tBodies[0].rows);
        seg.querySelectorAll('button').forEach(btn => btn.addEventListener('click', () => {
            seg.querySelectorAll('button').forEach(b => b.classList.toggle('active', b === btn));
            const s = btn.dataset.status;
            let shown = 0;
            orows.forEach(r => { const ok = !s || r.dataset.status === s; r.style.display = ok ? '' : 'none'; if (ok) shown++; });
            table.style.display = shown ? '' : 'none';
            box.style.display = shown ? 'none' : 'block';
            if (!shown) box.innerHTML = '<strong>Nothing in this view</strong>No orders with this status.';
        }));
    }
})();
</script>
@endpush