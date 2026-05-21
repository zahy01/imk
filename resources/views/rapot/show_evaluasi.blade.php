@extends('layouts.app')

@section('content')
<div class="bg-[#f8f6f2] min-h-screen font-['Inter',sans-serif] p-6 md:p-8">
    <div class="max-w-7xl mx-auto">
        {{-- Header --}}
        <div class="relative pl-4 mb-8">
            <div class="absolute left-0 top-0 bottom-0 w-1 bg-[#2d6a4f] rounded-full"></div>
            <div class="flex flex-wrap justify-between items-start gap-4">
                <div>
                    <h1 class="text-2xl md:text-3xl font-bold text-[#1e1e1e] tracking-tight">Detail Evaluasi Kinerja</h1>
                    <p class="text-sm text-stone-500 mt-1">
                        <span class="font-semibold text-[#2d6a4f]">{{ $rapot->user->name ?? 'N/A' }}</span> |
                        Periode: <span class="font-semibold">{{ $rapot->periode }}</span> |
                        Status:
                        <span class="font-semibold
                            {{ $rapot->status == 'Sangat Baik' ? 'text-emerald-600' :
                               ($rapot->status == 'Baik' ? 'text-blue-600' :
                               ($rapot->status == 'Perlu Perbaikan' ? 'text-amber-600' : 'text-stone-500')) }}">
                            {{ $rapot->status }}
                        </span>
                    </p>
                </div>
                <div>
                    @if(auth()->user()->role == 'admin')
                    <a href="{{ route('admin.rapot.index') }}"
                       class="inline-flex items-center gap-2 px-4 py-2 border border-stone-200 rounded-xl text-stone-600 text-sm font-semibold hover:bg-white hover:border-stone-300 transition">
                        <i class="fas fa-arrow-left text-xs"></i> Kembali ke Daftar
                    </a>
                    @else
                    <a href="{{ route('rapot.saya') }}"
                       class="inline-flex items-center gap-2 px-4 py-2 border border-stone-200 rounded-xl text-stone-600 text-sm font-semibold hover:bg-white hover:border-stone-300 transition">
                        <i class="fas fa-arrow-left text-xs"></i> Kembali
                    </a>
                    @endif
                </div>
            </div>
        </div>

        {{-- Informasi Utama - Grid 2 Kolom --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-8">
            {{-- Informasi Pegawai --}}
            <div class="bg-white rounded-2xl border border-stone-200 shadow-sm p-6">
                <h3 class="text-sm font-bold text-stone-700 mb-4 flex items-center gap-2">
                    <i class="fas fa-user-circle text-[#2d6a4f]"></i> Informasi Pegawai
                </h3>
                <div class="flex items-center gap-4 mb-5">
                    <div class="w-14 h-14 bg-emerald-100 rounded-2xl flex items-center justify-center">
                        <span class="text-2xl font-bold text-[#2d6a4f]">
                            {{ strtoupper(substr($rapot->user->name ?? 'N', 0, 1)) }}
                        </span>
                    </div>
                    <div>
                        <h4 class="font-bold text-stone-800 text-lg">{{ $rapot->user->name ?? 'N/A' }}</h4>
                        <span class="inline-block px-2.5 py-1 bg-emerald-50 text-[#2d6a4f] text-xs font-semibold rounded-full mt-1">
                            {{ ucfirst($rapot->user->role ?? 'user') }}
                        </span>
                    </div>
                </div>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between py-1 border-b border-stone-100">
                        <span class="text-stone-500">ID Pegawai</span>
                        <span class="font-semibold text-stone-700">{{ $rapot->user->id ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-stone-100">
                        <span class="text-stone-500">Evaluator</span>
                        <span class="font-semibold text-stone-700">{{ $rapot->evaluator->name ?? 'Admin' }}</span>
                    </div>
                    <div class="flex justify-between py-1">
                        <span class="text-stone-500">Dibuat</span>
                        <span class="font-semibold text-stone-700">{{ $rapot->created_at->format('d M Y H:i') }}</span>
                    </div>
                </div>
            </div>

            {{-- Ringkasan Evaluasi --}}
            <div class="bg-white rounded-2xl border border-stone-200 shadow-sm p-6">
                <h3 class="text-sm font-bold text-stone-700 mb-4 flex items-center gap-2">
                    <i class="fas fa-chart-line text-[#2d6a4f]"></i> Ringkasan Evaluasi
                </h3>
                @php
                    $statusColor = match($dataEvaluasi['status_evaluasi'] ?? 'draft') {
                        'draft' => 'bg-stone-100 text-stone-600',
                        'dikirim' => 'bg-blue-100 text-blue-700',
                        'selesai' => 'bg-emerald-100 text-emerald-700',
                        'Perlu Perbaikan' => 'bg-amber-100 text-amber-700',
                        default => 'bg-stone-100 text-stone-600'
                    };
                @endphp
                <div class="space-y-3">
                    <div class="flex justify-between items-center py-2 border-b border-stone-100">
                        <span class="text-stone-500 text-sm">Status Evaluasi</span>
                        <span class="px-2.5 py-1 rounded-full text-xs font-semibold {{ $statusColor }}">
                            {{ ucfirst($dataEvaluasi['status_evaluasi'] ?? 'draft') }}
                        </span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-stone-100">
                        <span class="text-stone-500 text-sm">Nilai Skala 10</span>
                        <span class="font-bold text-stone-800">{{ number_format(abs($dataEvaluasi['nilai_skala_10'] ?? 0), 1) }}/10</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-stone-100">
                        <span class="text-stone-500 text-sm">Tipe Evaluasi</span>
                        <span class="font-semibold text-stone-700">{{ $rapot->tipe == 'evaluasi_kinerja' ? 'Evaluasi Kinerja' : ucfirst($rapot->tipe) }}</span>
                    </div>
                    <div class="flex justify-between py-2">
                        <span class="text-stone-500 text-sm">Persentase Kehadiran</span>
                        <span class="font-bold {{ ($dataEvaluasi['persentase_kehadiran'] ?? 0) < 0 ? 'text-red-500' : 'text-emerald-600' }}">
                            {{ number_format(abs($dataEvaluasi['persentase_kehadiran'] ?? 0), 1) }}%
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Evaluasi Detail --}}
        <div class="bg-white rounded-2xl border border-stone-200 shadow-sm overflow-hidden mb-8">
            <div class="px-6 md:px-8 py-6 border-b border-stone-100 bg-stone-50/30">
                <h2 class="text-base font-bold text-stone-800 flex items-center gap-2">
                    <i class="fas fa-clipboard-list text-[#2d6a4f]"></i> Evaluasi Kinerja
                </h2>
            </div>

            <div class="p-6 md:p-8 space-y-8">
                {{-- Evaluasi Kerja --}}
                <div>
                    <h3 class="text-sm font-bold text-stone-700 mb-3 flex items-center gap-2">
                        <i class="fas fa-star text-amber-500 text-xs"></i> Evaluasi Kerja
                    </h3>
                    <div class="bg-emerald-50/30 rounded-xl p-5 border border-emerald-100">
                        <p class="text-stone-700 whitespace-pre-line text-sm leading-relaxed">
                            {{ $dataEvaluasi['evaluasi_kerja'] ?? ($rapot->evaluasi_kerja ?? 'Tidak ada evaluasi') }}
                        </p>
                    </div>
                </div>

                {{-- Saran Perbaikan --}}
                <div>
                    <h3 class="text-sm font-bold text-stone-700 mb-3 flex items-center gap-2">
                        <i class="fas fa-lightbulb text-amber-500 text-xs"></i> Saran Perbaikan
                    </h3>
                    <div class="bg-blue-50/30 rounded-xl p-5 border border-blue-100">
                        <p class="text-stone-700 whitespace-pre-line text-sm leading-relaxed">
                            {{ $dataEvaluasi['saran_perbaikan'] ?? ($rapot->saran_perbaikan ?? 'Tidak ada saran') }}
                        </p>
                    </div>
                </div>

                {{-- Catatan Tambahan --}}
                @if($rapot->catatan)
                <div>
                    <h3 class="text-sm font-bold text-stone-700 mb-3 flex items-center gap-2">
                        <i class="fas fa-pencil-alt text-stone-400 text-xs"></i> Catatan Tambahan
                    </h3>
                    <div class="bg-amber-50/30 rounded-xl p-5 border border-amber-100">
                        <p class="text-stone-700 whitespace-pre-line text-sm leading-relaxed">{{ $rapot->catatan }}</p>
                    </div>
                </div>
                @endif

                {{-- Statistik Detail --}}
                <div class="pt-4 border-t border-stone-100">
                    <h3 class="text-sm font-bold text-stone-700 mb-4 flex items-center gap-2">
                        <i class="fas fa-chart-simple text-[#2d6a4f] text-xs"></i> Statistik Detail
                    </h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                        <div class="bg-stone-50 rounded-xl p-4 text-center">
                            <p class="text-xs text-stone-500 mb-1">Persentase Kehadiran</p>
                            <p class="text-xl font-bold {{ ($dataEvaluasi['persentase_kehadiran'] ?? 0) < 0 ? 'text-red-500' : 'text-emerald-600' }}">
                                {{ number_format(abs($dataEvaluasi['persentase_kehadiran'] ?? 0), 1) }}%
                            </p>
                        </div>
                        <div class="bg-stone-50 rounded-xl p-4 text-center">
                            <p class="text-xs text-stone-500 mb-1">Hari Hadir</p>
                            <p class="text-xl font-bold text-stone-800">{{ $dataEvaluasi['hari_hadir'] ?? 0 }} <span class="text-xs font-normal text-stone-400">hari</span></p>
                        </div>
                        <div class="bg-stone-50 rounded-xl p-4 text-center">
                            <p class="text-xs text-stone-500 mb-1">Total Terlambat</p>
                            <p class="text-xl font-bold text-amber-600">{{ $dataEvaluasi['total_terlambat'] ?? 0 }} <span class="text-xs font-normal text-stone-400">kali</span></p>
                        </div>
                        <div class="bg-stone-50 rounded-xl p-4 text-center">
                            <p class="text-xs text-stone-500 mb-1">Rata-rata Jam/Hari</p>
                            <p class="text-xl font-bold text-stone-800">{{ number_format(abs($dataEvaluasi['rata_jam_perhari'] ?? 0), 2) }} <span class="text-xs font-normal text-stone-400">jam</span></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Detail Kehadiran --}}
        <div class="bg-white rounded-2xl border border-stone-200 shadow-sm overflow-hidden mb-8">
            <div class="px-6 md:px-8 py-6 border-b border-stone-100 bg-stone-50/30">
                <h2 class="text-base font-bold text-stone-800 flex items-center gap-2">
                    <i class="fas fa-calendar-check text-[#2d6a4f]"></i> Detail Kehadiran
                </h2>
                <p class="text-xs text-stone-400 mt-1">Total: {{ count($detailAbsen) }} hari kehadiran</p>
            </div>

            <div class="p-6 md:p-8">
                @if(!empty($detailAbsen) && count($detailAbsen) > 0)
                <div class="overflow-x-auto rounded-xl border border-stone-200">
                    <table class="w-full">
                        <thead class="bg-stone-50 border-b border-stone-100">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-stone-500">Tanggal</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-stone-500">Check In</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-stone-500">Check Out</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-stone-500">Jam Kerja</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-stone-500">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100">
                            @foreach($detailAbsen as $absen)
                            <tr class="hover:bg-[#fefcf7] transition">
                                <td class="px-4 py-3 text-sm text-stone-800 font-medium">
                                    @if(isset($absen['tanggal']) && $absen['tanggal'])
                                        @php
                                            try {
                                                if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $absen['tanggal'])) {
                                                    echo $absen['tanggal'];
                                                } elseif (preg_match('/^\d{4}-\d{2}-\d{2}$/', $absen['tanggal'])) {
                                                    echo \Carbon\Carbon::parse($absen['tanggal'])->format('d/m/Y');
                                                } else {
                                                    echo $absen['tanggal'];
                                                }
                                            } catch (\Exception $e) {
                                                echo $absen['tanggal'];
                                            }
                                        @endphp
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm text-stone-600">{{ $absen['check_in'] ?? '-' }}</td>
                                <td class="px-4 py-3 text-sm text-stone-600">{{ $absen['check_out'] ?? '-' }}</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex text-xs font-semibold px-2.5 py-1 rounded-full
                                        {{ ($absen['jam_kerja'] ?? 0) > 0 ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800' }}">
                                        {{ isset($absen['jam_kerja']) ? number_format(abs($absen['jam_kerja']), 2) . ' jam' : '-' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    @php
                                        $statusColor = match($absen['status'] ?? 'hadir') {
                                            'hadir', 'tepat waktu' => 'bg-emerald-100 text-emerald-800',
                                            'izin' => 'bg-yellow-100 text-yellow-800',
                                            'sakit' => 'bg-blue-100 text-blue-800',
                                            'terlambat' => 'bg-amber-100 text-amber-800',
                                            'alfa' => 'bg-red-100 text-red-800',
                                            default => 'bg-stone-100 text-stone-600'
                                        };
                                    @endphp
                                    <span class="px-2.5 py-1 text-xs font-semibold rounded-full {{ $statusColor }}">
                                        {{ ucfirst($absen['status'] ?? 'hadir') }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-12 border-2 border-dashed border-stone-200 rounded-xl">
                    <i class="fas fa-calendar-times text-3xl text-stone-300 mb-3 block"></i>
                    <p class="text-stone-500 font-semibold text-sm">Tidak ada data kehadiran untuk periode ini.</p>
                </div>
                @endif
            </div>
        </div>

        {{-- Footer Actions --}}
        <div class="flex flex-wrap justify-between items-center gap-4 pt-4 border-t border-stone-200">
            <div class="text-xs text-stone-400">
                <i class="far fa-clock mr-1"></i> Terakhir diupdate: {{ $rapot->updated_at->format('d M Y H:i') }}
            </div>
            @if(auth()->check() && auth()->user()->role == 'admin')
            <div class="flex gap-3">
                @if(Route::has('admin.rapot.delete'))
                <form action="{{ route('admin.rapot.delete', $rapot->id) }}" method="POST"
                      onsubmit="return confirm('Yakin ingin menghapus evaluasi ini?')" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="px-4 py-2 bg-red-500 text-white text-sm font-semibold rounded-xl hover:bg-red-600 transition flex items-center gap-2">
                        <i class="fas fa-trash"></i> Hapus Evaluasi
                    </button>
                </form>
                @endif
            </div>
            @endif
        </div>
    </div>

    {{-- Bukti Patroli dan Cleaning --}}
    <div class="bg-white rounded-2xl border border-stone-200 shadow-sm overflow-hidden mb-8">
        <div class="px-6 md:px-8 py-6 border-b border-stone-100 bg-stone-50/30">
            <h2 class="text-base font-bold text-stone-800 flex items-center gap-2">
                <i class="fas fa-file-alt text-[#2d6a4f]"></i> Bukti Patroli dan Cleaning
            </h2>
            <p class="text-xs text-stone-400 mt-1">Rekap bukti kegiatan lapangan di periode rapot</p>
        </div>

        <div class="p-6 md:p-8 grid grid-cols-1 xl:grid-cols-2 gap-5">
            <div class="bg-stone-50 rounded-2xl p-5 border border-stone-200">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-sm font-bold text-stone-700">Patroli Keamanan</h3>
                        <p class="text-xs text-stone-400">Total bukti: {{ $patrolEvidence->count() ?? 0 }}</p>
                    </div>
                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700">Patroli</span>
                </div>

                @if($patrolEvidence->isNotEmpty())
                <div class="overflow-x-auto rounded-xl border border-stone-200">
                    <table class="w-full text-sm">
                        <thead class="bg-white border-b border-stone-200">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-stone-500">Waktu</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-stone-500">Area</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-stone-500">Keterangan</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-stone-500">Foto</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100 bg-white">
                            @foreach($patrolEvidence as $item)
                            <tr class="hover:bg-[#f8f9fa] transition">
                                <td class="px-4 py-3 text-stone-700">
                                    {{ optional(\Carbon\Carbon::parse($item->waktu_patroli))->format('d/m/Y H:i') ?? '-' }}
                                </td>
                                <td class="px-4 py-3 text-stone-700">{{ $item->nama_area ?? '-' }}</td>
                                <td class="px-4 py-3 text-stone-600">{{ \Illuminate\Support\Str::limit($item->keterangan ?? '-', 40) }}</td>
                                <td class="px-4 py-3">
                                    @if($item->foto)
                                    <a href="{{ asset($item->foto) }}" target="_blank" rel="noopener" class="text-[#2d6a4f] font-semibold underline">Lihat</a>
                                    @else
                                    -
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-10 rounded-xl border border-dashed border-stone-200">
                    <p class="text-sm text-stone-500">Tidak ada data patroli untuk periode ini.</p>
                </div>
                @endif
            </div>

            <div class="bg-stone-50 rounded-2xl p-5 border border-stone-200">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-sm font-bold text-stone-700">Cleaning</h3>
                        <p class="text-xs text-stone-400">Total bukti: {{ $cleaningEvidence->count() ?? 0 }}</p>
                    </div>
                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-700">Cleaning</span>
                </div>

                @if($cleaningEvidence->isNotEmpty())
                <div class="overflow-x-auto rounded-xl border border-stone-200">
                    <table class="w-full text-sm">
                        <thead class="bg-white border-b border-stone-200">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-stone-500">Tanggal</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-stone-500">Area</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-stone-500">Keterangan</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-stone-500">Foto</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100 bg-white">
                            @foreach($cleaningEvidence as $item)
                            <tr class="hover:bg-[#f8f9fa] transition">
                                <td class="px-4 py-3 text-stone-700">{{ optional(\Carbon\Carbon::parse($item->tanggal))->format('d/m/Y') ?? '-' }}</td>
                                <td class="px-4 py-3 text-stone-700">{{ $item->area ?? '-' }}</td>
                                <td class="px-4 py-3 text-stone-600">{{ \Illuminate\Support\Str::limit($item->keterangan ?? '-', 40) }}</td>
                                <td class="px-4 py-3">
                                    @if($item->foto)
                                    <a href="{{ asset('storage/' . $item->foto) }}" target="_blank" rel="noopener" class="text-[#2d6a4f] font-semibold underline">Lihat</a>
                                    @else
                                    -
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-10 rounded-xl border border-dashed border-stone-200">
                    <p class="text-sm text-stone-500">Tidak ada data cleaning untuk periode ini.</p>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Footer Actions --}}

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Evaluasi detail loaded for ID: {{ $rapot->id }}');
    });
</script>
@endpush
@endsection
