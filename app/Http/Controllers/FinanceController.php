<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\User;
use App\Models\FinanceSetting;
use Illuminate\Http\Request;
use Carbon\Carbon;
use PDF;
use PhpOffice\PhpWord\TemplateProcessor;

class FinanceController extends Controller
{
    public function index(Request $request)
    {
        $teknisiId = $request->input('teknisi_id');
        $start = $request->input('start_date');
        $end = $request->input('end_date');

        // Set default filter: 2 weeks (Saturday to Friday current cycle + previous cycle)
        if (!$start && !$end) {
            $endOfCurrentCycle = Carbon::now()->isFriday() ? Carbon::now() : Carbon::now()->next(Carbon::FRIDAY);
            $startOfPreviousCycle = $endOfCurrentCycle->copy()->subDays(13); // 13 days before Friday = Saturday of previous week
            
            $start = $startOfPreviousCycle->format('Y-m-d');
            $end = $endOfCurrentCycle->format('Y-m-d');
        }

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
        }

        if ($start) $query->whereDate('created_at', '>=', $start);
        if ($end) $query->whereDate('created_at', '<=', $end);

        $customers = $query->orderBy('created_at', 'desc')->get();
        $teknisis = User::where('jabatan', 'Teknisi')->get();

        // Stats
        $stats = [
            'solo' => 0,
            'pair' => 0,
            'triple' => 0,
            'quad' => 0,
            'more' => 0,
        ];

        // Calculate earnings for the specific technician, or all if none selected
        $totalPendapatan = 0;
        foreach ($customers as $c) {
            $partners = is_array($c->partner_ids) ? $c->partner_ids : json_decode($c->partner_ids, true);
            $partnerCount = is_array($partners) ? count($partners) : 0;
            $totalTechs = 1 + $partnerCount;
            $feePerTech = $baseFee / $totalTechs;

            // Increment stats based on totalTechs
            if ($totalTechs == 1) $stats['solo']++;
            elseif ($totalTechs == 2) $stats['pair']++;
            elseif ($totalTechs == 3) $stats['triple']++;
            elseif ($totalTechs == 4) $stats['quad']++;
            else $stats['more']++;

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

        return view('finance.index', compact('customers', 'teknisis', 'teknisiId', 'start', 'end', 'baseFee', 'totalPendapatan', 'stats'));
    }

    public function export(Request $request)
    {
        $request->validate(['start_date' => 'required|date', 'end_date' => 'required|date']);

        $type = $request->query('type', 'pdf');
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
        
        $stats = [
            'solo' => 0,
            'pair' => 0,
            'triple' => 0,
            'quad' => 0,
            'more' => 0,
        ];

        foreach ($customers as $c) {
            $partners = is_array($c->partner_ids) ? $c->partner_ids : json_decode($c->partner_ids, true);
            $partnerCount = is_array($partners) ? count($partners) : 0;
            $totalTechs = 1 + $partnerCount;
            $feePerTech = $baseFee / $totalTechs;

            if ($totalTechs == 1) $stats['solo']++;
            elseif ($totalTechs == 2) $stats['pair']++;
            elseif ($totalTechs == 3) $stats['triple']++;
            elseif ($totalTechs == 4) $stats['quad']++;
            else $stats['more']++;

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

        $teknisi = $teknisiId ? User::find($teknisiId) : null;

        if ($type === 'docx') {
            return $this->exportDocx($customers, $request->start_date, $request->end_date, $teknisi, $totalPendapatan, $baseFee, $stats);
        }

        $pdf = PDF::loadView('finance.export', [
            'customers' => $customers,
            'start' => $request->start_date,
            'end' => $request->end_date,
            'teknisi' => $teknisi,
            'totalPendapatan' => $totalPendapatan,
            'baseFee' => $baseFee,
            'stats' => $stats
        ]);

        return $pdf->download("laporan-keuangan-teknisi-{$request->start_date}-ke-{$request->end_date}.pdf");
    }

    private function exportDocx($customers, $start, $end, $teknisi, $totalPendapatan, $baseFee, $stats)
    {
        $templatePath = resource_path('views/export/template-keuangan-teknisi.docx');
        $templateProcessor = new TemplateProcessor($templatePath);

        $templateProcessor->setValue('start', Carbon::parse($start)->translatedFormat('d F Y'));
        $templateProcessor->setValue('end', Carbon::parse($end)->translatedFormat('d F Y'));
        $templateProcessor->setValue('teknisi_nama', $teknisi ? $teknisi->nama_lengkap : 'Semua Teknisi');
        $templateProcessor->setValue('total_pendapatan', number_format($totalPendapatan, 0, ',', '.'));
        $templateProcessor->setValue('base_fee', number_format($baseFee, 0, ',', '.'));
        $templateProcessor->setValue('total_aktif', $customers->count());
        $templateProcessor->setValue('solo', $stats['solo']);
        $templateProcessor->setValue('pair', $stats['pair']);
        $templateProcessor->setValue('triple', $stats['triple']);
        $templateProcessor->setValue('quad', $stats['quad']);
        $templateProcessor->setValue('more', $stats['more']);

        $templateProcessor->cloneRow('no', $customers->count());

        foreach ($customers as $index => $c) {
            $rowIndex = $index + 1;
            $templateProcessor->setValue('no#' . $rowIndex, $rowIndex);
            $templateProcessor->setValue('nama#' . $rowIndex, $c->nama_lengkap);
            $templateProcessor->setValue('paket#' . $rowIndex, $c->paket_internet);
            $templateProcessor->setValue('tgl#' . $rowIndex, Carbon::parse($c->created_at)->format('d/m/Y'));
            $templateProcessor->setValue('team#' . $rowIndex, $c->total_techs . ' Org');
            $templateProcessor->setValue('fee#' . $rowIndex, number_format($c->calculated_fee, 0, ',', '.'));
        }

        $fileName = "laporan-keuangan-teknisi-" . Carbon::parse($start)->format('Y-m-d') . ".docx";
        $tempFile = tempnam(sys_get_temp_dir(), 'docx');
        $templateProcessor->saveAs($tempFile);

        return response()->download($tempFile, $fileName)->deleteFileAfterSend(true);
    }
}
