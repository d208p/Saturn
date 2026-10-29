<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage {{ $asset->title }} — Saturn Admin</title>
    <link rel="icon" type="image/png" href="{{ asset('images/tab.png') }}">
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --midnight: #0B1220;
            --ivory: #F5F2EA;
            --gold: #C6A15B;
            --slate: #667085;
            --gold-border: rgba(198, 161, 91, 0.35);
            --card-bg: rgba(255, 255, 255, 0.03);
            --card-border: rgba(255, 255, 255, 0.06);
            --red: #f87171;
            --green: #4ade80;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Manrope', system-ui, sans-serif; background: var(--midnight); color: var(--ivory); padding-bottom: 60px; }
        a { color: inherit; text-decoration: none; }
        .container { max-width: 1120px; margin: 0 auto; padding: 0 24px; }
        header { padding: 24px 0; border-bottom: 1px solid var(--card-border); margin-bottom: 32px; }
        .nav-inner { display: flex; align-items: center; justify-content: space-between; }
        
        .alert { padding: 14px 18px; border-radius: 10px; margin-bottom: 24px; font-size: 0.9rem; }
        .alert-success { background: rgba(74,222,128,0.1); border: 1px solid rgba(74,222,128,0.3); color: var(--green); }

        .stats-summary { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 32px; }
        .stat-card { background: var(--card-bg); border: 1px solid var(--card-border); border-radius: 12px; padding: 20px; }
        .stat-label { font-size: 0.75rem; color: var(--slate); text-transform: uppercase; }
        .stat-val { font-size: 1.4rem; font-weight: 700; margin-top: 4px; }

        .panel { background: var(--card-bg); border: 1px solid var(--card-border); border-radius: 16px; padding: 32px; margin-bottom: 32px; }
        .panel h2 { font-size: 1.25rem; color: var(--gold); margin-bottom: 24px; }

        form { display: flex; flex-direction: column; gap: 18px; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; }
        .field label { display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px; }
        .field input, .field select, .field textarea {
            width: 100%; padding: 11px 14px; border-radius: 8px; border: 1px solid var(--card-border);
            background: rgba(0, 0, 0, 0.25); color: var(--ivory); font-family: inherit; outline: none;
        }
        .field input:focus, .field select:focus, .field textarea:focus { border-color: var(--gold-border); }

        .gallery-preview { display: flex; gap: 12px; flex-wrap: wrap; margin-top: 8px; }
        .gallery-preview img { width: 100px; height: 75px; object-fit: cover; border-radius: 8px; border: 1px solid var(--card-border); }

        .btn-gold { background: var(--gold); color: var(--midnight); font-weight: 700; padding: 12px 24px; border-radius: 8px; border: none; cursor: pointer; align-self: flex-start; }
    </style>
</head>
<body>
    <header>
        <div class="container nav-inner">
            <h1 style="font-size: 1.2rem;">Manage Asset: {{ $asset->title }}</h1>
            <a href="{{ route('admin.dashboard') }}" style="color: var(--slate);">&larr; Back to Admin Dashboard</a>
        </div>
    </header>

    <main class="container">
        @if (session('success'))
            <div class="alert alert-success">✓ {{ session('success') }}</div>
        @endif

        {{-- Top Performance Stats --}}
        <div class="stats-summary">
            <div class="stat-card">
                <div class="stat-label">Shareholders</div>
                <div class="stat-val">{{ number_format($totalShareholders) }}</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Circulating Shares</div>
                <div class="stat-val">{{ number_format($circulatingShares) }} / {{ number_format($asset->total_shares) }}</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Current Share Price</div>
                <div class="stat-val" style="color: var(--gold);">€{{ number_format($asset->share_price, 2) }}</div>
            </div>
        </div>

        {{-- Asset Update Form --}}
        <div class="panel">
            <h2>Edit Asset Details</h2>
            <form action="{{ route('admin.assets.update', $asset->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="form-row">
                    <div class="field">
                        <label for="title">Asset Title</label>
                        <input type="text" name="title" id="title" value="{{ old('title', $asset->title) }}" required>
                    </div>
                    <div class="field">
                        <label for="category">Category</label>
                        <input type="text" name="category" id="category" value="{{ old('category', $asset->category) }}" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="field">
                        <label for="country">Country</label>
                        <input type="text" name="country" id="country" value="{{ old('country', $asset->country) }}" required>
                    </div>
                    <div class="field">
                        <label for="status">Asset Status</label>
                        <select name="status" id="status" required>
                            <option value="draft" {{ $asset->status == 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="active" {{ $asset->status == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="funded" {{ $asset->status == 'funded' ? 'selected' : '' }}>Funded</option>
                            <option value="closed" {{ $asset->status == 'closed' ? 'selected' : '' }}>Closed</option>
                        </select>
                    </div>
                </div>


<div class="form-row">
    <div class="field">
        <label for="market_liquidity">Market Liquidity</label>
        <select name="market_liquidity" id="market_liquidity" required>
            <option value="low" {{ old('market_liquidity', $asset->market_liquidity) == 'low' ? 'selected' : '' }}>Low</option>
            <option value="moderate" {{ old('market_liquidity', $asset->market_liquidity) == 'moderate' ? 'selected' : '' }}>Moderate</option>
            <option value="high" {{ old('market_liquidity', $asset->market_liquidity) == 'high' ? 'selected' : '' }}>High</option>
        </select>
    </div>

    <div class="field">
        <label for="development_exposure">Development Exposure (%)</label>
        <input
            type="number"
            step="0.01"
            min="0"
            max="100"
            name="development_exposure"
            id="development_exposure"
            value="{{ old('development_exposure', $asset->development_exposure) }}"
        >
    </div>
</div>


                <div class="form-row">
                    <div class="field">
                        <label for="total_valuation">Total Valuation (€)</label>
                        <input type="number" step="0.01" name="total_valuation" id="total_valuation" value="{{ old('total_valuation', $asset->total_valuation) }}" required>
                    </div>
                    <div class="field">
                        <label for="share_price">Share Price (€)</label>
                        <input type="number" step="0.01" name="share_price" id="share_price" value="{{ old('share_price', $asset->share_price) }}" required>
                    </div>
                </div>

                <div class="field">
                    <label>Current Gallery Images</label>
                    <div class="gallery-preview">
                        @if(!empty($asset->images))
                            @foreach($asset->images as $img)
                                <img src="{{ asset('storage/' . $img) }}" alt="Asset photo">
                            @endforeach
                        @else
                            <p style="color: var(--slate); font-size: 0.85rem;">No images uploaded yet.</p>
                        @endif
                    </div>
                </div>

                <div class="field">
                    <label for="images">Add More Images to Gallery</label>
                    <input type="file" name="images[]" id="images" multiple accept="image/*">
                </div>

                <div class="field">
                    <label for="description">Asset Description</label>
                    <textarea name="description" id="description" rows="4">{{ old('description', $asset->description) }}</textarea>
                </div>

                <button type="submit" class="btn-gold">Update Asset Settings</button>
            </form>
        </div>
    </main>
</body>
</html>