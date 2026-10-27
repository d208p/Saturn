@extends('layouts.app')

@section('title', 'Account')

@push('styles')
<style>
    .acct { --line: rgba(255,255,255,0.07); }
    .acct .tabs { border-bottom: 1px solid var(--line); gap: 28px; margin-bottom: 36px; }
    .acct .tab-btn { padding: 12px 0; font-size: .88rem; letter-spacing: .01em; }
    .acct .num { font-variant-numeric: tabular-nums; }
    .acct .eyebrow { font-size: .64rem; text-transform: uppercase; letter-spacing: .12em; color: var(--slate); font-weight: 600; }

    /* Alerts */
    .alert { padding: 12px 16px; border-radius: 8px; margin-bottom: 24px; font-size: .86rem; border: 1px solid; }
    .alert-success { background: var(--green-dim); border-color: rgba(74,222,128,.3); color: var(--green); }
    .alert-error { background: var(--red-dim); border-color: rgba(248,113,113,.3); color: var(--red); }
    .alert ul { margin: 0; padding-left: 18px; }

    /* Identity header */
    .id-head { display: flex; align-items: center; gap: 20px; padding-bottom: 32px; border-bottom: 1px solid var(--line); flex-wrap: wrap; }
    .id-avatar { width: 64px; height: 64px; border-radius: 50%; background: var(--gold-dim); color: var(--gold); display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 1.4rem; flex-shrink: 0; }
    .id-name { font-weight: 700; font-size: 1.15rem; letter-spacing: -.01em; }
    .id-meta { font-size: .82rem; color: var(--slate); margin-top: 2px; }
    .id-head .badge { margin-left: auto; }

    /* Sections */
    .sec { display: grid; grid-template-columns: 240px minmax(0, 1fr); gap: 48px; padding: 32px 0; border-bottom: 1px solid var(--line); }
    .sec:last-child { border-bottom: 0; }
    .sec h2 { font-size: .95rem; font-weight: 700; margin-bottom: 6px; }
    .sec .lede { font-size: .82rem; color: var(--slate); line-height: 1.55; }

    /* Fields */
    .field { margin-bottom: 20px; }
    .field label { display: block; font-size: .64rem; text-transform: uppercase; letter-spacing: .12em; color: var(--slate); margin-bottom: 6px; font-weight: 600; }
    .field input, .field select {
        width: 100%; background: transparent; border: 0; border-bottom: 1px solid var(--line);
        color: var(--ivory); font-family: inherit; font-size: .92rem; padding: 8px 2px; outline: none; border-radius: 0; transition: border-color .15s;
    }
    .field input::placeholder { color: rgba(102,112,133,.7); }
    .field select option { background: var(--midnight); color: var(--ivory); }
    .field input:focus, .field select:focus { border-bottom-color: var(--gold); }
    .field .hint { font-size: .74rem; margin-top: 6px; min-height: 1em; color: var(--slate); }
    .field .hint.bad { color: var(--red); }
    .field-row-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 0 28px; }
    .btn:disabled { opacity: .4; cursor: not-allowed; transform: none; box-shadow: none; }

    /* Rows */
    .row { display: flex; justify-content: space-between; align-items: center; gap: 16px; padding: 16px 0; border-top: 1px solid var(--line); }
    .row:first-child { border-top: 0; padding-top: 0; }
    .row .t { font-weight: 700; font-size: .9rem; }
    .row .s { font-size: .78rem; color: var(--slate); margin-top: 3px; line-height: 1.5; }
    .row .grow { min-width: 0; }
    .ellip { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .btn-link { text-decoration: none; }
    .state { font-size: .82rem; color: var(--slate); line-height: 1.6; }

    .empty { padding: 28px 0; color: var(--slate); font-size: .88rem; line-height: 1.6; }
    .empty strong { display: block; color: var(--ivory); font-size: .92rem; margin-bottom: 4px; }

    /* Modals */
    .modal-backdrop { display: none; position: fixed; inset: 0; background: rgba(5,9,18,.8); backdrop-filter: blur(6px); align-items: center; justify-content: center; z-index: 1000; padding: 20px; }
    .modal-backdrop.active { display: flex; }
    .modal-box { width: 100%; max-width: 460px; padding: 28px; background: #101828; border: 1px solid var(--line); border-radius: 12px; max-height: 92vh; overflow-y: auto; }
    .modal-box h3 { font-size: 1rem; font-weight: 700; margin-bottom: 10px; }
    .modal-box p { font-size: .86rem; color: var(--slate); line-height: 1.6; }
    .modal-actions { display: flex; gap: 10px; justify-content: flex-end; margin-top: 24px; }
    .codes { background: rgba(0,0,0,.35); border: 1px solid var(--line); border-radius: 8px; padding: 14px; margin: 18px 0 20px; display: grid; grid-template-columns: 1fr 1fr; gap: 8px; text-align: left; }
    .codes code { font-size: .82rem; color: var(--ivory); font-family: ui-monospace, monospace; }

    @media (max-width: 860px) {
        .sec { grid-template-columns: 1fr; gap: 20px; padding: 28px 0; }
    }
    @media (max-width: 640px) {
        .acct .tabs { gap: 20px; }
        .field-row-2 { grid-template-columns: 1fr; }
        .id-head .badge { margin-left: 0; }
        .row { flex-wrap: wrap; }
        .modal-actions .btn { flex: 1; }
    }
</style>
@endpush

@section('content')
@php
    $countries = ['Romania', 'Italy', 'France', 'Spain', 'Other'];
    $currentCountry = old('country', $user->country);
    if ($currentCountry && !in_array($currentCountry, $countries)) { array_unshift($countries, $currentCountry); } // keep saved value even if not listed
    $identityVerified = (bool) $user->identity_verified;
    $twoFactorOn = (bool) $user->two_factor_confirmed_at;
    $kyc = $user->kyc_status ?? 'unverified';
@endphp

<div class="acct">

    @if(session('success'))<div class="alert alert-success" role="status">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-error" role="alert">{{ session('error') }}</div>@endif
    @if($errors->any())
        <div class="alert alert-error" role="alert"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif

    <div class="tabs" data-panels="#account-panels">
        @foreach(['profile' => 'Profile', 'verification' => 'Verification', 'bank' => 'Bank', 'security' => 'Security', 'documents' => 'Documents'] as $id => $label)
            <button class="tab-btn {{ $loop->first ? 'active' : '' }}" data-tab="{{ $id }}">{{ $label }}</button>
        @endforeach
    </div>

    <div id="account-panels">

        {{-- ================= PROFILE ================= --}}
        <div class="tab-panel active" id="profile">
            <div class="id-head">
                <div class="id-avatar">{{ strtoupper(substr($user->name, 0, 1)) }}</div>
                <div>
                    <div class="id-name">{{ $user->name }}</div>
                    <div class="id-meta">{{ $user->email }}</div>
                    <div class="id-meta">Member since {{ $user->created_at->format('M Y') }}</div>
                </div>
                <span class="badge {{ $kyc === 'verified' ? 'positive' : 'neutral' }}">{{ ucfirst($kyc) }}</span>
            </div>

            <div class="sec">
                <div>
                    <h2>Personal information</h2>
                    <div class="lede">Used for your account and for regulatory checks. Keep it accurate and up to date.</div>
                </div>
                <form action="{{ route('account.profile') }}" method="POST" id="profileForm">
                    @csrf
                    @method('PUT')
                    <div class="field-row-2">
                        <div class="field"><label for="name">Full name</label>
                            <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required autocomplete="name"></div>
                        <div class="field"><label for="email">Email address</label>
                            <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required autocomplete="email"></div>
                    </div>
                    <div class="field-row-2">
                        <div class="field"><label for="phone">Phone number</label>
                            <input type="tel" id="phone" name="phone" value="{{ old('phone', $user->phone) }}" placeholder="+40 7xx xxx xxx" autocomplete="tel"></div>
                        <div class="field"><label for="dob">Date of birth</label>
                            <input type="date" id="dob" name="dob" value="{{ old('dob', optional($user->dob)->format('Y-m-d')) }}" autocomplete="bday"></div>
                    </div>
                    <div class="field-row-2">
                        <div class="field"><label for="country">Country of residence</label>
                            {{-- Hardcoded: country list is a short static set. Replace with the full supported-countries list. --}}
                            <select id="country" name="country">
                                @foreach($countries as $country)
                                    <option value="{{ $country }}" {{ $currentCountry === $country ? 'selected' : '' }}>{{ $country }}</option>
                                @endforeach
                            </select></div>
                        <div class="field"><label for="address">Address</label>
                            <input type="text" id="address" name="address" value="{{ old('address', $user->address) }}" placeholder="Street, city, postal code" autocomplete="street-address"></div>
                    </div>
                    <button type="submit" class="btn btn-gold" id="profileSave" disabled>Save changes</button>
                </form>
            </div>
        </div>

        {{-- ================= VERIFICATION ================= --}}
        <div class="tab-panel" id="verification">
            <div class="sec">
                <div>
                    <h2>Identity verification</h2>
                    <div class="lede">
                        @if($identityVerified) Your identity has been verified. @else Verify your identity to unlock higher investment limits. @endif
                    </div>
                </div>
                <div>
                    <div class="row">
                        <div class="grow">
                            <div class="t">Identity document and selfie</div>
                            <div class="s">Checked securely through Stripe Identity. Saturn does not store your document images.</div>
                        </div>
                        @if($identityVerified)
                            <span class="badge positive">Verified</span>
                        @else
                            <a href="{{ route('account.identity.start') }}" class="btn btn-gold btn-sm btn-link">Verify identity</a>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- ================= BANK ================= --}}
        <div class="tab-panel" id="bank">
            <div class="sec">
                <div>
                    <h2>Linked bank accounts</h2>
                    <div class="lede">Bank details are collected securely by Stripe.</div>
                </div>
                <div>
                    @forelse($stripeBankAccounts as $bank)
                        <div class="row">
                            <div class="grow">
                                <div class="t">{{ $bank['bank_name'] }}</div>
                                <div class="s num">•••• {{ $bank['last4'] }} · {{ strtoupper($bank['currency']) }}</div>
                            </div>
                            @if($bank['default_for_currency'])<span class="badge positive">Primary</span>@endif
                        </div>
                    @empty
                        <div class="empty" style="padding-top:0;"><strong>No bank account connected</strong>Connect one through Stripe to enable withdrawals.</div>
                    @endforelse
                    <button type="button" class="btn btn-outline btn-sm" style="margin-top:16px;" onclick="openModal('add-bank-modal')">
                        {{ count($stripeBankAccounts) ? 'Add / update bank' : 'Add bank account' }}
                    </button>
                </div>
            </div>

            <div class="sec">
                <div>
                    <h2>Withdrawals</h2>
                    <div class="lede">Sent to your verified primary bank account.</div>
                </div>
                <div>
                    <div class="eyebrow">Available cash</div>
                    <div class="num" style="font-size:1.6rem; font-weight:300; letter-spacing:-.02em; margin:6px 0 16px;">€{{ number_format($user->balance ?? 0, 2) }}</div>

                    @if(!$user->stripe_account_id)
                        <p class="state">Connect a bank account to enable withdrawals.</p>
                        <button type="button" class="btn btn-gold btn-sm" style="margin-top:14px;" onclick="openModal('add-bank-modal')">Connect bank account</button>
                    @elseif(!$payoutsReady)
                        <p class="state">Your Stripe payout setup is not complete yet.</p>
                        <a href="{{ route('account.stripe.onboarding') }}" class="btn btn-gold btn-sm btn-link" style="margin-top:14px;">Complete bank setup</a>
                    @elseif(count($stripeBankAccounts) === 0)
                        <p class="state">Your Stripe account is ready, but no bank account is available for withdrawals.</p>
                        <button type="button" class="btn btn-gold btn-sm" style="margin-top:14px;" onclick="openModal('add-bank-modal')">Add bank account</button>
                    @elseif(($user->balance ?? 0) <= 0)
                        <button type="button" class="btn btn-gold btn-sm" disabled>Withdraw funds</button>
                        <p class="state" style="margin-top:10px;">You have no available balance to withdraw.</p>
                    @else
                        <a href="{{ route('withdraw.show') }}" class="btn btn-gold btn-sm btn-link">Withdraw funds</a>
                    @endif
                </div>
            </div>
        </div>

        {{-- ================= SECURITY ================= --}}
        <div class="tab-panel" id="security">
            <div class="sec">
                <div>
                    <h2>Password</h2>
                    <div class="lede">Use a long, unique password that you don't use anywhere else.</div>
                </div>
                <form action="{{ route('account.password') }}" method="POST" id="pwForm">
                    @csrf
                    @method('PUT')
                    <div class="field"><label for="current_password">Current password</label>
                        <input type="password" id="current_password" name="current_password" placeholder="••••••••" required autocomplete="current-password"></div>
                    <div class="field-row-2">
                        <div class="field"><label for="new_password">New password</label>
                            <input type="password" id="new_password" name="new_password" placeholder="••••••••" required autocomplete="new-password"></div>
                        <div class="field"><label for="new_password_confirmation">Confirm new password</label>
                            <input type="password" id="new_password_confirmation" name="new_password_confirmation" placeholder="••••••••" required autocomplete="new-password">
                            <div class="hint" id="pwHint" aria-live="polite"></div></div>
                    </div>
                    <button type="submit" class="btn btn-gold" id="pwSave" disabled>Update password</button>
                </form>
            </div>

            <div class="sec">
                <div>
                    <h2>Two-factor authentication</h2>
                    <div class="lede">Adds a second step at login using an authenticator app.</div>
                </div>
                <div class="row" style="border-top:0; padding-top:0;">
                    <div class="grow">
                        <div class="t">{{ $twoFactorOn ? 'Enabled' : 'Not enabled' }}</div>
                        <div class="s">{{ $twoFactorOn ? 'Protected by an authenticator app.' : 'Require a code from your authenticator app when logging in.' }}</div>
                    </div>
                    @if($twoFactorOn)
                        <form action="{{ route('account.2fa.disable') }}" method="POST" onsubmit="return confirm('Disable two-factor authentication? Your account will be less protected.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger-outline btn-sm">Disable 2FA</button>
                        </form>
                    @else
                        <button type="button" class="btn btn-gold btn-sm" onclick="openModal('enable-2fa-modal')">Enable 2FA</button>
                    @endif
                </div>
            </div>

            <div class="sec">
                <div>
                    <h2>Active sessions</h2>
                    <div class="lede">Devices currently signed in. Log out any you don't recognise.</div>
                </div>
                <div>
                    @forelse($sessions as $session)
                        @php $currentSession = $session->id === request()->session()->getId(); @endphp
                        <div class="row">
                            <div class="grow">
                                <div class="t ellip">
                                    {{ \Illuminate\Support\Str::limit($session->user_agent ?? 'Unknown device', 45) }}
                                    @if($currentSession)<span class="badge positive" style="margin-left:6px;">This device</span>@endif
                                </div>
                                <div class="s">IP {{ $session->ip_address ?? 'Unknown' }} · Last active {{ \Carbon\Carbon::createFromTimestamp($session->last_activity)->diffForHumans() }}</div>
                            </div>
                            @unless($currentSession)
                                <form action="{{ route('account.session.destroy', $session->id) }}" method="POST" onsubmit="return confirm('Log out this device?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger-outline btn-sm">Log out</button>
                                </form>
                            @endunless
                        </div>
                    @empty
                        <div class="empty" style="padding-top:0;">No active sessions found.</div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- ================= DOCUMENTS ================= --}}
        <div class="tab-panel" id="documents">
            @php
                // Hardcoded: no documents source is wired up yet. Pass $documents from the controller as
                // [['title' => ..., 'type' => ..., 'date' => ..., 'url' => ...], ...]. The old sample list was fake and its buttons did nothing.
                $documents = collect($documents ?? []);
            @endphp
            <div class="sec">
                <div>
                    <h2>Documents</h2>
                    <div class="lede">Statements, agreements and tax summaries related to your account.</div>
                </div>
                <div>
                    @forelse($documents as $doc)
                        <div class="row">
                            <div class="grow">
                                <div class="t ellip">{{ $doc['title'] }}</div>
                                <div class="s">{{ $doc['type'] }} · {{ $doc['date'] }}</div>
                            </div>
                            <a href="{{ $doc['url'] }}" class="btn btn-outline btn-sm btn-link">Download</a>
                        </div>
                    @empty
                        <div class="empty" style="padding-top:0;"><strong>No documents available yet</strong>Statements and agreements will appear here once they are issued.</div>
                    @endforelse
                </div>
            </div>
        </div>

    </div>
