<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'SanthiGraha Auth')</title>
    <link rel="icon" type="image/png" href="{{ asset('images/santhigraha-logo-white.png') }}">
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Phosphor Icons -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Outfit', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#eef2ff',
                            100: '#e0e7ff',
                            500: '#6366f1',
                            600: '#4f46e5',
                            700: '#4338ca',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        .glass-panel {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.5);
        }
    </style>
</head>

<body
    class="min-h-screen flex items-center justify-center bg-gradient-to-br from-indigo-100 via-purple-100 to-pink-100 p-4 font-sans text-slate-800">

    <div
        class="glass-panel w-full max-w-md rounded-3xl p-6 sm:p-8 shadow-[0_8px_30px_rgb(0,0,0,0.04)] hover:shadow-[0_8px_30px_rgb(0,0,0,0.08)] transition-shadow duration-300">

        @if(session('success'))
            <div
                class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-600 text-sm font-medium animate-[slideDown_0.3s_ease-out]">
                {{ session('success') }}
            </div>
        @endif

        <div class="text-center mb-6 sm:mb-8">
            <!-- Logo & Brand Name -->
            <div class="flex items-center justify-center gap-2 mb-2">
                <img src="{{ asset('images/santhigraha-logo.png') }}" alt="Logo SanthiGraha"
                    class="w-10 h-10 sm:w-14 sm:h-14 object-contain">
                <span class="text-2xl sm:text-3xl font-extrabold tracking-tight">SANTRA</span>
            </div>

            <h1 class="text-base sm:text-lg font-bold text-slate-700 mb-1">
                @yield('header_title')
            </h1>
            <p class="text-slate-500 text-xs sm:text-sm font-medium">@yield('header_subtitle')</p>
        </div>

        @yield('content')

    </div>

</body>

</html>