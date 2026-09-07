<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Pppoe;
use App\Models\Radusergroup;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class IsolirController extends Controller
{
    /**
     * Tampilkan portal isolir pelanggan
     */
    public function index(Request $request)
    {
        $clientIp = $this->getClientIp($request);
        $searchQuery = trim($request->input('q', ''));
        $pppoe = null;
        $autoDetected = false;

        // 1. Jika ada query pencarian manual (Username / No WA / No Faktur)
        if (!empty($searchQuery)) {
            $pppoe = $this->findPppoeByQuery($searchQuery);
        }

        // 2. Jika tidak ada query atau belum ditemukan, coba deteksi via IP Address
        if (!$pppoe && !empty($clientIp) && $clientIp !== '127.0.0.1') {
            $usernameFromRadius = $this->findUsernameByIp($clientIp);
            if ($usernameFromRadius) {
                $pppoe = $this->findPppoeByQuery($usernameFromRadius);
                if ($pppoe) {
                    $autoDetected = true;
                    $searchQuery = $pppoe->username;
                }
            } else {
                $pppoeByIp = Pppoe::with(['paket', 'preOrder', 'invoices' => function ($q) {
                    $q->orderBy('due_date', 'asc')->orderBy('id', 'desc');
                }])->where('ip_address', $clientIp)->first();

                if ($pppoeByIp) {
                    $pppoe = $pppoeByIp;
                    $autoDetected = true;
                    $searchQuery = $pppoe->username;
                }
            }
        }

        $unpaidInvoices = collect();
        $isIsolated = false;
        $radiusGroup = null;

        if ($pppoe) {
            $unpaidInvoices = $pppoe->invoices->where('status', 'unpaid');
            
            // Periksa status group FreeRADIUS
            try {
                $radGroup = Radusergroup::where('username', $pppoe->username)->first();
                if ($radGroup) {
                    $radiusGroup = $radGroup->groupname;
                    if (strtoupper($radGroup->groupname) === 'ISOLIR') {
                        $isIsolated = true;
                    }
                }
            } catch (\Throwable $e) {
                // Ignore jika radius db tidak aktif
            }

            if ($unpaidInvoices->isNotEmpty() || $pppoe->status_invoice === 'belum_bayar') {
                $isIsolated = true;
            }
        }

        $companyName = Setting::get('company_name', 'ERLANGGA.NET');
        $csWhatsapp = Setting::get('company_phone', Setting::get('cs_whatsapp', '6281234567890'));
        $justPaid = $request->boolean('paid');

        return view('isolir.index', compact(
            'pppoe',
            'unpaidInvoices',
            'clientIp',
            'searchQuery',
            'autoDetected',
            'isIsolated',
            'radiusGroup',
            'companyName',
            'csWhatsapp',
            'justPaid'
        ));
    }

    /**
     * Submit form pencarian pelanggan
     */
    public function search(Request $request)
    {
        $request->validate([
            'q' => 'required|string|max:100',
        ]);

        return redirect()->route('isolir.index', ['q' => trim($request->q)]);
    }

    /**
     * Bayar invoice langsung via Xendit dari halaman Isolir
     */
    public function payXendit(Request $request, Invoice $invoice)
    {
        if ($invoice->status === 'paid') {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['error' => 'Tagihan ini sudah lunas.']);
            }
            return redirect()->route('isolir.index', ['q' => $invoice->pppoe ? $invoice->pppoe->username : '', 'paid' => 1])
                ->with('success', 'Tagihan ini sudah lunas.');
        }

        // Jika invoice Xendit sudah dibuat sebelumnya, langsung arahkan
        if ($invoice->xendit_invoice_url) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['url' => $invoice->xendit_invoice_url]);
            }
            return redirect($invoice->xendit_invoice_url);
        }

        try {
            \Xendit\Configuration::setXenditKey(Setting::get('xendit_secret_key', config('services.xendit.secret_key')));
            $apiInstance = new \Xendit\Invoice\InvoiceApi();

            $invoice->load(['pppoe.preOrder', 'pppoe.paket']);
            $pppoe = $invoice->pppoe;
            $preOrder = $pppoe ? $pppoe->preOrder : null;
            $payerEmail = ($preOrder && !empty($preOrder->alamat_email) && filter_var($preOrder->alamat_email, FILTER_VALIDATE_EMAIL))
                ? $preOrder->alamat_email
                : 'billing@erlangga.net';

            // Minimal masa berlaku payment link 1 hari
            $dueDate = $invoice->due_date ? Carbon::parse($invoice->due_date)->endOfDay() : now()->addDays(7);
            $durationSeconds = max(86400, $dueDate->diffInSeconds(now()));

            $items = [];
            $items[] = [
                'name' => 'Paket Internet ' . ($pppoe && $pppoe->paket ? $pppoe->paket->nama_paket : ''),
                'quantity' => 1,
                'price' => (float) $invoice->amount,
                'category' => 'INTERNET_SERVICE',
            ];

            if ($invoice->tax > 0) {
                $items[] = [
                    'name' => 'PPN / Pajak',
                    'quantity' => 1,
                    'price' => (float) $invoice->tax,
                    'category' => 'TAX',
                ];
            }

            if ($invoice->discount > 0) {
                $items[] = [
                    'name' => 'Diskon Promo',
                    'quantity' => 1,
                    'price' => -1 * (float) $invoice->discount,
                    'category' => 'DISCOUNT',
                ];
            }

            $customerName = $preOrder ? ($preOrder->nama_lengkap ?? $pppoe->username) : ($pppoe->username ?? 'Pelanggan');
            $customerPhone = $preOrder && !empty($preOrder->no_whatsapp) ? $preOrder->no_whatsapp : null;

            $redirectUrl = route('isolir.index', [
                'q' => $pppoe ? $pppoe->username : $invoice->invoice_number,
                'paid' => 1
            ]);

            $createInvoiceRequest = new \Xendit\Invoice\CreateInvoiceRequest([
                'external_id' => $invoice->invoice_number,
                'amount' => (float) $invoice->total,
                'payer_email' => $payerEmail,
                'description' => 'Pembayaran Tagihan ' . $invoice->invoice_number . ' - ' . ($pppoe ? $pppoe->username : ''),
                'invoice_duration' => (int) $durationSeconds,
                'customer' => [
                    'given_names' => $customerName,
                    'email' => $payerEmail,
                    'mobile_number' => $customerPhone,
                ],
                'items' => $items,
                'success_redirect_url' => $redirectUrl,
                'failure_redirect_url' => $redirectUrl,
            ]);

            $result = $apiInstance->createInvoice($createInvoiceRequest);

            $invoice->update([
                'xendit_invoice_id' => $result['id'],
                'xendit_invoice_url' => $result['invoice_url']
            ]);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['url' => $result['invoice_url']]);
            }

            return redirect($result['invoice_url']);
        } catch (\Throwable $e) {
            Log::error('Isolir Portal Xendit Creation Error: ' . $e->getMessage(), [
                'invoice' => $invoice->id,
                'trace' => $e->getTraceAsString(),
            ]);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['error' => 'Gagal menghubungkan ke gateway pembayaran: ' . $e->getMessage()], 500);
            }

            return redirect()->back()->with('error', 'Gagal memproses pembayaran: ' . $e->getMessage());
        }
    }

    /**
     * Endpoint polling status pembayaran (realtime AJAX dari portal isolir)
     */
    public function checkStatus(Request $request, Invoice $invoice)
    {
        // Jika belum lunas dan punya xendit_invoice_id, cek ke Xendit API untuk memastikan
        if ($invoice->status !== 'paid' && $invoice->xendit_invoice_id) {
            try {
                \Xendit\Configuration::setXenditKey(Setting::get('xendit_secret_key', config('services.xendit.secret_key')));
                $apiInstance = new \Xendit\Invoice\InvoiceApi();
                $res = $apiInstance->getInvoiceById($invoice->xendit_invoice_id);

                if (isset($res['status']) && in_array(strtoupper($res['status']), ['PAID', 'SETTLED'])) {
                    $channel = !empty($res['payment_channel']) ? strtoupper($res['payment_channel']) : (!empty($res['payment_method']) ? strtoupper($res['payment_method']) : 'ONLINE');
                    $invoice->markAsPaid('Xendit - ' . $channel, $invoice->total, $invoice->xendit_invoice_id);
                }
            } catch (\Throwable $e) {
                // Silently log
                Log::warning("Isolir status check error for invoice {$invoice->id}: " . $e->getMessage());
            }
        }

        $freshInvoice = $invoice->fresh();

        return response()->json([
            'id' => $freshInvoice->id,
            'invoice_number' => $freshInvoice->invoice_number,
            'status' => $freshInvoice->status,
            'is_paid' => $freshInvoice->status === 'paid',
            'payment_method' => $freshInvoice->payment_method,
        ]);
    }

    /**
     * Helper: Cari akun PPPoE dari query (Username, No WA, No Faktur, Nama)
     */
    private function findPppoeByQuery(string $q): ?Pppoe
    {
        // Cek dulu apakah $q adalah nomor faktur
        if (str_starts_with(strtoupper($q), 'INV-')) {
            $invoice = Invoice::where('invoice_number', $q)->with(['pppoe.paket', 'pppoe.preOrder', 'pppoe.invoices'])->first();
            if ($invoice && $invoice->pppoe) {
                return $invoice->pppoe;
            }
        }

        return Pppoe::with(['paket', 'preOrder', 'invoices' => function ($query) {
            $query->orderBy('due_date', 'asc')->orderBy('id', 'desc');
        }])->where(function ($query) use ($q) {
            $query->where('username', $q)
                ->orWhere('ip_address', $q)
                ->orWhereHas('preOrder', function ($po) use ($q) {
                    $po->where('no_whatsapp', $q)
                        ->orWhere('no_whatsapp', 'like', "%{$q}%")
                        ->orWhere('nama_lengkap', 'like', "%{$q}%");
                });
        })->first();
    }

    /**
     * Helper: Cari username PPPoE aktif dari radacct FreeRADIUS berdasarkan IP address
     */
    private function findUsernameByIp(string $ip): ?string
    {
        try {
            $acct = DB::connection('radius')->table('radacct')
                ->where('framedipaddress', $ip)
                ->whereNull('acctstoptime')
                ->orderBy('radacctid', 'desc')
                ->first();

            if ($acct && !empty($acct->username)) {
                return $acct->username;
            }
        } catch (\Throwable $e) {
            // DB radius connection failure fallback
        }

        return null;
    }

    /**
     * Helper: Dapatkan IP address asli client dari Request / Proxy Headers
     */
    private function getClientIp(Request $request): string
    {
        $headers = [
            'HTTP_CF_CONNECTING_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_REAL_IP',
            'HTTP_CLIENT_IP',
            'REMOTE_ADDR'
        ];

        foreach ($headers as $header) {
            if ($request->server($header)) {
                $ips = explode(',', $request->server($header));
                $ip = trim($ips[0]);
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }

        return $request->ip() ?: '127.0.0.1';
    }
}
