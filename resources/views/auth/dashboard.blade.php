<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Saturn</title>
</head>
<body>
    <header style="display: flex; justify-content: space-between; align-items: center; padding: 1rem 2rem; border-bottom: 1px solid #ccc;">
        <a href="/" class="logo">
            <img src="{{ asset('images/v5yuA.png') }}" alt="Saturn Logo" class="logo-img" style="height: 40px;">
        </a>

        <!-- Logout Form (POST request required for security) -->
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" style="padding: 8px 16px; cursor: pointer;">Log Out</button>
        </form>
    </header>

    <main style="padding: 2rem;">
        <h1>Welcome, {{ Auth::user()->name }}!</h1>
        <p>You are logged into your Saturn account.</p>

        <section style="margin-top: 2rem; padding: 1.5rem; border: 1px solid #ddd; border-radius: 8px;">
            <h3>Account Info</h3>
            <p><strong>Email:</strong> {{ Auth::user()->email }}</p>
            <p><strong>Joined:</strong> {{ Auth::user()->created_at->format('M d, Y') }}</p>
        </section>
    </main>
</body>
</html>