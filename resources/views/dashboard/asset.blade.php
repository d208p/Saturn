@extends('layouts.app')

@section('title', $asset->title)

@section('content')

    <a href="/portfolio#holdings"
       class="link-muted"
       style="margin-bottom: 20px; display: inline-flex; align-items: center; gap: 6px;">
        <svg class="icon" style="width: 14px; height: 14px;">
            <use href="#icon-arrow-left"/>
        </svg>
        Back to holdings
    </a>

    <!-- Asset Header -->
    <div class="card-header"
         style="align-items: flex-start; margin-bottom: 24px;">

        <div>
            <div style="display: flex; align-items: center; gap: 10px;">

                <h2 style="
                    font-size: 1.6rem;
                    font-weight: 800;
                    letter-spacing: -0.01em;
                ">
                    {{ $asset->title }}
                </h2>

                <button class="watch-toggle is-active">
                    <svg class="icon">
                        <use href="#icon-heart"/>
                    </svg>
                </button>

            </div>

            <div class="muted"
                 style="font-size: 0.9rem; margin-top: 4px;">

                {{ $asset->country ?? 'Global' }}
                ·
                {{ $asset->category }}

            </div>
        </div>

        <div style="
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            justify-content: flex-end;
        ">

            <span class="badge neutral">
                €{{ number_format($asset->total_valuation, 2) }} valuation
            </span>

            <span class="badge neutral">
                €{{ number_format($asset->share_price, 2) }} / share
            </span>

            <span class="badge {{ $asset->status === 'active' ? 'positive' : 'neutral' }}">
                {{ ucfirst($asset->status) }}
            </span>

        </div>
    </div>


    <!-- Main Asset Image -->
    @if($asset->image)

        <div class="card"
             style="
                padding: 0;
                overflow: hidden;
                margin-bottom: 24px;
             ">

            <img
                src="{{ asset('storage/' . $asset->image) }}"
                alt="{{ $asset->title }}"
                style="
                    width: 100%;
                    height: 420px;
                    object-fit: cover;
                    display: block;
                "
            >

        </div>

    @endif


    <!-- Main Grid -->
    <div class="grid grid-2"
         style="
            align-items: start;
            grid-template-columns: 2fr 1fr;
         ">

        <!-- LEFT COLUMN -->
        <div style="
            display: flex;
            flex-direction: column;
            gap: 20px;
        ">

            <!-- About -->
            <div class="card">

                <div class="section-title">
                    About this opportunity
                </div>

                @if($asset->description)

                    <p class="muted"
                       style="
                            font-size: 0.9rem;
                            line-height: 1.7;
                            margin: 0;
                       ">
                        {{ $asset->description }}
                    </p>

                @else

                    <p class="muted"
                       style="font-size: 0.9rem;">
                        No description has been provided for this investment opportunity.
                    </p>

                @endif

            </div>


            <!-- Asset Details -->
            <div class="card">

                <div class="section-title">
                    Asset details
                </div>

                <div class="grid grid-2"
                     style="gap: 0 24px;">

                    <div class="list-row">
                        <div class="primary">
                            Category
                        </div>

                        <div class="value">
                            {{ $asset->category }}
                        </div>
                    </div>

                    <div class="list-row">
                        <div class="primary">
                            Country
                        </div>

                        <div class="value">
                            {{ $asset->country ?? 'Global' }}
                        </div>
                    </div>

                    <div class="list-row">
                        <div class="primary">
                            Total valuation
                        </div>

                        <div class="value">
                            €{{ number_format($asset->total_valuation, 2) }}
                        </div>
                    </div>

                    <div class="list-row">
                        <div class="primary">
                            Share price
                        </div>

                        <div class="value">
                            €{{ number_format($asset->share_price, 2) }}
                        </div>
                    </div>

                    <div class="list-row">
                        <div class="primary">
                            Total shares
                        </div>

                        <div class="value">
                            {{ number_format($asset->total_shares) }}
                        </div>
                    </div>

                    <div class="list-row">
                        <div class="primary">
                            Available shares
                        </div>

                        <div class="value">
                            {{ number_format($asset->available_shares) }}
                        </div>
                    </div>

                </div>

            </div>


            <!-- Ownership -->
            <div class="card">

                <div class="section-title">
                    Ownership
                </div>

                @php
                    $soldShares = $asset->total_shares - $asset->available_shares;

                    $fundingPercentage = $asset->total_shares > 0
                        ? ($soldShares / $asset->total_shares) * 100
                        : 0;
                @endphp

                <div style="
                    display: flex;
                    justify-content: space-between;
                    margin-bottom: 8px;
                    font-size: 0.9rem;
                ">

                    <span class="muted">
                        {{ number_format($soldShares) }} shares allocated
                    </span>

                    <span>
                        {{ number_format($fundingPercentage, 1) }}%
                    </span>

                </div>

                <div style="
                    width: 100%;
                    height: 8px;
                    background: rgba(255,255,255,0.08);
                    border-radius: 999px;
                    overflow: hidden;
                ">

                    <div style="
                        width: {{ min(100, max(0, $fundingPercentage)) }}%;
                        height: 100%;
                        background: #C6A15B;
                        border-radius: 999px;
                    "></div>

                </div>

            </div>


            <!-- Distribution History -->
            <div class="card">

                <div class="section-title">
                    Distribution history
                </div>

                <table>

                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Amount</th>
                        </tr>
                    </thead>

                    <tbody>

                        @forelse($distributions as $distribution)

                            <tr>

                                <td>
                                    {{ $distribution->created_at->format('M d, Y') }}
                                </td>

                                <td class="muted">
                                    Distribution
                                </td>

                                <td>
                                    €{{ number_format($distribution->amount, 2) }}
                                </td>

                            </tr>

                        @empty

                            <tr>
                                <td colspan="3"
                                    class="muted"
                                    style="text-align: center; padding: 20px;">
                                    No distribution history found.
                                </td>
                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>


        <!-- RIGHT COLUMN -->
        <div style="
            display: flex;
            flex-direction: column;
            gap: 20px;
        ">

            <!-- Your Position -->
            <div class="card">

                <div class="section-title">
                    Your position
                </div>

                <div class="list-row">
                    <div class="primary">
                        Units owned
                    </div>

                    <div class="value">
                        {{ number_format($unitsOwned) }}
                    </div>
                </div>

                <div class="list-row">
                    <div class="primary">
                        Current value
                    </div>

                    <div class="value">
                        €{{ number_format($currentValue, 2) }}
                    </div>
                </div>

                <div class="list-row">
                    <div class="primary">
                        Invested
                    </div>

                    <div class="value">
                        €{{ number_format($totalInvested ?? 0, 2) }}
                    </div>
                </div>

                <div class="list-row">
                    <div class="primary">
                        Avg. purchase price
                    </div>

                    <div class="value">
                        €{{ number_format($avgPurchasePrice, 2) }}
                    </div>
                </div>

                <div class="list-row">
                    <div class="primary">
                        Current price
                    </div>

                    <div class="value">
                        €{{ number_format($asset->share_price, 2) }}
                    </div>
                </div>

                <div class="list-row">

                    <div class="primary">
                        Unrealized gain
                    </div>

                    <div class="value {{ $unrealizedGain >= 0 ? 'positive' : 'negative' }}">

                        {{ $unrealizedGain >= 0 ? '+' : '' }}
                        €{{ number_format($unrealizedGain, 2) }}

                    </div>

                </div>


                <!-- Trading buttons -->
                <div style="
                    display: flex;
                    gap: 10px;
                    margin-top: 18px;
                ">

                    @if($asset->status === 'active' && $asset->available_shares > 0)

                        <a
                            href="{{ route('trade.show', $asset->id) }}?side=buy"
                            class="btn btn-gold btn-block"
                        >
                            Buy
                        </a>

                    @else

                        <button
                            class="btn btn-gold btn-block"
                            disabled
                            style="opacity: 0.5; cursor: not-allowed;"
                        >
                            Unavailable
                        </button>

                    @endif


                    @if($unitsOwned > 0)

                        <a
                            href="{{ route('listings.create', $asset->id) }}"
                            class="btn btn-outline btn-block"
                        >
                            Sell
                        </a>

                    @else

                        <button
                            class="btn btn-outline btn-block"
                            disabled
                            style="opacity: 0.5; cursor: not-allowed;"
                        >
                            Sell
                        </button>

                    @endif

                </div>

            </div>


            <!-- Availability -->
            <div class="card">

                <div class="section-title">
                    Availability
                </div>

                @if($asset->available_shares > 0)

                    <span class="badge positive"
                          style="margin-bottom: 10px;">
                        Available
                    </span>

                    <p class="muted"
                       style="font-size: 0.85rem; margin: 0;">
                        {{ number_format($asset->available_shares) }}
                        shares are currently available.
                    </p>

                @else

                    <span class="badge neutral"
                          style="margin-bottom: 10px;">
                        Fully allocated
                    </span>

                    <p class="muted"
                       style="font-size: 0.85rem; margin: 0;">
                        All shares of this opportunity have been allocated.
                    </p>

                @endif

            </div>


            <!-- Investment summary -->
            <div class="card">

                <div class="section-title">
                    Investment summary
                </div>

                <div class="list-row">
                    <div class="primary">
                        Price per share
                    </div>

                    <div class="value">
                        €{{ number_format($asset->share_price, 2) }}
                    </div>
                </div>

                <div class="list-row">
                    <div class="primary">
                        Shares available
                    </div>

                    <div class="value">
                        {{ number_format($asset->available_shares) }}
                    </div>
                </div>

                <div class="list-row">
                    <div class="primary">
                        Asset valuation
                    </div>

                    <div class="value">
                        €{{ number_format($asset->total_valuation, 2) }}
                    </div>
                </div>

            </div>

        </div>

    </div>

@endsection