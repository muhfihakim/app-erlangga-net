<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PreOrder;
use App\Models\Customer;
use App\Models\Maintenance;
use App\Models\Dismantle;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use App\Services\MikroTikService;

class DashboardController extends Controller
{
    public function __construct(private MikroTikService $mikroTikService) {}

    public function stats(Request $request)
    {
        $user = $request->user();
        $today = Carbon::now();
        
        // Billing Cycle: 29th - 28th
        if ($today->day >= 29) {
            $startOfMonth = $today->copy()->startOfDay()->day(29);
            $endOfMonth = $today->copy()->addMonth()->startOfDay()->day(28)->endOfDay();
        } else {
            $startOfMonth = $today->copy()->subMonth()->startOfDay()->day(29);
            $endOfMonth = $today->copy()->startOfDay()->day(28)->endOfDay();
        }

        $response = [
            'user' => [
                'name' => $user->nama_lengkap,
                'role' => $user->jabatan,
            ],
            'period' => [
                'label' => $startOfMonth->format('d M Y') . ' - ' . $endOfMonth->format('d M Y'),
                'start' => $startOfMonth->toIso8601String(),
                'end' => $endOfMonth->toIso8601String(),
            ],
            'cards' => [],
            'charts' => []
        ];

        // 1. CARDS (Role Based)
        if ($user->jabatan === 'Administrator') {
            $response['cards'] = [
                'total_po' => PreOrder::whereBetween('created_at', [$startOfMonth, $endOfMonth])->count(),
                'total_aktivasi' => Customer::whereBetween('created_at', [$startOfMonth, $endOfMonth])->count(),
                'total_pending' => PreOrder::whereBetween('created_at', [$startOfMonth, $endOfMonth])
                    ->whereNotIn('id', Customer::pluck('pre_order_id'))->count(),
            ];
            
            // Charts for Admin
            $response['charts']['activation_trend'] = $this->getActivationTrend();
        } elseif ($user->jabatan === 'Sales') {
            $response['cards'] = [
                'total_pelanggan_saya' => Customer::where('id_sales', $user->id)->count(),
                'total_isolir_saya' => $this->getIsolirCount($user->id),
                'total_po_saya' => PreOrder::where('id_sales', $user->id)->whereBetween('created_at', [$startOfMonth, $endOfMonth])->count()
            ];
        } elseif ($user->jabatan === 'Teknisi') {
            // Weekly Team Stats (Sat-Fri)
            $startOfWeekSat = Carbon::now()->isSaturday() ? Carbon::now()->startOfDay() : Carbon::now()->previous(Carbon::SATURDAY)->startOfDay();
            $endOfWeekFri = $startOfWeekSat->copy()->addDays(6)->endOfDay();

            $response['cards'] = [
                'personal_maintenance' => Maintenance::where('id_teknisi_eksekutor', $user->id)
                    ->whereBetween('completed_at', [$startOfMonth, $endOfMonth])->count(),
                'aktivasi_minggu_ini' => Customer::whereBetween('created_at', [$startOfWeekSat, $endOfWeekFri])
                    ->where(fn($q) => $q->where('id_teknisi', $user->id)->orWhereJsonContains('partner_ids', (string)$user->id))->count()
            ];

            $response['stats']['team_pengerjaan'] = $this->getTeamStats($user->id, $startOfMonth, $endOfMonth);
        }

        return response()->json($response);
    }

    private function getActivationTrend()
    {
        $labels = []; $values = [];
        $mostRecentFriday = Carbon::now()->isFriday() ? Carbon::now() : Carbon::now()->previous(Carbon::FRIDAY);
        for ($i = 4; $i >= 0; $i--) {
            $endOfPeriod = $mostRecentFriday->copy()->subWeeks($i);
            $startOfPeriod = $endOfPeriod->copy()->subDays(6);
            $labels[] = $startOfPeriod->format('d M') . ' - ' . $endOfPeriod->format('d M');
            $values[] = Customer::withTrashed()->whereBetween('created_at', [$startOfPeriod->startOfDay(), $endOfPeriod->endOfDay()])->count();
        }
        return ['labels' => $labels, 'values' => $values];
    }

    private function getTeamStats($techId, $start, $end)
    {
        $maintenances = Maintenance::where('status', 'Selesai')->where('id_teknisi_eksekutor', $techId)
            ->whereBetween('completed_at', [$start, $end])->get();
        
        $stats = ['alone' => 0, 'pair' => 0, 'trio' => 0];
        foreach ($maintenances as $m) {
            $count = is_array($m->partner_ids) ? count($m->partner_ids) : 0;
            if ($count === 0) $stats['alone']++;
            elseif ($count === 1) $stats['pair']++;
            else $stats['trio']++;
        }
        return $stats;
    }

    private function getIsolirCount($salesId)
    {
        $pppoeData = Cache::get('mikrotik_pppoe_active') ?? $this->mikroTikService->getPppoeActive();
        if (!$pppoeData || !isset($pppoeData['success']) || !$pppoeData['success']) return 0;

        $isolirUsernames = collect($pppoeData['data'])->filter(fn($item) => str_starts_with($item['address'] ?? '', '172.30.'))
            ->pluck('name')->toArray();

        return Customer::whereIn('pppoe_username', $isolirUsernames)->where('id_sales', $salesId)->count();
    }
}