</div>

{{-- ================= MODALS ================= --}}
<div class="modal-backdrop" id="enable-2fa-modal" role="dialog" aria-modal="true" aria-labelledby="m2fa-title">
    <div class="modal-box" style="max-width:400px; text-align:center;">
        <h3 id="m2fa-title">Set up two-factor authentication</h3>
        <p>Scan this QR code with an authenticator app such as Google Authenticator or Authy, then enter the 6-digit code.</p>
        <div id="qr-code-container" style="margin:18px 0; background:#fff; padding:12px; display:inline-block; border-radius:8px;">{!! $twoFactorQr ?? '' !!}</div>
        <p style="font-size:.78rem;">Can't scan? Enter this key manually:<br><code style="color:var(--ivory); word-break:break-all;">{{ $twoFactorSecret ?? '' }}</code></p>
        <form action="{{ route('account.2fa.enable') }}" method="POST">
            @csrf
            <div class="field" style="text-align:left; margin-top:18px; margin-bottom:0;">
                <label for="code">Verification code</label>
                <input type="text" id="code" name="code" placeholder="123456" required autocomplete="one-time-code" inputmode="numeric" maxlength="6" style="text-align:center; letter-spacing:4px; font-size:1.1rem;">
            </div>
            <div class="modal-actions">
                <button type="button" class="btn btn-outline" onclick="closeModal('enable-2fa-modal')">Cancel</button>
                <button type="submit" class="btn btn-gold">Verify and enable</button>
            </div>
        </form>
    </div>
