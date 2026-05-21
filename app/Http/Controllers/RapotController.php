<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Rapot;
use App\Models\Attendance;
use App\Models\KinerjaCleaning;
use App\Models\PatroliSecurity;
use Carbon\Carbon;
use PDF;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

class RapotController extends Controller
{
    /**
     * ADMIN - LIST PEGAWAI UNTUK EVALUASI
     */
    public function index()
    {
        $users = User::whereIn('role', ['security', 'cleaning', 'kantoran', 'user'])
            ->where('id', '!=', Auth::id())
            ->orderBy('name')
            ->paginate(15);

        $rapots = Rapot::with('user')
            ->latest()
            ->take(10)
            ->get();

        return view('rapot.admin.index', compact('users', 'rapots'));
    }

    /**
     * FORM EVALUASI KINERJA PEGAWAI - FIXED VERSION
     */
    public function create(User $user)
    {
        if (!in_array(Auth::user()->role, ['admin', 'manager'])) {
            abort(403, 'Unauthorized access.');
        }

        $periodeStart = Carbon::now()->startOfMonth();
        $periodeEnd = Carbon::now()->endOfMonth();
        $periode = $periodeStart->format('d M Y') . ' - ' . $periodeEnd->format('d M Y');

        $absen = Attendance::where('user_id', $user->id)
            ->whereDate('date', '>=', $periodeStart)
            ->whereDate('date', '<=', $periodeEnd)
            ->orderBy('date', 'asc')
            ->get();

        Log::info('=== CREATE EVALUASI FORM ===', [
            'user' => $user->name,
            'user_id' => $user->id,
            'periode' => $periode,
            'total_absen' => $absen->count(),
            'periode_start' => $periodeStart->format('Y-m-d'),
            'periode_end' => $periodeEnd->format('Y-m-d')
        ]);

        $totalJam = 0;
        $hariKerja = 0;
        $hariHadir = 0;
        $totalTerlambat = 0;
        $detailAbsen = [];

        foreach ($absen as $index => $a) {
            // Debug: Tampilkan data mentah dari database
            Log::info("Processing absen #{$index}", [
                'date' => $a->date,
                'check_in' => $a->check_in,
                'check_out' => $a->check_out,
                'status' => $a->status
            ]);

            // Cek apakah check_in dan check_out valid
            if ($a->check_in && $a->check_out) {
                $hariHadir++;

                // Ekstrak hanya waktu (H:i) dari data
                $checkInTime = $this->extractTimeOnly($a->check_in);
                $checkOutTime = $this->extractTimeOnly($a->check_out);

                Log::info("Extracted times for absen #{$index}", [
                    'check_in_original' => $a->check_in,
                    'check_out_original' => $a->check_out,
                    'check_in_time' => $checkInTime,
                    'check_out_time' => $checkOutTime
                ]);

                // Hitung jam kerja
                $jamHariIni = $this->calculateWorkingHours($checkInTime, $checkOutTime);

                Log::info("Calculated hours for absen #{$index}", [
                    'jam_hari_ini' => $jamHariIni,
                    'check_in' => $checkInTime,
                    'check_out' => $checkOutTime
                ]);

                if ($jamHariIni > 0) {
                    $totalJam += $jamHariIni;
                    $hariKerja++;
                }

                if ($a->status == 'terlambat') {
                    $totalTerlambat++;
                }

                $detailAbsen[] = [
                    'tanggal' => $a->date->format('d/m/Y'),
                    'check_in' => $checkInTime,
                    'check_out' => $checkOutTime,
                    'jam_kerja' => $jamHariIni,
                    'status' => $a->status ?? 'hadir'
                ];
            } else {
                Log::warning("Absen #{$index} has incomplete data", [
                    'check_in' => $a->check_in,
                    'check_out' => $a->check_out
                ]);
            }
        }

        Log::info('=== SUMMARY ===', [
            'total_jam' => $totalJam,
            'hari_kerja' => $hariKerja,
            'hari_hadir' => $hariHadir,
            'total_terlambat' => $totalTerlambat,
            'detail_absen_count' => count($detailAbsen)
        ]);

        return view('rapot.admin.evaluasi', compact(
            'user',
            'periode',
            'hariKerja',
            'totalJam',
            'periodeStart',
            'periodeEnd',
            'detailAbsen',
            'hariHadir',
            'totalTerlambat'
        ));
    }

