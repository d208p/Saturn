@extends('layouts.app')

@section('title', 'Account')

@push('styles')
<style>
    .field { margin-bottom: 16px; }
    .field label { display: block; font-size: 0.82rem; font-weight: 600; margin-bottom: 7px; color: var(--ivory); }
    .field input, .field select {
        width: 100%; padding: 12px 14px; border-radius: 9px; border: 1px solid var(--card-border);
        background: rgba(0, 0, 0, 0.25); color: var(--ivory); font-family: inherit; font-size: 0.9rem; outline: none;
        transition: border-color .15s;
    }
    .field input::placeholder { color: var(--slate); }
    .field input:focus, .field select:focus { border-color: var(--gold-border); }
    .field-row-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }

    .toggle { position: relative; display: inline-block; width: 44px; height: 24px; flex-shrink: 0; }
    .toggle input { opacity: 0; width: 0; height: 0; }
    .toggle .slider { position: absolute; inset: 0; background: var(--card-border); border-radius: 100px; transition: .2s; cursor: pointer; }
    .toggle .slider:before { content: ''; position: absolute; width: 18px; height: 18px; left: 3px; top: 3px; background: var(--ivory); border-radius: 50%; transition: .2s; }
    .toggle input:checked + .slider { background: var(--gold); }
    .toggle input:checked + .slider:before { transform: translateX(20px); background: var(--midnight); }

    .profile-head { display: flex; align-items: center; gap: 16px; margin-bottom: 4px; }
    .profile-avatar { width: 64px; height: 64px; border-radius: 50%; background: var(--gold-dim); color: var(--gold); display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 1.4rem; flex-shrink: 0; }

    .alert { padding: 12px 16px; border-radius: 9px; margin-bottom: 20px; font-size: 0.88rem; }
    .alert-success { background: rgba(34, 197, 94, 0.15); border: 1px solid rgba(34, 197, 94, 0.3); color: #4ade80; }
    .alert-error { background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.3); color: #f87171; }

    .modal-backdrop { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.7); align-items: center; justify-content: center; z-index: 1000; }
    .modal-backdrop.active { display: flex; }
    .modal-box { background: var(--card-bg, #1e1e24); border: 1px solid var(--card-border); border-radius: 12px; width: 100%; max-width: 480px; padding: 24px; }

    @media (max-width: 640px) { .field-row-2 { grid-template-columns: 1fr; } }
</style>
@endpush

@section('content')
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-error">
            <ul style="margin: 0; padding-left: 18px;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="tabs" data-panels="#account-panels">
        <button class="tab-btn" data-tab="profile">Profile</button>
        <button class="tab-btn" data-tab="verification">Verification</button>
        <button class="tab-btn" data-tab="bank">Bank</button>
        <button class="tab-btn" data-tab="security">Security</button>
        <button class="tab-btn" data-tab="documents">Documents</button>
    </div>

    <div id="account-panels">

        <!-- Profile -->
        <div class="tab-panel" id="profile">
            <div class="card" style="margin-bottom: 20px;">
                <div class="profile-head">
                    <div class="profile-avatar">{{ strtoupper(substr($user->name, 0, 1)) }}</div>
                    <div>
                        <div style="font-weight: 700; font-size: 1.1rem;">{{ $user->name }}</div>
                        <div class="muted" style="font-size: 0.85rem;">{{ $user->email }}</div>
                        <div class="muted" style="font-size: 0.78rem; margin-top: 2px;">Member since {{ $user->created_at->format('M Y') }}</div>
                    </div>
                    <span class="badge {{ $user->kyc_status === 'verified' ? 'positive' : 'neutral' }}" style="margin-left: auto;">
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
                        <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required>
                    </div>
                    <div class="field">
                        <label for="email">Email address</label>
                        <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required>
                    </div>
                </div>
                <div class="field-row-2">
                    <div class="field">
                        <label for="phone">Phone number</label>
                        <input type="tel" id="phone" name="phone" value="{{ old('phone', $user->phone) }}" placeholder="+40 7xx xxx xxx">
                    </div>
                    <div class="field">
                        <label for="dob">Date of birth</label>
                        <input type="date" id="dob" name="dob" value="{{ old('dob', optional($user->dob)->format('Y-m-d')) }}">
                    </div>
                </div>
                <div class="field-row-2">
                    <div class="field">
                        <label for="country">Country of residence</label>
                        <select id="country" name="country">
                            <option value="Romania" {{ old('country', $user->country) === 'Romania' ? 'selected' : '' }}>Romania</option>
                            <option value="Italy" {{ old('country', $user->country) === 'Italy' ? 'selected' : '' }}>Italy</option>
                            <option value="France" {{ old('country', $user->country) === 'France' ? 'selected' : '' }}>France</option>
                            <option value="Spain" {{ old('country', $user->country) === 'Spain' ? 'selected' : '' }}>Spain</option>
                            <option value="Other" {{ old('country', $user->country) === 'Other' ? 'selected' : '' }}>Other</option>
                        </select>
                    </div>
                    <div class="field">
                        <label for="address">Address</label>
                        <input type="text" id="address" name="address" value="{{ old('address', $user->address) }}" placeholder="Street, city, postal code">
                    </div>
                </div>
                <button type="submit" class="btn btn-gold" style="margin-top: 4px;">Save changes</button>
            </form>
        </div>

        <!-- Dynamic Verification -->
        <div class="tab-panel" id="verification">
            @php
                $verifiedSteps = ($user->identity_verified ? 1 : 0) + ($user->address_verified ? 1 : 0) + ($user->selfie_verified ? 1 : 0);
                $progressPercent = round(($verifiedSteps / 3) * 100);
            @endphp
            <div class="card" style="margin-bottom: 20px;">
                <div class="card-header">
                    <div>
                        <div class="section-title" style="margin-bottom: 2px;">
                            Verification {{ $progressPercent === 100 ? 'Level 2' : 'Level 1' }}
                        </div>
                        <div class="muted" style="font-size: 0.85rem;">
                            {{ $progressPercent === 100 ? 'You can invest up to €50,000 per calendar year' : 'Complete verification to unlock higher investment limits' }}
                        </div>
                    </div>
                    <span class="badge {{ $progressPercent === 100 ? 'positive' : 'neutral' }}">
                        {{ $progressPercent === 100 ? 'Verified' : 'In Progress' }}
                    </span>
                </div>
                <div class="progress-track"><div class="progress-fill" style="width: {{ $progressPercent }}%;"></div></div>
            </div>

            <div class="card">
                <div class="section-title">Verification steps</div>
                <div class="list-row">
                    <div>
                        <div class="primary">Identity document</div>
                        <div class="secondary">Passport or national ID</div>
                    </div>
                    <span class="badge {{ $user->identity_verified ? 'positive' : 'neutral' }}">
                        {{ $user->identity_verified ? 'Verified' : 'Pending' }}
                    </span>
                </div>
                <div class="list-row">
                    <div>
                        <div class="primary">Proof of address</div>
                        <div class="secondary">Utility bill or bank statement</div>
                    </div>
                    <span class="badge {{ $user->address_verified ? 'positive' : 'neutral' }}">
                        {{ $user->address_verified ? 'Verified' : 'Pending' }}
                    </span>
                </div>
                <div class="list-row">
                    <div>
                        <div class="primary">Selfie verification</div>
                        <div class="secondary">Live photo match</div>
                    </div>
                    <span class="badge {{ $user->selfie_verified ? 'positive' : 'neutral' }}">
                        {{ $user->selfie_verified ? 'Verified' : 'Pending' }}
                    </span>
                </div>
                <div class="list-row">
                    <div>
                        <div class="primary">Accredited investor status</div>
                        <div class="secondary">Unlocks higher annual investment limits</div>
                    </div>
                    @if($user->accredited_investor)
                        <span class="badge positive">Verified</span>
                    @else
                        <form action="{{ route('account.accreditation') }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-outline btn-sm">Request</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        <!-- Dynamic Bank Tab -->
        <div class="tab-panel" id="bank">
            <div class="card" style="margin-bottom: 20px;">
                <div class="card-header">
                    <div class="section-title">Linked bank accounts</div>
                    <button class="btn btn-outline btn-sm" onclick="document.getElementById('add-bank-modal').classList.add('active')">Add bank account</button>
                </div>

                @forelse($user->bankAccounts as $bank)
                    <div class="list-row">
                        <div>
                            <div class="primary">{{ $bank->bank_name }}</div>
                            <div class="secondary">{{ $bank->masked_iban }} · {{ $bank->currency }}</div>
                        </div>
                        @if($bank->is_primary)
                            <span class="badge neutral">Primary</span>
                        @endif
                    </div>
                @empty
                    <div class="muted" style="padding: 12px 0; font-size: 0.88rem;">
                        No bank accounts linked yet. Click "Add bank account" above to connect your bank.
                    </div>
                @endforelse
            </div>

            <div class="card">
                <div class="section-title">Withdrawals</div>
                <p class="muted" style="font-size: 0.88rem;">
                    Withdrawals are sent to your verified primary bank account and typically arrive within 1–3 business days. Available cash: <strong style="color: var(--ivory);">€{{ number_format($user->balance ?? 0, 2) }}</strong>.
                </p>
                @if($user->bankAccounts->count() > 0 && ($user->balance ?? 0) > 0)
                    <button type="button" class="btn btn-gold btn-sm" style="margin-top: 16px;" onclick="document.getElementById('withdraw-modal').classList.add('active')">Withdraw funds</button>
                @else
                    <button type="button" class="btn btn-gold btn-sm" style="margin-top: 16px; opacity: 0.5;" disabled>Withdraw funds</button>
                @endif
            </div>
        </div>

        <!-- Security -->
        <div class="tab-panel" id="security">
            <form action="{{ route('account.password') }}" method="POST" class="card" style="margin-bottom: 20px;">
                @csrf
                @method('PUT')
                <div class="section-title">Change password</div>
                <div class="field">
                    <label for="current_password">Current password</label>
                    <input type="password" id="current_password" name="current_password" placeholder="••••••••" required>
                </div>
                <div class="field-row-2">
                    <div class="field">
                        <label for="new_password">New password</label>
                        <input type="password" id="new_password" name="new_password" placeholder="••••••••" required>
                    </div>
                    <div class="field">
                        <label for="new_password_confirmation">Confirm new password</label>
                        <input type="password" id="new_password_confirmation" name="new_password_confirmation" placeholder="••••••••" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-gold" style="margin-top: 4px;">Update password</button>
            </form>

            <div class="card" style="margin-bottom: 20px;">
                <div class="list-row" style="border-bottom: none;">
                    <div style="display: flex; align-items: center; gap: 14px;">
                        <label class="toggle">
                            <input type="checkbox" checked>
                            <span class="slider"></span>
                        </label>
                        <div>
                            <div class="primary">Two-factor authentication</div>
                            <div class="secondary">Require a code from your authenticator app when logging in</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="section-title">Active sessions</div>
                @foreach($sessions as $session)
                    <div class="list-row">
                        <div style="overflow: hidden;">
                            <div class="primary" style="white-space: nowrap; text-overflow: ellipsis; overflow: hidden;">
                                {{ Str::limit($session->user_agent, 45) }}
                                @if($session->id === request()->session()->getId())
                                    <span class="badge positive" style="margin-left: 6px;">This device</span>
                                @endif
                            </div>
                            <div class="secondary">
                                IP: {{ $session->ip_address }} · Last active {{ \Carbon\Carbon::createFromTimestamp($session->last_activity)->diffForHumans() }}
                            </div>
                        </div>
                        @if($session->id !== request()->session()->getId())
                            <form action="{{ route('account.session.destroy', $session->id) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger-outline btn-sm">Log out</button>
                            </form>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Documents -->
        <div class="tab-panel" id="documents">
            <div class="card">
                <table>
                    <thead><tr><th>Document</th><th>Type</th><th>Date</th><th></th></tr></thead>
                    <tbody>
                        <tr>
                            <td style="font-weight: 700;">Q2 2026 Distribution Statement</td>
                            <td class="muted">Statement</td>
                            <td class="muted">Jul 5, 2026</td>
                            <td><button class="btn btn-outline btn-sm">Download</button></td>
                        </tr>
                        <tr>
                            <td style="font-weight: 700;">Investment Agreement — Bucharest Hotel</td>
                            <td class="muted">Contract</td>
                            <td class="muted">May 12, 2022</td>
                            <td><button class="btn btn-outline btn-sm">Download</button></td>
                        </tr>
                        <tr>
                            <td style="font-weight: 700;">Investment Agreement — Solar Farm #12</td>
                            <td class="muted">Contract</td>
                            <td class="muted">Feb 20, 2025</td>
                            <td><button class="btn btn-outline btn-sm">Download</button></td>
                        </tr>
                        <tr>
                            <td style="font-weight: 700;">2025 Tax Summary</td>
                            <td class="muted">Tax</td>
                            <td class="muted">Jan 31, 2026</td>
                            <td><button class="btn btn-outline btn-sm">Download</button></td>
                        </tr>
                        <tr>
                            <td style="font-weight: 700;">Proof of Identity.pdf</td>
                            <td class="muted">KYC</td>
                            <td class="muted">Jan 3, 2022</td>
                            <td><button class="btn btn-outline btn-sm">Download</button></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <!-- Modal: Add Bank Account -->
    <div class="modal-backdrop" id="add-bank-modal">
        <div class="modal-box">
            <div class="section-title" style="margin-bottom: 16px;">Add Bank Account</div>
            <form action="{{ route('account.bank.store') }}" method="POST">
                @csrf
                <div class="field">
                    <label for="bank_name">Bank Name</label>
                    <input type="text" id="bank_name" name="bank_name" placeholder="e.g. Revolut, ING, BCR" required>
                </div>
                <div class="field">
                    <label for="iban">IBAN</label>
                    <input type="text" id="iban" name="iban" placeholder="RO49 RNCB 0000 0000 0000 0000" required>
                </div>
                <div class="field">
                    <label for="currency">Currency</label>
                    <select id="currency" name="currency">
                        <option value="EUR">EUR</option>
                        <option value="RON">RON</option>
                        <option value="USD">USD</option>
                    </select>
                </div>
                <div style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 20px;">
                    <button type="button" class="btn btn-outline" onclick="document.getElementById('add-bank-modal').classList.remove('active')">Cancel</button>
                    <button type="submit" class="btn btn-gold">Save Account</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal: Withdraw Funds -->
    <div class="modal-backdrop" id="withdraw-modal">
        <div class="modal-box">
            <div class="section-title" style="margin-bottom: 16px;">Withdraw Funds</div>
            <form action="{{ route('account.withdraw') }}" method="POST">
                @csrf
                <div class="field">
                    <label for="bank_account_id">Destination Bank</label>
                    <select id="bank_account_id" name="bank_account_id" required>
                        @foreach($user->bankAccounts as $bank)
                            <option value="{{ $bank->id }}">{{ $bank->bank_name }} ({{ $bank->masked_iban }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label for="amount">Amount (€)</label>
                    <input type="number" id="amount" name="amount" step="0.01" min="10" max="{{ $user->balance }}" placeholder="100.00" required>
                </div>
                <div style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 20px;">
                    <button type="button" class="btn btn-outline" onclick="document.getElementById('withdraw-modal').classList.remove('active')">Cancel</button>
                    <button type="submit" class="btn btn-gold">Confirm Withdrawal</button>
                </div>
            </form>
        </div>
    </div>
@endsection