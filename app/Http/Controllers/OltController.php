<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\OltTelnetService;
use Exception;

class OltController extends Controller
{
    public function __construct(
        private OltTelnetService $oltService
    ) {}

    public function index()
    {
        return view('monitoring.olt');
    }

    public function checkNextOnuId(Request $request)
    {
        $port = $request->input('olt_port');
        if (!$port) {
            return response()->json(['success' => false, 'error' => 'Port OLT harus diisi'], 400);
        }

        try {
            $output = $this->oltService->execute("show gpon onu state gpon-olt_{$port}");
            
            // Parsing Logic: Mencari ONU ID yang sudah terpakai
            // Baris biasanya: gpon-onu_1/2/8:1  ready  ...
            preg_match_all('/gpon-onu_[\d\/]+:(\d+)/', $output, $matches);
            $usedIds = array_map('intval', $matches[1] ?? []);
            sort($usedIds);

            // Cari ID terkecil yang belum dipakai (1-128)
            $nextId = 1;
            for ($i = 1; $i <= 128; $i++) {
                if (!in_array($i, $usedIds)) {
                    $nextId = $i;
                    break;
                }
            }

            return response()->json([
                'success' => true,
                'next_id' => $nextId,
                'used_ids' => $usedIds,
                'raw_output' => $output
            ]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function executeScript(Request $request)
    {
        $script = $request->input('script');
        if (!$script) {
            return response()->json(['success' => false, 'error' => 'Skrip kosong'], 400);
        }

        try {
            $lines = explode("\n", str_replace("\r", "", $script));
            $fullLog = "";

            foreach ($lines as $line) {
                $trimmed = trim($line);
                if ($trimmed === "" || $trimmed === "!") continue;
                
                $output = $this->oltService->execute($trimmed);
                $fullLog .= "> " . $trimmed . "\n" . $output . "\n";
            }

            return response()->json([
                'success' => true,
                'log' => $fullLog
            ]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
}
