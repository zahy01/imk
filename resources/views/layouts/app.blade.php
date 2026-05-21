<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Absensi Karyawan - PT. Sipirok Indah</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        /* Custom transition for mobile sidebar + desktop collapsible */
        .sidebar-transition {
            transition: transform 0.3s ease-in-out;
        }

        /* DESKTOP COLLAPSIBLE SIDEBAR STYLES */
        .desktop-sidebar {
            transition: width 0.3s cubic-bezier(0.2, 0.9, 0.4, 1.1);
            width: 280px;
        }
        .desktop-sidebar.collapsed {
            width: 80px;
        }
        .desktop-sidebar.collapsed .sidebar-logo-text,
        .desktop-sidebar.collapsed .nav-item span,
        .desktop-sidebar.collapsed .sidebar-brand-text,
        .desktop-sidebar.collapsed .sidebar-subtext {
            display: none;
        }
        .desktop-sidebar.collapsed .nav-item {
            justify-content: center;
            padding-left: 0;
            padding-right: 0;
        }
        .desktop-sidebar.collapsed .nav-item svg {
            margin-right: 0;
        }
        .desktop-sidebar.collapsed .sidebar-logo-container {
            justify-content: center;
        }
        .desktop-sidebar.collapsed .logo-image {
            margin: 0 auto;
        }

        /* Smooth main content shift */
        .main-content {
            transition: margin-left 0.3s cubic-bezier(0.2, 0.9, 0.4, 1.1);
            margin-left: 280px;
        }
        .main-content.expanded {
            margin-left: 80px;
        }

        /* Mobile menu adjustments */
        @media (max-width: 768px) {
            .main-content, .main-content.expanded {
                margin-left: 0 !important;
            }
            .desktop-sidebar {
                transform: translateX(-100%);
                position: fixed;
                z-index: 40;
                height: 100%;
                transition: transform 0.3s ease;
            }
            .desktop-sidebar.mobile-open {
                transform: translateX(0);
            }
            .main-content {
                width: 100%;
            }
        }

        /* Custom scroll */
        ::-webkit-scrollbar {
            width: 5px;
        }
        ::-webkit-scrollbar-track {
            background: #eef2f5;
        }
        ::-webkit-scrollbar-thumb {
            background: #cbd5e0;
            border-radius: 8px;
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen flex flex-col">

    <!-- DESKTOP SIDEBAR -->
    <aside id="desktopSidebar" class="desktop-sidebar fixed left-0 top-0 h-full bg-white shadow-lg z-30 flex flex-col overflow-y-auto">
        <!-- Sidebar Header with Logo -->
        <div class="p-5 border-b border-gray-100">
            <div class="sidebar-logo-container flex items-center gap-3">
                <img src="{{ asset('images/Logo 1.jpg') }}" alt="Logo PT Sipirok Indah" class="logo-image w-[42px] h-[42px] object-contain rounded-lg shadow-sm" onerror="this.src='https://placehold.co/42x42?text=SP'">
                <div class="sidebar-brand-text">
                    <div class="text-lg font-bold text-gray-800 tracking-tight">PT. SIPIROK INDAH</div>
                    <p class="text-xs text-gray-500 sidebar-subtext">Sistem Absensi</p>
                </div>
            </div>
        </div>

        <!-- Navigation Menu -->
        <nav class="flex-1 px-3 py-6 space-y-1 overflow-y-auto">
            @auth
                @switch(Auth::user()->role)
                    @case('admin')
                        <a href="{{ route('admin.dashboard') }}" class="nav-item flex items-center gap-3 px-4 py-2.5 rounded-xl text-gray-700 font-medium transition-all duration-200 {{ request()->routeIs('admin.dashboard') ? 'bg-[#2c5e4e] text-white' : 'hover:bg-[#eaf4f1] hover:text-[#2c5e4e]' }}">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                            <span>Dashboard</span>
                        </a>
                        <a href="{{ route('admin.pegawai') }}" class="nav-item flex items-center gap-3 px-4 py-2.5 rounded-xl text-gray-700 font-medium transition-all duration-200 {{ request()->routeIs('admin.pegawai') ? 'bg-[#2c5e4e] text-white' : 'hover:bg-[#eaf4f1] hover:text-[#2c5e4e]' }}">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                            <span>Pegawai</span>
                        </a>
                        <a href="{{ route('admin.laporan') }}" class="nav-item flex items-center gap-3 px-4 py-2.5 rounded-xl text-gray-700 font-medium transition-all duration-200 {{ request()->routeIs('admin.laporan') ? 'bg-[#2c5e4e] text-white' : 'hover:bg-[#eaf4f1] hover:text-[#2c5e4e]' }}">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            <span>Laporan</span>
                        </a>
                        <a href="{{ route('admin.rapot.index') }}" class="nav-item flex items-center gap-3 px-4 py-2.5 rounded-xl text-gray-700 font-medium transition-all duration-200 {{ request()->routeIs('admin.rapot.*') ? 'bg-[#2c5e4e] text-white' : 'hover:bg-[#eaf4f1] hover:text-[#2c5e4e]' }}">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            <span>Rapot</span>
                        </a>
                        <a href="{{ route('admin.pengumuman') }}" class="nav-item flex items-center gap-3 px-4 py-2.5 rounded-xl text-gray-700 font-medium transition-all duration-200 {{ request()->routeIs('admin.pengumuman') ? 'bg-[#2c5e4e] text-white' : 'hover:bg-[#eaf4f1] hover:text-[#2c5e4e]' }}">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
                            <span>Pengumuman</span>
                        </a>
                        <a href="{{ route('admin.dokumentasi') }}" class="nav-item flex items-center gap-3 px-4 py-2.5 rounded-xl text-gray-700 font-medium transition-all duration-200 {{ request()->routeIs('admin.dokumentasi') ? 'bg-[#2c5e4e] text-white' : 'hover:bg-[#eaf4f1] hover:text-[#2c5e4e]' }}">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            <span>Dokumentasi</span>
                        </a>
                        @break

                    @case('manager')
                        <a href="{{ route('manager.dashboard') }}" class="nav-item flex items-center gap-3 px-4 py-2.5 rounded-xl text-gray-700 font-medium transition-all duration-200 {{ request()->routeIs('manager.dashboard') ? 'bg-[#2c5e4e] text-white' : 'hover:bg-[#eaf4f1] hover:text-[#2c5e4e]' }}">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                            <span>Dashboard</span>
                        </a>
                        <a href="{{ route('manager.laporan') }}" class="nav-item flex items-center gap-3 px-4 py-2.5 rounded-xl text-gray-700 font-medium transition-all duration-200 {{ request()->routeIs('manager.laporan') ? 'bg-[#2c5e4e] text-white' : 'hover:bg-[#eaf4f1] hover:text-[#2c5e4e]' }}">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            <span>Laporan</span>
                        </a>
                        <a href="{{ route('manager.pegawai') }}" class="nav-item flex items-center gap-3 px-4 py-2.5 rounded-xl text-gray-700 font-medium transition-all duration-200 {{ request()->routeIs('manager.pegawai') ? 'bg-[#2c5e4e] text-white' : 'hover:bg-[#eaf4f1] hover:text-[#2c5e4e]' }}">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                            <span>Kelola Pegawai</span>
                        </a>
                        <a href="{{ route('manager.pengumuman') }}"
class="nav-item flex items-center gap-3 px-4 py-2.5 rounded-xl text-gray-700 font-medium transition-all duration-200 {{ request()->routeIs('manager.pengumuman') ? 'bg-[#2c5e4e] text-white' : 'hover:bg-[#eaf4f1] hover:text-[#2c5e4e]' }}">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
                            <span>Pengumuman</span>
                        </a>
                        @break

                    @case('user')
                        <a href="{{ route('user.dashboard') }}" class="nav-item flex items-center gap-3 px-4 py-2.5 rounded-xl text-gray-700 font-medium transition-all duration-200 {{ request()->routeIs('user.dashboard') ? 'bg-[#2c5e4e] text-white' : 'hover:bg-[#eaf4f1] hover:text-[#2c5e4e]' }}">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                            <span>Dashboard</span>
                        </a>
                        <a href="{{ route('attendance.history') }}" class="nav-item flex items-center gap-3 px-4 py-2.5 rounded-xl text-gray-700 font-medium transition-all duration-200 {{ request()->routeIs('attendance.history') ? 'bg-[#2c5e4e] text-white' : 'hover:bg-[#eaf4f1] hover:text-[#2c5e4e]' }}">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>Riwayat Absen</span>
                        </a>
                        <a href="{{ route('rapot.user') }}" class="nav-item flex items-center gap-3 px-4 py-2.5 rounded-xl text-gray-700 font-medium transition-all duration-200 {{ request()->routeIs('rapot.user') ? 'bg-[#2c5e4e] text-white' : 'hover:bg-[#eaf4f1] hover:text-[#2c5e4e]' }}">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            <span>Rapot Saya</span>
                        </a>
                        <a href="{{ route('pengumuman.user') }}" class="nav-item flex items-center gap-3 px-4 py-2.5 rounded-xl text-gray-700 font-medium transition-all duration-200 {{ request()->routeIs('pengumuman.user') ? 'bg-[#2c5e4e] text-white' : 'hover:bg-[#eaf4f1] hover:text-[#2c5e4e]' }}">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
                            <span>Pengumuman</span>
                        </a>
                        @break

                  @case('security')

@case('cleaning')
@case('kantoran')

    <a href="{{ route(Auth::user()->role . '.dashboard') }}"
       class="nav-item flex items-center gap-3 px-4 py-2.5 rounded-xl text-gray-700 font-medium transition-all duration-200 {{ request()->routeIs(Auth::user()->role . '.dashboard') ? 'bg-[#2c5e4e] text-white' : 'hover:bg-[#eaf4f1] hover:text-[#2c5e4e]' }}">
        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
        </svg>
        <span>Dashboard</span>
    </a>

    <a href="{{ route('attendance.history') }}"
       class="nav-item flex items-center gap-3 px-4 py-2.5 rounded-xl text-gray-700 font-medium transition-all duration-200 {{ request()->routeIs('attendance.history') ? 'bg-[#2c5e4e] text-white' : 'hover:bg-[#eaf4f1] hover:text-[#2c5e4e]' }}">
        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <span>Riwayat Absen</span>
    </a>
    {{-- KHUSUS SECURITY --}}
@if(Auth::user()->role == 'security')

    <a href="{{ route('security.patroli') }}"
       class="nav-item flex items-center gap-3 px-4 py-2.5 rounded-xl text-gray-700 font-medium transition-all duration-200 {{ request()->routeIs('security.patroli') ? 'bg-[#2c5e4e] text-white' : 'hover:bg-[#eaf4f1] hover:text-[#2c5e4e]' }}">

        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 01.553-.894L9 2m0 18l6-2m-6 2V2m6 16l5.447-2.724A1 1 0 0021 14.382V3.618a1 1 0 00-.553-.894L15 0m0 18V0"/>
        </svg>

        <span>Patroli</span>
    </a>

@endif

    {{-- KHUSUS CLEANING --}}
    @if(Auth::user()->role == 'cleaning')
        <a href="{{ route('cleaning.kinerja') }}"
           class="nav-item flex items-center gap-3 px-4 py-2.5 rounded-xl text-gray-700 font-medium transition-all duration-200 {{ request()->routeIs('cleaning.kinerja') ? 'bg-[#2c5e4e] text-white' : 'hover:bg-[#eaf4f1] hover:text-[#2c5e4e]' }}">

            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                    d="M3 7h18M3 12h18M3 17h18"/>
            </svg>

            <span>Input Kinerja</span>
        </a>
    @endif

    <a href="{{ route('rapot.user') }}"
       class="nav-item flex items-center gap-3 px-4 py-2.5 rounded-xl text-gray-700 font-medium transition-all duration-200 {{ request()->routeIs('rapot.user') ? 'bg-[#2c5e4e] text-white' : 'hover:bg-[#eaf4f1] hover:text-[#2c5e4e]' }}">
        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
        </svg>
        <span>Rapot</span>
    </a>

    <a href="{{ route('pengumuman.user') }}"
       class="nav-item flex items-center gap-3 px-4 py-2.5 rounded-xl text-gray-700 font-medium transition-all duration-200 {{ request()->routeIs('pengumuman.user') ? 'bg-[#2c5e4e] text-white' : 'hover:bg-[#eaf4f1] hover:text-[#2c5e4e]' }}">
        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 0 01-1.564-.317z"/>
        </svg>
        <span>Pengumuman</span>
    </a>

    @break
                @endswitch
            @endauth
        </nav>

        <!-- Sidebar bottom spacer -->
        <div class="p-4 border-t border-gray-100"></div>
    </aside>

    <!-- MAIN CONTENT AREA -->
    <div class="main-content flex-1 flex flex-col min-h-screen">
        <!-- TOP BAR -->
        <div class="bg-white border-b border-gray-100 sticky top-0 z-20 shadow-sm">
            <div class="flex justify-between items-center px-6 py-3">
                <!-- Left side: Toggle buttons -->
                <div class="flex items-center gap-3">
                    <!-- Desktop Toggle Collapse Button -->
                    <button id="sidebarCollapseBtn" class="hidden md:flex items-center justify-center w-9 h-9 rounded-full bg-white shadow-sm text-gray-600 hover:bg-[#eaf4f1] hover:text-[#2c5e4e] transition-all duration-200 focus:outline-none">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>

                    <!-- Mobile menu button -->
                    <button id="mobileMenuBtn" class="md:hidden flex items-center justify-center w-9 h-9 rounded-full bg-white shadow-sm text-gray-600 hover:bg-[#eaf4f1] hover:text-[#2c5e4e] transition-all duration-200 focus:outline-none">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>

                    <!-- Mobile inline logo -->
                    <div class="md:hidden flex items-center gap-2">
                        <img src="{{ asset('images/Logo 1.jpg') }}" class="w-7 h-7 rounded-md object-cover" onerror="this.src='https://placehold.co/32x32?text=Logo'">
                        <span class="text-sm font-semibold text-gray-700">PT. SIPIROK INDAH</span>
                    </div>
                </div>

                <!-- Right side: profile + logout -->
                <div class="flex items-center gap-4">
                    <div class="text-right hidden sm:block">
                        <div class="text-sm font-semibold text-gray-800">{{ Auth::user()->name ?? 'Karyawan' }}</div>
                        <div class="text-xs text-gray-500">
                            <span class="inline-block px-3 py-0.5 rounded-full bg-[#eaf4f1] text-[#2c5e4e] text-xs font-medium">{{ ucfirst(Auth::user()->role ?? 'User') }}</span>
                        </div>
                    </div>

                    <div class="w-10 h-10 rounded-full bg-[#2c5e4e] flex items-center justify-center text-white font-bold shadow-sm">
                        {{ strtoupper(substr(Auth::user()->name ?? 'G', 0, 1)) }}
                    </div>

                    <form action="{{ route('logout') }}" method="POST" class="inline">
                        @csrf
                        <button class="flex items-center gap-2 bg-white border border-gray-200 hover:bg-red-50 hover:text-red-600 hover:border-red-200 text-gray-700 px-4 py-2 rounded-xl font-medium text-sm transition-all duration-200 shadow-sm">
                            <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                            <span class="hidden sm:inline">Logout</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- PAGE CONTENT -->
        <main class="flex-1">
            @yield('content')
        </main>

        <!-- FOOTER -->
        <footer class="bg-white text-center py-4 shadow-inner text-gray-500 text-sm border-t border-gray-100">
            &copy; {{ date('Y') }} Sistem Absensi Perusahaan Sawit - PT. Sipirok Indah
        </footer>
    </div>

    <script>
        // DOM Elements
        const sidebar = document.getElementById('desktopSidebar');
        const mainContent = document.querySelector('.main-content');
        const collapseBtn = document.getElementById('sidebarCollapseBtn');
        const mobileMenuBtn = document.getElementById('mobileMenuBtn');

        // Check if desktop
        function isDesktop() {
            return window.innerWidth >= 768;
        }

        // Set sidebar collapsed state
        function setSidebarCollapsed(collapsed) {
            if (!isDesktop()) return;
            if (collapsed) {
                sidebar.classList.add('collapsed');
                mainContent.classList.add('expanded');
                localStorage.setItem('sidebarCollapsed', 'true');
            } else {
                sidebar.classList.remove('collapsed');
                mainContent.classList.remove('expanded');
                localStorage.setItem('sidebarCollapsed', 'false');
            }
        }

        // Initialize sidebar state
        function initSidebarState() {
            if (!isDesktop()) {
                sidebar.classList.remove('collapsed');
                mainContent.classList.remove('expanded');
                return;
            }
            const savedState = localStorage.getItem('sidebarCollapsed');
            setSidebarCollapsed(savedState === 'true');
        }

        // Toggle collapse on button click
        if (collapseBtn) {
            collapseBtn.addEventListener('click', () => {
                const isCollapsed = sidebar.classList.contains('collapsed');
                setSidebarCollapsed(!isCollapsed);
            });
        }

        // Mobile sidebar handlers
        function closeMobileSidebar() {
            if (sidebar.classList.contains('mobile-open')) {
                sidebar.classList.remove('mobile-open');
                document.body.style.overflow = '';
            }
        }

        function openMobileSidebar() {
            if (window.innerWidth < 768) {
                sidebar.classList.add('mobile-open');
                document.body.style.overflow = 'hidden';
            }
        }

        if (mobileMenuBtn) {
            mobileMenuBtn.addEventListener('click', openMobileSidebar);
        }

        // Close sidebar when clicking nav link on mobile
        document.querySelectorAll('.nav-item').forEach(link => {
            link.addEventListener('click', (e) => {
                if (window.innerWidth < 768) {
                    closeMobileSidebar();
                }
            });
        });

        // Close when clicking outside on mobile
        document.addEventListener('click', function(e) {
            if (window.innerWidth < 768 && sidebar.classList.contains('mobile-open')) {
                if (!sidebar.contains(e.target) && !mobileMenuBtn.contains(e.target)) {
                    closeMobileSidebar();
                }
            }
        });

        // Handle resize
        window.addEventListener('resize', function() {
            if (window.innerWidth >= 768) {
                closeMobileSidebar();
                initSidebarState();
                document.body.style.overflow = '';
            } else {
                sidebar.classList.remove('collapsed');
                mainContent.classList.remove('expanded');
            }
        });

        // Handle orientation change
        window.addEventListener('orientationchange', () => {
            closeMobileSidebar();
            document.body.style.overflow = '';
        });

        // Initialize
        initSidebarState();
    </script>
</body>
</html>
