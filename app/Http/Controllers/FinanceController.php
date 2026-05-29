<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\User;
use App\Models\FinanceSetting;
use Illuminate\Http\Request;
use Carbon\Carbon;
use PDF;

class FinanceController extends Controller
{
    public function index(Request $request)
    {
        $teknisiId = $request->input('teknisi_id');
        $start = $request->input('start_date');
        $end = $request->input('end_date');

        // Get the base activation fee
        $setting = FinanceSetting::first();
        $baseFee = $setting ? $setting->activation_fee : 150000;

        $query = Customer::withTrashed()->with(['teknisiUtama', 'sales']);

        if ($teknisiId) {
            $query->where(function ($q) use ($teknisiId) {
                $q->where('id_teknisi', $teknisiId)
                  ->orWhereJsonContains('partner_ids', (string)$teknisiId)
                  ->orWhereJsonContains('partner_ids', (int)$teknisiId);
            });
        } else {
            // If no specific technician, we might just show all activations, 
            // but the fee logic is per technician. It's better to show all activations and their split.
            // Or only show when a tech is selected? Let's just show all if none selected.
        }

        if ($start) $query->whereDate('created_at', '>=', $start);
        if ($end) $query->whereDate('created_at', '<=', $end);

        $customers = $query->orderBy('created_at', 'desc')->get();
        $teknisis = User::where('jabatan', 'Teknisi')->get();

        // Calculate earnings for the specific technician, or all if none selected
        $totalPendapatan = 0;
        foreach ($customers as $c) {
            $partners = is_array($c->partner_ids) ? $c->partner_ids : json_decode($c->partner_ids, true);
            $partnerCount = is_array($partners) ? count($partners) : 0;
            $totalTechs = 1 + $partnerCount;
            $feePerTech = $baseFee / $totalTechs;

            // If a specific technician is selected, we only sum their part
            if ($teknisiId) {
                // Double check if this tech is actually in this activation
                $isMain = $c->id_teknisi == $teknisiId;
                $isPartner = is_array($partners) && (in_array((string)$teknisiId, $partners) || in_array((int)$teknisiId, $partners));
                
                if ($isMain || $isPartner) {
                    $c->calculated_fee = $feePerTech;
                    $totalPendapatan += $feePerTech;
                } else {
                    $c->calculated_fee = 0;
                }
            } else {
                // If no specific technician, we might just display the fee per person
                $c->calculated_fee = $feePerTech;
                $totalPendapatan += $baseFee; // Sum of all fees across all activations
            }
            $c->total_techs = $totalTechs;
        }

        return view('finance.index', compact('customers', 'teknisis', 'teknisiId', 'start', 'end', 'baseFee', 'totalPendapatan'));
    }

    public function export(Request $request)
    {
        $request->validate(['start_date' => 'required|date', 'end_date' => 'required|date']);

        $teknisiId = $request->teknisi_id;
        $setting = FinanceSetting::first();
        $baseFee = $setting ? $setting->activation_fee : 150000;

        $query = Customer::withTrashed()->with(['teknisiUtama', 'sales'])
            ->whereBetween('created_at', [
                Carbon::parse($request->start_date)->startOfDay(),
                Carbon::parse($request->end_date)->endOfDay()
            ]);

        if ($teknisiId) {
            $query->where(function ($q) use ($teknisiId) {
                $q->where('id_teknisi', $teknisiId)
                  ->orWhereJsonContains('partner_ids', (string)$teknisiId)
                  ->orWhereJsonContains('partner_ids', (int)$teknisiId);
            });
        }

        $customers = $query->orderBy('created_at', 'asc')->get();
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

        $pdf = PDF::loadView('finance.export', [
            'customers' => $customers,
            'start' => $request->start_date,
            'end' => $request->end_date,
            'teknisi' => $teknisiId ? User::find($teknisiId) : null,
            'totalPendapatan' => $totalPendapatan,
            'baseFee' => $baseFee
        ]);

        return $pdf->download("laporan-keuangan-teknisi-{$request->start_date}-ke-{$request->end_date}.pdf");
    }
}
