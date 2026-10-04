@extends('layouts.app')

@section('title', 'Account')

@push('styles')
<style>
    .field { margin-bottom:16px; }
    .field label {
        display:block;
        font-size:.82rem;
        font-weight:600;
        margin-bottom:7px;
        color:var(--ivory);
    }
    .field input,
    .field select {
        width:100%;
        padding:12px 14px;
        border:1px solid var(--card-border);
        border-radius:9px;
        background:rgba(0,0,0,.25);
        color:var(--ivory);
        font:.9rem inherit;
        outline:none;
        transition:border-color .15s;
    }
    .field input::placeholder { color:var(--slate); }
    .field input:focus,
    .field select:focus { border-color:var(--gold-border); }

    .field-row-2 {
        display:grid;
        grid-template-columns:1fr 1fr;
        gap:16px;
    }

    .toggle {
        position:relative;
        display:inline-block;
        width:44px;
        height:24px;
        flex-shrink:0;
    }
    .toggle input {
        opacity:0;
        width:0;
        height:0;
    }
    .toggle .slider {
        position:absolute;
        inset:0;
        background:var(--card-border);
        border-radius:100px;
        cursor:pointer;
        transition:.2s;
    }
    .toggle .slider:before {
        content:'';
        position:absolute;
        width:18px;
        height:18px;
        left:3px;
        top:3px;
        background:var(--ivory);
        border-radius:50%;
        transition:.2s;
    }
    .toggle input:checked + .slider { background:var(--gold); }
    .toggle input:checked + .slider:before {
        transform:translateX(20px);
        background:var(--midnight);
    }

    .profile-head {
        display:flex;
        align-items:center;
        gap:16px;
    }

    .profile-avatar {
        width:64px;
        height:64px;
        border-radius:50%;
        background:var(--gold-dim);
        color:var(--gold);
        display:flex;
        align-items:center;
        justify-content:center;
        font-weight:800;
        font-size:1.4rem;
        flex-shrink:0;
    }

    .alert {
        padding:12px 16px;
        border-radius:9px;
        margin-bottom:20px;
        font-size:.88rem;
    }
    .alert-success {
        background:rgba(34,197,94,.15);
        border:1px solid rgba(34,197,94,.3);
        color:#4ade80;
    }
    .alert-error {
        background:rgba(239,68,68,.15);
        border:1px solid rgba(239,68,68,.3);
        color:#f87171;
    }

    .modal-backdrop {
        display:none;
        position:fixed;
        inset:0;
        background:rgba(0,0,0,.7);
        align-items:center;
        justify-content:center;
        z-index:1000;
        padding:20px;
    }
    .modal-backdrop.active { display:flex; }

    .modal-box {
        width:100%;
        max-width:480px;
        padding:24px;
        background:var(--card-bg,#1e1e24);
        border:1px solid var(--card-border);
        border-radius:12px;
    }

    @media (max-width:640px) {
        .field-row-2 { grid-template-columns:1fr; }
        .profile-head { align-items:flex-start; }
    }
</style>
@endpush

@section('content')

{{-- Alerts --}}
@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if(session('error'))
    <div class="alert alert-error">{{ session('error') }}</div>
@endif

@if($errors->any())
    <div class="alert alert-error">
        <ul style="margin:0;padding-left:18px;">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif


{{-- Tabs --}}
<div class="tabs" data-panels="#account-panels">
    @foreach([
        'profile' => 'Profile',
        'verification' => 'Verification',
        'bank' => 'Bank',
        'security' => 'Security',
        'documents' => 'Documents',
    ] as $id => $label)
        <button class="tab-btn" data-tab="{{ $id }}">
            {{ $label }}
        </button>
    @endforeach
</div>


<div id="account-panels">

    {{-- =========================================================
         PROFILE
    ========================================================== --}}
    <div class="tab-panel" id="profile">

        <div class="card" style="margin-bottom:20px;">
            <div class="profile-head">

                <div class="profile-avatar">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>

                <div>
                    <div style="font-weight:700;font-size:1.1rem;">
                        {{ $user->name }}
                    </div>

                    <div class="muted" style="font-size:.85rem;">
                        {{ $user->email }}
                    </div>

                    <div class="muted" style="font-size:.78rem;margin-top:2px;">
                        Member since {{ $user->created_at->format('M Y') }}
                    </div>
                </div>

                <span
                    class="badge {{ $user->kyc_status === 'verified' ? 'positive' : 'neutral' }}"
                    style="margin-left:auto;"
                >
                    {{ ucfirst($user->kyc_status ?? 'Unverified') }}
                </span>

            </div>
        </div>


        <form action="{{ route('account.profile') }}" method="POST" class="card">
            @csrf
            @method('PUT')

            <div class="section-title">Personal information</div>

            <div class="field-row-2">

                <div class="field">
                    <label for="name">Full name</label>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        value="{{ old('name', $user->name) }}"
                        required
                    >
                </div>

                <div class="field">
                    <label for="email">Email address</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="{{ old('email', $user->email) }}"
                        required
                    >
                </div>

            </div>

            <div class="field-row-2">

                <div class="field">
                    <label for="phone">Phone number</label>
                    <input
                        type="tel"
                        id="phone"
                        name="phone"
                        value="{{ old('phone', $user->phone) }}"
                        placeholder="+40 7xx xxx xxx"
                    >
                </div>

                <div class="field">
                    <label for="dob">Date of birth</label>
                    <input
                        type="date"
                        id="dob"
                        name="dob"
                        value="{{ old('dob', optional($user->dob)->format('Y-m-d')) }}"
                    >
                </div>

            </div>

            <div class="field-row-2">

                <div class="field">
                    <label for="country">Country of residence</label>

                    <select id="country" name="country">
                        @foreach(['Romania', 'Italy', 'France', 'Spain', 'Other'] as $country)
                            <option
                                value="{{ $country }}"
                                {{ old('country', $user->country) === $country ? 'selected' : '' }}
                            >
                                {{ $country }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="field">
                    <label for="address">Address</label>
                    <input
                        type="text"
                        id="address"
                        name="address"
                        value="{{ old('address', $user->address) }}"
                        placeholder="Street, city, postal code"
                    >
                </div>

            </div>

            <button type="submit" class="btn btn-gold" style="margin-top:4px;">
                Save changes
            </button>
        </form>

    </div>


    {{-- =========================================================
         VERIFICATION
    ========================================================== --}}
    <div class="tab-panel" id="verification">

        @php
            $identityVerified = (bool) $user->identity_verified;
        @endphp

        <div class="card">

            <div class="card-header">

                <div>
                    <div class="section-title" style="margin-bottom:2px;">
                        Identity verification
                    </div>

                    <div class="muted" style="font-size:.85rem;">
                        @if($identityVerified)
                            Your identity has been successfully verified.
                        @else
                            Verify your identity to unlock higher investment limits.
                        @endif
                    </div>
                </div>

                <span class="badge {{ $identityVerified ? 'positive' : 'neutral' }}">
                    {{ $identityVerified ? 'Verified' : 'Incomplete' }}
                </span>

            </div>


            <div class="list-row">

                <div>
                    <div class="primary">
                        Identity verification
                    </div>

                    <div class="secondary">
                        Your identity document and matching selfie are securely verified through Stripe Identity.
                    </div>
                </div>

                @if($identityVerified)

                    <span class="badge positive">
                        Verified
                    </span>

                @else

                    <a
                        href="{{ route('account.identity.start') }}"
                        class="btn btn-outline btn-sm"
                    >
                        Verify identity
                    </a>

                @endif

            </div>

        </div>

    </div>


    {{-- =========================================================
         BANK
    ========================================================== --}}
    <div class="tab-panel" id="bank">

        <div class="card" style="margin-bottom:20px;">

            <div class="card-header">

                <div>
                    <div class="section-title">
                        Linked bank accounts
                    </div>

                    <div class="muted" style="font-size:.82rem;margin-top:4px;">
                        Bank details are collected securely by Stripe.
                    </div>
                </div>

                <button
                    type="button"
                    class="btn btn-outline btn-sm"
                    onclick="openBankModal()"
                >
                    {{ count($stripeBankAccounts) ? 'Add / update bank' : 'Add bank account' }}
                </button>

            </div>


            @forelse($stripeBankAccounts as $bank)

                <div class="list-row">

                    <div>
                        <div class="primary">
                            {{ $bank['bank_name'] }}
                        </div>

                        <div class="secondary">
                            •••• {{ $bank['last4'] }}
                            · {{ strtoupper($bank['currency']) }}
                        </div>
                    </div>

                    @if($bank['default_for_currency'])
                        <span class="badge positive">Primary</span>
                    @endif

                </div>

            @empty

                <div class="muted" style="padding:16px 0;font-size:.88rem;line-height:1.6;">
                    No bank account is connected yet.
                    <br>
                    Connect your bank account through Stripe to enable withdrawals.
                </div>

            @endforelse

        </div>


        <div class="card">

            <div class="section-title">
                Withdrawals
            </div>

            <p class="muted" style="font-size:.88rem;line-height:1.6;">
                Withdrawals are sent to your verified primary bank account.
                <br>
                Available cash:
                <strong style="color:var(--ivory);">
                    €{{ number_format($user->balance ?? 0, 2) }}
                </strong>
            </p>


            @if(!$user->stripe_account_id)

                <div class="muted" style="margin-top:14px;font-size:.82rem;">
                    Connect a bank account to enable withdrawals.
                </div>

                <button
                    type="button"
                    class="btn btn-gold btn-sm"
                    style="margin-top:16px;"
                    onclick="openBankModal()"
                >
                    Connect bank account
                </button>


            @elseif(!$payoutsReady)

                <div class="muted" style="margin-top:14px;font-size:.82rem;">
                    Your Stripe payout setup is not complete yet.
                </div>

                <a
                    href="{{ route('account.stripe.onboarding') }}"
                    class="btn btn-gold btn-sm"
                    style="margin-top:16px;display:inline-block;text-decoration:none;"
                >
                    Complete bank setup
                </a>


            @elseif(count($stripeBankAccounts) === 0)

                <div class="muted" style="margin-top:14px;font-size:.82rem;">
                    Your Stripe account is ready, but no bank account is currently available for withdrawals.
                </div>

                <button
                    type="button"
                    class="btn btn-gold btn-sm"
                    style="margin-top:16px;"
                    onclick="openBankModal()"
                >
                    Add bank account
                </button>


            @elseif(($user->balance ?? 0) <= 0)

                <button
                    type="button"
                    class="btn btn-gold btn-sm"
                    style="margin-top:16px;opacity:.5;"
                    disabled
                >
                    Withdraw Funds
                </button>

                <div class="muted" style="margin-top:8px;font-size:.8rem;">
                    You have no available balance to withdraw.
                </div>


            @else

                <a
                    href="{{ route('withdraw.show') }}"
                    class="btn btn-gold btn-sm"
                    style="margin-top:16px;display:inline-block;text-decoration:none;"
                >
                    Withdraw Funds
                </a>

            @endif

        </div>

    </div>


    {{-- =========================================================
         SECURITY
    ========================================================== --}}
    <div class="tab-panel" id="security">

        <form
            action="{{ route('account.password') }}"
            method="POST"
            class="card"
            style="margin-bottom:20px;"
        >
            @csrf
            @method('PUT')

            <div class="section-title">
                Change password
            </div>

            <div class="field">
                <label for="current_password">Current password</label>

                <input
                    type="password"
                    id="current_password"
                    name="current_password"
                    placeholder="••••••••"
                    required
                >
            </div>

            <div class="field-row-2">

                <div class="field">
                    <label for="new_password">New password</label>

                    <input
                        type="password"
                        id="new_password"
                        name="new_password"
                        placeholder="••••••••"
                        required
                    >
                </div>

                <div class="field">
                    <label for="new_password_confirmation">
                        Confirm new password
                    </label>

                    <input
                        type="password"
                        id="new_password_confirmation"
                        name="new_password_confirmation"
                        placeholder="••••••••"
                        required
                    >
                </div>

            </div>

            <button type="submit" class="btn btn-gold" style="margin-top:4px;">
                Update password
            </button>
        </form>


        <div class="card" style="margin-bottom:20px;">

            <div class="list-row" style="border-bottom:none;">

                <div style="display:flex;align-items:center;gap:14px;">

                    <label class="toggle">
                        <input type="checkbox" checked>
                        <span class="slider"></span>
                    </label>

                    <div>
                        <div class="primary">
                            Two-factor authentication
                        </div>

                        <div class="secondary">
                            Require a code from your authenticator app when logging in
                        </div>
                    </div>

                </div>

            </div>

        </div>


        <div class="card">

            <div class="section-title">
                Active sessions
            </div>

            @forelse($sessions as $session)

                @php
                    $currentSession = $session->id === request()->session()->getId();
                @endphp

                <div class="list-row">

                    <div style="overflow:hidden;">

                        <div
                            class="primary"
                            style="white-space:nowrap;text-overflow:ellipsis;overflow:hidden;"
                        >
                            {{ \Illuminate\Support\Str::limit(
                                $session->user_agent ?? 'Unknown device',
                                45
                            ) }}

                            @if($currentSession)
                                <span class="badge positive" style="margin-left:6px;">
                                    This device
                                </span>
                            @endif
                        </div>

                        <div class="secondary">
                            IP: {{ $session->ip_address ?? 'Unknown' }}
                            ·
                            Last active
                            {{ \Carbon\Carbon::createFromTimestamp($session->last_activity)->diffForHumans() }}
                        </div>

                    </div>

                    @unless($currentSession)

                        <form
                            action="{{ route('account.session.destroy', $session->id) }}"
                            method="POST"
                        >
                            @csrf
                            @method('DELETE')

                            <button
                                type="submit"
                                class="btn btn-danger-outline btn-sm"
                            >
                                Log out
                            </button>
                        </form>

                    @endunless

                </div>

            @empty

                <div class="muted" style="padding:12px 0;font-size:.88rem;">
                    No active sessions found.
                </div>

            @endforelse

        </div>

    </div>


    {{-- =========================================================
         DOCUMENTS
    ========================================================== --}}
    <div class="tab-panel" id="documents">

        <div class="card">

            <table>

                <thead>
                    <tr>
                        <th>Document</th>
                        <th>Type</th>
                        <th>Date</th>
                        <th></th>
                    </tr>
                </thead>

                <tbody>

                    @foreach([
                        ['Q2 2026 Distribution Statement', 'Statement', 'Jul 5, 2026'],
                        ['Investment Agreement — Bucharest Hotel', 'Contract', 'May 12, 2022'],
                        ['Investment Agreement — Solar Farm #12', 'Contract', 'Feb 20, 2025'],
                        ['2025 Tax Summary', 'Tax', 'Jan 31, 2026'],
                        ['Proof of Identity.pdf', 'KYC', 'Jan 3, 2022'],
                    ] as [$document, $type, $date])

                        <tr>
                            <td style="font-weight:700;">
                                {{ $document }}
                            </td>

                            <td class="muted">
                                {{ $type }}
                            </td>

                            <td class="muted">
                                {{ $date }}
                            </td>

                            <td>
                                <button
                                    type="button"
                                    class="btn btn-outline btn-sm"
                                >
                                    Download
                                </button>
                            </td>
                        </tr>

                    @endforeach

                </tbody>

            </table>

        </div>

    </div>

</div>


{{-- =========================================================
     BANK MODAL
========================================================== --}}
<div class="modal-backdrop" id="add-bank-modal">

    <div class="modal-box">

        <div class="section-title" style="margin-bottom:16px;">
            Connect Bank Account
        </div>

        <p
            class="muted"
            style="font-size:.88rem;line-height:1.6;margin-bottom:20px;"
        >
            Your bank account will be securely connected through Stripe.
            Saturn does not collect or store your full IBAN.
        </p>

        <div style="display:flex;gap:10px;justify-content:flex-end;">

            <button
                type="button"
                class="btn btn-outline"
                onclick="closeBankModal()"
            >
                Cancel
            </button>

            <a
                href="{{ route('account.stripe.onboarding') }}"
                class="btn btn-gold"
                style="text-decoration:none;"
            >
                Continue with Stripe
            </a>

        </div>

    </div>

</div>


<script>
function openBankModal() {
    document.getElementById('add-bank-modal').classList.add('active');
}

function closeBankModal() {
    document.getElementById('add-bank-modal').classList.remove('active');
}

document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('add-bank-modal');

    if (!modal) return;

    modal.addEventListener('click', function (event) {
        if (event.target === modal) {
            closeBankModal();
        }
    });
});
</script>

@endsection