    /**
     * EKSTRAK HANYA WAKTU (H:i) DARI INPUT
     */
    private function extractTimeOnly($timeInput)
    {
        if (empty($timeInput)) {
            return null;
        }

        // Jika sudah dalam format H:i (08:00)
        if (is_string($timeInput) && preg_match('/^\d{1,2}:\d{2}$/', $timeInput)) {
            return $timeInput;
        }

        // Jika Carbon object
        if ($timeInput instanceof Carbon) {
            return $timeInput->format('H:i');
        }

        // Jika string datetime (2025-12-17 08:11:00)
        if (is_string($timeInput)) {
            try {
                // Coba parse dengan Carbon
                $carbonTime = Carbon::parse($timeInput);
                return $carbonTime->format('H:i');
            } catch (\Exception $e) {
                Log::warning('Cannot parse time input: ' . $e->getMessage(), [
                    'time_input' => $timeInput
                ]);

                // Coba ekstrak manual dari string
                if (str_contains($timeInput, ' ')) {
                    $parts = explode(' ', $timeInput);
                    if (count($parts) >= 2) {
                        $timePart = $parts[1];
                        $timeComponents = explode(':', $timePart);
                        if (count($timeComponents) >= 2) {
                            return $timeComponents[0] . ':' . $timeComponents[1];
                        }
                    }
                }
            }
        }

        return null;
    }

    /**
     * HITUNG JAM KERJA - SIMPLE & RELIABLE
     */
    private function calculateWorkingHours($checkIn, $checkOut)
    {
        if (empty($checkIn) || empty($checkOut)) {
            return 0;
        }

        Log::info('calculateWorkingHours INPUT', [
            'check_in' => $checkIn,
            'check_out' => $checkOut
        ]);

        try {
            // Parse jam dan menit
            list($inHour, $inMinute) = explode(':', $checkIn);
            list($outHour, $outMinute) = explode(':', $checkOut);

            $inHour = intval($inHour);
            $inMinute = intval($inMinute);
            $outHour = intval($outHour);
            $outMinute = intval($outMinute);

            // Konversi ke menit dari midnight
            $inTotalMinutes = ($inHour * 60) + $inMinute;
            $outTotalMinutes = ($outHour * 60) + $outMinute;

            // Jika check out lebih kecil dari check in (shift malam), tambah 24 jam
            if ($outTotalMinutes < $inTotalMinutes) {
                $outTotalMinutes += (24 * 60); // Tambah 24 jam dalam menit
            }

            // Hitung total menit kerja
            $totalMinutes = $outTotalMinutes - $inTotalMinutes;

            // Kurangi waktu istirahat (60 menit = 1 jam)
            $istirahatMinutes = 60;
            $effectiveMinutes = max(0, $totalMinutes - $istirahatMinutes);

            // Konversi ke jam dengan 2 desimal
            $hours = $effectiveMinutes / 60;
            $roundedHours = round($hours, 2);

            Log::info('calculateWorkingHours RESULT', [
                'check_in' => "{$inHour}:{$inMinute}",
                'check_out' => "{$outHour}:{$outMinute}",
                'in_total_minutes' => $inTotalMinutes,
                'out_total_minutes' => $outTotalMinutes,
                'total_minutes' => $totalMinutes,
                'effective_minutes' => $effectiveMinutes,
                'hours' => $hours,
                'rounded_hours' => $roundedHours
            ]);

            return $roundedHours;

        } catch (\Exception $e) {
            Log::error('Error in calculateWorkingHours: ' . $e->getMessage(), [
                'check_in' => $checkIn,
                'check_out' => $checkOut,
                'trace' => $e->getTraceAsString()
            ]);
            return 0;
        }
    }

