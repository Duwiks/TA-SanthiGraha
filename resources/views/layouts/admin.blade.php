<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'SanthiGraha Dashboard')</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Phosphor Icons (Matches exactly with the uploaded image) -->
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
                        sidebar: '#ffffff',
                        'sidebar-border': '#e2e8f0', /* Light purple/gray tint */
                        brand: {
                            500: '#6366f1',
                            600: '#4f46e5',
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

    <!-- Sidebar -->
    <aside class="w-72 bg-white h-screen border-r border-indigo-100 flex flex-col shadow-sm flex-shrink-0 z-10 transition-all duration-300">
        
        <!-- Logo Area -->
        <div class="h-20 flex items-center px-8 border-b border-indigo-100/50 border-dashed">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 flex items-center justify-center rounded-lg">
                    <i class="ph ph-buildings text-2xl text-slate-800"></i>
                </div>
                <span class="font-bold text-[17px] tracking-wide text-gray-900 mt-1">CV SANTHI GRAHA</span>
            </div>
        </div>

        <!-- Navigation Menu -->
        <nav class="flex-1 overflow-y-auto py-6 px-4 space-y-1.5 custom-scrollbar border-t border-indigo-100/50 border-dashed mt-1">
            
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3.5 px-4 py-3 rounded-xl text-slate-600 hover:bg-slate-50 hover:text-brand-600 transition-colors @if(request()->routeIs('dashboard')) bg-indigo-50/70 text-brand-600 font-medium @endif">
                <i class="ph ph-squares-four text-[22px]"></i>
                <span class="text-[15px]">Dashboard</span>
            </a>

            <a href="{{ route('transactions.index') }}" class="flex items-center gap-3.5 px-4 py-3 rounded-xl text-slate-600 hover:bg-slate-50 hover:text-brand-600 transition-colors @if(request()->routeIs('transactions.index')) bg-indigo-50/70 text-brand-600 font-medium @endif">
                <i class="ph ph-currency-dollar text-[22px]"></i>
                <span class="text-[15px]">Transaksi</span>
            </a>

            <a href="{{ route('approvals.index') }}" class="flex items-center gap-3.5 px-4 py-3 rounded-xl {{ request()->routeIs('approvals.*') ? 'bg-brand-50 text-brand-600 font-medium' : 'text-slate-600 hover:bg-slate-50 hover:text-brand-600 transition-colors' }}">
                <i class="ph ph-check-square text-[22px]"></i>
                <span class="text-[15px]">Approval</span>
                @php
                    $pendingCount = \App\Models\Transaction::where('status', 'pending')->count();
                @endphp
                @if($pendingCount > 0)
                    <span class="ml-auto bg-red-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full">{{ $pendingCount }}</span>
                @endif
            </a>

            <a href="{{ route('projects.index') }}" class="flex items-center gap-3.5 px-4 py-3 rounded-xl text-slate-600 hover:bg-slate-50 hover:text-brand-600 transition-colors @if(request()->routeIs('projects.*')) bg-indigo-50/70 text-brand-600 font-medium @endif">
                <i class="ph ph-folder text-[22px]"></i>
                <span class="text-[15px]">Manajemen Proyek</span>
            </a>

            <!-- Dropdown Master Data -->
            <div x-data="{ open: true }" class="pt-1">
                <button type="button" onclick="toggleDropdown()" class="w-full flex items-center justify-between px-4 py-3 rounded-xl text-slate-600 hover:bg-slate-50 hover:text-brand-600 transition-colors">
                    <div class="flex items-center gap-3.5">
                        <i class="ph ph-database text-[22px]"></i>
                        <span class="text-[15px]">Master Data</span>
                    </div>
                    <i class="ph ph-caret-down text-sm transition-transform duration-200" id="masterDataIcon"></i>
                </button>
                
                <div id="masterDataMenu" class="pl-12 pr-4 py-2 space-y-1 block">
                    <a href="{{ route('categories.index') }}" class="block px-4 py-2.5 rounded-lg text-[14.5px] text-slate-600 hover:text-brand-600 hover:bg-slate-50 transition-colors @if(request()->routeIs('categories.*')) text-brand-600 font-medium @endif">
                        Kategori Transaksi
                    </a>
                </div>
            </div>

            <a href="{{ route('rekap.index') }}" class="flex items-center gap-3.5 px-4 py-3 rounded-xl text-slate-600 hover:bg-slate-50 hover:text-brand-600 transition-colors mt-2 @if(request()->routeIs('rekap.*')) bg-indigo-50/70 text-brand-600 font-medium @endif">
                <i class="ph ph-file-text text-[22px]"></i>
                <span class="text-[15px]">Rekap & Laporan</span>
            </a>

            <a href="#" class="flex items-center gap-3.5 px-4 py-3 rounded-xl text-slate-600 hover:bg-slate-50 hover:text-brand-600 transition-colors">
                <i class="ph ph-gear text-[22px]"></i>
                <span class="text-[15px]">Pengaturan Akun</span>
            </a>

            <div class="pt-6 pb-2 border-t border-indigo-100/50 border-dashed mt-4">
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
                <h2 class="text-xl font-bold text-slate-800">@yield('page_title', 'Dashboard')</h2>
            </div>
            <div class="flex items-center gap-4">
                <div class="flex items-center gap-3 bg-slate-50 px-4 py-2 rounded-full border border-slate-200">
                    <div class="w-8 h-8 rounded-full bg-brand-500 flex items-center justify-center text-white font-bold text-sm">
                        {{ substr(auth()->user()->name ?? 'U', 0, 1) }}
                    </div>
                    <div class="flex flex-col">
                        <span class="text-sm font-semibold leading-tight text-slate-800">{{ auth()->user()->name ?? 'User Name' }}</span>
                        <span class="text-xs text-slate-500 font-medium capitalize">{{ auth()->user()->role ?? 'Role' }}</span>
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
        // Simple Dropdown Toggle Logic
        function toggleDropdown() {
            const menu = document.getElementById('masterDataMenu');
            const icon = document.getElementById('masterDataIcon');
            if (menu.classList.contains('hidden')) {
                menu.classList.remove('hidden');
                menu.classList.add('block');
                icon.style.transform = "rotate(0deg)";
            } else {
                menu.classList.remove('block');
                menu.classList.add('hidden');
                icon.style.transform = "rotate(-90deg)";
            }
        }

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
