@extends('layouts.admin')

@section('title', 'Master Kelompok Pembayaran - SanthiGraha')
@section('page_title', 'Kelompok Proyek / Pembayaran')

@section('content')

    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5 mb-6">
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100 flex items-center gap-4 hover:shadow-md transition-shadow">
            <div class="w-12 h-12 rounded-full bg-blue-50 flex items-center justify-center text-blue-500 shrink-0">
                <i class="ph ph-stack text-2xl"></i>
            </div>
            <div>
                <p class="text-xs font-medium text-slate-500 mb-0.5">Total Kelompok</p>
                <h3 class="text-xl font-bold text-slate-800">{{ $groups->total() }}</h3>
            </div>
        </div>
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100 flex items-center gap-4 hover:shadow-md transition-shadow">
            <div class="w-12 h-12 rounded-full bg-amber-50 flex items-center justify-center text-amber-500 shrink-0">
                <i class="ph ph-hourglass-high text-2xl"></i>
            </div>
            <div>
                <p class="text-xs font-medium text-slate-500 mb-0.5">Sedang Proses</p>
                <h3 class="text-xl font-bold text-amber-600">
                    {{ \App\Models\PaymentGroup::whereIn('payment_status', ['uang_muka', 'proses'])->count() }}
                </h3>
            </div>
        </div>
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100 flex items-center gap-4 hover:shadow-md transition-shadow">
            <div class="w-12 h-12 rounded-full bg-emerald-50 flex items-center justify-center text-emerald-500 shrink-0">
                <i class="ph ph-check-circle text-2xl"></i>
            </div>
            <div>
                <p class="text-xs font-medium text-slate-500 mb-0.5">Pembayaran Selesai</p>
                <h3 class="text-xl font-bold text-emerald-600">
                    {{ \App\Models\PaymentGroup::where('payment_status', 'selesai')->count() }}
                </h3>
            </div>
        </div>
    </div>

    {{-- Filter Section --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 mb-6 p-5">
        <form action="{{ route('payment-groups.index') }}" method="GET">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 mb-3">
                {{-- Search --}}
                <div class="relative">
                    <i class="ph ph-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-lg pointer-events-none"></i>
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Cari proyek, kategori..."
                        class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-emerald-400 outline-none">
                </div>

                {{-- Tipe --}}
                <div>
                    <select name="type" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-emerald-400 outline-none cursor-pointer">
                        <option value="">Semua Tipe</option>
                        <option value="pengeluaran" @selected(request('type') === 'pengeluaran')>Pengeluaran</option>
                        <option value="pemasukan"   @selected(request('type') === 'pemasukan')>Pemasukan</option>
                    </select>
                </div>

                {{-- Proyek --}}
                <div>
                    <select name="project_id" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-emerald-400 outline-none cursor-pointer">
                        <option value="">Semua Proyek</option>
                        @foreach($projects as $project)
                            <option value="{{ $project->id }}" @selected(request('project_id') == $project->id)>
                                {{ $project->project_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Kategori --}}
                <div>
                    <select name="category_id" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-emerald-400 outline-none cursor-pointer">
                        <option value="">Semua Kategori</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" @selected(request('category_id') == $category->id)>
                                {{ $category->category_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Status --}}
                <div>
                    <select name="payment_status" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-emerald-400 outline-none cursor-pointer">
                        <option value="">Semua Status</option>
                        <option value="uang_muka" @selected(request('payment_status') === 'uang_muka')>Uang Muka</option>
                        <option value="proses"    @selected(request('payment_status') === 'proses')>Proses</option>
                        <option value="selesai"   @selected(request('payment_status') === 'selesai')>Selesai</option>
                    </select>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <button type="submit" class="px-5 py-2.5 bg-brand-500 hover:bg-brand-600 text-white text-sm font-semibold rounded-xl shadow-md shadow-brand-500/20 transition-all flex items-center gap-2">
                    <i class="ph ph-funnel"></i> Terapkan Filter
                </button>
                @if(request()->anyFilled(['search', 'type', 'project_id', 'category_id', 'payment_status']))
                    <a href="{{ route('payment-groups.index') }}" class="px-5 py-2.5 bg-red-50 hover:bg-red-100 text-red-600 text-sm font-semibold rounded-xl transition-colors flex items-center gap-2">
                        <i class="ph ph-x"></i> Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Cards Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6 mb-6">
        @forelse($groups as $group)
            @php
                $statusConfig = [
                    'uang_muka' => [
                        'color' => 'bg-blue-50 text-blue-700 border-blue-200',
                        'badge' => 'bg-blue-500',
                        'label' => 'Uang Muka',
                        'icon'  => 'ph-clock-countdown',
                    ],
                    'proses' => [
                        'color' => 'bg-amber-50 text-amber-700 border-amber-200',
                        'badge' => 'bg-amber-500',
                        'label' => 'Proses',
                        'icon'  => 'ph-spinner-gap',
                    ],
                    'selesai' => [
                        'color' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                        'badge' => 'bg-emerald-500',
                        'label' => 'Selesai',
                        'icon'  => 'ph-check-circle',
                    ],
                ];
                $sc = $statusConfig[$group->payment_status] ?? [
                    'color' => 'bg-slate-50 text-slate-700 border-slate-200',
                    'badge' => 'bg-slate-500',
                    'label' => $group->payment_status,
                    'icon'  => 'ph-question',
                ];

                $latestTrx = $group->transactions->first();
                $receiptCount = $group->approved_count ?? $group->transactions->count();
            @endphp

            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 hover:shadow-md hover:border-slate-200 transition-all duration-200 flex flex-col justify-between overflow-hidden group">
                {{-- Card Header --}}
                <div class="p-5 border-b border-slate-100">
                    <div class="flex items-start justify-between gap-3 mb-3">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl {{ $group->type === 'pemasukan' ? 'bg-emerald-50 text-emerald-600' : 'bg-indigo-50 text-brand-500' }} flex items-center justify-center font-bold shrink-0">
                                <i class="ph {{ $group->type === 'pemasukan' ? 'ph-trend-up' : 'ph-folder' }} text-xl"></i>
                            </div>
                            <div class="min-w-0">
                                <h3 class="font-bold text-slate-800 text-base truncate" title="{{ $group->project->project_name ?? '-' }}">
                                    {{ $group->project->project_name ?? '-' }}
                                </h3>
                                @if($group->project && $group->project->location)
                                    <p class="text-xs text-slate-400 flex items-center gap-1 mt-0.5 truncate">
                                        <i class="ph ph-map-pin"></i> {{ $group->project->location }}
                                    </p>
                                @endif
                            </div>
                        </div>

                        {{-- Status Badge --}}
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold border {{ $sc['color'] }} shrink-0">
                            <span class="w-1.5 h-1.5 rounded-full {{ $sc['badge'] }}"></span>
                            {{ $sc['label'] }}
                        </span>
                    </div>

                    {{-- Category, Type & Label --}}
                    <div class="flex flex-wrap items-center gap-2 mt-2">
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-bold {{ $group->type === 'pemasukan' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-red-50 text-red-700 border border-red-200' }}">
                            <i class="ph {{ $group->type === 'pemasukan' ? 'ph-trend-up' : 'ph-trend-down' }}"></i>
                            {{ ucfirst($group->type ?? 'pengeluaran') }}
                        </span>
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-slate-100 text-slate-700 text-xs font-semibold">
                            <i class="ph ph-tag text-slate-400"></i>
                            {{ $group->category->category_name ?? '-' }}
                        </span>
                        @if($group->label)
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-amber-50 text-amber-700 text-xs font-medium border border-amber-100 italic" title="Label Kelompok">
                                <i class="ph ph-bookmark-simple"></i>
                                {{ $group->label }}
                            </span>
                        @endif
                    </div>
                </div>

                {{-- Card Body / Metrics --}}
                <div class="p-5 bg-slate-50/50 flex-1 flex flex-col justify-center">
                    <div class="flex items-center justify-between gap-2 mb-2">
                        <span class="text-xs font-medium text-slate-500 uppercase tracking-wider">Total Terbayar</span>
                        <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-600 border border-emerald-100">
                            {{ $receiptCount }} Nota / Transaksi
                        </span>
                    </div>
                    <div class="text-xl sm:text-2xl font-bold text-slate-800">
                        Rp {{ number_format($group->total_approved, 2, ',', '.') }}
                    </div>

                    @if($latestTrx)
                        <p class="text-xs text-slate-400 mt-2 flex items-center gap-1">
                            <i class="ph ph-calendar-blank"></i> Nota terakhir: {{ \Carbon\Carbon::parse($latestTrx->transaction_date)->format('d M Y') }}
                        </p>
                    @endif
                </div>

                {{-- Card Footer / Action --}}
                <div class="p-4 bg-white border-t border-slate-100 flex items-center justify-between gap-2">
                    <span class="text-xs text-slate-400 font-medium">ID #{{ $group->id }}</span>
                    <div class="flex items-center gap-1.5">
                        <form action="{{ route('payment-groups.destroy', $group->id) }}" method="POST" class="inline"
                              onsubmit="return confirm('Apakah Anda yakin ingin menghapus kelompok pembayaran ini beserta seluruh transaksinya?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="p-2 rounded-xl bg-red-50 text-red-600 hover:bg-red-100 text-xs font-semibold transition-colors border border-red-200" title="Hapus Kelompok">
                                <i class="ph ph-trash"></i>
                            </button>
                        </form>
                        <a href="{{ route('payment-groups.show', $group->id) }}"
                            class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-brand-500 hover:bg-brand-600 text-white text-xs font-semibold shadow-sm hover:shadow transition-all">
                            <span>Lihat Rincian</span>
                            <i class="ph ph-arrow-right font-bold"></i>
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full bg-white rounded-2xl p-12 text-center border border-slate-100 shadow-sm">
                <div class="w-16 h-16 rounded-full bg-slate-50 flex items-center justify-center mx-auto mb-4 text-slate-300">
                    <i class="ph ph-stack text-3xl"></i>
                </div>
                <h3 class="text-base font-bold text-slate-800">Belum Ada Kelompok Pembayaran</h3>
                <p class="text-sm text-slate-500 mt-1 max-w-md mx-auto">
                    Kelompok pembayaran akan otomatis terbentuk saat transaksi pengeluaran diajukan dengan memilih status pembayaran (Uang Muka / Proses / Selesai).
                </p>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if($groups->hasPages())
        <div class="bg-white rounded-2xl p-4 shadow-sm border border-slate-100">
            {{ $groups->links() }}
        </div>
    @endif

@endsection
