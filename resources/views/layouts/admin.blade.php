<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'SanthiGraha Dashboard')</title>
    <link rel="icon" type="image/png" href="{{ asset('images/santhigraha-logo-white.png') }}">
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        .amount-card {
            font-size: clamp(14px, 1vw, 22px);
            font-weight: 700;
            line-height: 1.2;
            white-space: nowrap;
        }
    </style>

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

    <!-- Rupiah Formatter -->
    <script src="{{ asset('js/rupiah.js') }}"></script>
</head>

<body class="bg-slate-50 font-sans text-slate-800 antialiased flex h-screen overflow-hidden">

    <!-- Mobile Overlay (klik untuk tutup sidebar) -->
    <div id="sidebarOverlay" onclick="closeSidebar()"
        class="fixed inset-0 bg-black/50 backdrop-blur-sm z-40 hidden lg:hidden transition-opacity duration-300">
    </div>

    <!-- Sidebar -->
    <aside id="sidebar"
        class="fixed lg:static inset-y-0 left-0 z-50 w-72 bg-white h-screen border-r border-indigo-100 flex flex-col shadow-sm flex-shrink-0 transition-transform duration-300 -translate-x-full lg:translate-x-0">

        <!-- Logo Area -->
        <div class="h-20 flex items-center px-8 border-b border-indigo-100/50 border-dashed">
            <div class="flex items-center gap-3">
                <img src="{{ asset('images/santhigraha-logo.png') }}" alt="Logo CV Santhi Graha"
                    class="w-10 h-10 object-contain">

                <span class="font-bold text-[17px] tracking-wide text-gray-900">
                    CV SANTHI GRAHA
                </span>
            </div>
        </div>

        <!-- Navigation Menu -->
        <nav
            class="flex-1 overflow-y-auto py-6 px-4 space-y-1.5 custom-scrollbar border-t border-indigo-100/50 border-dashed mt-1">

            <a href="{{ route('dashboard') }}"
                class="flex items-center gap-3.5 px-4 py-3 rounded-xl {{ request()->routeIs('dashboard') ? 'bg-emerald-50 text-emerald-600 font-medium' : 'text-slate-600 hover:bg-slate-50 hover:text-brand-600 transition-colors' }}">
                <i class="ph ph-squares-four text-[22px]"></i>
                <span class="text-[15px]">Dashboard</span>
            </a>

            <a href="{{ route('transactions.index') }}"
                class="flex items-center gap-3.5 px-4 py-3 rounded-xl {{ request()->routeIs('transactions.*') ? 'bg-emerald-50 text-emerald-600 font-medium' : 'text-slate-600 hover:bg-slate-50 hover:text-brand-600 transition-colors' }}">
                <i class="ph ph-currency-dollar text-[22px]"></i>
                <span class="text-[15px]">Transaksi</span>
            </a>

            <a href="{{ route('approvals.index') }}"
                class="flex items-center gap-3.5 px-4 py-3 rounded-xl {{ request()->routeIs('approvals.*') ? 'bg-emerald-50 text-emerald-600 font-medium' : 'text-slate-600 hover:bg-slate-50 hover:text-brand-600 transition-colors' }}">
                <i class="ph ph-check-square text-[22px]"></i>
                <span class="text-[15px]">Approval</span>
                @php
                    $pendingCount = \App\Models\Transaction::where('status', 'pending')->count();
                @endphp
                @if($pendingCount > 0)
                    <span
                        class="ml-auto bg-red-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full">{{ $pendingCount }}</span>
                @endif
            </a>

            <a href="{{ route('nota-merah.index') }}"
                class="flex items-center gap-3.5 px-4 py-3 rounded-xl {{ request()->routeIs('nota-merah.*') ? 'bg-emerald-50 text-emerald-600 font-medium' : 'text-slate-600 hover:bg-slate-50 hover:text-brand-600 transition-colors' }}">
                <i class="ph ph-note-pencil text-[22px]"></i>
                <span class="text-[15px]">Nota Merah</span>
                @php
                    $notaMerahCount = \App\Models\NotaMerah::whereIn('status', ['menunggu_persetujuan', 'menunggu_konfirmasi'])->count();
                @endphp
                @if($notaMerahCount > 0)
                    <span
                        class="ml-auto bg-amber-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full">{{ $notaMerahCount }}</span>
                @endif
            </a>

            <a href="{{ route('projects.index') }}"
                class="flex items-center gap-3.5 px-4 py-3 rounded-xl {{ request()->routeIs('projects.*') ? 'bg-emerald-50 text-emerald-600 font-medium' : 'text-slate-600 hover:bg-slate-50 hover:text-brand-600 transition-colors' }}">
                <i class="ph ph-folder text-[22px]"></i>
                <span class="text-[15px]">Manajemen Proyek</span>
            </a>

            <!-- Dropdown Master Data -->
            <div x-data="{ open: true }" class="pt-1">
                <button type="button" onclick="toggleDropdown()"
                    class="w-full flex items-center justify-between px-4 py-3 rounded-xl text-slate-600 hover:bg-slate-50 hover:text-brand-600 transition-colors">
                    <div class="flex items-center gap-3.5">
                        <i class="ph ph-database text-[22px]"></i>
                        <span class="text-[15px]">Master Data</span>
                    </div>
                    <i class="ph ph-caret-down text-sm transition-transform duration-200" id="masterDataIcon"
                        style="transform: {{ request()->routeIs('categories.*') ? 'rotate(0deg)' : 'rotate(-90deg)' }}"></i>
                </button>

                <div id="masterDataMenu"
                    class="pl-12 pr-4 py-2 space-y-1 {{ request()->routeIs('categories.*') ? 'block' : 'hidden' }}">
                    <a href="{{ route('categories.index') }}"
                        class="block px-4 py-2.5 rounded-lg text-[14.5px] {{ request()->routeIs('categories.*') ? 'bg-emerald-50 text-emerald-600 font-medium' : 'text-slate-600 hover:text-brand-600 hover:bg-slate-50 transition-colors' }}">
                        Kategori Transaksi
                    </a>
                </div>
            </div>

            <a href="{{ route('rekap.index') }}"
                class="flex items-center gap-3.5 px-4 py-3 rounded-xl mt-2 {{ request()->routeIs('rekap.*') ? 'bg-emerald-50 text-emerald-600 font-medium' : 'text-slate-600 hover:bg-slate-50 hover:text-brand-600 transition-colors' }}">
                <i class="ph ph-file-text text-[22px]"></i>
                <span class="text-[15px]">Laporan</span>
            </a>

            <a href="{{ route('pegawai.index') }}"
                class="flex items-center gap-3.5 px-4 py-3 rounded-xl {{ request()->routeIs('pegawai.*') ? 'bg-emerald-50 text-emerald-600 font-medium' : 'text-slate-600 hover:bg-slate-50 hover:text-brand-600 transition-colors' }}">
                <i class="ph ph-identification-badge text-[22px]"></i>
                <span class="text-[15px]">Data Akun Pegawai</span>
            </a>

            <a href="{{ route('account.index') }}"
                class="flex items-center gap-3.5 px-4 py-3 rounded-xl {{ request()->routeIs('account.*') ? 'bg-emerald-50 text-emerald-600 font-medium' : 'text-slate-600 hover:bg-slate-50 hover:text-brand-600 transition-colors' }}">
                <i class="ph ph-gear text-[22px]"></i>
                <span class="text-[15px]">Pengaturan Akun</span>
            </a>

            <div class="pt-6 pb-2 border-t border-indigo-100/50 border-dashed mt-4">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                        class="w-full flex items-center gap-3.5 px-4 py-3 rounded-xl text-slate-600 hover:bg-red-50 hover:text-red-600 transition-colors">
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
        <header
            class="h-20 bg-white/80 backdrop-blur-md border-b border-slate-200/60 flex items-center justify-between px-4 md:px-8 z-10">
            <div class="flex items-center gap-3">
                <!-- Hamburger Button (mobile only) -->
                <button id="hamburgerBtn" onclick="openSidebar()"
                    class="lg:hidden p-2.5 rounded-xl text-slate-600 hover:bg-slate-100 active:bg-slate-200 transition-colors"
                    aria-label="Buka Menu">
                    <i class="ph ph-list text-2xl"></i>
                </button>
                <h2 class="text-lg md:text-xl font-bold text-slate-800">@yield('page_title', 'Dashboard')</h2>
            </div>
            <div class="flex items-center gap-4">
                <div class="flex items-center gap-3 bg-slate-50 px-4 py-2 rounded-full border border-slate-200">
                    <div
                        class="w-8 h-8 rounded-full bg-brand-500 flex items-center justify-center text-white font-bold text-sm">
                        {{ substr(auth()->user()->name ?? 'U', 0, 1) }}
                    </div>
                    <div class="flex flex-col">
                        <span
                            class="text-sm font-semibold leading-tight text-slate-800">{{ auth()->user()->name ?? 'User Name' }}</span>
                        <span
                            class="text-xs text-slate-500 font-medium capitalize">{{ auth()->user()->role ?? 'Role' }}</span>
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
        // Sidebar Hamburger Toggle
        function openSidebar() {
            document.getElementById('sidebar').classList.remove('-translate-x-full');
            document.getElementById('sidebarOverlay').classList.remove('hidden');
        }

        function closeSidebar() {
            document.getElementById('sidebar').classList.add('-translate-x-full');
            document.getElementById('sidebarOverlay').classList.add('hidden');
        }

        // Tutup sidebar otomatis saat layar diperbesar ke desktop
        window.addEventListener('resize', () => {
            if (window.innerWidth >= 1024) {
                document.getElementById('sidebarOverlay').classList.add('hidden');
            }
        });

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

        // SweetAlert2 Toast for success messages
        @if(session('success'))
            const ToastSuccess = Swal.mixin({
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

            ToastSuccess.fire({
                icon: 'success',
                title: '{{ session('success') }}'
            });
        @endif

        // SweetAlert2 Popup for error messages (e.g. cannot delete)
        @if(session('error'))
            Swal.fire({
                title: 'Tidak Dapat Dihapus!',
                text: '{{ session('error') }}',
                icon: 'error',
                confirmButtonColor: '#6366f1',
                confirmButtonText: 'Mengerti',
                customClass: {
                    popup: 'rounded-2xl shadow-xl border border-slate-100',
                    title: 'text-slate-800 font-bold text-lg',
                    htmlContainer: 'text-slate-600 text-sm',
                    confirmButton: 'px-6 py-2.5 rounded-xl font-semibold text-sm',
                }
            });
        @endif

        function confirmDelete(formId, message = 'Apakah Anda yakin ingin menghapus data ini?') {
            Swal.fire({
                title: 'Konfirmasi Hapus',
                text: message,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal',
                customClass: {
                    popup: 'rounded-2xl shadow-xl border border-slate-100',
                    confirmButton: 'px-5 py-2.5 rounded-xl font-semibold text-sm mr-2',
                    cancelButton: 'px-5 py-2.5 rounded-xl font-semibold text-sm'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById(formId).submit();
                }
            });
        }
    </script>
</body>

</html>