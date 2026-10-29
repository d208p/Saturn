@extends('layouts.app')

@section('title', 'Market')

@push('styles')
<style>
    .mk { --line: rgba(255,255,255,0.07); }
    .mk .tabs { border-bottom: 1px solid var(--line); gap: 28px; margin-bottom: 32px; }
    .mk .tab-btn { padding: 12px 0; font-size: .88rem; letter-spacing: .01em; }
    .mk .num { font-variant-numeric: tabular-nums; }
    .mk .eyebrow { font-size: .64rem; text-transform: uppercase; letter-spacing: .12em; color: var(--slate); font-weight: 600; }
    /* Toolbar */
    .mk-toolbar { display: grid; grid-template-columns: 1.6fr 1fr 1fr auto; gap: 12px; align-items: end; }
    .mk-toolbar.no-cat { grid-template-columns: 1.6fr 1fr auto; }
    .field label { display: block; font-size: .64rem; text-transform: uppercase; letter-spacing: .12em; color: var(--slate); margin-bottom: 6px; font-weight: 600; }
    .field input, .field select {
        width: 100%; background: transparent; border: 0; border-bottom: 1px solid var(--line);
        color: var(--ivory); font-family: inherit; font-size: .85rem; padding: 8px 2px; outline: none; border-radius: 0;
    }
    .field select option { background: var(--midnight); color: var(--ivory); }

    .field input:focus, .field select:focus { border-bottom-color: var(--gold); }

    .link-btn { background: none; border: 0; color: var(--gold); font-size: .8rem; font-weight: 600; cursor: pointer; padding: 8px 4px; white-space: nowrap; font-family: inherit; }

    .link-btn:disabled { color: var(--slate); opacity: .5; cursor: default; }

    .result-bar { display: flex; justify-content: space-between; align-items: center; gap: 12px; margin: 20px 0 20px; }

    .result-count { font-size: .78rem; color: var(--slate); }

    /* View switch */

    .view-switch { display: inline-flex; border: 1px solid var(--line); border-radius: 9px; padding: 3px; gap: 2px; }

    .view-switch button { width: 32px; height: 28px; border: 0; background: transparent; color: var(--slate); border-radius: 6px; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: color .15s, background .15s; }

    .view-switch button:hover { color: var(--ivory); }

    .view-switch button.active { background: var(--gold-dim); color: var(--gold); }

    .view-switch svg { width: 16px; height: 16px; fill: none; stroke: currentColor; stroke-width: 1.8; stroke-linecap: round; stroke-linejoin: round; }

    .seg { display: inline-flex; gap: 22px; margin-bottom: 20px; flex-wrap: wrap; }

    .seg button { background: none; border: 0; color: var(--slate); font-size: .82rem; font-weight: 600; padding: 6px 0; cursor: pointer; border-bottom: 2px solid transparent; font-family: inherit; }

    .seg button.active { color: var(--gold); border-bottom-color: var(--gold); }

    /* Card grid */

    .mk-grid { display: grid; gap: 24px; grid-template-columns: repeat(2, minmax(0, 1fr)); }

    .mk-grid[data-view="3"] { grid-template-columns: repeat(3, minmax(0, 1fr)); }

    .mk-grid[data-view="list"] { grid-template-columns: 1fr; gap: 16px; }

    .mk-card {

        display: flex; flex-direction: column; overflow: hidden;

        background: var(--card-bg); border: 1px solid var(--line); border-radius: 14px;

        transition: border-color .2s, transform .2s, box-shadow .2s;

    }

    .mk-card:hover { border-color: var(--gold-border); transform: translateY(-2px); box-shadow: 0 14px 34px rgba(0,0,0,.28); }

    .mk-media { position: relative; display: block; aspect-ratio: 16 / 7; overflow: hidden; background: #0f1729; flex-shrink: 0; }

    .mk-carousel { position: absolute; inset: 0; overflow: hidden; }
    .mk-carousel-track { display: flex; width: 100%; height: 100%; transform: translateX(0); transition: transform .35s cubic-bezier(.2,.7,.2,1); }
    .mk-carousel-slide { position: relative; flex: 0 0 100%; width: 100%; height: 100%; min-width: 0; }
    .mk-carousel-slide img { width: 100%; height: 100%; object-fit: cover; display: block; }
    .mk-media::after { content: ''; position: absolute; inset: 0; background: linear-gradient(to top, rgba(11,18,32,.45), transparent 48%); pointer-events: none; z-index: 1; }
    .mk-ph { width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; font-size: 3rem; font-weight: 300; color: var(--gold); background: radial-gradient(circle at 30% 20%, rgba(198,161,91,.16), transparent 60%), #0f1729; }
    .mk-carousel-nav { position: absolute; z-index: 3; top: 50%; transform: translateY(-50%); display: flex; align-items: center; justify-content: center; width: 30px; height: 30px; padding: 0; border: 1px solid rgba(255,255,255,.18); border-radius: 50%; color: var(--ivory); background: rgba(11,18,32,.72); backdrop-filter: blur(8px); cursor: pointer; opacity: 0; transition: opacity .2s, background .2s, border-color .2s; }
    .mk-media:hover .mk-carousel-nav, .mk-carousel:focus-within .mk-carousel-nav { opacity: 1; }
    .mk-carousel-nav:hover { background: var(--gold); border-color: var(--gold); color: var(--midnight); }
    .mk-carousel-nav.prev { left: 10px; }
    .mk-carousel-nav.next { right: 10px; }
    .mk-carousel-nav svg { width: 15px; height: 15px; fill: none; stroke: currentColor; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }
    .mk-carousel-dots { position: absolute; z-index: 3; bottom: 11px; left: 50%; transform: translateX(-50%); display: flex; align-items: center; justify-content: center; gap: 5px; max-width: calc(100% - 100px); }
    .mk-carousel-dot { width: 5px; height: 5px; border: 0; border-radius: 50px; padding: 0; background: rgba(245,242,234,.55); cursor: pointer; transition: width .2s, background .2s; }
    .mk-carousel-dot.active { width: 16px; background: var(--gold); }
    .mk-carousel-count { position: absolute; z-index: 3; right: 10px; bottom: 10px; padding: 3px 7px; border-radius: 20px; color: var(--ivory); background: rgba(11,18,32,.72); font-size: .65rem; font-variant-numeric: tabular-nums; }
    @media (hover: none) { .mk-carousel-nav { opacity: 1; } }
    @media (prefers-reduced-motion: reduce) { .mk-carousel-track { transition: none; } }

    .mk-chip {

        position: absolute; z-index: 1; top: 12px; font-size: .68rem; font-weight: 700; letter-spacing: .03em;

        padding: 4px 10px; border-radius: 100px; background: rgba(11,18,32,.72); backdrop-filter: blur(8px);

        border: 1px solid rgba(255,255,255,.1); color: var(--ivory); display: inline-flex; align-items: center; gap: 6px;

    }

    .mk-chip.l { left: 12px; }

    .mk-chip.r { right: 12px; }

    .mk-chip .dot { width: 6px; height: 6px; border-radius: 50%; background: var(--green); }

    .mk-body { padding: 14px 16px 16px; display: flex; flex-direction: column; gap: 10px; flex: 1; min-width: 0; }

    .mk-title { font-weight: 700; font-size: 1.05rem; letter-spacing: -.01em; line-height: 1.3; }

    a.mk-title:hover { color: var(--gold); }

    .mk-sub { font-size: .8rem; color: var(--slate); margin-top: 4px; }

    .mk-progress .top { display: flex; justify-content: space-between; font-size: .78rem; margin-bottom: 5px; gap: 10px; }

    .mk-progress .bar { height: 4px; background: var(--line); border-radius: 2px; overflow: hidden; }

    .mk-progress .bar b { display: block; height: 100%; background: var(--gold); }

    .mk-stats { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; padding-top: 16px; border-top: 1px solid var(--line); }

    .mk-stat .v { font-weight: 700; font-size: .98rem; margin-top: 4px; }

    .mk-cta { margin-top: auto; }

    .mk-cta .btn { width: 100%; }

    /* List view */

    .mk-grid[data-view="list"] .mk-card { flex-direction: row; }

    .mk-grid[data-view="list"] .mk-media { width: 260px; aspect-ratio: auto; min-height: 190px; }

    .mk-grid[data-view="list"] .mk-body { display: grid; grid-template-columns: minmax(0, 1.2fr) minmax(0, 1fr) auto; align-items: center; gap: 24px; }

    .mk-grid[data-view="list"] .mk-stats { border-top: 0; padding-top: 0; }

    .mk-grid[data-view="list"] .mk-cta { margin-top: 0; }

    .mk-grid[data-view="list"] .mk-cta .btn { width: auto; }

    .mk-grid[data-view="list"] .mk-card.has-progress .mk-body { grid-template-columns: minmax(0, 1.1fr) minmax(0, 1fr) minmax(0, 1fr) auto; }

    .mk-grid .empty { grid-column: 1 / -1; }

    .empty { text-align: center; padding: 56px 16px; color: var(--slate); font-size: .88rem; border: 1px dashed var(--line); border-radius: 14px; }

    .empty strong { display: block; color: var(--ivory); font-size: .95rem; margin-bottom: 6px; }

    .empty .link-btn { margin-top: 10px; }

    /* Listings table */

    .mk-table { width: 100%; border-collapse: collapse; }

    .mk-table th { font-size: .64rem; padding: 12px 10px; border-bottom: 1px solid var(--line); }

    .mk-table td { padding: 14px 10px; border-bottom: 1px solid var(--line); font-size: .88rem; vertical-align: middle; }

    .mk-table .r { text-align: right; }

    .asset-cell { display: flex; align-items: center; gap: 12px; font-weight: 700; }

    .thumb { width: 44px; height: 44px; border-radius: 8px; object-fit: cover; flex-shrink: 0; background: #0f1729; }

    .thumb.ph { display: flex; align-items: center; justify-content: center; color: var(--gold); font-weight: 600; }

    .btn-cancel { background: transparent; color: var(--red); border: 1px solid rgba(248,113,113,.3); }

    .btn-cancel:hover { background: var(--red-dim); }

    @media (max-width: 1100px) {

        .mk-grid[data-view="3"] { grid-template-columns: repeat(2, minmax(0, 1fr)); }

        .mk-grid[data-view="list"] .mk-body,

        .mk-grid[data-view="list"] .mk-card.has-progress .mk-body { grid-template-columns: 1fr; gap: 14px; }

        .mk-grid[data-view="list"] .mk-media { width: 220px; }

    }

    @media (max-width: 980px) {

        .mk-toolbar, .mk-toolbar.no-cat { grid-template-columns: 1fr 1fr; }

        .mk-toolbar .field.search { grid-column: 1 / -1; }

    }

    @media (max-width: 680px) {

        .mk .tabs { gap: 20px; }

        .mk-grid, .mk-grid[data-view="3"] { grid-template-columns: 1fr; gap: 18px; }

        .mk-grid[data-view="list"] .mk-card { flex-direction: column; }

        .mk-grid[data-view="list"] .mk-media { width: 100%; aspect-ratio: 16 / 10; min-height: 0; }

        .mk-grid[data-view="list"] .mk-cta .btn { width: 100%; }

        .view-switch { display: none; }

        .mk-table thead { display: none; }

        .mk-table, .mk-table tbody, .mk-table tr, .mk-table td { display: block; width: 100%; }

        .mk-table tr { display: grid; grid-template-columns: 1fr auto; gap: 6px 12px; padding: 16px 0; border-bottom: 1px solid var(--line); }

        .mk-table td { border: 0; padding: 0; text-align: left !important; }

        .mk-table td.c-status { text-align: right !important; align-self: center; }

        .mk-table td.c-units, .mk-table td.c-price, .mk-table td.c-date { grid-column: 1 / -1; font-size: .78rem; color: var(--slate); }

        .mk-table td[data-label]::before { content: attr(data-label) "  "; font-size: .64rem; text-transform: uppercase; letter-spacing: .1em; margin-right: 6px; }

        .mk-table td.c-action { grid-column: 1 / -1; margin-top: 6px; }

        .mk-table td.c-action .btn { width: 100%; }

    }

</style>

@endpush

@section('content')

@php

    $fmt = fn($n) => '€' . number_format($n, 2);

    // Supports an `images` JSON/array column and the legacy single `image` column.
    // Relative paths are expected to live on Laravel's public storage disk.
    $images = function ($asset) {
        $raw = $asset->images ?? [];

        if (is_string($raw)) {
            $decoded = json_decode($raw, true);
            $raw = is_array($decoded) ? $decoded : [$raw];
        }

        if (!is_array($raw)) {
            $raw = [];
        }

        if (!empty($asset->image)) {
            array_unshift($raw, $asset->image);
        }

        $raw = array_values(array_unique(array_filter($raw, function ($path) {
            return is_string($path) && trim($path) !== '';
        })));

        return array_map(function ($path) {
            $path = trim($path);

            if (preg_match('#^https?://#i', $path)) {
                return $path;
            }

            $path = ltrim($path, '/');

            if (Str::startsWith($path, 'storage/')) {
                return asset($path);
            }

            if (Str::startsWith($path, 'public/')) {
                $path = substr($path, 7);
            }

            return asset('storage/' . $path);
        }, $raw);
    };

    $img = function ($asset) use ($images) {
        $sources = $images($asset);
        return $sources[0] ?? null;
    };

    $initial = fn($a) => Str::upper(Str::substr($a->title ?? '?', 0, 1));

    $discover = collect($newProjects)->map(function ($p) {

        $total = (float) ($p->total_valuation ?? 0);

        $funded = ($p->total_shares - $p->available_shares) * $p->share_price;

        return [

            'p' => $p, 'funded' => $funded, 'total' => $total,

            'progress' => $total > 0 ? min(100, round(($funded / $total) * 100)) : 0,

        ];

    });

    $discoverCats  = $discover->pluck('p.category')->filter()->unique()->sort()->values();

    $secondary     = collect($secondaryAssets);

    $secondaryCats = $secondary->pluck('category')->filter()->unique()->sort()->values();

    $watch         = collect($watchlist);

    $listings      = collect($userListings);

    // Compact icons for the view switch (2-up, 3-up, list).

    $icoTwo   = '<svg viewBox="0 0 24 24"><rect x="4" y="5" width="7" height="14" rx="1.5"/><rect x="13" y="5" width="7" height="14" rx="1.5"/></svg>';

    $icoThree = '<svg viewBox="0 0 24 24"><rect x="3.5" y="5" width="5" height="14" rx="1.2"/><rect x="9.5" y="5" width="5" height="14" rx="1.2"/><rect x="15.5" y="5" width="5" height="14" rx="1.2"/></svg>';

    $icoList  = '<svg viewBox="0 0 24 24"><rect x="4" y="5" width="16" height="5.5" rx="1.5"/><rect x="4" y="13.5" width="16" height="5.5" rx="1.5"/></svg>';

@endphp

<div class="mk">

    <div class="tabs" data-panels="#market-panels">

        <button class="tab-btn active" data-tab="discover">Discover</button>

        <button class="tab-btn" data-tab="secondary">Secondary Market</button>

        <button class="tab-btn" data-tab="watchlist">Watchlist</button>

        <button class="tab-btn" data-tab="my-listings">My Listings</button>

    </div>

    <div id="market-panels">

        {{-- ================= DISCOVER ================= --}}

        <div class="tab-panel active" id="discover">

            <div class="mk-filterable" data-scope="discover">

                <div class="mk-toolbar" role="search">

                    <div class="field search"><label for="d-q">Search</label><input id="d-q" data-f="q" type="search" placeholder="Project name" autocomplete="off"></div>

                    <div class="field"><label for="d-cat">Category</label>

                        <select id="d-cat" data-f="cat"><option value="">All</option>@foreach($discoverCats as $c)<option value="{{ $c }}">{{ $c }}</option>@endforeach</select></div>

                    <div class="field"><label for="d-sort">Sort by</label>

                        <select id="d-sort" data-f="sort">

                            <option value="new-desc">Newest</option>

                            <option value="progress-desc">Most funded</option>

                            <option value="progress-asc">Least funded</option>

                            <option value="price-asc">Share price, low to high</option>

                            <option value="price-desc">Share price, high to low</option>

                            <option value="name-asc">Name, A–Z</option>

                        </select></div>

                    <button type="button" class="link-btn" data-reset disabled>Reset</button>

                </div>

                <div class="result-bar">

                    <div class="result-count" data-count aria-live="polite"></div>

                    <div class="view-switch" role="group" aria-label="Layout">

                        <button type="button" data-view="2" title="2 columns" aria-label="2 columns">{!! $icoTwo !!}</button>

                        <button type="button" data-view="3" title="3 columns" aria-label="3 columns">{!! $icoThree !!}</button>

                        <button type="button" data-view="list" title="List" aria-label="List">{!! $icoList !!}</button>

                    </div>

                </div>

                <div class="mk-grid mk-list" data-view="2">

                    @foreach($discover as $i => $d)

                        @php $p = $d['p']; $src = $img($p); @endphp

                        <article class="mk-card mk-item has-progress" data-name="{{ Str::lower($p->title) }}" data-cat="{{ $p->category }}"

                                 data-price="{{ $p->share_price }}" data-progress="{{ $d['progress'] }}" data-new="{{ count($discover) - $i }}">

                            {{-- Hardcoded: image and title link to /asset/{id}. Adjust if your asset page route differs. --}}

<div class="mk-media">
                                @if(count($srcs = $images($p)) > 0)
                                    <div class="mk-carousel" data-carousel>
                                        <div class="mk-carousel-track" data-carousel-track>
                                            @foreach($srcs as $source)
                                                <div class="mk-carousel-slide"><img src="{{ $source }}" alt="{{ $p->title }}" loading="lazy"></div>
                                            @endforeach
                                        </div>
                                        @if(count($srcs) > 1)
                                            <button type="button" class="mk-carousel-nav prev" data-carousel-prev aria-label="Previous image">
                                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>
                                            </button>
                                            <button type="button" class="mk-carousel-nav next" data-carousel-next aria-label="Next image">
                                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg>
                                            </button>
                                            <div class="mk-carousel-dots" aria-label="Choose image">
                                                @foreach($srcs as $dotIndex => $source)
                                                    <button type="button" class="mk-carousel-dot{{ $dotIndex === 0 ? ' active' : '' }}" data-carousel-dot="{{ $dotIndex }}" aria-label="Show image {{ $dotIndex + 1 }}"></button>
                                                @endforeach
                                            </div>
                                            <span class="mk-carousel-count"><span data-carousel-current>1</span> / {{ count($srcs) }}</span>
                                        @endif
                                    </div>
                                @else
                                    <div class="mk-ph">{{ $initial($p) }}</div>
                                @endif
                                @if($p->category)<span class="mk-chip l">{{ $p->category }}</span>@endif
                                <span class="mk-chip r"><span class="dot"></span>{{ ucfirst($p->status) }}</span>
                            </div>

                            <div class="mk-body">

                                <div>

                                    <a href="/asset/{{ $p->id }}" class="mk-title">{{ $p->title }}</a>

                                    <div class="mk-sub">{{ $p->country ?? 'Global' }}</div>

                                </div>

                                <div class="mk-progress">

                                    <div class="top"><span class="muted num">{{ $fmt($d['funded']) }} of {{ $fmt($d['total']) }}</span><strong class="num">{{ $d['progress'] }}%</strong></div>

                                    <div class="bar"><b style="width: {{ $d['progress'] }}%"></b></div>

                                </div>

                                <div class="mk-stats">

                                    <div class="mk-stat"><div class="eyebrow">Share price</div><div class="v num">{{ $fmt($p->share_price) }}</div></div>

                                    <div class="mk-stat"><div class="eyebrow">Available</div><div class="v num">{{ number_format($p->available_shares) }}</div></div>

                                </div>

                                <div class="mk-cta"><a href="{{ route('trade.show', $p->id) }}" class="btn btn-gold btn-sm">Invest</a></div>

                            </div>

                        </article>

                    @endforeach

                    <div class="empty" data-empty @if($discover->isNotEmpty()) style="display:none;" @endif>

                        @if($discover->isEmpty())

                            <strong>No new projects right now</strong>New opportunities will appear here as they open.

                        @else

                            <strong>No projects match these filters</strong>Try widening your search.

                            <div><button type="button" class="link-btn" data-reset>Reset filters</button></div>

                        @endif

                    </div>

                </div>

            </div>

        </div>

        {{-- ================= SECONDARY ================= --}}

        <div class="tab-panel" id="secondary">

            <div class="mk-filterable" data-scope="secondary">

                <div class="mk-toolbar" role="search">

                    <div class="field search"><label for="s-q">Search</label><input id="s-q" data-f="q" type="search" placeholder="Asset name" autocomplete="off"></div>

                    <div class="field"><label for="s-cat">Category</label>

                        <select id="s-cat" data-f="cat"><option value="">All</option>@foreach($secondaryCats as $c)<option value="{{ $c }}">{{ $c }}</option>@endforeach</select></div>

                    <div class="field"><label for="s-sort">Sort by</label>

                        <select id="s-sort" data-f="sort">

                            <option value="price-asc">Lowest ask</option>

                            <option value="price-desc">Highest ask</option>

                            <option value="units-desc">Most units available</option>

                            <option value="offers-desc">Most offers</option>

                            <option value="name-asc">Name, A–Z</option>

                        </select></div>

                    <button type="button" class="link-btn" data-reset disabled>Reset</button>

                </div>

                <div class="result-bar">

                    <div class="result-count" data-count aria-live="polite"></div>

                    <div class="view-switch" role="group" aria-label="Layout">

                        <button type="button" data-view="2" title="2 columns" aria-label="2 columns">{!! $icoTwo !!}</button>

                        <button type="button" data-view="3" title="3 columns" aria-label="3 columns">{!! $icoThree !!}</button>

                        <button type="button" data-view="list" title="List" aria-label="List">{!! $icoList !!}</button>

                    </div>

                </div>

                <div class="mk-grid mk-list" data-view="2">

                    @foreach($secondary as $asset)

                        @php $src = $img($asset); @endphp

                        <article class="mk-card mk-item" data-name="{{ Str::lower($asset->title) }}" data-cat="{{ $asset->category ?? 'General' }}"

                                 data-price="{{ $asset->min_price }}" data-units="{{ $asset->total_secondary_shares }}" data-offers="{{ $asset->active_listings_count }}">

<div class="mk-media">
                                @if(count($srcs = $images($asset)) > 0)
                                    <div class="mk-carousel" data-carousel>
                                        <div class="mk-carousel-track" data-carousel-track>
                                            @foreach($srcs as $source)
                                                <div class="mk-carousel-slide"><img src="{{ $source }}" alt="{{ $asset->title }}" loading="lazy"></div>
                                            @endforeach
                                        </div>
                                        @if(count($srcs) > 1)
                                            <button type="button" class="mk-carousel-nav prev" data-carousel-prev aria-label="Previous image">
                                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>
                                            </button>
                                            <button type="button" class="mk-carousel-nav next" data-carousel-next aria-label="Next image">
                                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg>
                                            </button>
                                            <div class="mk-carousel-dots" aria-label="Choose image">
                                                @foreach($srcs as $dotIndex => $source)
                                                    <button type="button" class="mk-carousel-dot{{ $dotIndex === 0 ? ' active' : '' }}" data-carousel-dot="{{ $dotIndex }}" aria-label="Show image {{ $dotIndex + 1 }}"></button>
                                                @endforeach
                                            </div>
                                            <span class="mk-carousel-count"><span data-carousel-current>1</span> / {{ count($srcs) }}</span>
                                        @endif
                                    </div>
                                @else
                                    <div class="mk-ph">{{ $initial($asset) }}</div>
                                @endif
                                <span class="mk-chip l">{{ $asset->category ?? 'General' }}</span>
                                <span class="mk-chip r">{{ $asset->active_listings_count }} {{ Str::plural('offer', $asset->active_listings_count) }}</span>
                            </div>

                            <div class="mk-body">

                                <div>

                                    <a href="{{ route('secondary.asset.show', $asset->id) }}" class="mk-title">{{ $asset->title }}</a>

                                    <div class="mk-sub">{{ $asset->country ?? 'Global' }} · Secondary market</div>

                                </div>

                                <div class="mk-stats">

                                    <div class="mk-stat"><div class="eyebrow">Starting from</div><div class="v num" style="color: var(--gold);">{{ $fmt($asset->min_price) }}</div></div>

                                    <div class="mk-stat"><div class="eyebrow">Units available</div><div class="v num">{{ number_format($asset->total_secondary_shares) }}</div></div>

                                </div>

                                <div class="mk-cta"><a href="{{ route('secondary.asset.show', $asset->id) }}" class="btn btn-gold btn-sm">View &amp; buy</a></div>

                            </div>

                        </article>

                    @endforeach

                    <div class="empty" data-empty @if($secondary->isNotEmpty()) style="display:none;" @endif>

                        @if($secondary->isEmpty())

                            <strong>No active listings</strong>Seller offers will appear here when shareholders list their units.

                        @else

                            <strong>No assets match these filters</strong>Try widening your search.

                            <div><button type="button" class="link-btn" data-reset>Reset filters</button></div>

                        @endif

                    </div>

                </div>

            </div>

        </div>

        {{-- ================= WATCHLIST ================= --}}

        <div class="tab-panel" id="watchlist">

            <div class="mk-filterable" data-scope="watchlist">

                <div class="mk-toolbar no-cat" role="search">

                    <div class="field search"><label for="w-q">Search</label><input id="w-q" data-f="q" type="search" placeholder="Asset name" autocomplete="off"></div>

                    <div class="field"><label for="w-sort">Sort by</label>

                        <select id="w-sort" data-f="sort">

                            <option value="name-asc">Name, A–Z</option>

                            <option value="price-asc">Price, low to high</option>

                            <option value="price-desc">Price, high to low</option>

                        </select></div>

                    <button type="button" class="link-btn" data-reset disabled>Reset</button>

                </div>

                <div class="result-bar">

                    <div class="result-count" data-count aria-live="polite"></div>

                    <div class="view-switch" role="group" aria-label="Layout">

                        <button type="button" data-view="2" title="2 columns" aria-label="2 columns">{!! $icoTwo !!}</button>

                        <button type="button" data-view="3" title="3 columns" aria-label="3 columns">{!! $icoThree !!}</button>

                        <button type="button" data-view="list" title="List" aria-label="List">{!! $icoList !!}</button>

                    </div>

                </div>

                <div class="mk-grid mk-list" data-view="2">

                    @foreach($watch as $item)

                        @php $src = $img($item); @endphp

                        {{-- Hardcoded: link target assumes /asset/{id}, and there is no remove-from-watchlist route yet (the controller returns an empty collection). --}}

                        <article class="mk-card mk-item" data-name="{{ Str::lower($item->title) }}" data-price="{{ $item->share_price }}">

<div class="mk-media">
                                @if(count($srcs = $images($item)) > 0)
                                    <div class="mk-carousel" data-carousel>
                                        <div class="mk-carousel-track" data-carousel-track>
                                            @foreach($srcs as $source)
                                                <div class="mk-carousel-slide"><img src="{{ $source }}" alt="{{ $item->title }}" loading="lazy"></div>
                                            @endforeach
                                        </div>
                                        @if(count($srcs) > 1)
                                            <button type="button" class="mk-carousel-nav prev" data-carousel-prev aria-label="Previous image">
                                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>
                                            </button>
                                            <button type="button" class="mk-carousel-nav next" data-carousel-next aria-label="Next image">
                                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg>
                                            </button>
                                            <div class="mk-carousel-dots" aria-label="Choose image">
                                                @foreach($srcs as $dotIndex => $source)
                                                    <button type="button" class="mk-carousel-dot{{ $dotIndex === 0 ? ' active' : '' }}" data-carousel-dot="{{ $dotIndex }}" aria-label="Show image {{ $dotIndex + 1 }}"></button>
                                                @endforeach
                                            </div>
                                            <span class="mk-carousel-count"><span data-carousel-current>1</span> / {{ count($srcs) }}</span>
                                        @endif
                                    </div>
                                @else
                                    <div class="mk-ph">{{ $initial($item) }}</div>
                                @endif
                                @if(!empty($item->category))<span class="mk-chip l">{{ $item->category }}</span>@endif
                                
                            </div>

                            <div class="mk-body">

                                <div>

                                    <a href="/asset/{{ $item->id ?? '' }}" class="mk-title">{{ $item->title }}</a>

                                    <div class="mk-sub">{{ $item->country ?? 'Global' }}</div>

                                </div>

                                <div class="mk-stats">

                                    <div class="mk-stat"><div class="eyebrow">Price / unit</div><div class="v num">{{ $fmt($item->share_price) }}</div></div>

                                </div>

                                <div class="mk-cta"><a href="/asset/{{ $item->id ?? '' }}" class="btn btn-outline btn-sm">View asset</a></div>

                            </div>

                        </article>

                    @endforeach

                    <div class="empty" data-empty @if($watch->isNotEmpty()) style="display:none;" @endif>

                        @if($watch->isEmpty())

                            <strong>Your watchlist is empty</strong>Save assets you're following to keep an eye on them here.

                            <div><a class="link-btn" href="#discover" onclick="location.hash='discover'">Browse opportunities →</a></div>

                        @else

                            <strong>No assets match your search</strong>Try a different name.

                            <div><button type="button" class="link-btn" data-reset>Reset filters</button></div>

                        @endif

                    </div>

                </div>

            </div>

        </div>

        {{-- ================= MY LISTINGS ================= --}}

        <div class="tab-panel" id="my-listings">

            <div class="seg" id="listingStatus" role="tablist">

                <button type="button" class="active" data-status="">All ({{ $listings->count() }})</button>

                <button type="button" data-status="active">Active ({{ $listings->where('status', 'active')->count() }})</button>

                <button type="button" data-status="filled">Filled ({{ $listings->where('status', 'filled')->count() }})</button>

                <button type="button" data-status="other">Cancelled / other ({{ $listings->whereNotIn('status', ['active', 'filled'])->count() }})</button>

            </div>

            <table class="mk-table" id="listingTable" @if($listings->isEmpty()) style="display:none;" @endif>

                <thead>

                    <tr><th>Asset</th><th class="r">Units</th><th class="r">Ask price</th><th>Listed</th><th>Status</th><th class="r">Action</th></tr>

                </thead>

                <tbody>

                    @foreach($listings as $listing)

                        @php

                            $bucket = in_array($listing->status, ['active', 'filled']) ? $listing->status : 'other';

                            $la = $listing->asset; $lsrc = $la ? $img($la) : null;

                        @endphp

                        <tr data-status="{{ $bucket }}">

                            <td class="c-asset">

                                <div class="asset-cell">

                                    @if($lsrc)<img src="{{ $lsrc }}" alt="" class="thumb" loading="lazy">@else<span class="thumb ph">{{ $la ? $initial($la) : '–' }}</span>@endif

                                    <span>{{ $la->title ?? 'N/A' }}</span>

                                </div>

                            </td>

                            <td class="c-units r num" data-label="Units">{{ number_format($listing->shares) }}</td>

                            <td class="c-price r num" data-label="Ask">{{ $fmt($listing->price_per_share) }}</td>

                            <td class="c-date muted" data-label="Listed">{{ $listing->created_at->format('M d, Y') }}</td>

                            <td class="c-status">

                                @if($listing->status === 'active')<span class="badge positive">Active</span>

                                @elseif($listing->status === 'filled')<span class="badge neutral">Filled</span>

                                @else<span class="badge negative">{{ ucfirst($listing->status) }}</span>@endif

                            </td>

                            <td class="c-action r">

                                @if($listing->status === 'active')

                                    <form action="{{ route('listings.cancel', $listing->id) }}" method="POST" style="display:inline;"

                                          onsubmit="return confirm('Cancel this listing? Your units will be returned to your portfolio.');">

                                        @csrf

                                        <button type="submit" class="btn btn-sm btn-cancel">Cancel</button>

                                    </form>

                                @else

                                    <span class="muted">—</span>

                                @endif

                            </td>

                        </tr>

                    @endforeach

                </tbody>

            </table>

            <div class="empty" id="listingEmpty" @if($listings->isNotEmpty()) style="display:none;" @endif>

                <strong>@if($listings->isEmpty()) No listings yet @else Nothing in this view @endif</strong>

                @if($listings->isEmpty())

                    Units you put up for sale will appear here, along with their status.

                    <div><a class="link-btn" href="/portfolio#holdings">Go to your holdings →</a></div>

                @else

                    No listings with this status.

                @endif

            </div>

        </div>

    </div>

</div>

@endsection

@push('scripts')

<script>

(function () {

    /* ---------- Layout switch (2 / 3 / list), shared across tabs and remembered ---------- */

    const grids = Array.from(document.querySelectorAll('.mk-grid'));

    const switches = Array.from(document.querySelectorAll('.view-switch button'));

    let saved = '2';

    try { saved = localStorage.getItem('saturn.marketView') || '2'; } catch (e) {}

    if (!['2', '3', 'list'].includes(saved)) saved = '2';

    function setView(v) {

        grids.forEach(g => g.dataset.view = v);

        switches.forEach(b => b.classList.toggle('active', b.dataset.view === v));

        try { localStorage.setItem('saturn.marketView', v); } catch (e) {}

    }

    switches.forEach(b => b.addEventListener('click', () => setView(b.dataset.view)));

    setView(saved);

    /* ---------- Asset image carousels ---------- */
    document.querySelectorAll('[data-carousel]').forEach(carousel => {
        const track = carousel.querySelector('[data-carousel-track]');
        const slides = Array.from(track ? track.children : []);
        const dots = Array.from(carousel.querySelectorAll('[data-carousel-dot]'));
        const current = carousel.querySelector('[data-carousel-current]');
        const prev = carousel.querySelector('[data-carousel-prev]');
        const next = carousel.querySelector('[data-carousel-next]');

        if (!track || slides.length < 2) return;

        let index = 0;

        function showSlide(nextIndex) {
            index = (nextIndex + slides.length) % slides.length;
            track.style.transform = `translateX(-${index * 100}%)`;

            dots.forEach((dot, dotIndex) => {
                const active = dotIndex === index;
                dot.classList.toggle('active', active);
                dot.setAttribute('aria-current', active ? 'true' : 'false');
            });

            if (current) current.textContent = String(index + 1);
        }

        if (prev) prev.addEventListener('click', event => {
            event.preventDefault();
            event.stopPropagation();
            showSlide(index - 1);
        });

        if (next) next.addEventListener('click', event => {
            event.preventDefault();
            event.stopPropagation();
            showSlide(index + 1);
        });

        dots.forEach((dot, dotIndex) => {
            dot.addEventListener('click', event => {
                event.preventDefault();
                event.stopPropagation();
                showSlide(dotIndex);
            });
        });

        let touchStartX = 0;
        let touchStartY = 0;

        carousel.addEventListener('touchstart', event => {
            const touch = event.changedTouches[0];
            touchStartX = touch.clientX;
            touchStartY = touch.clientY;
        }, { passive: true });

        carousel.addEventListener('touchend', event => {
            const touch = event.changedTouches[0];
            const deltaX = touch.clientX - touchStartX;
            const deltaY = touch.clientY - touchStartY;

            if (Math.abs(deltaX) > 40 && Math.abs(deltaX) > Math.abs(deltaY)) {
                showSlide(index + (deltaX < 0 ? 1 : -1));
            }
        }, { passive: true });

        showSlide(0);
    });

    /* ---------- Broken image fallback: swap for the initial-letter placeholder ---------- */

    document.addEventListener('error', (e) => {

        const img = e.target;

        if (!img || img.tagName !== 'IMG' || !img.closest('.mk-media, .asset-cell')) return;

        const card = img.closest('.mk-item');

        const title = card ? (card.querySelector('.mk-title')?.textContent || '?') : (img.closest('.asset-cell')?.textContent || '?');

        const ph = document.createElement('div');

        ph.className = img.classList.contains('thumb') ? 'thumb ph' : 'mk-ph';

        ph.textContent = title.trim().charAt(0).toUpperCase();

        img.replaceWith(ph);

    }, true);

    /* ---------- Generic filter / sort for Discover, Secondary and Watchlist ---------- */

    document.querySelectorAll('.mk-filterable').forEach(scope => {

        const list = scope.querySelector('.mk-list');

        const items = Array.from(list.querySelectorAll('.mk-item'));

        const empty = scope.querySelector('[data-empty]');

        const count = scope.querySelector('[data-count]');

        const resets = scope.querySelectorAll('[data-reset]');

        const q = scope.querySelector('[data-f="q"]');

        const cat = scope.querySelector('[data-f="cat"]');

        const sort = scope.querySelector('[data-f="sort"]');

        const defaultSort = sort.value;

        function apply() {

            const term = q.value.trim().toLowerCase();

            let shown = 0;

            items.forEach(el => {

                const ok = (!term || el.dataset.name.includes(term)) && (!cat || !cat.value || el.dataset.cat === cat.value);

                el.style.display = ok ? '' : 'none';

                if (ok) shown++;

            });

            const [key, dir] = sort.value.split('-'), m = dir === 'asc' ? 1 : -1;

            items.sort((a, b) => key === 'name'

                ? a.dataset.name.localeCompare(b.dataset.name) * m

                : (parseFloat(a.dataset[key]) - parseFloat(b.dataset[key])) * m

            ).forEach(el => list.insertBefore(el, empty));

            if (items.length) {

                empty.style.display = shown ? 'none' : 'block';

                count.textContent = shown + ' of ' + items.length + (items.length === 1 ? ' result' : ' results');

            } else {

                count.textContent = '';

            }

            const dirty = q.value !== '' || (cat && cat.value !== '') || sort.value !== defaultSort;

            scope.querySelector('.mk-toolbar [data-reset]').disabled = !dirty;

        }

        function reset() { q.value = ''; if (cat) cat.value = ''; sort.value = defaultSort; apply(); }

        q.addEventListener('input', apply);

        if (cat) cat.addEventListener('change', apply);

        sort.addEventListener('change', apply);

        resets.forEach(b => b.addEventListener('click', reset));

        apply();

    });

    /* ---------- My Listings: status filter ---------- */

    const seg = document.getElementById('listingStatus');

    const table = document.getElementById('listingTable');

    const emptyBox = document.getElementById('listingEmpty');

    if (seg && table) {

        const rows = Array.from(table.tBodies[0].rows);

        seg.querySelectorAll('button').forEach(btn => btn.addEventListener('click', () => {

            seg.querySelectorAll('button').forEach(b => b.classList.toggle('active', b === btn));

            const s = btn.dataset.status;

            let shown = 0;

            rows.forEach(r => { const ok = !s || r.dataset.status === s; r.style.display = ok ? '' : 'none'; if (ok) shown++; });

            table.style.display = shown ? '' : 'none';

            emptyBox.style.display = shown ? 'none' : 'block';

            if (!shown && rows.length) emptyBox.innerHTML = '<strong>Nothing in this view</strong>No listings with this status.';

        }));

    }

})();

</script>

@endpush