</div>

@if(session('recovery_codes'))
<div class="modal-backdrop active" id="recovery-codes-modal" role="dialog" aria-modal="true" aria-labelledby="rc-title">
    <div class="modal-box" style="max-width:440px; text-align:center;">
        <h3 id="rc-title" style="color:var(--gold);">Save your recovery codes</h3>
        <p>Store these in a secure password manager. If you lose your authenticator device, you can use them to log in. <strong style="color:var(--red);">They will not be shown again.</strong></p>
        <div class="codes">@foreach(session('recovery_codes') as $code)<code>{{ $code }}</code>@endforeach</div>
        <button type="button" class="btn btn-gold btn-block" onclick="closeModal('recovery-codes-modal')">I have saved these codes</button>
    </div>
</div>
@endif

<div class="modal-backdrop" id="add-bank-modal" role="dialog" aria-modal="true" aria-labelledby="mb-title">
    <div class="modal-box">
        <h3 id="mb-title">Connect bank account</h3>
        <p>Your bank account is connected securely through Stripe. Saturn does not collect or store your full IBAN.</p>
        <div class="modal-actions">
            <button type="button" class="btn btn-outline" onclick="closeModal('add-bank-modal')">Cancel</button>
            <a href="{{ route('account.stripe.onboarding') }}" class="btn btn-gold btn-link">Continue with Stripe</a>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function openModal(id) { document.getElementById(id).classList.add('active'); }