    /**
     * SIMPAN EVALUASI KINERJA DAN BUAT RAPOT
     */
    public function store(Request $request, User $user)
    {
        if (!in_array(Auth::user()->role, ['admin', 'manager'])) {
            abort(403, 'Unauthorized access.');
        }

        $request->validate([
            'periode_start' => 'required|date',
            'periode_end' => 'required|date|after_or_equal:periode_start',
            'evaluasi_kerja' => 'required|string|min:10',
            'saran_perbaikan' => 'required|string|min:10',
            'catatan' => 'nullable|string|max:1000',
            'status' => 'required|in:draft,dikirim,selesai'
        ]);

        $start = Carbon::parse($request->periode_start)->startOfDay();
        $end = Carbon::parse($request->periode_end)->endOfDay();

        $absen = Attendance::where('user_id', $user->id)
            ->whereBetween('date', [$start, $end])
            ->get();

        $totalJam = 0;
        $hariKerja = 0;
        $hariHadir = 0;
        $totalTerlambat = 0;
        $detailAbsen = [];

        foreach ($absen as $a) {
            if ($a->check_in && $a->check_out) {
                $hariHadir++;

                $checkInTime = $this->extractTimeOnly($a->check_in);
                $checkOutTime = $this->extractTimeOnly($a->check_out);
                $jamHariIni = $this->calculateWorkingHours($checkInTime, $checkOutTime);

                if ($jamHariIni > 0) {
                    $totalJam += $jamHariIni;
                    $hariKerja++;
                }

                if ($a->status == 'terlambat') {
                    $totalTerlambat++;
                }

                $detailAbsen[] = [
                    'tanggal' => $a->date->format('d/m/Y'),
                    'check_in' => $checkInTime,
                    'check_out' => $checkOutTime,
                    'jam_kerja' => $jamHariIni,
                    'status' => $a->status ?? 'hadir'
                ];
            }
        }

        $totalHariPeriode = $end->diffInDays($start) + 1;
        $persentaseKehadiran = $hariHadir > 0 ? ($hariHadir / $totalHariPeriode) * 100 : 0;
        $nilaiAkhir = round($persentaseKehadiran, 2);
        $nilaiSkala10 = round(($nilaiAkhir / 100) * 10, 1);
        $statusRapot = $this->tentukanStatus($nilaiSkala10);

        $dataEvaluasi = [
            'evaluasi_kerja' => $request->evaluasi_kerja,
            'saran_perbaikan' => $request->saran_perbaikan,
            'nilai_akhir' => $nilaiAkhir,
            'nilai_skala_10' => $nilaiSkala10,
            'hari_hadir' => $hariHadir,
            'total_jam_kerja' => $totalJam,
            'rata_jam_perhari' => $hariKerja > 0 ? round($totalJam / $hariKerja, 2) : 0,
            'total_terlambat' => $totalTerlambat,
            'persentase_kehadiran' => round($persentaseKehadiran, 2),
            'total_hari_periode' => $totalHariPeriode,
            'status_evaluasi' => $request->status
        ];

        $userField = $this->getUserFieldName();

        $rapotData = [
            $userField       => $user->id,
            'evaluator_id'  => Auth::id(),
            'periode'       => "{$start->format('d M Y')} - {$end->format('d M Y')}",
            'periode_start' => $start,
            'periode_end'   => $end,
            'total_jam'     => $totalJam,
            'hari_kerja'    => $hariKerja,
            'hari_hadir'    => $hariHadir,
            'nilai'         => $nilaiAkhir,
            'rata_rata'     => $nilaiSkala10,
            'status'        => $statusRapot,
            'catatan'       => $request->catatan ?? '',
            'detail_absen'  => json_encode($detailAbsen),
            'data_evaluasi' => json_encode($dataEvaluasi),
            'evaluasi_kerja' => $request->evaluasi_kerja,
            'saran_perbaikan' => $request->saran_perbaikan,
            'generated_at'  => now(),
            'tipe'          => 'evaluasi_kinerja',
        ];

        $rapot = Rapot::create($rapotData);

        $user->update(['last_evaluated_at' => now()]);

        return redirect()->route('admin.rapot.index')
            ->with('success', "✅ Evaluasi kinerja untuk {$user->name} berhasil disimpan!");
    }

