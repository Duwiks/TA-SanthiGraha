<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Pegawai Dashboard')</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Phosphor Icons -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            500: '#10b981', // Emerald green custom color for Pegawai for distinction
                            600: '#059669',
                        }
                    }
                }
            }
        }
    </script>
    
    <!-- SweetAlert2 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-slate-50 font-sans text-slate-800 antialiased flex h-screen overflow-hidden">

    <!-- Sidebar Pegawai -->
    <aside class="w-72 bg-white h-screen border-r border-emerald-100 flex flex-col shadow-sm flex-shrink-0 z-10 transition-all duration-300">
        
        <!-- Logo Area -->
        <div class="h-20 flex items-center px-8 border-b border-emerald-100/50 border-dashed">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 flex items-center justify-center rounded-lg">
                    <i class="ph ph-buildings text-2xl text-slate-800"></i>
                </div>
                <span class="font-bold text-[17px] tracking-wide text-gray-900 mt-1">CV SANTHI GRAHA</span>
            </div>
        </div>

        <!-- Navigation Menu -->
        <nav class="flex-1 overflow-y-auto py-6 px-4 space-y-1.5 custom-scrollbar border-t border-emerald-100/50 border-dashed mt-1">
            
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3.5 px-4 py-3 rounded-xl text-slate-600 hover:bg-slate-50 hover:text-brand-600 transition-colors @if(request()->routeIs('dashboard')) bg-emerald-50/70 text-brand-600 font-medium @endif">
                <i class="ph ph-squares-four text-[22px]"></i>
                <span class="text-[15px]">Dashboard</span>
            </a>

            <a href="{{ route('transactions.index') }}" class="flex items-center gap-3.5 px-4 py-3 rounded-xl text-slate-600 hover:bg-slate-50 hover:text-brand-600 transition-colors @if(request()->routeIs('transactions.*')) bg-emerald-50/70 text-brand-600 font-medium @endif">
                <i class="ph ph-currency-dollar text-[22px]"></i>
                <span class="text-[15px]">Transaksi</span>
            </a>

            <a href="{{ route('account.index') }}" class="flex items-center gap-3.5 px-4 py-3 rounded-xl text-slate-600 hover:bg-slate-50 hover:text-brand-600 transition-colors @if(request()->routeIs('account.*')) bg-emerald-50/70 text-brand-600 font-medium @endif">
                <i class="ph ph-gear text-[22px]"></i>
                <span class="text-[15px]">Pengaturan Akun</span>
            </a>

            <div class="pt-6 pb-2 border-t border-emerald-100/50 border-dashed mt-4">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full flex items-center gap-3.5 px-4 py-3 rounded-xl text-slate-600 hover:bg-red-50 hover:text-red-600 transition-colors">
                        <i class="ph ph-sign-out text-[22px]"></i>
                        <span class="text-[15px]">Logout</span>
                    </button>
                </form>
            </div>
            
        </nav>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 flex flex-col h-screen overflow-hidden relative">
        <!-- Top Navbar -->
        <header class="h-20 bg-white/80 backdrop-blur-md border-b border-slate-200/60 flex items-center justify-between px-8 z-10">
            <div>
                <h2 class="text-xl font-bold text-slate-800">@yield('page_title', 'Area Pegawai')</h2>
            </div>
            <div class="flex items-center gap-4">
                <div class="flex items-center gap-3 bg-slate-50 px-4 py-2 rounded-full border border-slate-200">
                    <div class="w-8 h-8 rounded-full bg-brand-500 flex items-center justify-center text-white font-bold text-sm">
                        {{ substr(auth()->user()->name ?? 'U', 0, 1) }}
                    </div>
                    <div class="flex flex-col">
                        <span class="text-sm font-semibold leading-tight text-slate-800">{{ auth()->user()->name ?? 'Nama Pegawai' }}</span>
                        <span class="text-xs text-brand-600 font-bold capitalize">{{ auth()->user()->role ?? 'Pegawai' }}</span>
                    </div>
                </div>
            </div>
        </header>

        <!-- Dynamic Content Area -->
        <div class="flex-1 overflow-y-auto p-8 custom-scrollbar relative z-0">
            @yield('content')
        </div>
    </main>

    <!-- Scripts -->
    <script>
        // SweetAlert2 Toast for Login Success or any success messages
        @if(session('success'))
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 4000,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer)
                    toast.addEventListener('mouseleave', Swal.resumeTimer)
                }
            });

            Toast.fire({
                icon: 'success',
                title: '{{ session('success') }}'
            });
        @endif
    </script>
</body>
</html>
