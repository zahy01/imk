<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;
use App\Models\User;
use App\Models\CatatanPanen;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\KinerjaCleaning;
use App\Models\PatroliSecurity;
use App\Exports\SheetAbsenExport;
use App\Exports\RekapSemuaExport;

class HomeController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        return match ($user->role) {
            'admin' => redirect()->route('admin.dashboard'),
            'manager' => redirect()->route('manager.dashboard'),
            'user' => redirect()->route('user.dashboard'),
            'security' => redirect()->route('security.dashboard'),
            'cleaning' => redirect()->route('cleaning.dashboard'),
            'kantoran' => redirect()->route('kantoran.dashboard'),
            default => redirect()->route('user.dashboard'),
        };
    }

    public function adminDashboard()
{
    $today = now('Asia/Jakarta')->startOfDay();

    $totalPegawai = User::whereNotIn('role', ['admin', 'manager'])->count();

    // Hitung kehadiran hari ini
    $hadirHariIni = Attendance::whereDate('date', $today->toDateString())
        ->whereNotNull('check_in')
        ->count();

    // Hitung total terlambat
    $totalTerlambat = Attendance::whereDate('date', $today->toDateString())
        ->where('status', 'terlambat')
        ->count();

    // Hitung Alpha (pegawai yang belum absen sama sekali hari ini)
    $pegawaiIdsWithAttendance = Attendance::whereDate('date', $today->toDateString())
        ->whereNotNull('check_in')
        ->pluck('user_id')
        ->toArray();

    $totalAlpha = User::whereNotIn('role', ['admin', 'manager'])
        ->whereNotIn('id', $pegawaiIdsWithAttendance)
        ->count();

    // Produksi hari ini
    $produksiHariIni = CatatanPanen::whereDate('tanggal', $today->toDateString())
        ->sum('berat_kg') ?? 0;

    // Aktivitas keamanan dan kebersihan hari ini
    $totalCleaningHariIni = KinerjaCleaning::whereDate('tanggal', $today->toDateString())
        ->count();
    $totalPatroliHariIni = PatroliSecurity::whereDate('waktu_patroli', $today->toDateString())
        ->count();

    $cleaningUserIds = KinerjaCleaning::whereDate('tanggal', $today->toDateString())
        ->pluck('user_id')
        ->filter()
        ->unique()
        ->toArray();
    $patroliUserIds = PatroliSecurity::whereDate('waktu_patroli', $today->toDateString())
        ->pluck('user_id')
        ->filter()
        ->unique()
        ->toArray();

    $reportingUsers = count(array_unique(array_merge($cleaningUserIds, $patroliUserIds)));

    $cleaningAreas = KinerjaCleaning::whereDate('tanggal', $today->toDateString())
        ->pluck('area')
        ->filter()
        ->unique()
        ->toArray();
    $patroliAreas = PatroliSecurity::whereDate('waktu_patroli', $today->toDateString())
        ->pluck('nama_area')
        ->filter()
        ->unique()
        ->toArray();

    $totalAreas = count(array_unique(array_merge($cleaningAreas, $patroliAreas)));

    // Rate kehadiran (hitung berdasarkan yang sudah absen, baik tepat waktu maupun terlambat)
    $totalHadirDanTerlambat = $hadirHariIni;
    $rateKehadiran = $totalPegawai > 0 ? round(($totalHadirDanTerlambat / $totalPegawai) * 100) : 0;

    // Aktivitas terbaru
    $recentActivities = Attendance::with('user')
        ->whereDate('date', $today->toDateString())
        ->whereNotNull('check_in')
        ->orderBy('check_in', 'desc')
        ->limit(5)
        ->get();

    // Data departemen
    $roles = ['user' => 'Kebun & Panen', 'security' => 'Security', 'cleaning' => 'Cleaning', 'kantoran' => 'Administrasi'];
    $departments = [];

    foreach ($roles as $role => $name) {
        $total = User::where('role', $role)->count();
        $hadir = Attendance::whereDate('date', $today->toDateString())
            ->whereNotNull('check_in')
            ->whereHas('user', fn($q) => $q->where('role', $role))
            ->count();
        $departments[$role] = [
            'name' => $name,
            'total' => $total,
            'hadir' => $hadir,
            'percentage' => $total > 0 ? round(($hadir / $total) * 100) : 0,
        ];
    }

    return view('admin.dashboard', compact(
        'totalPegawai',
        'hadirHariIni',
        'produksiHariIni',
        'totalCleaningHariIni',
        'totalPatroliHariIni',
        'reportingUsers',
        'totalAreas',
        'rateKehadiran',
        'recentActivities',
        'departments',
        'totalTerlambat',
        'totalAlpha'
    ));
}

    public function userDashboard()
    {
        $user = Auth::user();
        $today = Carbon::today('Asia/Jakarta');

        // Ambil absensi hari ini
        $absenHariIni = Attendance::where('user_id', $user->id)
            ->whereDate('date', $today)
            ->first();

        // Hitung total kehadiran bulan ini
        $monthlyCount = Attendance::where('user_id', $user->id)
            ->whereMonth('date', now('Asia/Jakarta')->month)
            ->whereYear('date', now('Asia/Jakarta')->year)
            ->count();

        // Default nilai panen
        $monthlyPalmWeight = 0;
        $averageDailyPalmWeight = 0;
        $todayPalmWeight = 0;

        // Jika pekerja sawit, hitung panennya
        if ($user->role == 'user') {
            // Total berat sawit bulan ini
            $monthlyPalmWeight = CatatanPanen::where('id_pegawai', $user->id)
                ->whereMonth('tanggal', now('Asia/Jakarta')->month)
                ->whereYear('tanggal', now('Asia/Jakarta')->year)
                ->sum('berat_kg') ?? 0;

            // Panen hari ini
            $panenHariIni = CatatanPanen::where('id_pegawai', $user->id)
                ->whereDate('tanggal', $today)
                ->first();

            if ($panenHariIni) {
                $todayPalmWeight = $panenHariIni->berat_kg;
            }

            // Hitung rata-rata panen
            if ($monthlyCount > 0 && $monthlyPalmWeight > 0) {
                $averageDailyPalmWeight = $monthlyPalmWeight / $monthlyCount;
            }
        }

        return view('user.dashboard', [
            'absenHariIni' => $absenHariIni,
            'monthlyCount' => $monthlyCount,
            'monthlyPalmWeight' => $monthlyPalmWeight,
            'averageDailyPalmWeight' => $averageDailyPalmWeight,
            'todayPalmWeight' => $todayPalmWeight
        ]);
    }

    public function managerDashboard()
    {
        $today = now('Asia/Jakarta')->startOfDay();

        $absenHariIni = Attendance::where('user_id', Auth::id())
            ->whereDate('date', $today->toDateString())
            ->first();

        $totalTim = User::whereIn('role', ['user', 'security', 'cleaning', 'kantoran'])->count();
        $hadirHariIni = Attendance::whereDate('date', $today->toDateString())
            ->whereNotNull('check_in')
            ->count();

        // Menggunakan model CatatanPanen untuk menghitung produksi
        $produksiHariIni = CatatanPanen::whereDate('tanggal', $today->toDateString())
            ->sum('berat_kg') ?? 0;

        $totalTerlambat = Attendance::whereDate('date', $today->toDateString())
            ->where('status', 'terlambat')
            ->count();

        $pegawaiIdsWithAttendance = Attendance::whereDate('date', $today->toDateString())
            ->whereNotNull('check_in')
            ->pluck('user_id')
            ->toArray();

        $totalAlpha = User::whereIn('role', ['user', 'security', 'cleaning', 'kantoran'])
            ->whereNotIn('id', $pegawaiIdsWithAttendance)
            ->count();

        // Data produktivitas 30 hari terakhir
        $produktivitasData = CatatanPanen::select(
                DB::raw('DATE(tanggal) as tanggal'),
                DB::raw('COALESCE(SUM(berat_kg), 0) as total_produksi')
            )
            ->where('tanggal', '>=', Carbon::now('Asia/Jakarta')->subDays(30))
            ->groupBy('tanggal')
            ->orderBy('tanggal')
            ->get()
            ->map(function($item) {
                return [
                    'tanggal' => $item->tanggal,
                    'total_produksi' => (float) $item->total_produksi
                ];
            });

        // Top 5 performer hari ini
        $topPerformers = CatatanPanen::select(
                'users.id',
                'users.name',
                'users.role',
                DB::raw('COALESCE(SUM(catatan_panen.berat_kg), 0) as total_produksi'),
                DB::raw('MAX(attendances.check_in) as check_in_time')
            )
            ->join('users', 'catatan_panen.id_pegawai', '=', 'users.id')
            ->leftJoin('attendances', function($join) use ($today) {
                $join->on('users.id', '=', 'attendances.user_id')
                    ->whereDate('attendances.date', $today->toDateString());
            })
            ->whereDate('catatan_panen.tanggal', $today->toDateString())
            ->where('users.role', 'user')
            ->groupBy('users.id', 'users.name', 'users.role')
            ->orderBy('total_produksi', 'desc')
            ->limit(5)
            ->get();

        // Jika tidak ada data produksi, ambil dari attendance saja untuk ranking
        if ($topPerformers->isEmpty() || $topPerformers->sum('total_produksi') == 0) {
            $topPerformers = Attendance::select(
                    'users.id',
                    'users.name',
                    'users.role',
                    DB::raw('0 as total_produksi'),
                    'attendances.check_in as check_in_time'
                )
                ->join('users', 'attendances.user_id', '=', 'users.id')
                ->whereDate('attendances.date', $today->toDateString())
                ->whereNotNull('attendances.check_in')
                ->where('users.role', 'user')
                ->orderBy('attendances.check_in', 'asc')
                ->limit(5)
                ->get();
        }

        // Statistik tambahan
        $avgProduksi = $produktivitasData->avg('total_produksi') ?? 0;

        $totalProduksiBulanIni = CatatanPanen::whereMonth('tanggal', now('Asia/Jakarta')->month)
            ->whereYear('tanggal', now('Asia/Jakarta')->year)
            ->sum('berat_kg') ?? 0;

        $peakProduksi = $produktivitasData->max('total_produksi') ?? 0;

        // Hitung trend produksi
        $trend = 'Stabil';
        if ($produktivitasData->count() >= 2) {
            $latestData = $produktivitasData->last();
            $previousData = $produktivitasData->slice(-2, 1)->first();

            if ($latestData && $previousData && $previousData['total_produksi'] > 0) {
                $latest = $latestData['total_produksi'];
                $previous = $previousData['total_produksi'];
                $change = (($latest - $previous) / $previous) * 100;

                if ($change > 10) $trend = 'Naik';
                else if ($change < -10) $trend = 'Turun';
            }
        }

        // Aktivitas terbaru - dengan produksi
        $recentActivities = Attendance::with('user')
            ->whereDate('date', $today->toDateString())
            ->whereNotNull('check_in')
            ->whereHas('user', fn($q) => $q->whereIn('role', ['user', 'security', 'cleaning', 'kantoran']))
            ->orderBy('check_in', 'desc')
            ->limit(5)
            ->get()
            ->map(function($item) {
                // Ambil data produksi untuk user ini hari ini
                $produksi = CatatanPanen::where('id_pegawai', $item->user_id)
                    ->whereDate('tanggal', now('Asia/Jakarta')->toDateString())
                    ->sum('berat_kg');

                $item->produksi_harian = $produksi;
                return $item;
            });

        return view('manager.dashboard', compact(
            'absenHariIni',
            'totalTim',
            'hadirHariIni',
            'produksiHariIni',
            'totalTerlambat',
            'totalAlpha',
            'produktivitasData',
            'topPerformers',
            'avgProduksi',
            'totalProduksiBulanIni',
            'peakProduksi',
            'trend',
            'recentActivities'
        ));
    }

  public function securityDashboard()
{
    $today = now('Asia/Jakarta')->toDateString();

    // Absen hari ini
    $absenHariIni = Attendance::where('user_id', Auth::id())
        ->whereDate('date', $today)
        ->first();

    // Data patroli hari ini
    $patroliHariIni = \App\Models\PatroliSecurity::where('user_id', Auth::id())
        ->whereDate('created_at', $today)
        ->latest()
        ->get();

    // Total patroli hari ini
    $totalPatroliHariIni = $patroliHariIni->count();

    return view('security.dashboard', compact(
        'absenHariIni',
        'patroliHariIni',
        'totalPatroliHariIni'
    ));
}

   public function cleaningDashboard()
{
    $absenHariIni = Attendance::where('user_id', Auth::id())
        ->whereDate('created_at', today())
        ->first();

    $jumlahAreaHariIni = KinerjaCleaning::where('user_id', Auth::id())
        ->whereDate('tanggal', now()->toDateString())
        ->count();

    return view('cleaning.dashboard', compact(
        'absenHariIni',
        'jumlahAreaHariIni'
    ));
}

    public function kantoranDashboard()
    {
        $today = now('Asia/Jakarta')->startOfDay();
        $absenHariIni = Attendance::where('user_id', Auth::id())
            ->whereDate('date', $today->toDateString())
            ->first();
        return view('kantoran.dashboard', compact('absenHariIni'));
    }

    public function kelolaPegawai()
    {
        $pegawai = User::all();
        return view('admin.pegawai', compact('pegawai'));
    }

    public function managerPegawai()
    {
        if (Auth::user()->role != 'manager') return redirect('/');

        $pegawai = User::whereIn('role', ['user', 'security', 'cleaning', 'kantoran'])
            ->orderBy('name')
            ->get();

        return view('manager.pegawai', compact('pegawai'));
    }

    public function managerTambahPegawai(Request $request)
    {
        if (Auth::user()->role != 'manager') return redirect('/');

        $request->validate([
            'name' => 'required|string|max:255',
            'no_hp' => 'required|string|max:20|unique:users,no_hp',
            'role' => 'required|in:user,security,cleaning,kantoran',
            'password' => 'required|min:6',
        ]);

        User::create([
            'name' => $request->name,
            'no_hp' => $request->no_hp,
            'role' => $request->role,
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('manager.pegawai')->with('success', 'Pegawai berhasil ditambahkan!');
    }

    public function managerUpdatePegawai(Request $request, $id)
    {
        if (Auth::user()->role != 'manager') return redirect('/');

        $pegawai = User::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'no_hp' => 'required|string|max:20|unique:users,no_hp,' . $id,
            'role' => 'required|in:user,security,cleaning,kantoran',
        ]);

        $data = [
            'name' => $request->name,
            'no_hp' => $request->no_hp,
            'role' => $request->role,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $pegawai->update($data);

        return redirect()->route('manager.pegawai')->with('success', 'Data pegawai berhasil diupdate!');
    }

    public function laporanAdmin(Request $request)
    {
        $today = now('Asia/Jakarta')->startOfDay();

        $startDate = $request->start_date
            ? Carbon::parse($request->start_date)->startOfDay()
            : $today->copy()->startOfMonth();

        $endDate = $request->end_date
            ? Carbon::parse($request->end_date)->endOfDay()
            : $today->copy()->endOfMonth();

        $role = $request->input('role');
        $dataType = $request->input('data_type', 'today'); // Default hari ini

        // Query untuk detail attendance dengan data_type filter
        $query = Attendance::with('user')
            ->whereNotNull('check_in'); // Hanya yang sudah check_in

        // Filter berdasarkan data_type
        if ($dataType == 'today') {
            $query->whereDate('date', $today->toDateString());
        } else {
            $query->whereBetween('date', [
                $startDate->toDateString(),
                $endDate->toDateString()
            ]);
        }

        // Filter berdasarkan role jika dipilih
        if ($role) {
            $query->whereHas('user', function($q) use ($role) {
                $q->where('role', $role);
            });
        }

        // Ambil data detail attendance
        $detailedAttendances = $query->orderBy('date', 'desc')
            ->orderBy('check_in', 'desc')
            ->paginate(20)
            ->appends($request->except('page'));

        // Statistik utama
        $userQuery = User::whereNotIn('role', ['admin', 'manager']);
        if ($role) {
            $userQuery->where('role', $role);
        }
        $totalPegawai = $userQuery->count();

        // Query total berat sawit yang benar
        $totalPalmWeight = 0;
        $averagePalmWeight = 0;

        // Hanya hitung jika role 'user' atau tidak ada filter
        if (!$role || $role == 'user') {
            // Query untuk total berat sawit dari CatatanPanen
            $palmQuery = CatatanPanen::query();

            // Filter berdasarkan periode
            if ($dataType == 'today') {
                $palmQuery->whereDate('tanggal', $today->toDateString());
            } else {
                $palmQuery->whereBetween('tanggal', [
                    $startDate->toDateString(),
                    $endDate->toDateString()
                ]);
            }

            if ($role == 'user') {
                // Filter berdasarkan user yang role 'user'
                $palmQuery->whereHas('pegawai', function($q) {
                    $q->where('role', 'user');
                });
            }

            $totalPalmWeight = $palmQuery->sum('berat_kg') ?? 0;

            // Hitung rata-rata berdasarkan jumlah user dengan panen
            $countPanen = $palmQuery->distinct('id_pegawai')->count('id_pegawai');
            $averagePalmWeight = $countPanen > 0 ? round($totalPalmWeight / $countPanen, 2) : 0;
        }

        // Total kehadiran
        $hadirQuery = Attendance::whereNotNull('check_in');

        // Filter berdasarkan periode
        if ($dataType == 'today') {
            $hadirQuery->whereDate('date', $today->toDateString());
        } else {
            $hadirQuery->whereBetween('date', [
                $startDate->toDateString(),
                $endDate->toDateString()
            ]);
        }

        if ($role) {
            $hadirQuery->whereHas('user', function($q) use ($role) {
                $q->where('role', $role);
            });
        }

        $totalHadir = $hadirQuery->distinct('user_id')->count('user_id');

        // Data untuk chart panen harian (7 hari terakhir DARI HARI INI)
        $chartEndDate = now('Asia/Jakarta')->startOfDay();
        $chartStartDate = $chartEndDate->copy()->subDays(6);

        $dailyPalmWeight = CatatanPanen::select(
                DB::raw('DATE(tanggal) as date'),
                DB::raw('SUM(berat_kg) as total_weight')
            )
            ->whereBetween('tanggal', [
                $chartStartDate->toDateString(),
                $chartEndDate->toDateString()
            ])
            ->when($role == 'user', function($q) {
                $q->whereHas('pegawai', function($q2) {
                    $q2->where('role', 'user');
                });
            })
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Data kehadiran harian (7 hari terakhir DARI HARI INI)
        $dailyAttendance = Attendance::select(
                DB::raw('DATE(date) as date'),
                DB::raw('COUNT(DISTINCT user_id) as total')
            )
            ->whereBetween('date', [
                $chartStartDate->toDateString(),
                $chartEndDate->toDateString()
            ])
            ->whereNotNull('check_in')
            ->when($role, function($q) use ($role) {
                $q->whereHas('user', function($q2) use ($role) {
                    $q2->where('role', $role);
                });
            })
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Top performers - berdasarkan CatatanPanen
        $topPerformers = collect();
        if (!$role || $role == 'user') {
            $topPerformersQuery = CatatanPanen::with('pegawai')
                ->select(
                    'id_pegawai',
                    DB::raw('SUM(berat_kg) as total_weight'),
                    DB::raw('COUNT(*) as total_days')
                );

            // Filter berdasarkan periode
            if ($dataType == 'today') {
                $topPerformersQuery->whereDate('tanggal', $today->toDateString());
            } else {
                $topPerformersQuery->whereBetween('tanggal', [
                    $startDate->toDateString(),
                    $endDate->toDateString()
                ]);
            }

            $topPerformers = $topPerformersQuery
                ->when($role == 'user', function($q) {
                    $q->whereHas('pegawai', function($q2) {
                        $q2->where('role', 'user');
                    });
                })
                ->groupBy('id_pegawai')
                ->orderBy('total_weight', 'desc')
                ->limit(5)
                ->get()
                ->map(function($item) {
                    $item->total_hadir = $item->total_days;
                    return $item;
                });
        }

        // Check jika ada akses ke data panen
        $hasPalmAccess = !$role || $role == 'user';

        // Ringkasan hari ini untuk tampilan
        $todayAttendanceCount = Attendance::whereDate('date', $today->toDateString())
            ->whereNotNull('check_in')
            ->when($role, function($q) use ($role) {
                $q->whereHas('user', function($q2) use ($role) {
                    $q2->where('role', $role);
                });
            }, function($q) {
                $q->whereHas('user', function($q2) {
                    $q2->whereIn('role', ['user', 'security', 'cleaning', 'kantoran']);
                });
            })
            ->distinct('user_id')
            ->count('user_id');

        $todayPalmWeight = CatatanPanen::whereDate('tanggal', $today->toDateString())
            ->when($role == 'user', function($q) {
                $q->whereHas('pegawai', function($q2) {
                    $q2->where('role', 'user');
                });
            }, function($q) {
                $q->whereHas('pegawai', function($q2) {
                    $q2->where('role', 'user');
                });
            })
            ->sum('berat_kg') ?? 0;

        return view('admin.laporan', compact(
            'startDate',
            'endDate',
            'role',
            'dataType',
            'totalPegawai',
            'totalPalmWeight',
            'averagePalmWeight',
            'totalHadir',
            'dailyPalmWeight',
            'dailyAttendance',
            'topPerformers',
            'detailedAttendances',
            'hasPalmAccess',
            'todayAttendanceCount',
            'todayPalmWeight'
        ));
    }

    public function managerHapusPegawai($id)
    {
        if (Auth::user()->role != 'manager') return redirect('/');

        $pegawai = User::findOrFail($id);

        // Cek apakah pegawai memiliki riwayat
        $hasAttendance = Attendance::where('user_id', $id)->exists();
        $hasPanen = CatatanPanen::where('id_pegawai', $id)->exists();
        $hasRapot = \App\Models\Rapot::where('id_user', $id)->exists();

        if ($hasAttendance || $hasPanen || $hasRapot) {
            // Kirim ke view dengan data riwayat untuk konfirmasi force delete
            return redirect()->route('manager.pegawai')->with('warning',
                'Pegawai memiliki riwayat data. Gunakan Hapus Paksa untuk menghapus semua data terkait.');
        }

        $pegawai->delete();

        return redirect()->route('manager.pegawai')->with('success', 'Pegawai berhasil dihapus!');
    }

    /**
     * Menghapus pegawai secara paksa beserta semua riwayatnya
     */
    public function managerForceDeletePegawai(Request $request, $id)
    {
        if (Auth::user()->role != 'manager') return redirect('/');

        $pegawai = User::findOrFail($id);

        // Validasi konfirmasi
        if (!$request->has('confirm_delete') || $request->confirm_delete !== 'YA') {
            return redirect()->route('manager.pegawai')->with('error',
                'Konfirmasi tidak valid. Harap centang konfirmasi dan ketik YA.');
        }

        // Mulai transaksi database
        DB::beginTransaction();

        try {
            $pegawaiName = $pegawai->name;
            $pegawaiId = $pegawai->id;

            // Hapus riwayat rapot terlebih dahulu (jika ada foreign key constraint)
            if (class_exists('\App\Models\Rapot')) {
                \App\Models\Rapot::where('id_user', $pegawaiId)->delete();
                \App\Models\Rapot::where('evaluator_id', $pegawaiId)->update(['evaluator_id' => null]);
            }

            // Hapus riwayat panen
            CatatanPanen::where('id_pegawai', $pegawaiId)->delete();

            // Hapus riwayat absensi
            Attendance::where('user_id', $pegawaiId)->delete();

            // Hapus riwayat pengumuman yang dibuat (jika ada)
            if (class_exists('\App\Models\Announcement')) {
                \App\Models\Announcement::where('created_by', $pegawaiId)->update(['created_by' => null]);
            }

            // Hapus user
            $pegawai->delete();

            // Commit transaksi
            DB::commit();

            return redirect()->route('manager.pegawai')->with('success',
                "Pegawai <strong>$pegawaiName</strong> berhasil dihapus beserta semua riwayatnya!");

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Force delete pegawai gagal: ' . $e->getMessage());

            return redirect()->route('manager.pegawai')->with('error',
                'Terjadi kesalahan saat menghapus pegawai: ' . $e->getMessage());
        }
    }

    public function managerLog(Request $request)
    {
        // Validasi role
        if (Auth::user()->role !== 'manager') {
            return redirect('/');
        }

        $today = now('Asia/Jakarta')->startOfDay();

        // ── Handle date filter ─────────────────────────────────────────────────
        $dateFilter = $request->input('date_filter', 'today');

        if ($dateFilter === 'all') {
            $selectedDate = null;
        } elseif ($dateFilter === 'custom') {
            $selectedDate = $request->date
                ? Carbon::parse($request->date, 'Asia/Jakarta')->startOfDay()
                : $today;
        } else {
            // Default: today
            $dateFilter   = 'today';
            $selectedDate = $today;
        }

        // ── Query utama attendance (untuk tabel + paginasi) ────────────────────
        $query = Attendance::with('user');

        if ($dateFilter !== 'all') {
            $query->whereDate('date', $selectedDate->toDateString());
        }

        // Filter role
        if ($request->filled('role')) {
            $query->whereHas('user', fn($q) => $q->where('role', $request->role));
        } else {
            // Pastikan hanya pegawai (bukan admin/manager)
            $query->whereHas('user', fn($q) => $q->whereIn('role', ['user', 'security', 'cleaning', 'kantoran']));
        }

        // Filter status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter nama / no_hp
        if ($request->filled('search')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('no_hp', 'like', "%{$request->search}%");
            });
        }

        $attendances = $query
            ->orderBy('date', 'desc')
            ->orderBy('check_in', 'desc')
            ->paginate(10)
            ->appends($request->except('page'));

        // ── Statistik ──────────────────────────────────────────────────────────

        // Total pegawai (fresh query, tidak tercampur kondisi lain)
        $totalPegawai = User::whereIn('role', ['user', 'security', 'cleaning', 'kantoran'])
            ->when($request->filled('role'), fn($q) => $q->where('role', $request->role))
            ->count();

        // Helper: closure filter role pada Attendance
        // Selalu batasi hanya ke role pegawai agar admin/manager tidak ikut terhitung
        $applyRoleFilter = function ($q) use ($request) {
            if ($request->filled('role')) {
                $q->whereHas('user', fn($q2) => $q2->where('role', $request->role));
            } else {
                $q->whereHas('user', fn($q2) => $q2->whereIn('role', ['user', 'security', 'cleaning', 'kantoran']));
            }
        };

        if ($dateFilter === 'all') {

            // ── Filter: Semua Tanggal ──────────────────────────────────────────

            // Total Tepat Waktu = jumlah record dengan status tepat waktu (semua tanggal)
            $totalHadir = Attendance::where('status', 'tepat waktu')
                ->tap($applyRoleFilter)
                ->count();

            // Total Terlambat = jumlah record dengan status terlambat
            $totalTerlambat = Attendance::where('status', 'terlambat')
                ->tap($applyRoleFilter)
                ->count();

            // Alpha = pegawai yang BELUM PERNAH hadir sama sekali (tidak punya record check_in)
            $pernahHadirIds = Attendance::whereNotNull('check_in')
                ->tap($applyRoleFilter)
                ->pluck('user_id')
                ->unique()
                ->toArray();

            $totalAlpha = User::whereIn('role', ['user', 'security', 'cleaning', 'kantoran'])
                ->when($request->filled('role'), fn($q) => $q->where('role', $request->role))
                ->whereNotIn('id', $pernahHadirIds)
                ->count();

        } else {

            // ── Filter: Hari Ini / Custom ──────────────────────────────────────

            $date = $selectedDate->toDateString();

            // Total Tepat Waktu pada tanggal tersebut
            $totalHadir = Attendance::whereDate('date', $date)
                ->where('status', 'tepat waktu')
                ->tap($applyRoleFilter)
                ->distinct('user_id')
                ->count('user_id');

            // Total Terlambat pada tanggal tersebut
            $totalTerlambat = Attendance::whereDate('date', $date)
                ->where('status', 'terlambat')
                ->tap($applyRoleFilter)
                ->count();

            // Alpha = pegawai yang tidak punya record hadir pada tanggal tersebut
            $hadirIds = Attendance::whereDate('date', $date)
                ->whereNotNull('check_in')
                ->tap($applyRoleFilter)
                ->pluck('user_id')
                ->toArray();

            // PENTING: fresh query — tidak mereuse query yang sudah di-count sebelumnya
            $totalAlpha = User::whereIn('role', ['user', 'security', 'cleaning', 'kantoran'])
                ->when($request->filled('role'), fn($q) => $q->where('role', $request->role))
                ->whereNotIn('id', $hadirIds)
                ->count();
        }

        // ── Display date untuk heading tabel ──────────────────────────────────
        if ($dateFilter === 'all') {
            $displayDate = 'Semua Tanggal';
        } elseif ($dateFilter === 'custom' && $selectedDate) {
            $displayDate = $selectedDate->toDateString();
        } else {
            $displayDate = $today->toDateString();
        }

        return view('manager.log', compact(
            'attendances',
            'totalPegawai',
            'totalHadir',
            'totalTerlambat',
            'totalAlpha',
            'selectedDate',
            'displayDate',
            'dateFilter'
        ));
    }

    public function laporanManager(Request $request)
    {
        if (Auth::user()->role !== 'manager') {
            return redirect('/');
        }

        $today = now('Asia/Jakarta')->startOfDay();

        $startDate = $request->start_date
            ? Carbon::parse($request->start_date)->startOfDay()
            : $today->copy()->startOfMonth();

        $endDate = $request->end_date
            ? Carbon::parse($request->end_date)->endOfDay()
            : $today->copy()->endOfMonth();

        $role = $request->input('role');
        $dataType = $request->input('data_type', 'today'); // Tambahkan data_type

        // Query untuk detail attendance dengan data_type filter
        $query = Attendance::with('user')
            ->whereNotNull('check_in'); // Hanya yang sudah check_in

        // Filter berdasarkan data_type
        if ($dataType == 'today') {
            $query->whereDate('date', $today->toDateString());
        } else {
            $query->whereBetween('date', [
                $startDate->toDateString(),
                $endDate->toDateString()
            ]);
        }

        // Filter berdasarkan role jika dipilih
        if ($role) {
            $query->whereHas('user', function($q) use ($role) {
                $q->where('role', $role);
            });
        } else {
            // Default hanya role user, security, cleaning, kantoran untuk manager
            $query->whereHas('user', function($q) {
                $q->whereIn('role', ['user', 'security', 'cleaning', 'kantoran']);
            });
        }

        // Ambil data detail attendance
        $detailedAttendances = $query->orderBy('date', 'desc')
            ->orderBy('check_in', 'desc')
            ->paginate(20)
            ->appends($request->except('page'));

        // Statistik utama
        $userQuery = User::whereIn('role', ['user', 'security', 'cleaning', 'kantoran']);
        if ($role) {
            $userQuery->where('role', $role);
        }
        $totalPegawai = $userQuery->count();

        // Query total berat sawit yang benar
        $totalPalmWeight = 0;
        $averagePalmWeight = 0;

        // Hanya hitung jika role 'user' atau tidak ada filter
        if (!$role || $role == 'user') {
            // Query untuk total berat sawit dari CatatanPanen
            $palmQuery = CatatanPanen::query();

            // Filter berdasarkan periode
            if ($dataType == 'today') {
                $palmQuery->whereDate('tanggal', $today->toDateString());
            } else {
                $palmQuery->whereBetween('tanggal', [
                    $startDate->toDateString(),
                    $endDate->toDateString()
                ]);
            }

            if ($role == 'user') {
                // Filter berdasarkan user yang role 'user'
                $palmQuery->whereHas('pegawai', function($q) {
                    $q->where('role', 'user');
                });
            } else {
                // Untuk manager, default hanya user
                $palmQuery->whereHas('pegawai', function($q) {
                    $q->where('role', 'user');
                });
            }

            $totalPalmWeight = $palmQuery->sum('berat_kg') ?? 0;

            // Hitung rata-rata berdasarkan jumlah user dengan panen
            $countPanen = $palmQuery->distinct('id_pegawai')->count('id_pegawai');
            $averagePalmWeight = $countPanen > 0 ? round($totalPalmWeight / $countPanen, 2) : 0;
        }

        // Total kehadiran
        $hadirQuery = Attendance::whereNotNull('check_in');

        // Filter berdasarkan periode
        if ($dataType == 'today') {
            $hadirQuery->whereDate('date', $today->toDateString());
        } else {
            $hadirQuery->whereBetween('date', [
                $startDate->toDateString(),
                $endDate->toDateString()
            ]);
        }

        if ($role) {
            $hadirQuery->whereHas('user', function($q) use ($role) {
                $q->where('role', $role);
            });
        } else {
            $hadirQuery->whereHas('user', function($q) {
                $q->whereIn('role', ['user', 'security', 'cleaning', 'kantoran']);
            });
        }

        $totalHadir = $hadirQuery->distinct('user_id')->count('user_id');

        // Data untuk chart panen harian (7 hari terakhir DARI HARI INI)
        $chartEndDate = now('Asia/Jakarta')->startOfDay();
        $chartStartDate = $chartEndDate->copy()->subDays(6);

        $dailyPalmWeight = CatatanPanen::select(
                DB::raw('DATE(tanggal) as date'),
                DB::raw('SUM(berat_kg) as total_weight')
            )
            ->whereBetween('tanggal', [
                $chartStartDate->toDateString(),
                $chartEndDate->toDateString()
            ])
            ->whereHas('pegawai', function($q) use ($role) {
                if ($role) {
                    $q->where('role', $role);
                } else {
                    $q->where('role', 'user');
                }
            })
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Data kehadiran harian (7 hari terakhir DARI HARI INI)
        $dailyAttendance = Attendance::select(
                DB::raw('DATE(date) as date'),
                DB::raw('COUNT(DISTINCT user_id) as total')
            )
            ->whereBetween('date', [
                $chartStartDate->toDateString(),
                $chartEndDate->toDateString()
            ])
            ->whereNotNull('check_in')
            ->when($role, function($q) use ($role) {
                $q->whereHas('user', function($q2) use ($role) {
                    $q2->where('role', $role);
                });
            }, function($q) {
                $q->whereHas('user', function($q2) {
                    $q2->whereIn('role', ['user', 'security', 'cleaning', 'kantoran']);
                });
            })
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Top performers - berdasarkan CatatanPanen
        $topPerformers = collect();
        if (!$role || $role == 'user') {
            $topPerformersQuery = CatatanPanen::with('pegawai')
                ->select(
                    'id_pegawai',
                    DB::raw('SUM(berat_kg) as total_weight'),
                    DB::raw('COUNT(*) as total_days')
                );

            // Filter berdasarkan periode
            if ($dataType == 'today') {
                $topPerformersQuery->whereDate('tanggal', $today->toDateString());
            } else {
                $topPerformersQuery->whereBetween('tanggal', [
                    $startDate->toDateString(),
                    $endDate->toDateString()
                ]);
            }

            $topPerformers = $topPerformersQuery
                ->when($role == 'user', function($q) {
                    $q->whereHas('pegawai', function($q2) {
                        $q2->where('role', 'user');
                    });
                }, function($q) {
                    $q->whereHas('pegawai', function($q2) {
                        $q2->where('role', 'user');
                    });
                })
                ->groupBy('id_pegawai')
                ->orderBy('total_weight', 'desc')
                ->limit(5)
                ->get()
                ->map(function($item) {
                    $item->total_hadir = $item->total_days;
                    return $item;
                });
        }

        // Check jika ada akses ke data panen
        $hasPalmAccess = !$role || $role == 'user';

        // Ringkasan hari ini untuk tampilan
        $todayAttendanceCount = Attendance::whereDate('date', $today->toDateString())
            ->whereNotNull('check_in')
            ->when($role, function($q) use ($role) {
                $q->whereHas('user', function($q2) use ($role) {
                    $q2->where('role', $role);
                });
            }, function($q) {
                $q->whereHas('user', function($q2) {
                    $q2->whereIn('role', ['user', 'security', 'cleaning', 'kantoran']);
                });
            })
            ->distinct('user_id')
            ->count('user_id');

        $todayPalmWeight = CatatanPanen::whereDate('tanggal', $today->toDateString())
            ->when($role == 'user', function($q) {
                $q->whereHas('pegawai', function($q2) {
                    $q2->where('role', 'user');
                });
            }, function($q) {
                $q->whereHas('pegawai', function($q2) {
                    $q2->where('role', 'user');
                });
            })
            ->sum('berat_kg') ?? 0;

        return view('manager.laporan', compact(
            'startDate',
            'endDate',
            'role',
            'dataType',
            'totalPegawai',
            'totalPalmWeight',
            'averagePalmWeight',
            'totalHadir',
            'dailyPalmWeight',
            'dailyAttendance',
            'topPerformers',
            'detailedAttendances',
            'hasPalmAccess',
            'todayAttendanceCount',
            'todayPalmWeight'
        ));
    }

    public function userRiwayat()
    {
        $userId = Auth::id();

        $attendances = Attendance::where('user_id', $userId)
            ->orderBy('date', 'desc')
            ->paginate(10);

        $panenHistory = CatatanPanen::where('id_pegawai', $userId)
            ->orderBy('tanggal', 'desc')
            ->paginate(10);

        return view('user.riwayat', compact('attendances', 'panenHistory'));
    }

    public function userAbsen(Request $request)
    {
        $request->validate([
            'photos' => 'nullable|array',
            'photos.*' => 'image|mimes:jpeg,png,jpg|max:2048',
            'description' => 'nullable|string|max:255',
        ]);

        $today = now('Asia/Jakarta')->startOfDay();
        $userId = Auth::id();

        // Cek apakah sudah absen hari ini
        $existingAttendance = Attendance::where('user_id', $userId)
            ->whereDate('date', $today->toDateString())
            ->first();

        if ($existingAttendance) {
            return redirect()->route('user.dashboard')->with('error', 'Anda sudah absen hari ini!');
        }

        // Upload foto jika ada
        $photoPaths = [];
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $photo) {
                $path = $photo->store('attendance-photos', 'public');
                $photoPaths[] = $path;
            }
        }

        // Hitung keterlambatan
        $checkInTime = now('Asia/Jakarta');
        $jamMasuk = Carbon::createFromTime(7, 0, 0, 'Asia/Jakarta');
        $status = $checkInTime->greaterThan($jamMasuk) ? 'terlambat' : 'tepat waktu';

        // Simpan absensi
        Attendance::create([
            'user_id' => $userId,
            'date' => $today->toDateString(),
            'check_in' => $checkInTime->toTimeString(),
            'status' => $status,
            'photos' => !empty($photoPaths) ? json_encode($photoPaths) : null,
            'description' => $request->description ?? null,
        ]);

        return redirect()->route('user.dashboard')->with('success', 'Absen berhasil!');
    }

    public function exportAllCsv()
    {
        // Cek apakah user adalah admin atau manager
        $user = Auth::user();
        if (!in_array($user->role, ['admin', 'manager'])) {
            abort(403, 'Unauthorized action.');
        }

        $from = request('from');
        $to   = request('to');

        // Validasi tanggal
        if (!$from || !$to) {
            return redirect()->back()->with('error', 'Harap pilih tanggal mulai dan tanggal akhir');
        }

        try {
            \Carbon\Carbon::parse($from);
            \Carbon\Carbon::parse($to);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Format tanggal tidak valid');
        }

        return Excel::download(
            new \App\Exports\RekapSemuaExport($from, $to),
            "rekap_semua_{$from}_{$to}.xlsx"
        );
    }

    public function exportAllCsvAllTime()
    {
        // Cek apakah user adalah admin atau manager
        $user = Auth::user();
        if (!in_array($user->role, ['admin', 'manager'])) {
            abort(403, 'Unauthorized action.');
        }

        // Ambil tanggal pertama dan terakhir dari database
        $firstAttendance = \App\Models\Attendance::min('date');
        $lastAttendance = \App\Models\Attendance::max('date');

        $firstPanen = \App\Models\CatatanPanen::min('tanggal');
        $lastPanen = \App\Models\CatatanPanen::max('tanggal');

        $from = min($firstAttendance, $firstPanen) ?: now()->subMonth()->format('Y-m-d');
        $to = max($lastAttendance, $lastPanen) ?: now()->format('Y-m-d');

        return Excel::download(
            new \App\Exports\RekapSemuaExport($from, $to),
            "rekap_semua_data.xlsx"
        );
    }

    public function exportSheetAbsen()
    {
        // Cek apakah user adalah admin atau manager
        $user = Auth::user();
        if (!in_array($user->role, ['admin', 'manager'])) {
            abort(403, 'Unauthorized action.');
        }

        $from = request('from');
        $to   = request('to');

        // Validasi tanggal
        if (!$from || !$to) {
            return redirect()->back()->with('error', 'Harap pilih tanggal mulai dan tanggal akhir');
        }

        return Excel::download(
            new \App\Exports\SheetAbsenExport($from, $to),
            "rekap_absen_per_pegawai_{$from}_{$to}.xlsx"
        );
    }

    public function userInputPanen(Request $request)
    {
        $request->validate([
            'berat_kg' => 'required|numeric|min:0.1',
            'keterangan' => 'nullable|string|max:255',
        ]);

        CatatanPanen::create([
            'id_pegawai' => Auth::id(),
            'tanggal' => now('Asia/Jakarta')->toDateString(),
            'berat_kg' => $request->berat_kg,
            'keterangan' => $request->keterangan,
        ]);

        return redirect()->route('user.dashboard')->with('success', 'Data panen berhasil disimpan!');
    }
}
