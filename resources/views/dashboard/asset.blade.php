@extends('layouts.app')

@section('title', $asset->title)

@push('styles')
<style>
    .ast { --line: rgba(255,255,255,0.07); }
    .ast .num { font-variant-numeric: tabular-nums; }
    .ast .eyebrow { font-size: .64rem; text-transform: uppercase; letter-spacing: .12em; color: var(--slate); font-weight: 600; }

    .back { display: inline-flex; align-items: center; gap: 6px; font-size: .82rem; color: var(--slate); font-weight: 600; margin-bottom: 24px; transition: color .15s; }
    .back:hover { color: var(--gold); }
    .back .icon { width: 14px; height: 14px; }

    .a-head { display: flex; justify-content: space-between; align-items: flex-start; gap: 24px; flex-wrap: wrap; margin-bottom: 28px; }
    .a-title { display: flex; align-items: center; gap: 12px; }
    .a-title h1 { font-size: clamp(1.5rem, 3.4vw, 2.1rem); font-weight: 700; letter-spacing: -.02em; line-height: 1.15; }
    .a-meta { font-size: .88rem; color: var(--slate); margin-top: 6px; }

    /* Image carousel */
    .a-hero { position: relative; border-radius: 12px; overflow: hidden; margin-bottom: 40px; background: #0f1729; }
    .a-carousel-track { position: relative; aspect-ratio: 16 / 7; min-height: 220px; }
    .a-slide { position: absolute; inset: 0; opacity: 0; visibility: hidden; transition: opacity .35s ease; }
    .a-slide.is-active { opacity: 1; visibility: visible; z-index: 1; }
    .a-slide img { width: 100%; height: 100%; object-fit: cover; display: block; }
    .a-slide-fallback { width: 100%; height: 100%; min-height: 220px; display: flex; align-items: center; justify-content: center; color: var(--gold); font-size: 4rem; font-weight: 300; background: radial-gradient(circle at 30% 20%, rgba(198,161,91,.16), transparent 60%), #0f1729; }
    .a-carousel-arrow { position: absolute; z-index: 3; top: 50%; transform: translateY(-50%); width: 42px; height: 42px; border: 1px solid rgba(255,255,255,.18); border-radius: 50%; background: rgba(11,18,32,.72); color: var(--ivory); display: flex; align-items: center; justify-content: center; cursor: pointer; backdrop-filter: blur(8px); transition: background .2s, border-color .2s; }
    .a-carousel-arrow:hover { background: rgba(11,18,32,.95); border-color: var(--gold); }
    .a-carousel-arrow:focus-visible, .a-carousel-dot:focus-visible { outline: 2px solid var(--gold); outline-offset: 3px; }
    .a-carousel-arrow.prev { left: 16px; }
    .a-carousel-arrow.next { right: 16px; }
    .a-carousel-arrow svg { width: 18px; height: 18px; fill: none; stroke: currentColor; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }
    .a-carousel-footer { position: absolute; z-index: 3; inset: auto 0 0; display: flex; justify-content: center; align-items: center; gap: 8px; padding: 28px 16px 14px; background: linear-gradient(transparent, rgba(11,18,32,.72)); }
    .a-carousel-dot { width: 7px; height: 7px; border: 0; border-radius: 50%; padding: 0; background: rgba(245,242,234,.5); cursor: pointer; transition: width .2s, background .2s; }
    .a-carousel-dot.is-active { width: 22px; border-radius: 10px; background: var(--gold); }
    .a-carousel-count { position: absolute; z-index: 3; right: 14px; bottom: 14px; font-size: .72rem; color: var(--ivory); background: rgba(11,18,32,.72); border: 1px solid rgba(255,255,255,.12); border-radius: 100px; padding: 4px 9px; font-variant-numeric: tabular-nums; }
    .a-hero.single .a-carousel-footer, .a-hero.single .a-carousel-arrow, .a-hero.single .a-carousel-count { display: none; }

    .a-grid { display: grid; grid-template-columns: minmax(0, 1fr) 340px; gap: 64px; align-items: start; }
    .a-main > section { padding: 32px 0; border-top: 1px solid var(--line); }
    .a-main > section:first-child { border-top: 0; padding-top: 0; }
    .a-main h2, .a-side h2 { font-size: .95rem; font-weight: 700; margin-bottom: 14px; }
    .a-text { font-size: .92rem; color: var(--slate); line-height: 1.75; max-width: 68ch; }

    .dl { display: grid; grid-template-columns: 1fr 1fr; gap: 0 48px; }
    .dl .r { display: flex; justify-content: space-between; gap: 12px; padding: 13px 0; border-top: 1px solid var(--line); font-size: .88rem; }
    .dl .r span:first-child { color: var(--slate); }
    .dl .r span:last-child { font-weight: 700; text-align: right; }

    .bar { height: 6px; background: var(--line); border-radius: 3px; overflow: hidden; }
    .bar b { display: block; height: 100%; background: var(--gold); border-radius: 3px; }
    .bar-row { display: flex; justify-content: space-between; font-size: .85rem; margin-bottom: 10px; gap: 12px; }

    .buyout { border-left: 2px solid var(--gold); padding-left: 24px; }
    .buyout .vote-bar { position: relative; height: 8px; background: var(--line); border-radius: 4px; overflow: visible; margin: 8px 0 12px; }
    .buyout .vote-bar b { display: block; height: 100%; background: var(--green); border-radius: 4px; }
    .buyout .vote-bar i { position: absolute; top: -4px; bottom: -4px; width: 2px; background: var(--ivory); opacity: .6; }
    .vote-actions { display: flex; gap: 10px; align-items: center; flex-wrap: wrap; margin-top: 16px; }
    .alert { padding: 12px 16px; border-radius: 8px; font-size: .86rem; border: 1px solid; margin-top: 12px; }
    .alert-success { background: var(--green-dim); border-color: rgba(74,222,128,.3); color: var(--green); }
    .alert-error { background: var(--red-dim); border-color: rgba(248,113,113,.3); color: var(--red); }
    .btn:disabled { opacity: .45; cursor: not-allowed; transform: none; box-shadow: none; }

    .dist { display: grid; grid-template-columns: 1fr 1fr auto; gap: 16px; padding: 14px 0; border-top: 1px solid var(--line); font-size: .88rem; }
    .dist-list > :last-child { border-bottom: 1px solid var(--line); }
    .dist.head { border-top: 0; font-size: .64rem; text-transform: uppercase; letter-spacing: .12em; color: var(--slate); font-weight: 600; padding-top: 0; }
    .dist .amt { text-align: right; font-weight: 700; }
    .empty { padding: 28px 0; color: var(--slate); font-size: .88rem; border-top: 1px solid var(--line); border-bottom: 1px solid var(--line); text-align: center; }

    .a-side { position: sticky; top: 96px; }
    .pos-total { font-size: 2rem; font-weight: 300; letter-spacing: -.02em; margin: 6px 0 4px; }
    .pos-gain { font-size: .86rem; font-weight: 600; }
    .kv { display: flex; justify-content: space-between; gap: 12px; padding: 12px 0; border-top: 1px solid var(--line); font-size: .86rem; }
    .kv span:first-child { color: var(--slate); }
    .kv span:last-child { font-weight: 700; }
    .side-block { padding-bottom: 28px; margin-bottom: 28px; border-bottom: 1px solid var(--line); }
    .side-block:last-child { border-bottom: 0; margin-bottom: 0; padding-bottom: 0; }
    .actions { display: flex; gap: 10px; margin-top: 20px; }
    .state { font-size: .84rem; color: var(--slate); line-height: 1.6; margin-top: 12px; }

    @media (max-width: 980px) {
        .a-grid { grid-template-columns: 1fr; gap: 40px; }
        .a-side { position: static; order: -1; }
        .a-carousel-track { aspect-ratio: 16 / 9; }
    }
    @media (max-width: 560px) {
        .dl { grid-template-columns: 1fr; }
        .vote-actions .btn { flex: 1; }
        .a-carousel-track { aspect-ratio: 4 / 3; min-height: 190px; }
        .a-carousel-arrow { width: 36px; height: 36px; }
        .a-carousel-arrow.prev { left: 10px; }
        .a-carousel-arrow.next { right: 10px; }
    }
    @media (prefers-reduced-motion: reduce) {
        .a-slide, .a-carousel-arrow, .a-carousel-dot { transition: none; }
    }
</style>
@endpush

@section('content')
@php
    $fmt = fn($n) => '€' . number_format((float) ($n ?? 0), 2);

    $soldShares = $asset->total_shares - $asset->available_shares;
    $fundingPercentage = $asset->total_shares > 0 ? ($soldShares / $asset->total_shares) * 100 : 0;

    $gainPct = ($totalInvested ?? 0) > 0 ? ($unrealizedGain / $totalInvested) * 100 : null;
    $canBuy = $asset->status === 'active' && $asset->available_shares > 0;

    $activeBuyout = $asset->buyoutProposals()->whereIn('status', ['voting', 'approved_pending_payout'])->latest()->first();

    // Accepts either a single image field or multiple paths in an images array/JSON column.
    $gallery = $asset->images ?? [];
    if (is_string($gallery)) {
        $decoded = json_decode($gallery, true);
        $gallery = is_array($decoded) ? $decoded : [$gallery];
    }
    if (!is_array($gallery)) {
        $gallery = [];
    }
    if (!empty($asset->image)) {
        array_unshift($gallery, $asset->image);
    }
    $gallery = collect($gallery)->filter(fn($path) => is_string($path) && trim($path) !== '')
        ->map(function ($path) {
            $path = trim($path);
            if (preg_match('~^https?://~i', $path)) return $path;
            if (str_starts_with($path, '/')) return $path;
            if (str_starts_with($path, 'storage/')) return asset($path);
            return asset('storage/' . ltrim($path, '/'));
        })->unique()->values();
    $initial = \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($asset->title ?? '?', 0, 1));
@endphp

<div class="ast">
    <a href="/portfolio#holdings" class="back"><svg class="icon"><use href="#icon-arrow-left"/></svg> Back to holdings</a>

    <div class="a-head">
        <div>
            <div class="a-title">
                <h1>{{ $asset->title }}</h1>
            </div>
            <div class="a-meta">{{ $asset->country ?? 'Global' }} · {{ $asset->category }}</div>
        </div>
        <button type="button" class="watch-toggle {{ ($isWatched ?? false) ? 'is-active' : '' }}" aria-label="Add to watchlist" title="Add to watchlist">
            <svg class="icon"><use href="#icon-heart"/></svg>
        </button>
    </div>

    <div class="a-hero {{ $gallery->count() <= 1 ? 'single' : '' }}" data-asset-carousel>
        <div class="a-carousel-track" data-carousel-track>
            @forelse($gallery as $index => $src)
                <div class="a-slide {{ $index === 0 ? 'is-active' : '' }}" data-slide aria-hidden="{{ $index === 0 ? 'false' : 'true' }}">
                    <img src="{{ $src }}" alt="{{ $asset->title }} image {{ $index + 1 }}" loading="{{ $index === 0 ? 'eager' : 'lazy' }}" @if($index === 0) fetchpriority="high" @endif>
                </div>
            @empty
                <div class="a-slide is-active" data-slide aria-hidden="false">
                    <div class="a-slide-fallback">{{ $initial }}</div>
                </div>
            @endforelse
        </div>

        @if($gallery->count() > 1)
            <button type="button" class="a-carousel-arrow prev" data-carousel-prev aria-label="Previous image">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>
            </button>
            <button type="button" class="a-carousel-arrow next" data-carousel-next aria-label="Next image">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg>
            </button>
            <div class="a-carousel-footer" aria-label="Choose image">
                @foreach($gallery as $index => $src)
                    <button type="button" class="a-carousel-dot {{ $index === 0 ? 'is-active' : '' }}" data-carousel-dot="{{ $index }}" aria-label="Show image {{ $index + 1 }}" aria-current="{{ $index === 0 ? 'true' : 'false' }}"></button>
                @endforeach
            </div>
            <span class="a-carousel-count" data-carousel-count>1 / {{ $gallery->count() }}</span>
        @endif
    </div>

    <div class="a-grid">
        <div class="a-main">
            <section>
                <h2>About this opportunity</h2>
                @if($asset->description)
                    <p class="a-text">{{ $asset->description }}</p>
                @else
                    <p class="a-text">No description has been provided for this opportunity yet.</p>
                @endif
            </section>

            <section>
                <h2>Asset details</h2>
                <div class="dl">
                    <div class="r"><span>Category</span><span>{{ $asset->category }}</span></div>
                    <div class="r"><span>Country</span><span>{{ $asset->country ?? 'Global' }}</span></div>
                    <div class="r"><span>Total valuation</span><span class="num">{{ $fmt($asset->total_valuation) }}</span></div>
                    <div class="r"><span>Share price</span><span class="num">{{ $fmt($asset->share_price) }}</span></div>
                    <div class="r"><span>Total shares</span><span class="num">{{ number_format($asset->total_shares) }}</span></div>
                    <div class="r"><span>Available shares</span><span class="num">{{ number_format($asset->available_shares) }}</span></div>
                </div>
            </section>

            @if($activeBuyout)
                @php
                    $totalShares = $asset->total_shares;
                    $yesVotes = $activeBuyout->totalYesVotes();
                    $noVotes = $activeBuyout->totalNoVotes();
                    $yesPercent = $totalShares > 0 ? round(($yesVotes / $totalShares) * 100, 1) : 0;
                    $userVote = $activeBuyout->votes()->where('user_id', auth()->id())->first();
                @endphp
                <section>
                    <div class="buyout">
                        <div class="eyebrow" style="color: var(--gold);">Buyout offer</div>
                        <h2 style="margin-top:6px;">{{ $fmt($activeBuyout->offered_amount) }} offered for 100% of {{ $asset->title }}</h2>
                        <p class="a-text" style="margin-bottom:18px;">
                            <strong style="color:var(--ivory);">{{ $activeBuyout->buyer_name }}</strong> has proposed to acquire the whole asset. Shareholders vote with one vote per share.
                        </p>
                        <div class="bar-row">
                            <span>Yes votes <strong class="num">{{ number_format($yesVotes) }}</strong> · <span class="num">{{ $yesPercent }}%</span></span>
                            <span class="muted">66% required</span>
                        </div>
                        <div class="vote-bar" aria-label="{{ $yesPercent }}% yes votes of 66% required">
                            <b style="width: {{ min(100, $yesPercent) }}%"></b>
                            <i style="left: 66%;"></i>
                        </div>

                        @if($activeBuyout->status === 'approved_pending_payout')
                            <div class="alert alert-success">The proposal passed. It is pending administrator execution and distribution of funds.</div>
                        @elseif(now()->greaterThan($activeBuyout->voting_ends_at))
                            <div class="alert alert-error">Voting has concluded. Awaiting final resolution.</div>
                        @elseif($unitsOwned > 0)
                            <form action="{{ route('buyout.vote', $activeBuyout->id) }}" method="POST" class="vote-actions">
                                @csrf
                                <span class="muted" style="font-size:.85rem;">Cast your <span class="num">{{ number_format($unitsOwned) }}</span> {{ Str::plural('vote', $unitsOwned) }}</span>
                                <button type="submit" name="vote" value="yes" class="btn btn-gold btn-sm" {{ $userVote?->vote === 'yes' ? 'disabled' : '' }}>
                                    {{ $userVote?->vote === 'yes' ? 'Voted yes' : 'Vote yes' }}
                                </button>
                                <button type="submit" name="vote" value="no" class="btn btn-outline btn-sm" {{ $userVote?->vote === 'no' ? 'disabled' : '' }}>
                                    {{ $userVote?->vote === 'no' ? 'Voted no' : 'Vote no' }}
                                </button>
                            </form>
                        @else
                            <p class="state">Only current shareholders can vote on this proposal.</p>
                        @endif
                    </div>
                </section>
            @endif

            <section>
                <h2>Ownership</h2>
                <div class="bar-row">
                    <span class="muted"><span class="num">{{ number_format($soldShares) }}</span> of <span class="num">{{ number_format($asset->total_shares) }}</span> shares allocated</span>
                    <strong class="num">{{ number_format($fundingPercentage, 1) }}%</strong>
                </div>
                <div class="bar"><b style="width: {{ min(100, max(0, $fundingPercentage)) }}%"></b></div>
            </section>

            <section>
                <h2>Distribution history</h2>
                @if(collect($distributions)->isNotEmpty())
                    <div class="dist head"><span>Date</span><span>Type</span><span class="amt">Amount</span></div>
                    <div class="dist-list">
                        @foreach($distributions as $distribution)
                            <div class="dist">
                                <span class="num">{{ $distribution->created_at->format('M d, Y') }}</span>
                                <span class="muted">Distribution</span>
                                <span class="amt num">{{ $fmt($distribution->amount) }}</span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="empty">No distributions have been paid for this asset yet.</div>
                @endif
            </section>
        </div>

        <aside class="a-side">
            <div class="side-block">
                <div class="eyebrow">Your position</div>
                @if($unitsOwned > 0)
                    <div class="pos-total num">{{ $fmt($currentValue) }}</div>
                    <div class="pos-gain num {{ $unrealizedGain >= 0 ? 'positive' : 'negative' }}">
                        {{ $unrealizedGain >= 0 ? '+' : '−' }}{{ $fmt(abs($unrealizedGain)) }}
                        @if($gainPct !== null)({{ $unrealizedGain >= 0 ? '+' : '−' }}{{ number_format(abs($gainPct), 2) }}%)@endif
                    </div>
                    <div style="margin-top:18px;">
                        <div class="kv"><span>Units owned</span><span class="num">{{ number_format($unitsOwned) }}</span></div>
                        <div class="kv"><span>Invested</span><span class="num">{{ $fmt($totalInvested ?? 0) }}</span></div>
                        <div class="kv"><span>Avg. purchase price</span><span class="num">{{ $fmt($avgPurchasePrice) }}</span></div>
                        <div class="kv"><span>Current price</span><span class="num">{{ $fmt($asset->share_price) }}</span></div>
                    </div>
                @else
                    <div class="pos-total num">{{ $fmt($asset->share_price) }}</div>
                    <div class="state" style="margin-top:0;">per share. You don't hold units in this asset yet.</div>
                @endif

                <div class="actions">
                    @if($canBuy)
                        <a href="{{ route('trade.show', $asset->id) }}?side=buy" class="btn btn-gold btn-block">Buy</a>
                    @else
                        <button type="button" class="btn btn-gold btn-block" disabled>Unavailable</button>
                    @endif
                    @if($unitsOwned > 0)
                        <a href="{{ route('listings.create', $asset->id) }}" class="btn btn-outline btn-block">Sell</a>
                    @else
                        <button type="button" class="btn btn-outline btn-block" disabled>Sell</button>
                    @endif
                </div>
            </div>

            <div class="side-block">
                <div class="eyebrow">Availability</div>
                @if($asset->available_shares > 0)
                    <div style="margin-top:10px;"><span class="badge positive">Available</span></div>
                    <p class="state"><span class="num">{{ number_format($asset->available_shares) }}</span> shares are currently available at {{ $fmt($asset->share_price) }} each.</p>
                @else
                    <div style="margin-top:10px;"><span class="badge neutral">Fully allocated</span></div>
                    <p class="state">All shares have been allocated. Units may still appear on the secondary market.</p>
                @endif
            </div>
        </aside>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const watchBtn = document.querySelector('.watch-toggle');
        if (!watchBtn) return;

        watchBtn.addEventListener('click', function () {
            fetch('{{ route("asset.watch", $asset->id) }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'added') {
                    watchBtn.classList.add('is-active');
                } else {
                    watchBtn.classList.remove('is-active');
                }
            })
            .catch(error => console.error('Error:', error));
        });
    });