function closeModal(id) { document.getElementById(id).classList.remove('active'); }

document.addEventListener('DOMContentLoaded', function () {
    // Backdrop click + Escape close
    document.querySelectorAll('.modal-backdrop').forEach(m => m.addEventListener('click', e => { if (e.target === m) m.classList.remove('active'); }));
    document.addEventListener('keydown', e => { if (e.key === 'Escape') document.querySelectorAll('.modal-backdrop.active').forEach(m => m.classList.remove('active')); });

    // Reopen the 2FA setup modal if validation failed
    @if(session('open_2fa'))
        openModal('enable-2fa-modal');
    @endif

    // Profile: enable Save only when something changed
    const pf = document.getElementById('profileForm');
    if (pf) {
        const save = document.getElementById('profileSave');
        const snap = () => JSON.stringify(Array.from(pf.querySelectorAll('input:not([type=hidden]), select')).map(i => i.value));
        const initial = snap();
        const check = () => { save.disabled = snap() === initial; };
        pf.addEventListener('input', check); pf.addEventListener('change', check);
        // after a failed validation the old() values already differ from the saved ones, so leave the button usable
        @if($errors->any()) save.disabled = false; @endif
    }

    // Password: confirm must match before submitting
    const np = document.getElementById('new_password'), cp = document.getElementById('new_password_confirmation'),
          cur = document.getElementById('current_password'), hint = document.getElementById('pwHint'), pwSave = document.getElementById('pwSave');
    if (np && cp) {
        const check = () => {
            const mismatch = cp.value.length > 0 && np.value !== cp.value;
            hint.textContent = mismatch ? 'Passwords do not match.' : '';
            hint.classList.toggle('bad', mismatch);
            pwSave.disabled = !(cur.value && np.value && cp.value && !mismatch);
        };
        [np, cp, cur].forEach(i => i.addEventListener('input', check));
    }
});
</script>
@endpush