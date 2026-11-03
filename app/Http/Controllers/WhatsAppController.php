<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Pppoe;
use App\Models\PreOrder;
use App\Models\Setting;
use App\Models\WhatsAppMessage;
use App\Services\WhatsAppService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WhatsAppController extends Controller
{
    /**
     * Halaman Utama WhatsApp Center
     */
    public function index(Request $request)
    {
        $templates = WhatsAppService::getQuickTemplates();

        // Cek status konfigurasi WhatsApp API
        $apiKey = Setting::get('whatsapp_api_key', env('WHATSAPP_API_KEY', ''));
        $apiUrl = Setting::get('whatsapp_api_url', env('WHATSAPP_API_URL', ''));
        $isConfigured = !empty($apiKey) && !empty($apiUrl);

        // Parameter inisial jika diarahkan dari halaman lain (misal dari daftar invoice)
        $initialPhone = $request->input('phone', '');
        $initialPppoeId = $request->input('pppoe_id');
        $initialInvoiceId = $request->input('invoice_id');

        $initialContact = null;
        $initialInvoice = null;

        if ($initialInvoiceId) {
            $initialInvoice = Invoice::with(['pppoe.preOrder', 'pppoe.paket'])->find($initialInvoiceId);
            if ($initialInvoice && $initialInvoice->pppoe) {
                $initialPppoeId = $initialInvoice->pppoe_id;
                $phone = $initialInvoice->pppoe->preOrder->no_whatsapp ?? '';
                if ($phone) {
                    $initialPhone = $phone;
                }
            }
        }

        if ($initialPppoeId && empty($initialPhone)) {
            $pppoe = Pppoe::with('preOrder')->find($initialPppoeId);
            if ($pppoe && $pppoe->preOrder && $pppoe->preOrder->no_whatsapp) {
                $initialPhone = $pppoe->preOrder->no_whatsapp;
            }
        }

        if (!empty($initialPhone)) {
            $normalized = WhatsAppService::normalizePhone($initialPhone);
            $initialContact = $this->resolveContactData($normalized);
        }

        return view('whatsapp.index', compact(
            'templates',
            'isConfigured',
            'initialPhone',
            'initialPppoeId',
            'initialInvoiceId',
            'initialContact',
            'initialInvoice'
        ));
    }

    /**
     * Ambil daftar kontak (Pelanggan / PPPoE / PreOrder) untuk sidebar chat
     */
    public function getContacts(Request $request)
    {
        $search = trim($request->input('search', ''));
        $filter = $request->input('filter', 'all'); // 'all', 'unpaid', 'active', 'preorder'
        $limit = min(50, max(10, (int) $request->input('limit', 30)));

        $query = Pppoe::with(['preOrder', 'paket', 'invoices' => function ($q) {
            $q->where('status', 'unpaid')->latest();
        }]);

        // Filter berdasarkan status
        if ($filter === 'unpaid') {
            $query->whereHas('invoices', function ($q) {
                $q->where('status', 'unpaid');
            });
        } elseif ($filter === 'active') {
            $query->where('is_isolir', false);
        }

        // Pencarian nama, nomor whatsapp, username
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('username', 'LIKE', "%{$search}%")
                    ->orWhereHas('preOrder', function ($po) use ($search) {
                        $po->where('nama_lengkap', 'LIKE', "%{$search}%")
                            ->orWhere('no_whatsapp', 'LIKE', "%{$search}%")
                            ->orWhere('alamat_lengkap', 'LIKE', "%{$search}%");
                    });
            });
        }

        // Ambil percakapan aktif dari tabel pesan agar pesan masuk via webhook selalu muncul di daftar teratas
        $recentChatSub = WhatsAppMessage::select('phone', DB::raw('MAX(id) as max_id'))
            ->groupBy('phone')
            ->orderByDesc('max_id')
            ->take(30);

        $recentMsgs = WhatsAppMessage::joinSub($recentChatSub, 'latest', function ($join) {
            $join->on('whatsapp_messages.id', '=', 'latest.max_id');
        })->orderByDesc('whatsapp_messages.id')->get();

        $recentPhoneMap = [];
        $recentContacts = [];

        foreach ($recentMsgs as $rm) {
            $normalized = $rm->phone;
            $recentPhoneMap[$normalized] = [
                'message' => $rm->message,
                'direction' => $rm->direction,
                'status' => $rm->status,
                'created_at' => $rm->created_at->diffForHumans(),
            ];

            // Resolve contact data
            $resolved = $this->resolveContactData($normalized);

            // Filter status jika aktif
            if ($filter === 'unpaid' && ($resolved['unpaid_count'] ?? 0) === 0) {
                continue;
            }

            // Filter search jika ada
            if (!empty($search)) {
                $nameMatch = str_contains(strtolower($resolved['name']), strtolower($search));
                $phoneMatch = str_contains($normalized, $search);
                $userMatch = str_contains(strtolower($resolved['username'] ?? ''), strtolower($search));
                if (!$nameMatch && !$phoneMatch && !$userMatch) {
                    continue;
                }
            }

            $recentContacts[$normalized] = [
                'id' => $resolved['pppoe_id'] ?? null,
                'name' => $resolved['name'],
                'username' => $resolved['username'] ?? '-',
                'phone' => $resolved['display_phone'] ?? $normalized,
                'normalized_phone' => $normalized,
                'paket' => $resolved['paket'] ?? '-',
                'is_isolir' => $resolved['is_isolir'] ?? false,
                'unpaid_count' => $resolved['unpaid_count'] ?? 0,
                'unpaid_amount' => $resolved['unpaid_total'] ?? 0,
                'last_message' => $recentPhoneMap[$normalized],
            ];
        }

        // Ambil daftar PPPoE untuk kontak lainnya
        $pppoeList = $query->latest('id')->paginate($limit);

        // Ambil pesan terakhir untuk tiap kontak PPPoE
        $phoneList = $pppoeList->map(function ($p) {
            return WhatsAppService::normalizePhone($p->preOrder->no_whatsapp ?? '');
        })->filter()->unique()->values()->all();

        $latestMessages = $recentPhoneMap;
        $unqueriedPhones = array_diff($phoneList, array_keys($recentPhoneMap));

        if (!empty($unqueriedPhones)) {
            $sub = WhatsAppMessage::whereIn('phone', $unqueriedPhones)
                ->select('phone', DB::raw('MAX(id) as max_id'))
                ->groupBy('phone');

            $msgs = WhatsAppMessage::joinSub($sub, 'latest', function ($join) {
                $join->on('whatsapp_messages.id', '=', 'latest.max_id');
            })->get(['whatsapp_messages.*']);

            foreach ($msgs as $m) {
                $latestMessages[$m->phone] = [
                    'message' => $m->message,
                    'direction' => $m->direction,
                    'status' => $m->status,
                    'created_at' => $m->created_at->diffForHumans(),
                ];
            }
        }

        $allContacts = array_values($recentContacts);
        $existingPhones = array_keys($recentContacts);

        foreach ($pppoeList as $p) {
            $phone = $p->preOrder->no_whatsapp ?? '';
            if (empty($phone)) {
                $phone = Customer::where('pppoe_username', $p->username)->value('no_whatsapp') ?? '';
            }
            $normalizedPhone = WhatsAppService::normalizePhone($phone);

            if (empty($normalizedPhone) || in_array($normalizedPhone, $existingPhones)) {
                continue;
            }

            $unpaidInvoices = $p->invoices;
            $unpaidCount = $unpaidInvoices->count();
            $unpaidAmount = $unpaidInvoices->sum('total');

            $allContacts[] = [
                'id' => $p->id,
                'name' => $p->preOrder->nama_lengkap ?? $p->username,
                'username' => $p->username,
                'phone' => $phone,
                'normalized_phone' => $normalizedPhone,
                'paket' => $p->paket->nama_paket ?? '-',
                'paket_harga' => $p->paket ? (float)$p->paket->total_harga : 0,
                'is_isolir' => (bool)$p->is_isolir,
                'unpaid_count' => $unpaidCount,
                'unpaid_amount' => $unpaidAmount,
                'last_message' => $latestMessages[$normalizedPhone] ?? null,
            ];
        }

        return response()->json([
            'success' => true,
            'contacts' => $allContacts,
            'has_more' => $pppoeList->hasMorePages(),
            'current_page' => $pppoeList->currentPage(),
            'total' => count($allContacts),
        ]);
    }

    /**
     * Ambil riwayat percakapan & profil kontak spesifik
     */
    public function getConversation(Request $request, string $phone)
    {
        $normalized = WhatsAppService::normalizePhone($phone);

        if (empty($normalized)) {
            return response()->json(['success' => false, 'message' => 'Nomor WhatsApp tidak valid.'], 400);
        }

        $contact = $this->resolveContactData($normalized);
        $contact['normalized_phone'] = $normalized;

        // Ambil riwayat percakapan (maksimal 150 pesan terakhir)
        $messages = WhatsAppMessage::where('phone', $normalized)
            ->orWhere('phone', $phone)
            ->with('sender:id,name')
            ->orderBy('id', 'asc')
            ->take(150)
            ->get()
            ->map(function ($msg) {
                return [
                    'id' => $msg->id,
                    'direction' => $msg->direction,
                    'type' => $msg->type,
                    'message' => $msg->message,
                    'status' => $msg->status,
                    'sender_name' => $msg->sender->name ?? ($msg->direction === 'outbound' ? 'Admin' : 'Pelanggan'),
                    'metadata' => $msg->metadata,
                    'reply_to' => $msg->metadata['reply_to'] ?? null,
                    'is_edited' => !empty($msg->metadata['is_edited']),
                    'time' => $msg->created_at ? $msg->created_at->format('H:i') : '',
                    'date' => $msg->created_at ? $msg->created_at->translatedFormat('d M Y') : '',
                    'raw_date' => $msg->created_at ? $msg->created_at->format('Y-m-d') : '',
                    'is_today' => $msg->created_at ? $msg->created_at->isToday() : false,
                ];
            });

        return response()->json([
            'success' => true,
            'contact' => $contact,
            'messages' => $messages,
        ]);
    }

    /**
     * Kirim pesan teks langsung
     */
    public function sendMessage(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'message' => 'required|string',
            'pppoe_id' => 'nullable|integer',
            'customer_id' => 'nullable|integer',
            'reply_to_id' => 'nullable|integer',
        ]);

        $phone = $request->phone;
        $message = trim($request->message);

        if (empty($message)) {
            return response()->json(['success' => false, 'message' => 'Isi pesan tidak boleh kosong.'], 422);
        }

        $normalized = WhatsAppService::normalizePhone($phone);
        $contact = $this->resolveContactData($normalized);

        $metadata = [];
        if ($request->filled('reply_to_id')) {
            $replyMsg = WhatsAppMessage::find($request->reply_to_id);
            if ($replyMsg) {
                $metadata['reply_to'] = [
                    'id' => $replyMsg->id,
                    'sender' => $replyMsg->direction === 'outbound' ? 'Admin' : ($replyMsg->customer_name ?: 'Pelanggan'),
                    'message' => \Illuminate\Support\Str::limit($replyMsg->message, 120),
                ];
            }
        }

        $result = WhatsAppService::sendMessage($phone, $message, [
            'type' => 'text',
            'pppoe_id' => $request->pppoe_id ?? ($contact['pppoe_id'] ?? null),
            'customer_id' => $request->customer_id ?? ($contact['customer_id'] ?? null),
            'customer_name' => $contact['name'] ?? null,
            'sender_id' => auth()->id(),
            'metadata' => $metadata,
        ]);

        return response()->json($result);
    }

    /**
     * Edit / perbarui isi pesan teks outbound
     */
    public function updateMessage(Request $request, WhatsAppMessage $message)
    {
        $request->validate([
            'message' => 'required|string',
        ]);

        $newText = trim($request->message);
        if (empty($newText)) {
            return response()->json(['success' => false, 'message' => 'Pesan tidak boleh kosong.'], 422);
        }

        $metadata = $message->metadata ?? [];
        $metadata['is_edited'] = true;
        $metadata['edited_at'] = now()->toIso8601String();
        $metadata['original_message'] = $message->message;

        $message->update([
            'message' => $newText,
            'metadata' => $metadata,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pesan berhasil diperbarui.',
            'data' => [
                'id' => $message->id,
                'message' => $message->message,
                'is_edited' => true,
            ],
        ]);
    }

    /**
     * Hapus pesan WhatsApp dari riwayat sistem
     */
    public function deleteMessage(Request $request, WhatsAppMessage $message)
    {
        $permanent = $request->boolean('permanent', true);

        if ($permanent) {
            $message->delete();
        } else {
            $metadata = $message->metadata ?? [];
            $metadata['deleted'] = true;
            $metadata['deleted_at'] = now()->toIso8601String();

            $message->update([
                'message' => '🚫 Pesan ini telah dihapus',
                'status' => 'deleted',
                'metadata' => $metadata,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Pesan berhasil dihapus.',
        ]);
    }

    /**
     * Kirim tagihan invoice spesifik ke pelanggan via WhatsApp
     */
    public function sendInvoice(Request $request)
    {
        $request->validate([
            'invoice_id' => 'required|exists:invoices,id',
            'phone' => 'required|string',
            'custom_message' => 'nullable|string',
        ]);

        $invoice = Invoice::with(['pppoe.preOrder', 'pppoe.paket'])->findOrFail($request->invoice_id);
        $phone = $request->phone;

        // Gunakan pesan kustom atau buat teks invoice resmi otomatis
        $messageText = !empty(trim($request->custom_message))
            ? trim($request->custom_message)
            : WhatsAppService::formatInvoiceText($invoice);

        $contact = $this->resolveContactData(WhatsAppService::normalizePhone($phone));

        $result = WhatsAppService::sendMessage($phone, $messageText, [
            'type' => 'invoice',
            'pppoe_id' => $invoice->pppoe_id,
            'customer_id' => $contact['customer_id'] ?? null,
            'customer_name' => $contact['name'] ?? null,
            'sender_id' => auth()->id(),
            'metadata' => [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'total' => $invoice->total,
                'due_date' => $invoice->due_date,
            ],
        ]);

        return response()->json($result);
    }

    /**
     * Preview teks tagihan invoice sebelum dikirim
     */
    public function previewInvoiceText(Invoice $invoice)
    {
        $text = WhatsAppService::formatInvoiceText($invoice);
        return response()->json([
            'success' => true,
            'text' => $text,
            'invoice' => [
                'id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'total' => $invoice->total,
                'total_formatted' => number_format($invoice->total, 0, ',', '.'),
                'due_date' => $invoice->due_date ? Carbon::parse($invoice->due_date)->format('d/m/Y') : '-',
            ]
        ]);
    }

    /**
     * Webhook publik untuk menerima pesan masuk dari WhatsApp Gateway / Provider API (termasuk api.co.id)
     */
    public function handleWebhook(Request $request)
    {
        // 1. Handshake verification (GET) - Meta Cloud API / Api.co.id challenge
        if ($request->isMethod('GET')) {
            $challenge = $request->input('hub_challenge') 
                ?? $request->input('hub.challenge') 
                ?? $request->input('challenge');

            if ($challenge) {
                return response($challenge, 200)->header('Content-Type', 'text/plain');
            }
            return response('WhatsApp Webhook Endpoint Active', 200)->header('Content-Type', 'text/plain');
        }

        // 2. Incoming Event / Message (POST)
        $payload = $request->all();
        Log::info('WhatsApp Webhook Received: ' . json_encode($payload));

        // Ekstraksi data pesan (mendukung payload api.co.id {event: "message.received", data: {...}} dan gateway umum)
        $data = $payload['data'] ?? $payload;
        $phone = $data['customer_phone'] ?? ($data['phone_number'] ?? ($data['sender'] ?? ($data['from'] ?? ($data['phone'] ?? null))));
        $message = $data['content'] ?? ($data['message'] ?? ($data['text'] ?? ($data['body'] ?? null)));
        $mediaUrl = $data['media_url'] ?? null;

        if (empty($phone) || empty($message)) {
            return response()->json(['status' => 'ignored', 'message' => 'Event diproses atau data pesan kosong.'], 200);
        }

        $normalized = WhatsAppService::normalizePhone($phone);
        $contact = $this->resolveContactData($normalized);

        $msgRecord = WhatsAppMessage::create([
            'phone' => $normalized,
            'customer_name' => $contact['name'] ?? null,
            'customer_id' => $contact['customer_id'] ?? null,
            'pppoe_id' => $contact['pppoe_id'] ?? null,
            'direction' => 'inbound',
            'type' => 'text',
            'message' => $message,
            'media_url' => $mediaUrl,
            'status' => 'delivered',
            'metadata' => ['raw_payload' => $payload],
        ]);

        return response()->json(['status' => 'received', 'id' => $msgRecord->id]);
    }

    /**
     * Helper privat untuk melengkapi data kontak berdasarkan nomor HP
     */
    private function resolveContactData(string $normalizedPhone): array
    {
        // 1. Cari di tabel Customers
        $customer = Customer::where('no_whatsapp', 'LIKE', "%{$normalizedPhone}%")
            ->orWhereRaw("REPLACE(REPLACE(REPLACE(no_whatsapp, '-', ''), ' ', ''), '+', '') LIKE ?", ["%{$normalizedPhone}%"])
            ->first();

        // 2. Cari di tabel PreOrders / PPPoE
        $preOrder = PreOrder::with(['pppoe.paket', 'pppoe.invoices' => function ($q) {
            $q->latest();
        }])
            ->where('no_whatsapp', 'LIKE', "%{$normalizedPhone}%")
            ->orWhereRaw("REPLACE(REPLACE(REPLACE(no_whatsapp, '-', ''), ' ', ''), '+', '') LIKE ?", ["%{$normalizedPhone}%"])
            ->first();

        $pppoe = $preOrder && $preOrder->pppoe ? $preOrder->pppoe : null;
        if (!$pppoe && $customer && $customer->pppoe_username) {
            $pppoe = Pppoe::with(['paket', 'invoices' => function ($q) {
                $q->latest();
            }])->where('username', $customer->pppoe_username)->first();
        }

        $name = $customer->nama_lengkap ?? ($preOrder->nama_lengkap ?? 'Pelanggan');
        $username = $pppoe ? $pppoe->username : ($customer->pppoe_username ?? '-');
        $paketName = $pppoe && $pppoe->paket ? $pppoe->paket->nama_paket : ($customer->paket_id ?? '-');
        $address = $customer->alamat_lengkap ?? ($preOrder->alamat_lengkap ?? '-');

        // Invoice list
        $invoices = [];
        $unpaidCount = 0;
        $unpaidTotal = 0;

        if ($pppoe && $pppoe->invoices) {
            foreach ($pppoe->invoices as $inv) {
                if ($inv->status === 'unpaid') {
                    $unpaidCount++;
                    $unpaidTotal += (float)$inv->total;
                }
                $invoices[] = [
                    'id' => $inv->id,
                    'invoice_number' => $inv->invoice_number,
                    'amount' => (float)$inv->amount,
                    'total' => (float)$inv->total,
                    'total_formatted' => number_format($inv->total, 0, ',', '.'),
                    'status' => $inv->status,
                    'due_date' => $inv->due_date ? Carbon::parse($inv->due_date)->format('d/m/Y') : '-',
                    'is_overdue' => $inv->status === 'unpaid' && $inv->due_date && Carbon::parse($inv->due_date)->isPast(),
                    'xendit_url' => $inv->xendit_invoice_url,
                ];
            }
        }

        return [
            'name' => $name,
            'phone' => $normalizedPhone,
            'normalized_phone' => $normalizedPhone,
            'display_phone' => $customer->no_whatsapp ?? ($preOrder->no_whatsapp ?? $normalizedPhone),
            'username' => $username,
            'paket' => $paketName,
            'address' => $address,
            'pppoe_id' => $pppoe ? $pppoe->id : null,
            'customer_id' => $customer ? $customer->id : null,
            'is_isolir' => $pppoe ? (bool)$pppoe->is_isolir : false,
            'invoices' => $invoices,
            'unpaid_count' => $unpaidCount,
            'unpaid_total' => $unpaidTotal,
            'unpaid_total_formatted' => number_format($unpaidTotal, 0, ',', '.'),
        ];
    }
}
