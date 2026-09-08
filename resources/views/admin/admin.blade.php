@extends('layouts.app')
@section('title', 'Admin Panel')

@section('content')
    <div style="margin-bottom: 24px;">
        <h2>Admin Panel</h2>
        <span class="muted">Platform administration and asset creation</span>
    </div>

    @if(session('success'))
        <div style="padding: 12px; background: rgba(0, 200, 100, 0.1); border: 1px solid green; margin-bottom: 20px; border-radius: 6px;">
            {{ session('success') }}
        </div>
    @endif

    <!-- System Stats -->
    <div class="grid grid-3" style="margin-bottom: 24px;">
        <div class="card">
            <div class="stat-label">Total Registered Users</div>
            <div class="stat-value">{{ $stats['total_users'] }}</div>
        </div>
        <div class="card">
            <div class="stat-label">Total Assets Emitted</div>
            <div class="stat-value">{{ $stats['total_assets'] }}</div>
        </div>
        <div class="card">
            <div class="stat-label">Total Transaction Volume</div>
            <div class="stat-value">€{{ number_format($stats['total_volume'], 2) }}</div>
        </div>
    </div>

    <!-- Create New Opportunity Form -->
    <div class="card" style="margin-bottom: 24px;">
        <div class="section-title">Emit New Investment Opportunity</div>
        <form action="{{ route('admin.assets.store') }}" method="POST" style="display: grid; gap: 16px; margin-top: 16px;">
            @csrf
            <div>
                <label>Title</label>
                <input type="text" name="title" required style="width: 100%; padding: 8px;">
            </div>
            <div>
                <label>Category</label>
                <input type="text" name="category" placeholder="e.g. Hospitality, Energy" required style="width: 100%; padding: 8px;">
            </div>
            <div>
                <label>Valuation (€)</label>
                <input type="number" step="0.01" name="total_valuation" required style="width: 100%; padding: 8px;">
            </div>
            <div>
                <label>Total Shares</label>
                <input type="number" name="total_shares" required style="width: 100%; padding: 8px;">
            </div>
            <div>
                <label>Share Price (€)</label>
                <input type="number" step="0.01" name="share_price" required style="width: 100%; padding: 8px;">
            </div>
            <div>
                <label>Description</label>
                <textarea name="description" style="width: 100%; padding: 8px;"></textarea>
            </div>
            <button type="submit" style="padding: 10px 16px; cursor: pointer;">Emit Opportunity</button>
        </form>
    </div>

    <!-- Active Opportunities List -->
    <div class="card">
        <div class="section-title">Emitted Opportunities</div>
        <table>
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Available Shares</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($assets as $asset)
                    <tr>
                        <td>{{ $asset->title }}</td>
                        <td>{{ $asset->category }}</td>
                        <td>€{{ number_format($asset->share_price, 2) }}</td>
                        <td>{{ $asset->available_shares }} / {{ $asset->total_shares }}</td>
                        <td>{{ ucfirst($asset->status) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="muted" style="text-align: center;">No opportunities emitted yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection