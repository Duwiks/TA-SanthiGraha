@extends('layouts.admin')

@section('title', 'Dashboard - SanthiGraha')
@section('page_title', 'Dashboard')

@section('content')
    <!-- Welcome Banner Section -->
    <div
        class="bg-gradient-to-r from-brand-600 to-indigo-500 rounded-3xl p-8 text-white shadow-lg shadow-brand-500/20 relative overflow-hidden mb-8">
        <!-- Abstract Shapes -->
        <div class="absolute -right-10 -top-20 w-64 h-64 bg-white/10 rounded-full blur-3xl"></div>
        <div class="absolute right-40 -bottom-20 w-40 h-40 bg-white/10 rounded-full blur-2xl"></div>

        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <h2 class="text-3xl font-bold mb-2">Selamat Datang, {{ auth()->user()->name ?? 'Admin' }}! 👋</h2>
                <p class="text-indigo-100 text-lg max-w-xl">Anda masuk sebagai <span
                        class="font-semibold capitalize">{{ auth()->user()->role ?? 'Pegawai' }}</span>. Pantau dan kelola
                    seluruh transaksi serta proyek CV Santhi Graha dengan mudah melalui sistem ini.</p>
            </div>
            <div class="shrink-0 bg-white/20 backdrop-blur-sm px-6 py-4 rounded-2xl border border-white/20 text-center">
                <p class="text-sm font-medium text-indigo-100 mb-1">Tanggal Hari Ini</p>
                <p class="text-xl font-bold">{{ date('d M Y') }}</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">

        <!-- Metric Card 1 -->
        <div
            class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 flex items-center gap-5 hover:shadow-md transition-shadow">
            <div class="w-14 h-14 rounded-full bg-blue-50 flex items-center justify-center text-blue-500">
                <i class="ph ph-users text-2xl"></i>
            </div>
            <div>
                <p class="text-sm font-medium text-slate-500 mb-1">Total Pegawai</p>
                <h3 class="text-2xl font-bold text-slate-800">{{ $totalPegawai }}</h3>
            </div>
        </div>

        <!-- Metric Card 2 -->
        <div
            class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 flex items-center gap-5 hover:shadow-md transition-shadow">
            <div class="w-14 h-14 rounded-full bg-emerald-50 flex items-center justify-center text-emerald-500">
                <i class="ph ph-folder text-2xl"></i>
            </div>
            <div>
                <p class="text-sm font-medium text-slate-500 mb-1">Proyek Aktif</p>
                <h3 class="text-2xl font-bold text-slate-800">{{ $proyekAktif }}</h3>
            </div>
        </div>

        <!-- Metric Card 3 -->
        <div
            class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 flex items-center gap-5 hover:shadow-md transition-shadow">
            <div class="w-14 h-14 rounded-full bg-amber-50 flex items-center justify-center text-amber-500">
                <i class="ph ph-hourglass-high text-2xl"></i>
            </div>
            <div>
                <p class="text-sm font-medium text-slate-500 mb-1">Menunggu Approval</p>
                <h3 class="text-2xl font-bold text-slate-800">{{ $menungguApproval }}</h3>
            </div>
        </div>

        <!-- Metric Card 4 -->
        <div
            class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 flex items-center gap-5 hover:shadow-md transition-shadow">
            <div class="w-14 h-14 rounded-full bg-purple-50 flex items-center justify-center text-purple-500">
                <i class="ph ph-wallet text-2xl"></i>
            </div>
            <div>
                <p class="text-sm font-medium text-slate-500 mb-1">Total Transaksi</p>
                <h3 class="text-2xl font-bold text-slate-800">{{ $totalTransaksi }}</h3>
            </div>
        </div>

    </div>

    <!-- Grafik Section -->
    <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-100 mb-8">
        <div
            class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6 pb-6 border-b border-slate-100 border-dashed">
            <div>
                <h3 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                    <i class="ph ph-chart-bar text-brand-500 text-xl"></i>
                    Grafik Pemasukan & Pengeluaran
                </h3>
                <p class="text-sm text-slate-500 mt-1">Pergerakan arus kas tahun {{ $year }}</p>
            </div>

            <!-- Filter Proyek -->
            <form action="{{ route('dashboard') }}" method="GET" class="shrink-0">
                <div class="flex items-center gap-2 bg-slate-50 p-1.5 rounded-xl border border-slate-200">
                    <div class="pl-3 py-1 flex items-center justify-center text-slate-400">
                        <i class="ph ph-funnel text-lg"></i>
                    </div>
                    <select name="project_id" onchange="this.form.submit()"
                        class="bg-transparent border-none text-sm font-semibold text-slate-700 py-1.5 pr-4 pl-1 focus:ring-0 cursor-pointer outline-none w-48">
                        <option value="">Semua Proyek (Global)</option>
                        @foreach($projects as $project)
                            <option value="{{ $project->id }}" {{ $projectId == $project->id ? 'selected' : '' }}>
                                {{ $project->project_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </form>
        </div>

        <!-- Chart Container -->
        <div id="financeChart" class="w-full h-[350px]"></div>
    </div>

    <!-- ApexCharts Library & Script -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var options = {
                series: [{
                    name: 'Pemasukan',
                    data: @json($pemasukanData)
                }, {
                    name: 'Pengeluaran',
                    data: @json($pengeluaranData)
                }],
                chart: {
                    type: 'bar',
                    height: 350,
                    toolbar: {
                        show: false
                    },
                    fontFamily: 'Inter, sans-serif'
                },
                plotOptions: {
                    bar: {
                        horizontal: false,
                        columnWidth: '55%',
                        borderRadius: 4,
                    },
                },
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    show: true,
                    width: 2,
                    colors: ['transparent']
                },
                xaxis: {
                    categories: @json($months),
                    axisBorder: {
                        show: false,
                    },
                    axisTicks: {
                        show: false,
                    },
                    labels: {
                        style: {
                            colors: '#64748b',
                            fontSize: '13px',
                            fontWeight: 500,
                        }
                    }
                },
                yaxis: {
                    labels: {
                        formatter: function (value) {
                            if (value >= 1000000) {
                                return 'Rp ' + (value / 1000000).toFixed(0) + ' Jt';
                            }
                            return 'Rp ' + value.toLocaleString('id-ID');
                        },
                        style: {
                            colors: '#64748b',
                            fontSize: '12px',
                            fontWeight: 500,
                        }
                    }
                },
                colors: ['#10b981', '#ef4444'], // Emerald-500 & Red-500
                fill: {
                    opacity: 1
                },
                grid: {
                    borderColor: '#f1f5f9',
                    strokeDashArray: 4,
                    yaxis: {
                        lines: {
                            show: true
                        }
                    }
                },
                legend: {
                    position: 'top',
                    horizontalAlign: 'right',
                    markers: {
                        radius: 12,
                    },
                    fontWeight: 600,
                    itemMargin: {
                        horizontal: 10,
                        vertical: 0
                    }
                },
                tooltip: {
                    y: {
                        formatter: function (val) {
                            return "Rp " + val.toLocaleString('id-ID')
                        }
                    },
                    theme: 'light',
                    style: {
                        fontSize: '13px',
                        fontFamily: 'Inter, sans-serif'
                    }
                }
            };

            var chart = new ApexCharts(document.querySelector("#financeChart"), options);
            chart.render();
        });
    </script>
@endsection