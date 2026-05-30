<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\User;
use App\Models\FinanceSetting;
use Illuminate\Http\Request;
use Carbon\Carbon;

class FinanceController extends Controller
{
    public function index(Request $request)
    {
        $teknisiId = $request->input('teknisi_id');
        $start = $request->input('start_date');
        $end = $request->input('end_date');

        if (!$start && !$end) {
            $endOfCurrentCycle = Carbon::now()->isFriday() ? Carbon::now() : Carbon::now()->next(Carbon::FRIDAY);
            $startOfPreviousCycle = $endOfCurrentCycle->copy()->subDays(13);
            $start = $startOfPreviousCycle->format('Y-m-d');
            $end = $endOfCurrentCycle->format('Y-m-d');
        }

        $setting = FinanceSetting::first();
        $baseFee = $setting ? $setting->activation_fee : 150000;

        $query = Customer::withTrashed()->with(['teknisiUtama', 'sales']);

        if ($teknisiId) {
            $query->where(function ($q) use ($teknisiId) {
                $q->where('id_teknisi', $teknisiId)
                  ->orWhereJsonContains('partner_ids', (string)$teknisiId)
                  ->orWhereJsonContains('partner_ids', (int)$teknisiId);
            });
        }

        if ($start) $query->whereDate('created_at', '>=', $start);
        if ($end) $query->whereDate('created_at', '<=', $end);

        $customers = $query->orderBy('created_at', 'desc')->get();
        $totalPendapatan = 0;
        
        foreach ($customers as $c) {
            $partners = is_array($c->partner_ids) ? $c->partner_ids : json_decode($c->partner_ids, true);
            $partnerCount = is_array($partners) ? count($partners) : 0;
            $totalTechs = 1 + $partnerCount;
            $feePerTech = $baseFee / $totalTechs;

            if ($teknisiId) {
                $isMain = $c->id_teknisi == $teknisiId;
                $isPartner = is_array($partners) && (in_array((string)$teknisiId, $partners) || in_array((int)$teknisiId, $partners));
                if ($isMain || $isPartner) {
                    $c->calculated_fee = $feePerTech;
                    $totalPendapatan += $feePerTech;
                } else {
                    $c->calculated_fee = 0;
                }
            } else {
                $c->calculated_fee = $feePerTech;
                $totalPendapatan += $baseFee;
            }
            $c->total_techs = $totalTechs;
        }

        return response()->json([
            'total_pendapatan' => $totalPendapatan,
            'base_fee' => $baseFee,
            'data' => $customers
        ]);
    }
}
