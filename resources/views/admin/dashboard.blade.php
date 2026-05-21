@extends('layouts.app')

@section('content')
<div class="bg-gray-50 min-h-screen p-6 md:p-8">
<div class="max-w-7xl mx-auto">

    {{-- Header --}}
    <div class="mb-8 pb-5 border-b border-gray-200">
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
            <div>
                <p class="text-sm text-gray-500 uppercase tracking-wide mb-1">Admin</p>
                <h1 class="text-2xl md:text-3xl font-bold text-[#2c5e4e]">Admin Dashboard</h1>
                <p class="text-sm text-gray-500 mt-1">Dashboard Sistem Manajemen Absensi Perusahaan Sawit</p>
            </div>
            <span class="inline-block px-4 py-1.5 bg-[#eaf4f1] text-[#2c5e4e] rounded-full text-sm font-medium self-start sm:self-center">
                PT. Sipirok Indah
            </span>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-5 mb-8">
        <div class="bg-white rounded-2xl p-5 border border-gray-200 transition-all hover:border-[#eaf4f1] hover:shadow-sm">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-400 mb-2">Total Pegawai</p>
                    <p class="text-3xl font-bold text-gray-800">{{ number_format($totalPegawai ?? 0) }}</p>
                    <p class="text-xs text-gray-400 mt-1">Seluruh Tim</p>
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
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-400 mb-2">Hadir Hari Ini</p>
                    <p class="text-3xl font-bold text-[#2c5e4e]">{{ number_format($hadirHariIni ?? 0) }}</p>
                    <p class="text-xs text-gray-400 mt-1">Total Kehadiran</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-[#eaf4f1] flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-[#2c5e4e]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-2xl p-5 border border-gray-200 transition-all hover:border-[#eaf4f1] hover:shadow-sm">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-400 mb-2">Produksi Hari Ini</p>
                    <p class="text-3xl font-bold text-[#2c5e4e]">{{ number_format($produksiHariIni ?? 0, 1) }} <span class="text-sm font-medium text-gray-400">kg</span></p>
                    <p class="text-xs text-gray-400 mt-1">Total Panen</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-[#eaf4f1] flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-[#2c5e4e]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
            </div>
        </div>
        <div class="bg-[#2c5e4e] rounded-2xl p-5 transition-all hover:bg-[#1f4a3d] hover:shadow-sm">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-white/70 mb-2">Jumlah Alpha</p>
                    <p class="text-3xl font-bold text-white">{{ number_format($totalAlpha ?? 0) }}</p>
                    <p class="text-xs text-white/60 mt-1">Tidak Hadir / Belum Absen</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-white/15 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    {{-- Field Activity Summary --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-5 mb-8">
        <div class="bg-white rounded-2xl p-5 border border-gray-200 transition-all hover:border-[#eaf4f1] hover:shadow-sm">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-400 mb-2">Patroli Hari Ini</p>
                    <p class="text-3xl font-bold text-[#2c5e4e]">{{ number_format($totalPatroliHariIni ?? 0) }}</p>
                    <p class="text-xs text-gray-400 mt-1">Laporan keamanan</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-[#eaf4f1] flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-[#2c5e4e]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m4 4h-1V9m-6 7h.01M6 20h12a2 2 0 002-2V8a2 2 0 00-2-2H6a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-2xl p-5 border border-gray-200 transition-all hover:border-[#eaf4f1] hover:shadow-sm">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-400 mb-2">Cleaning Hari Ini</p>
                    <p class="text-3xl font-bold text-[#2c5e4e]">{{ number_format($totalCleaningHariIni ?? 0) }}</p>
                    <p class="text-xs text-gray-400 mt-1">Laporan kebersihan</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-[#eaf4f1] flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-[#2c5e4e]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a4 4 0 014-4h10a4 4 0 014 4v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 14h10M7 18h6"></path>
                    </svg>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-2xl p-5 border border-gray-200 transition-all hover:border-[#eaf4f1] hover:shadow-sm">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-400 mb-2">Pelapor Hari Ini</p>
                    <p class="text-3xl font-bold text-[#2c5e4e]">{{ number_format($reportingUsers ?? 0) }}</p>
                    <p class="text-xs text-gray-400 mt-1">Orang</p>
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
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-400 mb-2">Area Terlapor</p>
                    <p class="text-3xl font-bold text-[#2c5e4e]">{{ number_format($totalAreas ?? 0) }}</p>
                    <p class="text-xs text-gray-400 mt-1">Area</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-[#eaf4f1] flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-[#2c5e4e]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10l1.553 2.33a2 2 0 001.606.96H9m6 0h3.841a2 2 0 001.605-.96L21 10M3 10V6a3 3 0 013-3h12a3 3 0 013 3v4M3 10v8a3 3 0 003 3h12a3 3 0 003-3v-8"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    {{-- Charts Section --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mb-6">

        {{-- Donut Chart --}}
        <div class="bg-white rounded-2xl p-5 md:p-6 border border-gray-200 shadow-sm">
            <h3 class="text-sm font-semibold text-gray-700 mb-1">Status Absensi Hari Ini</h3>
            <p class="text-xs text-gray-400 mb-4">Distribusi kehadiran tim</p>
            <div class="h-[200px] relative">
                <canvas id="attendanceChart"></canvas>
            </div>
            <div class="mt-4 space-y-2.5">
                <div class="flex justify-between items-center text-xs">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-[#2c5e4e]"></span>
                        <span class="text-gray-600">Hadir</span>
                    </div>
                    <span class="font-semibold text-gray-800">{{ $hadirHariIni ?? 0 }}</span>
                </div>
                <div class="flex justify-between items-center text-xs">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-[#d4a373]"></span>
                        <span class="text-gray-600">Terlambat</span>
                    </div>
                    <span class="font-semibold text-gray-800">{{ $totalTerlambat ?? 0 }}</span>
                </div>
                <div class="flex justify-between items-center text-xs">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-red-500"></span>
                        <span class="text-gray-600">Alpha</span>
                    </div>
                    <span class="font-semibold text-gray-800">{{ $totalAlpha ?? 0 }}</span>
                </div>
            </div>
            <p class="text-xs text-gray-400 mt-3 text-center">*Alpha = Pegawai yang belum melakukan absensi hari ini</p>
        </div>

        {{-- Ringkasan Hari Ini --}}
        <div class="bg-white rounded-2xl p-5 md:p-6 border border-gray-200 shadow-sm">
            <h3 class="text-sm font-semibold text-gray-700 mb-5">Ringkasan Hari Ini</h3>
            <div class="space-y-3">
                <div class="flex items-center justify-between p-4 rounded-xl border border-gray-100 hover:bg-gray-50 transition">
                    <span class="text-sm font-medium text-gray-600">Kehadiran</span>
                    <span class="text-xs font-semibold px-3 py-1 rounded-full bg-[#eaf4f1] text-[#2c5e4e]">
                        @php
                            $totalHadir = ($hadirHariIni ?? 0) + ($totalTerlambat ?? 0);
                            $totalPegawaiFix = $totalPegawai ?? 1;
                            $rateKehadiran = $totalPegawaiFix > 0 ? round(($totalHadir / $totalPegawaiFix) * 100) : 0;
                        @endphp
                        {{ $rateKehadiran }}%
                    </span>
                </div>
                <div class="flex items-center justify-between p-4 rounded-xl border border-gray-100 hover:bg-gray-50 transition">
                    <span class="text-sm font-medium text-gray-600">Produktivitas</span>
                    <span class="text-xs font-semibold px-3 py-1 rounded-full bg-[#eaf4f1] text-[#2c5e4e]">
                        @if(($produksiHariIni ?? 0) > 100) Tinggi
                        @elseif(($produksiHariIni ?? 0) > 50) Sedang
                        @else Rendah
                        @endif
                    </span>
                </div>
                <div class="flex items-center justify-between p-4 rounded-xl border border-gray-100 hover:bg-gray-50 transition">
                    <span class="text-sm font-medium text-gray-600">Alpha (Belum Absen)</span>
                    <span class="text-xs font-semibold px-3 py-1 rounded-full bg-red-100 text-red-700">
                        {{ $totalAlpha ?? 0 }} Orang
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- Perbandingan Antara Patroli dan Cleaning --}}
    <div class="bg-white rounded-2xl p-5 md:p-6 border border-gray-200 shadow-sm mb-5">
        <div class="flex flex-wrap justify-between items-start gap-3 mb-4">
            <div>
                <h3 class="text-sm font-semibold text-gray-700">Perbandingan Antara Patroli dan Cleaning</h3>
                <p class="text-xs text-gray-400">Ringkasan jumlah laporan hari ini</p>
            </div>
            <div class="text-xs text-gray-500">Patroli: {{ $totalPatroliHariIni ?? 0 }} • Cleaning: {{ $totalCleaningHariIni ?? 0 }}</div>
        </div>
        <div class="h-[220px]">
            <canvas id="patrolCleaningChart"></canvas>
        </div>
    </div>

    {{-- Bottom Panels --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

        {{-- Aktivitas Terbaru --}}
        <div class="bg-white rounded-2xl p-5 md:p-6 border border-gray-200 shadow-sm">
            <h3 class="text-sm font-semibold text-gray-700 mb-1">Aktivitas Terbaru</h3>
            <p class="text-xs text-gray-400 mb-4">Check in tim hari ini</p>
            <div class="space-y-3">
                @forelse($recentActivities as $activity)
                <div class="flex items-center gap-3 p-3 rounded-xl border border-gray-100 hover:bg-gray-50 transition">
                    <div class="w-10 h-10 bg-[#eaf4f1] rounded-xl flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-[#2c5e4e]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h5 class="font-semibold text-gray-800 text-sm truncate">{{ $activity->user->name }}</h5>
                        <p class="text-xs text-gray-400 mt-0.5">{{ ucfirst($activity->user->role) }} — Check In: {{ $activity->check_in ? \Carbon\Carbon::parse($activity->check_in)->format('H:i') : '-' }} WIB</p>
                    </div>
                    <div class="text-xs text-gray-400 flex-shrink-0">
                        @if($activity->check_in)
                            {{ \Carbon\Carbon::parse($activity->check_in)->diffForHumans() }}
                        @endif
                    </div>
                </div>
                @empty
                <div class="py-8 text-center text-gray-400">
                    <svg class="w-10 h-10 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                    </svg>
                    <p class="font-semibold text-sm">Belum ada aktivitas hari ini</p>
                </div>
                @endforelse
            </div>
        </div>

        {{-- Overview Departemen --}}
        <div class="bg-white rounded-2xl p-5 md:p-6 border border-gray-200 shadow-sm">
            <h3 class="text-sm font-semibold text-gray-700 mb-1">Overview Departemen</h3>
            <p class="text-xs text-gray-400 mb-5">Kehadiran per departemen hari ini</p>
            @forelse($departments as $role => $dept)
            <div class="mb-4">
                <div class="flex justify-between text-xs mb-1.5">
                    <span class="font-medium text-gray-600">{{ $dept['name'] }}</span>
                    <span class="font-semibold text-[#2c5e4e]">{{ $dept['hadir'] }}/{{ $dept['total'] }}</span>
                </div>
                <div class="w-full bg-gray-100 rounded-full h-2 overflow-hidden">
                    <div class="bg-[#2c5e4e] h-full rounded-full transition-all" style="width: {{ $dept['percentage'] }}%"></div>
                </div>
            </div>
            @empty
            <div class="py-8 text-center text-gray-400">
                <svg class="w-10 h-10 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
                <p class="font-semibold text-sm">Belum ada data departemen</p>
            </div>
            @endforelse
        </div>
    </div>

</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const attendanceCtx = document.getElementById('attendanceChart').getContext('2d');
    new Chart(attendanceCtx, {
        type: 'doughnut',
        data: {
            labels: ['Hadir', 'Terlambat', 'Alpha'],
            datasets: [{
                data: [{{ $hadirHariIni ?? 0 }}, {{ $totalTerlambat ?? 0 }}, {{ $totalAlpha ?? 0 }}],
                backgroundColor: ['#2c5e4e', '#d4a373', '#ef4444'],
                borderWidth: 0,
                cutout: '70%'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#1a2e25',
                    padding: 10,
                    titleColor: '#fff',
                    bodyColor: '#a7c4bb',
                    callbacks: {
                        label: (ctx) => {
                            let label = ctx.label;
                            if (ctx.label === 'Alpha') {
                                return `${label}: ${ctx.parsed} (Belum Absen)`;
                            }
                            return `${label}: ${ctx.parsed}`;
                        }
                    }
                }
            }
        }
    });

    const patrolCleaningCtx = document.getElementById('patrolCleaningChart').getContext('2d');
    new Chart(patrolCleaningCtx, {
        type: 'bar',
        data: {
            labels: ['Patroli', 'Cleaning'],
            datasets: [{
                label: 'Jumlah Laporan',
                data: [{{ $totalPatroliHariIni ?? 0 }}, {{ $totalCleaningHariIni ?? 0 }}],
                backgroundColor: ['#2563eb', '#10b981'],
                borderRadius: 12,
                borderSkipped: false,
                maxBarThickness: 40
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { color: '#475569', font: { size: 12 } }
                },
                y: {
                    beginAtZero: true,
                    ticks: {
                        color: '#475569',
                        precision: 0,
                        stepSize: 1
                    },
                    grid: {
                        color: '#f1f5f9',
                        drawBorder: false
                    }
                }
            },
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#111827',
                    padding: 10,
                    titleColor: '#fff',
                    bodyColor: '#f8fafc',
                    callbacks: {
                        label: (ctx) => `${ctx.dataset.label}: ${ctx.parsed.y}`
                    }
                }
            }
        }
    });
});
</script>
@endsection
