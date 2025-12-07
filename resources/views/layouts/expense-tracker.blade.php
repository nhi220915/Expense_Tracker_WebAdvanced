<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Expense Tracker PRO')</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css'])
    @stack('styles')
</head>
<body>
    <header>
        <div class="logo">Expense Tracker PRO</div>
        <div class="header-controls">
            <div class="user-info">
                Hello, {{ auth()->user()->name }}
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" style="padding: 8px 15px; background: var(--mint-dark); color: white; border: none; border-radius: 5px; cursor: pointer;">Logout</button>
            </form>
        </div>
    </header>

    <!-- Flash Messages -->
    @if(session('success'))
        <div style="background: #27ae60; color: white; padding: 15px; margin: 20px 40px; border-radius: 8px; text-align: center;">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div style="background: #e74c3c; color: white; padding: 15px; margin: 20px 40px; border-radius: 8px; text-align: center;">
            {{ session('error') }}
        </div>
    @endif

    <div class="grid-container">
        @yield('content')
    </div>

    @vite(['resources/js/app.js'])
    @stack('scripts')
</body>
</html>