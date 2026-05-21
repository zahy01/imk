<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PatroliSecurity;
use App\Models\KinerjaCleaning;
use App\Models\User;
use Carbon\Carbon;

class AdminDocumentationController extends Controller
{
    public function index(Request $request)
    {
        $type = $request->query('type', 'all'); // all, patroli, cleaning
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');
        $userId = $request->query('user_id');

        // Default date range: last 30 days
        if (!$startDate || !$endDate) {
            $startDate = Carbon::now()->subDays(30)->toDateString();
            $endDate = Carbon::today()->toDateString();
        }

        $query = null;
        $items = collect();

        if ($type === 'patroli' || $type === 'all') {
            $patroliQuery = PatroliSecurity::whereBetween('waktu_patroli', [
                Carbon::parse($startDate)->startOfDay(),
                Carbon::parse($endDate)->endOfDay()
            ]);

            if ($userId) {
                $patroliQuery->where('user_id', $userId);
            }

            $patroli = $patroliQuery->with('user')->latest('waktu_patroli')->get()->map(function ($item) {
                return [
                    'id' => $item->id,
                    'type' => 'patroli',
                    'foto' => asset($item->foto),
                    'area' => $item->nama_area,
                    'keterangan' => $item->keterangan,
                    'user_name' => $item->user->name ?? 'Unknown',
                    'user_id' => $item->user_id,
                    'datetime' => $item->waktu_patroli,
                    'date' => $item->waktu_patroli->format('d M Y'),
                    'time' => $item->waktu_patroli->format('H:i'),
                ];
            });

            $items = $items->concat($patroli);
        }

        if ($type === 'cleaning' || $type === 'all') {
            $cleaningQuery = KinerjaCleaning::whereBetween('tanggal', [
                $startDate,
                $endDate
            ]);

            if ($userId) {
                $cleaningQuery->where('user_id', $userId);
            }

            $cleaning = $cleaningQuery->with('user')->latest('created_at')->get()->map(function ($item) {
                return [
                    'id' => $item->id,
                    'type' => 'cleaning',
                    'foto' => asset('storage/' . $item->foto),
                    'area' => $item->area,
                    'keterangan' => $item->keterangan,
                    'user_name' => $item->user->name ?? 'Unknown',
                    'user_id' => $item->user_id,
                    'datetime' => $item->created_at,
                    'date' => $item->tanggal,
                    'time' => $item->created_at->format('H:i'),
                ];
            });

            $items = $items->concat($cleaning);
        }

        // Sort by datetime descending
        $items = $items->sortByDesc('datetime')->values();

        // Get users for filter (security & cleaning)
        $users = User::whereIn('role', ['security', 'cleaning'])
            ->orderBy('name')
            ->get();

        return view('admin.dokumentasi-cleaning-security', [
            'items' => $items,
            'users' => $users,
            'type' => $type,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'userId' => $userId,
            'totalItems' => $items->count(),
            'patroliCount' => $items->where('type', 'patroli')->count(),
            'cleaningCount' => $items->where('type', 'cleaning')->count(),
        ]);
    }
}