    /**
     * TENTUKAN STATUS BERDASARKAN NILAI AKHIR
     */
    private function tentukanStatus($nilai)
    {
        if ($nilai >= 9.0) return 'Sangat Baik';
        if ($nilai >= 8.0) return 'Baik';
        if ($nilai >= 7.0) return 'Cukup Baik';
        if ($nilai >= 6.0) return 'Cukup';
        return 'Perlu Perbaikan';
    }

    /**
     * SIMPAN EVALUASI KINERJA (Alias untuk store)
     */
    public function storeEvaluasi(Request $request, User $user)
    {
        return $this->store($request, $user);
    }

    /**
     * DETEKSI NAMA KOLOM USER DI DATABASE
     */
    private function getUserFieldName()
    {
        if (Schema::hasColumn('rapots', 'user_id')) {
            return 'user_id';
        }
        return 'id_user';
    }

    /**
     * USER RAPOT LIST
     */
    public function indexUser()
    {
        $userField = $this->getUserFieldName();
        $rapots = Rapot::where($userField, Auth::id())
            ->orderBy('periode_start', 'desc')
            ->paginate(10);

        return view('rapot.user', compact('rapots'));
    }

    /**
     * DETAIL RAPOT
     */
    public function show(Rapot $rapot)
    {
        $userField = $this->getUserFieldName();
        $userFieldValue = $rapot->$userField ?? null;

        if (!Auth::check() || (Auth::user()->role != 'admin' && $userFieldValue != Auth::id())) {
            abort(403, 'Unauthorized');
        }

        $detailAbsen = $rapot->detail_absen;
        if (is_string($detailAbsen)) {
            $detailAbsen = json_decode($detailAbsen, true) ?? [];
        }
        if (!is_array($detailAbsen)) {
            $detailAbsen = [];
        }

        $dataEvaluasi = $rapot->data_evaluasi;
        if (is_string($dataEvaluasi)) {
            $dataEvaluasi = json_decode($dataEvaluasi, true) ?? [];
        }
        if (!is_array($dataEvaluasi)) {
            $dataEvaluasi = [];
        }

        $cleaningEvidence = KinerjaCleaning::where('user_id', $userFieldValue)
            ->whereBetween('tanggal', [$rapot->periode_start, $rapot->periode_end])
            ->orderBy('tanggal', 'desc')
            ->get();

        $patrolEvidence = PatroliSecurity::where('user_id', $userFieldValue)
            ->whereBetween('waktu_patroli', [$rapot->periode_start, $rapot->periode_end])
            ->orderBy('waktu_patroli', 'desc')
            ->get();

        if ($rapot->tipe === 'evaluasi_kinerja') {
            return view('rapot.show_evaluasi', compact('rapot', 'detailAbsen', 'dataEvaluasi', 'cleaningEvidence', 'patrolEvidence'));
        }

        return view('rapot.show_evaluasi', compact('rapot', 'detailAbsen', 'cleaningEvidence', 'patrolEvidence'));
    }

