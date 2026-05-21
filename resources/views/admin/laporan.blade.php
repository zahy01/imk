@extends('layouts.app')

@section('content')
<div class="bg-gray-50 min-h-screen p-6 md:p-8">
<div class="max-w-7xl mx-auto">

    {{-- Header --}}
    <div class="mb-8 pb-5 border-b border-gray-200">
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
            <div>
                <p class="text-sm text-gray-500 uppercase tracking-wide mb-1">Admin</p>
                <h1 class="text-2xl md:text-3xl font-bold text-[#2c5e4e]">Laporan Hasil Panen Sawit</h1>
                <p class="text-sm text-gray-500 mt-1">Dashboard analisis produktivitas dan kehadiran pekerja sawit</p>
            </div>
            <span class="inline-block px-4 py-1.5 bg-[#eaf4f1] text-[#2c5e4e] rounded-full text-sm font-medium self-start sm:self-center">
                PT. Sipirok Indah
            </span>
        </div>
    </div>

    {{-- Filter Box --}}
    <form method="GET" action="{{ route('admin.laporan') }}" class="bg-white rounded-2xl p-5 md:p-6 mb-6 border border-gray-200 shadow-sm">
        <h3 class="text-xs font-semibold uppercase tracking-wide text-gray-400 mb-4">Filter Laporan</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1.5">Dari Tanggal</label>
                <input type="date" name="start_date" value="{{ request('start_date', $startDate) }}"
                    class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-[#2c5e4e] focus:ring-1 focus:ring-[#2c5e4e]">
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1.5">Sampai Tanggal</label>
                <input type="date" name="end_date" value="{{ request('end_date', $endDate) }}"
                    class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-[#2c5e4e] focus:ring-1 focus:ring-[#2c5e4e]">
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1.5">Role</label>
                <select name="role" class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-[#2c5e4e] focus:ring-1 focus:ring-[#2c5e4e]">
                    <option value="">Semua Role</option>
                    <option value="user" {{ request('role')=='user' ? 'selected':'' }}>Kebun & Panen</option>
                    <option value="security" {{ request('role')=='security' ? 'selected':'' }}>Security</option>
                    <option value="cleaning" {{ request('role')=='cleaning' ? 'selected':'' }}>Cleaning</option>
                    <option value="kantoran" {{ request('role')=='kantoran' ? 'selected':'' }}>Kantoran</option>
                </select>
            </div>
        </div>
        <div class="mt-4">
            <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1.5">Tampilkan Data</label>
            <select name="data_type" class="w-full md:w-64 border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-[#2c5e4e] focus:ring-1 focus:ring-[#2c5e4e]">
                <option value="today" {{ request('data_type', 'today') == 'today' ? 'selected' : '' }}>Hari Ini Saja</option>
                <option value="all" {{ request('data_type') == 'all' ? 'selected' : '' }}>Semua Data (Berdasarkan Filter Tanggal)</option>
            </select>
        </div>
        <div class="flex gap-3 mt-6">
            <button type="submit"
                class="bg-[#2c5e4e] hover:bg-[#1f4a3d] text-white px-5 py-2.5 rounded-xl font-semibold text-sm transition-all hover:-translate-y-0.5 shadow-md">
                Terapkan Filter
            </button>
            <a href="{{ route('admin.laporan') }}"
                class="bg-white text-gray-500 px-5 py-2.5 rounded-xl font-medium text-sm border border-gray-200 hover:border-gray-300 hover:text-gray-700 transition">
                Reset
            </a>
        </div>
    </form>

    @php
        $selectedRole = request('role');
        $hasPalmAccess = !$selectedRole || $selectedRole == 'user';
        $dataType = request('data_type', 'today');
        $todayDate = \Carbon\Carbon::now()->translatedFormat('l, d F Y');
    @endphp

    {{-- Today Banner --}}
    @if($dataType == 'today' && $hasPalmAccess)
    <div class="bg-[#2c5e4e] rounded-2xl p-6 mb-6 text-white flex flex-wrap items-center justify-between gap-4">
        <div>
            <div class="text-xs font-semibold uppercase tracking-wide text-white/60 mb-1">Ringkasan Hari Ini</div>
            <div class="text-base md:text-lg font-bold text-white">{{ $todayDate }}</div>
        </div>
        <div class="flex flex-wrap gap-8">
            <div class="text-center">
                <div class="text-xs font-semibold uppercase text-white/60 mb-1">Hadir</div>
                <div class="text-2xl md:text-3xl font-bold text-white">{{ $todayAttendanceCount ?? 0 }}</div>
            </div>
            <div class="text-center">
                <div class="text-xs font-semibold uppercase text-white/60 mb-1">Panen</div>
                <div class="text-2xl md:text-3xl font-bold text-white">{{ number_format($todayPalmWeight ?? 0, 1) }} <span class="text-sm font-medium text-white/60">kg</span></div>
            </div>
            <div class="text-center">
                <div class="text-xs font-semibold uppercase text-white/60 mb-1">Rata-rata</div>
                @php
                    $avgToday = ($todayAttendanceCount > 0 && $todayPalmWeight > 0)
                        ? number_format($todayPalmWeight / $todayAttendanceCount, 1)
                        : 0;
                @endphp
                <div class="text-2xl md:text-3xl font-bold text-white">{{ $avgToday }} <span class="text-sm font-medium text-white/60">kg/org</span></div>
            </div>
        </div>
    </div>
    @endif

    {{-- Summary Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-5 mb-8">
        <div class="bg-white rounded-2xl p-5 border border-gray-200 transition-all hover:border-[#eaf4f1] hover:shadow-sm">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-400 mb-2">Total Pekerja</p>
                    <p class="text-3xl font-bold text-gray-800">{{ number_format($totalPegawai) }}</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-[#eaf4f1] flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-[#2c5e4e]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-2xl p-5 border border-gray-200 transition-all hover:border-[#eaf4f1] hover:shadow-sm">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-400 mb-2">Total Berat Sawit</p>
                    <p class="text-3xl font-bold text-[#2c5e4e]">{{ $hasPalmAccess ? number_format($totalPalmWeight, 1) : '-' }}</p>
                    @if($hasPalmAccess)<p class="text-xs text-gray-400 mt-1">kilogram</p>@endif
                </div>
                <div class="w-10 h-10 rounded-xl bg-[#eaf4f1] flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-[#2c5e4e]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"></path>
                    </svg>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-2xl p-5 border border-gray-200 transition-all hover:border-[#eaf4f1] hover:shadow-sm">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-400 mb-2">Rata-rata Panen</p>
                    <p class="text-3xl font-bold text-gray-800">{{ $hasPalmAccess ? number_format($averagePalmWeight, 1) : '-' }}</p>
                    @if($hasPalmAccess)<p class="text-xs text-gray-400 mt-1">kg / pekerja</p>@endif
                </div>
                <div class="w-10 h-10 rounded-xl bg-[#eaf4f1] flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-[#2c5e4e]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
            </div>
        </div>
        @if($dataType == 'today')
        <div class="bg-[#2c5e4e] rounded-2xl p-5 transition-all hover:bg-[#1f4a3d] hover:shadow-sm">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-white/70 mb-2">Total Kehadiran</p>
                    <p class="text-3xl font-bold text-white">{{ number_format($totalHadir ?? 0) }}</p>
                    <p class="text-xs text-white/60 mt-1">hari ini</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-white/15 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>
        @else
        <div class="bg-white rounded-2xl p-5 border border-gray-200 opacity-0 pointer-events-none"></div>
        @endif
    </div>

    {{-- Charts --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mb-8">
        @if($hasPalmAccess)
        <div class="bg-white rounded-2xl p-5 md:p-6 border border-gray-200 shadow-sm">
            <h3 class="text-sm font-semibold text-gray-700 mb-1">Hasil Panen 7 Hari Terakhir</h3>
            <p class="text-xs text-gray-400 mb-5">Dalam satuan kilogram (kg)</p>
            @if($dailyPalmWeight->count())
                @php $maxWeight = $dailyPalmWeight->max('total_weight') ?: 1; @endphp
                <div class="space-y-4">
                    @foreach($dailyPalmWeight as $daily)
                    <div>
                        <div class="flex justify-between text-xs text-gray-500 mb-1.5">
                            <span>{{ \Carbon\Carbon::parse($daily->date)->format('d M Y') }}</span>
                            <span class="font-semibold text-gray-700">{{ number_format($daily->total_weight, 1) }} kg</span>
                        </div>
                        <div class="w-full bg-gray-100 rounded-full h-2 overflow-hidden">
                            <div class="bg-[#2c5e4e] h-full rounded-full" style="width: {{ ($daily->total_weight / $maxWeight) * 100 }}%"></div>
                        </div>
                    </div>
                    @endforeach
                </div>
            @else
                <div class="h-36 flex items-center justify-center text-gray-400 text-sm">Tidak ada data panen</div>
            @endif
        </div>
        @endif

        <div class="bg-white rounded-2xl p-5 md:p-6 border border-gray-200 shadow-sm">
            <h3 class="text-sm font-semibold text-gray-700 mb-1">Kehadiran 7 Hari Terakhir</h3>
            <p class="text-xs text-gray-400 mb-5">Jumlah pekerja hadir per hari</p>
            @if($dailyAttendance->count())
                <div class="space-y-4">
                    @foreach($dailyAttendance as $daily)
                    <div>
                        <div class="flex justify-between text-xs text-gray-500 mb-1.5">
                            <span>{{ \Carbon\Carbon::parse($daily->date)->format('d M Y') }}</span>
                            <span class="font-semibold text-gray-700">{{ number_format($daily->total) }} pekerja</span>
                        </div>
                        <div class="w-full bg-gray-100 rounded-full h-2 overflow-hidden">
                            <div class="bg-[#2c5e4e] h-full rounded-full" style="width: {{ $totalPegawai > 0 ? ($daily->total / $totalPegawai) * 100 : 0 }}%"></div>
                        </div>
                    </div>
                    @endforeach
                </div>
            @else
                <div class="h-36 flex items-center justify-center text-gray-400 text-sm">Tidak ada data kehadiran</div>
            @endif
        </div>
    </div>

    {{-- Data Table --}}
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="flex flex-wrap items-center justify-between gap-3 px-6 py-5 border-b border-gray-100">
            <div>
                <h3 class="text-sm font-semibold text-gray-800">
                    @if($dataType == 'today')
                        Detail Kehadiran & Panen Hari Ini
                    @else
                        Detail Kehadiran & Panen
                    @endif
                </h3>
            </div>
            @if($dataType == 'today')
                <span class="bg-[#eaf4f1] text-[#2c5e4e] px-3 py-1 rounded-full text-xs font-medium">{{ $todayDate }}</span>
            @else
                <span class="bg-[#eaf4f1] text-[#2c5e4e] px-3 py-1 rounded-full text-xs font-medium">
                    Periode: {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} – {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}
                </span>
            @endif
        </div>

        @if($detailedAttendances->count())
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        @if($dataType == 'all')
                        <th class="text-left px-4 py-3 text-xs font-semibold uppercase tracking-wide text-gray-500">Tanggal</th>
                        @endif
                        <th class="text-left px-4 py-3 text-xs font-semibold uppercase tracking-wide text-gray-500">Nama Karyawan</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold uppercase tracking-wide text-gray-500">Role</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold uppercase tracking-wide text-gray-500">Absen Masuk</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold uppercase tracking-wide text-gray-500">Absen Keluar</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold uppercase tracking-wide text-gray-500">Berat Panen</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold uppercase tracking-wide text-gray-500">Catatan</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold uppercase tracking-wide text-gray-500">Status</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold uppercase tracking-wide text-gray-500">Bukti</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($detailedAttendances as $a)
                        @php
                            $panen = \App\Models\CatatanPanen::where('id_pegawai', $a->user_id)
                                ->whereDate('tanggal', $a->date)
                                ->first();
                        @endphp
                        <tr class="border-b border-gray-100 hover:bg-gray-50 transition">
                            @if($dataType == 'all')
                            <td class="px-4 py-3 text-sm text-gray-600">{{ \Carbon\Carbon::parse($a->date)->format('d M Y') }}</td>
                            @endif
                            <td class="px-4 py-3 text-sm font-semibold text-gray-800">{{ $a->user?->name ?? 'Nama Tidak Diketahui' }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-[#eaf4f1] text-[#2c5e4e]">
                                    {{ $a->user?->role ?? 'unknown' }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                @if($a->check_in)
                                    <span class="text-sm font-medium text-gray-800">{{ \Carbon\Carbon::parse($a->check_in)->format('H:i') }} <span class="text-xs text-gray-400">WIB</span></span>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @if($a->check_out)
                                    <span class="text-sm font-medium text-gray-800">{{ \Carbon\Carbon::parse($a->check_out)->format('H:i') }} <span class="text-xs text-gray-400">WIB</span></span>
                                @elseif($a->check_in)
                                    <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-700">Belum Checkout</span>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @if($hasPalmAccess && $panen && $panen->berat_kg)
                                    <span class="font-semibold text-[#2c5e4e] text-sm">{{ number_format($panen->berat_kg, 1) }} kg</span>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-500">{{ $a->note ?? '-' }}</td>
                            <td class="px-4 py-3">
                                @if($a->status == 'tepat waktu')
                                    <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-[#eaf4f1] text-[#2c5e4e]">Hadir</span>
                                @elseif($a->status == 'terlambat')
                                    <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-700">Terlambat</span>
                                @elseif($a->status == 'cuti')
                                    <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-purple-100 text-purple-700">Cuti</span>
                                @else
                                    <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-700">Alpha</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @if($a->photo_path)
                                    <a href="{{ asset('storage/'.$a->photo_path) }}" target="_blank"
                                        class="inline-flex px-3 py-1.5 rounded-full text-xs font-semibold bg-[#eaf4f1] text-[#2c5e4e] hover:bg-[#d5ecdf] transition">Lihat</a>
                                @elseif($a->checkout_photo_path)
                                    <a href="{{ asset('storage/'.$a->checkout_photo_path) }}" target="_blank"
                                        class="inline-flex px-3 py-1.5 rounded-full text-xs font-semibold bg-[#eaf4f1] text-[#2c5e4e] hover:bg-[#d5ecdf] transition">Lihat</a>
                                @elseif($panen && $panen->foto_panen)
                                    <a href="{{ asset('storage/'.$panen->foto_panen) }}" target="_blank"
                                        class="inline-flex px-3 py-1.5 rounded-full text-xs font-semibold bg-[#eaf4f1] text-[#2c5e4e] hover:bg-[#d5ecdf] transition">Lihat</a>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-gray-100">
            {{ $detailedAttendances->links() }}
        </div>
        @else
        <div class="py-14 text-center text-gray-400">
            <svg class="w-10 h-10 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
            </svg>
            @if($dataType == 'today')
                <p class="font-semibold text-sm text-gray-500">Belum ada absensi hari ini</p>
                <span class="text-xs">Data kehadiran akan muncul setelah pekerja melakukan check-in</span>
            @else
                <p class="font-semibold text-sm text-gray-500">Tidak ada data untuk periode ini</p>
                <span class="text-xs">Coba ubah filter tanggal atau role untuk melihat data</span>
            @endif
        </div>
        @endif
    </div>

</div>
</div>
@endsection
