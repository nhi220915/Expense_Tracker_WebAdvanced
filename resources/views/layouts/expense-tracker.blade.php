<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Expense Tracker PRO')</title>
    <!-- <p>DEBUG ID: {{ config('services.google_analytics.measurement_id') }}</p> -->
    @if (config('services.google_analytics.measurement_id'))
        <!-- Google tag (gtag.js) -->
        <script async src="https://www.googletagmanager.com/gtag/js?id={{ config('services.google_analytics.measurement_id') }}"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());

            gtag('config', '{{ config('services.google_analytics.measurement_id') }}', {
                'anonymize_ip': true
            });
        </script>
    @endif

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="bg-gray-100">
    
    <header class="main-header">
        <div class="header-container">
            {{-- LOGO (Sử dụng class .logo cho CSS tùy chỉnh) --}}
            <div class="logo">TnF</div>
            
            <div class="header-controls">
                {{-- USER INFO --}}
                <div class="user-info">Hello, {{ auth()->user()->name }}</div>
                
                {{-- LOGOUT BUTTON (Sử dụng class .btn-logout cho CSS tùy chỉnh) --}}
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn-logout">
                        Logout
                    </button>
                </form>
            </div>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-4">
        @if(session('success'))
            <div class="rounded-lg bg-emerald-500 text-white px-4 py-3 text-sm shadow">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="rounded-lg bg-warning text-white px-4 py-3 text-sm shadow">
                {{ session('error') }}
            </div>
        @endif

        @yield('content')
    </main>

    @stack('modals')
    @stack('scripts')
</body>
</html>