    /**
     * DETAIL RAPOT EVALUASI
     */
    public function showEvaluasi(Rapot $rapot)
    {
        $userField = $this->getUserFieldName();
        $userFieldValue = $rapot->$userField ?? null;

        if (!Auth::check() || (Auth::user()->role != 'admin' && $userFieldValue != Auth::id())) {
            abort(403, 'Unauthorized');
        }

        $detailAbsen = $rapot->detail_absen;
        if (is_string($detailAbsen)) {
            $detailAbsen = json_decode($detailAbsen, true) ?? [];
        }
        if (!is_array($detailAbsen)) {
            $detailAbsen = [];
        }

        $dataEvaluasi = $rapot->data_evaluasi;
        if (is_string($dataEvaluasi)) {
            $dataEvaluasi = json_decode($dataEvaluasi, true) ?? [];
        }
        if (!is_array($dataEvaluasi)) {
            $dataEvaluasi = [];
        }

        $cleaningEvidence = KinerjaCleaning::where('user_id', $rapot->$userField)
            ->whereBetween('tanggal', [$rapot->periode_start, $rapot->periode_end])
            ->orderBy('tanggal', 'desc')
            ->get();

        $patrolEvidence = PatroliSecurity::where('user_id', $rapot->$userField)
            ->whereBetween('waktu_patroli', [$rapot->periode_start, $rapot->periode_end])
            ->orderBy('waktu_patroli', 'desc')
            ->get();

        return view('rapot.show_evaluasi', compact('rapot', 'detailAbsen', 'dataEvaluasi', 'cleaningEvidence', 'patrolEvidence'));
    }

    /**
     * GENERATE RAPOT OTOMATIS
     */
    public function generateRapot(Request $request, User $user)
    {
        if (!in_array(Auth::user()->role, ['admin', 'manager'])) {
            abort(403, 'Unauthorized');
        }

        $request->validate([
            'periode_start' => 'required|date',
            'periode_end' => 'required|date|after_or_equal:periode_start',
            'catatan' => 'nullable|string|max:500'
        ]);

        $start = Carbon::parse($request->periode_start)->startOfDay();
        $end = Carbon::parse($request->periode_end)->endOfDay();

        $absen = Attendance::where('user_id', $user->id)
            ->whereBetween('date', [$start, $end])
            ->get();

        $totalJam = 0;
        $hariKerja = 0;
        $hariHadir = 0;
        $totalTerlambat = 0;
        $detailAbsen = [];

        foreach ($absen as $a) {
            if ($a->check_in && $a->check_out) {
                $hariHadir++;

                $checkInTime = $this->extractTimeOnly($a->check_in);
                $checkOutTime = $this->extractTimeOnly($a->check_out);
                $jamHariIni = $this->calculateWorkingHours($checkInTime, $checkOutTime);

                $totalJam += max(0, $jamHariIni);

                if ($jamHariIni >= 4) {
                    $hariKerja++;
                }

                if ($a->status == 'terlambat') {
                    $totalTerlambat++;
                }

                $detailAbsen[] = [
                    'tanggal' => $a->date->format('d/m/Y'),
                    'check_in' => $checkInTime,
                    'check_out' => $checkOutTime,
                    'jam_kerja' => $jamHariIni,
                    'status' => $a->status ?? 'hadir'
                ];
            }
        }

        $userField = $this->getUserFieldName();

        $rapot = Rapot::create([
            $userField       => $user->id,
            'evaluator_id'  => Auth::id(),
            'periode'       => "{$start->format('d M Y')} - {$end->format('d M Y')}",
            'periode_start' => $start,
            'periode_end'   => $end,
            'total_jam'     => $totalJam,
            'nilai'         => $totalJam,
            'rata_rata'     => $hariKerja > 0 ? round($totalJam / $hariKerja, 2) : 0,
            'hari_kerja'    => $hariKerja,
            'hari_hadir'    => $hariHadir,
            'catatan'       => $request->catatan ?? "Total jam kerja periode tersebut",
            'detail_absen'  => json_encode($detailAbsen),
            'generated_at'  => now(),
            'tipe'          => 'standar',
            'status'        => 'standar'
        ]);

        return back()->with('success', "Rapot untuk {$user->name} berhasil dibuat!");
    }

