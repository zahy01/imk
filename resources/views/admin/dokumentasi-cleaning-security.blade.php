@extends('layouts.app')

@section('content')
<div class="bg-gray-50 min-h-screen p-6 md:p-8">
    <div class="max-w-7xl mx-auto">

        {{-- ============================================================ --}}
        {{-- HEADER SECTION --}}
        {{-- ============================================================ --}}
        <div class="mb-8 pb-5 border-b border-gray-200">
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
                <div>
                    <p class="text-sm text-gray-500 uppercase tracking-wide mb-1">Admin</p>
                    <h1 class="text-2xl md:text-3xl font-bold text-[#2c5e4e]">Dokumentasi Cleaning & Security</h1>
                    <p class="text-sm text-gray-500 mt-1">Galeri bukti patroli keamanan dan hasil pekerjaan cleaning</p>
                </div>
                <span class="inline-block px-4 py-1.5 bg-[#eaf4f1] text-[#2c5e4e] rounded-full text-sm font-medium self-start sm:self-center">
                    PT. Sipirok Indah
                </span>
            </div>
        </div>

        {{-- ============================================================ --}}
        {{-- SUMMARY CARDS --}}
        {{-- ============================================================ --}}
        <div class="grid grid-cols-3 md:grid-cols-4 gap-4 md:gap-5 mb-8">
            <div class="bg-white rounded-xl md:rounded-2xl p-4 md:p-5 border border-gray-200 transition-all hover:shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 md:w-12 md:h-12 rounded-xl bg-[#eaf4f1] flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 md:w-6 md:h-6 text-[#2c5e4e]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Total Dokumentasi</p>
                        <p class="text-2xl font-bold text-gray-800 mt-1">{{ $totalItems }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl md:rounded-2xl p-4 md:p-5 border border-gray-200 transition-all hover:shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 md:w-12 md:h-12 rounded-xl bg-blue-100 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 md:w-6 md:h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 01.553-.894L9 2m0 18l6-2m-6 2V2m6 16l5.447-2.724A1 1 0 0021 14.382V3.618a1 1 0 00-.553-.894L15 0m0 18V0"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Patroli</p>
                        <p class="text-2xl font-bold text-blue-600 mt-1">{{ $patroliCount }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl md:rounded-2xl p-4 md:p-5 border border-gray-200 transition-all hover:shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 md:w-12 md:h-12 rounded-xl bg-green-100 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 md:w-6 md:h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Cleaning</p>
                        <p class="text-2xl font-bold text-green-600 mt-1">{{ $cleaningCount }}</p>
                    </div>
                </div>
            </div>

            <div class="hidden md:flex bg-gradient-to-br from-[#2c5e4e] to-[#1f4a3d] rounded-xl md:rounded-2xl p-4 md:p-5 transition-all hover:shadow-sm items-center gap-3">
                <div class="w-10 h-10 md:w-12 md:h-12 rounded-xl bg-white/15 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 md:w-6 md:h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-white/70">Range</p>
                    <p class="text-sm font-bold text-white mt-1">{{ date('d M Y', strtotime($startDate)) }} - {{ date('d M Y', strtotime($endDate)) }}</p>
                </div>
            </div>
        </div>

        {{-- ============================================================ --}}
        {{-- FILTER SECTION --}}
        {{-- ============================================================ --}}
        <div class="bg-white rounded-xl md:rounded-2xl shadow-sm border border-gray-200 p-5 md:p-6 mb-8">
            <form method="GET" action="{{ route('admin.dokumentasi') }}" class="space-y-4">
                <div class="flex items-center gap-2 pb-4 border-b border-gray-200">
                    <svg class="w-5 h-5 text-[#2c5e4e]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                    </svg>
                    <h3 class="text-base font-semibold text-gray-800">Filter Dokumentasi</h3>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    {{-- Tanggal Mulai --}}
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Tanggal Mulai</label>
                        <input type="date" name="start_date" value="{{ $startDate }}"
                               class="w-full px-4 py-2.5 rounded-lg border border-gray-200 focus:border-[#2c5e4e] focus:ring-2 focus:ring-[#2c5e4e]/20 outline-none transition text-sm">
                    </div>

                    {{-- Tanggal Akhir --}}
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Tanggal Akhir</label>
                        <input type="date" name="end_date" value="{{ $endDate }}"
                               class="w-full px-4 py-2.5 rounded-lg border border-gray-200 focus:border-[#2c5e4e] focus:ring-2 focus:ring-[#2c5e4e]/20 outline-none transition text-sm">
                    </div>

                    {{-- Tipe Dokumentasi --}}
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Tipe Dokumentasi</label>
                        <select name="type" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 focus:border-[#2c5e4e] focus:ring-2 focus:ring-[#2c5e4e]/20 outline-none transition text-sm">
                            <option value="all" {{ $type === 'all' ? 'selected' : '' }}>Semua</option>
                            <option value="patroli" {{ $type === 'patroli' ? 'selected' : '' }}>Patroli Keamanan</option>
                            <option value="cleaning" {{ $type === 'cleaning' ? 'selected' : '' }}>Cleaning Service</option>
                        </select>
                    </div>

                    {{-- User/Petugas --}}
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Petugas</label>
                        <select name="user_id" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 focus:border-[#2c5e4e] focus:ring-2 focus:ring-[#2c5e4e]/20 outline-none transition text-sm">
                            <option value="">Semua Petugas</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ $userId == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }} ({{ ucfirst($user->role) }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="flex gap-3 pt-4">
                    <button type="submit" class="flex items-center gap-2 bg-[#2c5e4e] hover:bg-[#1f4a3d] text-white px-6 py-2.5 rounded-lg font-semibold transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        Terapkan Filter
                    </button>
                    <a href="{{ route('admin.dokumentasi') }}" class="flex items-center gap-2 bg-gray-200 hover:bg-gray-300 text-gray-800 px-6 py-2.5 rounded-lg font-semibold transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        Reset
                    </a>
                </div>
            </form>
        </div>

        {{-- ============================================================ --}}
        {{-- GALLERY SECTION --}}
        {{-- ============================================================ --}}
        <div class="mb-8">
            @if($items->count() > 0)
                <h3 class="text-lg font-semibold text-gray-800 mb-5 flex items-center gap-2">
                    <svg class="w-5 h-5 text-[#2c5e4e]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    Galeri ({{ $totalItems }} dokumen)
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5 md:gap-6">
                    @foreach($items as $item)
                        <div class="bg-white rounded-xl overflow-hidden border border-gray-200 transition-all hover:shadow-lg hover:border-[#2c5e4e]/20 group cursor-pointer"
                             onclick="openLightbox({{ json_encode($item) }})">

                            {{-- Image Container --}}
                            <div class="relative overflow-hidden bg-gray-200 aspect-video">
                                <img src="{{ $item['foto'] }}"
                                     alt="{{ $item['area'] }}"
                                     class="w-full h-full object-cover transition-transform group-hover:scale-105"
                                     onerror="this.src='https://placehold.co/500x300?text=Foto+Tidak+Ada'">

                                {{-- Type Badge --}}
                                <div class="absolute top-3 right-3">
                                    @if($item['type'] === 'patroli')
                                        <span class="inline-block px-3 py-1.5 bg-blue-500 text-white text-xs font-semibold rounded-full">
                                            🔐 Patroli
                                        </span>
                                    @else
                                        <span class="inline-block px-3 py-1.5 bg-green-500 text-white text-xs font-semibold rounded-full">
                                            🧹 Cleaning
                                        </span>
                                    @endif
                                </div>

                                {{-- View Icon Overlay --}}
                                <div class="absolute inset-0 bg-black/0 group-hover:bg-black/30 transition-colors flex items-center justify-center">
                                    <div class="opacity-0 group-hover:opacity-100 transition-opacity">
                                        <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                    </div>
                                </div>
                            </div>

                            {{-- Content --}}
                            <div class="p-4 md:p-5">
                                <h4 class="font-semibold text-gray-800 mb-1 text-base truncate">{{ $item['area'] }}</h4>
                                <p class="text-xs text-gray-500 mb-3">
                                    <span class="inline-block">{{ $item['date'] }} - {{ $item['time'] }}</span>
                                </p>

                                <p class="text-sm text-gray-600 line-clamp-2 mb-3">{{ $item['keterangan'] }}</p>

                                <div class="flex items-center justify-between pt-3 border-t border-gray-100">
                                    <span class="text-xs font-medium text-gray-500 flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                        </svg>
                                        {{ $item['user_name'] }}
                                    </span>
                                    <button class="text-[#2c5e4e] hover:text-[#1f4a3d] font-semibold text-sm flex items-center gap-1 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                        </svg>
                                        Lihat
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="bg-white rounded-xl md:rounded-2xl shadow-sm border border-gray-200 p-12 text-center">
                    <div class="flex justify-center mb-4">
                        <svg class="w-16 h-16 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-600 mb-1">Tidak Ada Dokumentasi</h3>
                    <p class="text-gray-500 mb-5">Tidak ada bukti patroli atau cleaning dengan filter yang dipilih.</p>
                    <a href="{{ route('admin.dokumentasi') }}" class="inline-block bg-[#2c5e4e] hover:bg-[#1f4a3d] text-white px-6 py-2.5 rounded-lg font-semibold transition-all">
                        Reset Filter
                    </a>
                </div>
            @endif
        </div>

    </div>
</div>

{{-- ============================================================ --}}
{{-- LIGHTBOX MODAL --}}
{{-- ============================================================ --}}
<div id="lightboxModal" class="hidden fixed inset-0 bg-black/80 z-50 flex items-center justify-center p-4"
     onclick="if(event.target.id === 'lightboxModal') closeLightbox()">
    <div class="bg-white rounded-xl max-w-4xl w-full max-h-[90vh] flex flex-col overflow-hidden" onclick="event.stopPropagation()">

        {{-- Header --}}
        <div class="flex items-center justify-between p-4 md:p-6 border-b border-gray-200 bg-gray-50">
            <div class="flex-1">
                <h2 id="lightboxTitle" class="text-lg md:text-xl font-bold text-gray-800"></h2>
                <p id="lightboxDateTime" class="text-sm text-gray-500 mt-1"></p>
            </div>
            <button onclick="closeLightbox()" class="text-gray-500 hover:text-gray-700 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        {{-- Content --}}
        <div class="flex-1 overflow-y-auto p-4 md:p-6">
            <div class="space-y-6">
                {{-- Image --}}
                <div class="bg-gray-200 rounded-lg overflow-hidden">
                    <img id="lightboxImage" src="" alt="Full image" class="w-full h-auto object-contain max-h-[400px]">
                </div>

                {{-- Details --}}
                <div class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {{-- Tipe --}}
                        <div>
                            <p class="text-xs font-semibold uppercase text-gray-500 mb-1">Tipe Dokumentasi</p>
                            <div class="flex items-center gap-2">
                                <span id="lightboxTypeIcon"></span>
                                <span id="lightboxType" class="text-sm font-semibold text-gray-800"></span>
                            </div>
                        </div>

                        {{-- Petugas --}}
                        <div>
                            <p class="text-xs font-semibold uppercase text-gray-500 mb-1">Petugas</p>
                            <p id="lightboxUser" class="text-sm font-semibold text-gray-800"></p>
                        </div>

                        {{-- Area --}}
                        <div>
                            <p class="text-xs font-semibold uppercase text-gray-500 mb-1">Area/Lokasi</p>
                            <p id="lightboxArea" class="text-sm text-gray-700"></p>
                        </div>

                        {{-- Waktu --}}
                        <div>
                            <p class="text-xs font-semibold uppercase text-gray-500 mb-1">Waktu</p>
                            <p id="lightboxTime" class="text-sm text-gray-700"></p>
                        </div>
                    </div>

                    {{-- Keterangan --}}
                    <div>
                        <p class="text-xs font-semibold uppercase text-gray-500 mb-2">Keterangan/Catatan</p>
                        <p id="lightboxKeterangan" class="text-sm text-gray-700 leading-relaxed bg-gray-50 p-4 rounded-lg"></p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Footer --}}
        <div class="p-4 md:p-6 border-t border-gray-200 bg-gray-50 flex justify-end gap-3">
            <button onclick="closeLightbox()" class="px-6 py-2.5 rounded-lg border border-gray-300 text-gray-700 font-semibold hover:bg-gray-100 transition-all">
                Tutup
            </button>
            <a id="downloadBtn" href="#" target="_blank" class="px-6 py-2.5 rounded-lg bg-[#2c5e4e] hover:bg-[#1f4a3d] text-white font-semibold transition-all flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                </svg>
                Unduh Gambar
            </a>
        </div>
    </div>
</div>

<script>
function openLightbox(item) {
    const modal = document.getElementById('lightboxModal');

    // Set content
    document.getElementById('lightboxTitle').textContent = item.area;
    document.getElementById('lightboxDateTime').textContent = item.date + ' - ' + item.time;
    document.getElementById('lightboxImage').src = item.foto;
    document.getElementById('lightboxArea').textContent = item.area;
    document.getElementById('lightboxUser').textContent = item.user_name;
    document.getElementById('lightboxKeterangan').textContent = item.keterangan;
    document.getElementById('lightboxTime').textContent = item.date + ' Jam ' + item.time;

    // Set type
    if (item.type === 'patroli') {
        document.getElementById('lightboxType').textContent = 'Patroli Keamanan';
        document.getElementById('lightboxTypeIcon').innerHTML = '🔐';
    } else {
        document.getElementById('lightboxType').textContent = 'Cleaning Service';
        document.getElementById('lightboxTypeIcon').innerHTML = '🧹';
    }

    // Set download link
    document.getElementById('downloadBtn').href = item.foto;

    // Show modal
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeLightbox() {
    document.getElementById('lightboxModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
}

// Close lightbox dengan ESC key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeLightbox();
});
</script>

@endsection