(function () {
    document.querySelectorAll('[data-asset-carousel]').forEach(function (carousel) {
        const slides = Array.from(carousel.querySelectorAll('[data-slide]'));
        const dots = Array.from(carousel.querySelectorAll('[data-carousel-dot]'));
        const prev = carousel.querySelector('[data-carousel-prev]');
        const next = carousel.querySelector('[data-carousel-next]');
        const count = carousel.querySelector('[data-carousel-count]');
        if (slides.length < 2) return;

        let current = 0;
        let touchStartX = null;

        function show(index) {
            current = (index + slides.length) % slides.length;
            slides.forEach((slide, i) => {
                const active = i === current;
                slide.classList.toggle('is-active', active);
                slide.setAttribute('aria-hidden', active ? 'false' : 'true');
            });
            dots.forEach((dot, i) => {
                const active = i === current;
                dot.classList.toggle('is-active', active);
                dot.setAttribute('aria-current', active ? 'true' : 'false');
            });
            if (count) count.textContent = (current + 1) + ' / ' + slides.length;
        }

        if (prev) prev.addEventListener('click', () => show(current - 1));
        if (next) next.addEventListener('click', () => show(current + 1));
        dots.forEach((dot, i) => dot.addEventListener('click', () => show(i)));

        carousel.addEventListener('keydown', function (event) {
            if (event.key === 'ArrowLeft') { event.preventDefault(); show(current - 1); }
            if (event.key === 'ArrowRight') { event.preventDefault(); show(current + 1); }
        });
        carousel.setAttribute('tabindex', '0');

        carousel.addEventListener('touchstart', event => {
            touchStartX = event.changedTouches[0].clientX;
        }, { passive: true });
        carousel.addEventListener('touchend', event => {
            if (touchStartX === null) return;
            const delta = event.changedTouches[0].clientX - touchStartX;
            if (Math.abs(delta) > 45) show(delta < 0 ? current + 1 : current - 1);
            touchStartX = null;
        }, { passive: true });

        slides.forEach(slide => {
            const img = slide.querySelector('img');
            if (!img) return;
            img.addEventListener('error', function () {
                const fallback = document.createElement('div');
                fallback.className = 'a-slide-fallback';
                fallback.textContent = @json($initial);
                slide.replaceChildren(fallback);
            }, { once: true });
        });
    });
})();
</script>
@endpush