    /**
     * EDIT RAPOT
     */
    public function edit(Rapot $rapot)
    {
        if (!in_array(Auth::user()->role, ['admin', 'manager'])) {
            abort(403, 'Unauthorized');
        }

        return view('rapot.edit', compact('rapot'));
    }

    /**
     * UPDATE RAPOT
     */
    public function update(Request $request, Rapot $rapot)
    {
        if (!in_array(Auth::user()->role, ['admin', 'manager'])) {
            abort(403, 'Unauthorized');
        }

        $request->validate([
            'catatan' => 'required|string|max:500',
            'nilai'   => 'required|numeric|min:0|max:100',
        ]);

        $statusRapot = $this->tentukanStatus($request->nilai / 10);

        $rapot->update([
            'catatan' => $request->catatan,
            'nilai' => $request->nilai,
            'status' => $statusRapot,
        ]);

        return redirect()->route('admin.rapot.index')
            ->with('success', "Rapot berhasil diperbarui!");
    }

    /**
     * DELETE RAPOT
     */
    public function destroy(Rapot $rapot)
    {
        if (!in_array(Auth::user()->role, ['admin', 'manager'])) {
            abort(403, 'Unauthorized');
        }

        $namaUser = $rapot->user->name ?? 'User';
        $rapot->delete();

        return redirect()
            ->route('admin.rapot.index')
            ->with('success', "Rapot {$namaUser} berhasil dihapus!");
    }

    /**
     * DEBUG METHOD: Test perhitungan jam kerja
     */
    public function testJamKerja()
    {
        $testCases = [
            ['check_in' => '08:11', 'check_out' => '16:10'],
            ['check_in' => '08:00', 'check_out' => '16:38'],
            ['check_in' => '07:15', 'check_out' => '15:20'],
            ['check_in' => '09:00', 'check_out' => '17:00'],
            ['check_in' => '13:00', 'check_out' => '21:00'],
        ];

        $results = [];
        foreach ($testCases as $test) {
            $jam = $this->calculateWorkingHours($test['check_in'], $test['check_out']);
            $results[] = [
                'test_case' => $test,
                'jam_kerja' => $jam
            ];
        }

        return response()->json([
            'success' => true,
            'results' => $results,
            'note' => 'Perhitungan: (check_out - check_in - 1 jam istirahat)'
        ]);
    }

    /**
     * DEBUG METHOD: Cek data absen mentah
     */
    public function debugDataAbsen(User $user)
    {
        $absen = Attendance::where('user_id', $user->id)
            ->whereDate('date', '>=', Carbon::now()->startOfMonth())
            ->whereDate('date', '<=', Carbon::now()->endOfMonth())
            ->orderBy('date', 'asc')
            ->get();

        $formattedData = [];
        foreach ($absen as $a) {
            $checkInTime = $this->extractTimeOnly($a->check_in);
            $checkOutTime = $this->extractTimeOnly($a->check_out);
            $jamKerja = $this->calculateWorkingHours($checkInTime, $checkOutTime);

            $formattedData[] = [
                'id' => $a->id,
                'date' => $a->date,
                'check_in_original' => $a->check_in,
                'check_out_original' => $a->check_out,
                'check_in_extracted' => $checkInTime,
                'check_out_extracted' => $checkOutTime,
                'jam_kerja' => $jamKerja,
                'status' => $a->status,
                'check_in_type' => gettype($a->check_in),
                'check_out_type' => gettype($a->check_out)
            ];
        }

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'role' => $user->role
            ],
            'periode' => Carbon::now()->startOfMonth()->format('Y-m-d') . ' to ' . Carbon::now()->endOfMonth()->format('Y-m-d'),
            'total_records' => count($formattedData),
            'data' => $formattedData
        ]);
    }
}
