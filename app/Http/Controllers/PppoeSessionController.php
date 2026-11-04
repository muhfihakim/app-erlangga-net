<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Paket;
use App\Models\Pppoe;
use App\Models\Radacct;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Yajra\DataTables\Facades\DataTables;

class PppoeSessionController extends Controller
{
    /**
     * Tampilkan daftar akun PPPoE dengan status sesi real-time dari RADIUS.
     */
    public function index(Request $request)
    {
        $filterStatus = $request->input('status', '');
        $filterPaket = $request->input('paket_id', '');

        if ($request->ajax()) {
            // Ambil session online dari FreeRADIUS (cache 30 detik untuk performa)
            $onlineSessions = Cache::remember('radius_online_sessions_map', 30, function () {
                return Radacct::whereNull('acctstoptime')
                    ->select(
                        'radacctid',
                        'username',
                        'framedipaddress',
                        'callingstationid',
                        'acctstarttime',
                        'acctsessiontime',
                        'acctoutputoctets',
                        'acctinputoctets'
                    )
                    ->get()
                    ->keyBy('username');
            });

            $onlineUsernames = $onlineSessions->keys()->all();

            $query = Pppoe::with(['customer', 'paket'])->select('pppoes.*');

            // Filter Status
            if ($filterStatus === 'online') {
                $query->whereIn('pppoes.username', $onlineUsernames);
            } elseif ($filterStatus === 'offline') {
                $query->whereNotIn('pppoes.username', $onlineUsernames);
            }

            // Filter Paket
            if (!empty($filterPaket)) {
                $query->where('pppoes.paket_id', $filterPaket);
            }

            // Live Search
            if ($search = $request->input('search.value')) {
                $cleanSearch = trim($search);
                $query->where(function ($q) use ($cleanSearch) {
                    $q->where('pppoes.username', 'like', "%{$cleanSearch}%")
                        ->orWhere('pppoes.ip_address', 'like', "%{$cleanSearch}%")
                        ->orWhereHas('customer', function ($cq) use ($cleanSearch) {
                            $cq->where('nama_lengkap', 'like', "%{$cleanSearch}%")
                                ->orWhere('no_whatsapp', 'like', "%{$cleanSearch}%")
                                ->orWhere('id_pelanggan', 'like', "%{$cleanSearch}%");
                        });
                });
            }

            return DataTables::eloquent($query)
                ->addColumn('no', function () {
                    return ''; // Diisi oleh JS render DataTables
                })
                ->addColumn('pelanggan', function ($pppoe) {
                    $cust = $pppoe->customer;
                    if ($cust && !empty($cust->nama_lengkap)) {
                        $phone = $cust->no_whatsapp ? '<span style="font-size: 11px; color: #64748b; font-weight: 500;">' . e($cust->no_whatsapp) . '</span>' : '';
                        return '<div><div style="font-weight: 600; color: #1e293b; font-size: 12.5px; text-transform: none !important;">' . e($cust->nama_lengkap) . '</div>' . $phone . '</div>';
                    }

                    if (!empty($pppoe->keterangan)) {
                        return '<div><div style="font-weight: 600; color: #1e293b; font-size: 12.5px;">' . e($pppoe->keterangan) . '</div><span style="font-size: 10px; color: #64748b; background: #f1f5f9; padding: 1px 6px; border-radius: 4px; font-weight: 600;">PPPoE Mandiri</span></div>';
                    }

                    return '<div><span style="font-size: 11px; color: #94a3b8; font-style: italic; font-weight: 500;">Tanpa Profil</span></div>';
                })
                ->addColumn('username_col', function ($pppoe) {
                    return '<div class="pppoe-username"><div class="pppoe-username-val" style="font-weight: 600; color: #0f172a; font-family: monospace; font-size: 12px; text-transform: none !important;">'
                        . e($pppoe->username)
                        . '</div></div>';
                })
                ->addColumn('paket', function ($pppoe) {
                    $namaPaket = $pppoe->paket->nama_paket ?? '-';
                    return '<div><span class="badge" style="background:#e0f2fe; color:#0369a1; font-weight:700; font-size:11px; padding:2px 8px; border-radius:6px;">' . e($namaPaket) . '</span></div>';
                })
                ->addColumn('status_badge', function ($pppoe) use ($onlineSessions) {
                    $isOnline = $onlineSessions->has($pppoe->username);
                    if ($isOnline) {
                        return '<div style="text-align: center;"><span class="badge" style="background:#dcfce7; color:#15803d; border:1px solid #bbf7d0; font-size:11px; padding:2px 8px; border-radius:6px; font-weight:700;"><i class="fa-solid fa-circle text-[7px] mr-1 text-emerald-500"></i> ONLINE</span></div>';
                    }
                    return '<div style="text-align: center;"><span class="badge" style="background:#f1f5f9; color:#64748b; border:1px solid #e2e8f0; font-size:11px; padding:2px 8px; border-radius:6px; font-weight:600;">OFFLINE</span></div>';
                })
                ->addColumn('ip_address', function ($pppoe) use ($onlineSessions) {
                    $session = $onlineSessions->get($pppoe->username);
                    $ip = $session?->framedipaddress ?: ($pppoe->ip_address ?: '-');
                    return '<div style="text-align: center; font-family: monospace; font-size: 11.5px; color: #334155; font-weight: 600;">' . e($ip) . '</div>';
                })
                ->addColumn('uptime', function ($pppoe) use ($onlineSessions) {
                    $session = $onlineSessions->get($pppoe->username);
                    if (!$session || !$session->acctstarttime) {
                        return '<div style="text-align: center; color: #94a3b8; font-size: 11px;">-</div>';
                    }
                    $start = Carbon::parse($session->acctstarttime)->setTimezone(config('app.timezone', 'Asia/Jakarta'));
                    $seconds = max(0, Carbon::parse($session->acctstarttime)->diffInSeconds(now()));
                    $days = floor($seconds / 86400);
                    $hours = floor(($seconds % 86400) / 3600);
                    $mins = floor(($seconds % 3600) / 60);

                    $parts = [];
                    if ($days > 0) $parts[] = "{$days}h";
                    if ($hours > 0) $parts[] = "{$hours}j";
                    if ($mins > 0 || empty($parts)) $parts[] = "{$mins}m";
                    $duration = implode(' ', $parts);

                    return '<div style="text-align: center;"><div style="font-size: 11px; font-weight: 600; color: #15803d; font-family: monospace;">' . $duration . '</div><div style="font-size: 10px; color: #94a3b8;">sejak ' . $start->format('d/m H:i') . '</div></div>';
                })
                ->addColumn('traffic', function ($pppoe) use ($onlineSessions) {
                    $session = $onlineSessions->get($pppoe->username);
                    if (!$session) {
                        return '<div style="text-align: center; color: #94a3b8; font-size: 11px;">-</div>';
                    }
                    $download = Radacct::formatBytes($session->acctoutputoctets ?? 0);
                    $upload = Radacct::formatBytes($session->acctinputoctets ?? 0);

                    return '<div style="text-align: center; font-family: monospace; font-size: 11px; line-height: 1.3;">'
                        . '<div style="color: #166534;"><i class="fa-solid fa-arrow-down text-[9px]"></i> ' . $download . '</div>'
                        . '<div style="color: #0369a1;"><i class="fa-solid fa-arrow-up text-[9px]"></i> ' . $upload . '</div>'
                        . '</div>';
                })
                ->addColumn('opsi', function ($pppoe) {
                    $custName = $pppoe->customer->nama_lengkap ?? '';
                    return '<div class="flex items-center justify-center">'
                        . '<button type="button" class="btn-cancel !h-7.5 !px-2.5 text-xs font-semibold flex items-center gap-1.5 bg-white border border-slate-200 text-slate-700 hover:bg-slate-50" onclick="showAccountDetail(\'' . addslashes($pppoe->username) . '\', \'' . addslashes($custName) . '\')" title="Detail Sesi Lengkap & Riwayat">'
                        . '<i class="fa-solid fa-circle-info text-sky-600"></i> Detail'
                        . '</button>'
                        . '</div>';
                })
                ->rawColumns(['pelanggan', 'username_col', 'paket', 'status_badge', 'ip_address', 'uptime', 'traffic', 'opsi'])
                ->make(true);
        }

        // Statistik ringkas (Total Akun PPPoE, Sesi Online Aktif, Aktivitas 24 Jam)
        $totalPppoeCount = Cache::remember('pppoe_total_count', 60, function () {
            return Pppoe::count();
        });

        $onlineSessionsCount = Cache::remember('radius_online_count', 30, function () {
            return Radacct::whereNull('acctstoptime')->count();
        });

        $active24h = Cache::remember('radacct_active_24h_count', 60, function () {
            return Radacct::where('acctupdatetime', '>=', now()->subHours(24))
                ->orWhere('acctstarttime', '>=', now()->subHours(24))
                ->count();
        });

        $pakets = Paket::orderBy('nama_paket')->get();

        $stats = [
            'total_pppoe' => $totalPppoeCount,
            'online' => $onlineSessionsCount,
            'active24h' => $active24h,
        ];

        return view('pppoe.sessions', compact('stats', 'pakets', 'filterStatus', 'filterPaket'));
    }

    /**
     * Ambil data detail lengkap sesi terbaru serta riwayat sesi untuk 1 user PPPoE.
     */
    public function userDetail(Request $request)
    {
        $username = trim($request->input('username', ''));
        if (empty($username)) {
            return response()->json(['success' => false, 'message' => 'Username tidak valid'], 400);
        }

        $cleanUsername = explode('@', $username)[0];
        $customer = Customer::where('pppoe_username', $username)
            ->orWhere('pppoe_username', $cleanUsername)
            ->with('paket')
            ->first();

        $pppoe = Pppoe::where('username', $username)
            ->orWhere('username', $cleanUsername)
            ->with(['paket', 'customer'])
            ->first();

        $sessions = Radacct::where('username', $username)
            ->orWhere('username', $cleanUsername)
            ->orderByDesc('radacctid')
            ->take(50)
            ->get();

        $latestSession = $sessions->first();
        $latestData = null;

        if ($latestSession) {
            $dlLatest = (int) ($latestSession->acctoutputoctets ?? 0);
            $ulLatest = (int) ($latestSession->acctinputoctets ?? 0);
            $totalLatest = $dlLatest + $ulLatest;

            $start = $latestSession->acctstarttime ? Carbon::parse($latestSession->acctstarttime)->setTimezone(config('app.timezone', 'Asia/Jakarta'))->format('d/m/Y H:i:s') . ' WIB' : '-';
            $update = $latestSession->acctupdatetime ? Carbon::parse($latestSession->acctupdatetime)->setTimezone(config('app.timezone', 'Asia/Jakarta'))->format('d/m/Y H:i:s') . ' WIB' : '-';
            $stop = $latestSession->acctstoptime ? Carbon::parse($latestSession->acctstoptime)->setTimezone(config('app.timezone', 'Asia/Jakarta'))->format('d/m/Y H:i:s') . ' WIB' : ($latestSession->is_online ? 'Sedang Aktif (Online)' : '-');

            $latestData = [
                'radacctid' => $latestSession->radacctid,
                'acctsessionid' => $latestSession->acctsessionid ?: '-',
                'acctuniqueid' => $latestSession->acctuniqueid ?: '-',
                'username' => $latestSession->username,
                'is_online' => $latestSession->is_online,
                'framedipaddress' => $latestSession->framedipaddress ?: ($pppoe->ip_address ?? '-'),
                'callingstationid' => $latestSession->callingstationid ?: '-',
                'nasipaddress' => $latestSession->nasipaddress ?: '-',
                'nasportid' => $latestSession->nasportid ?: '-',
                'nasporttype' => $latestSession->nasporttype ?: 'Ethernet',
                'calledstationid' => $latestSession->calledstationid ?: '-',
                'servicetype' => $latestSession->servicetype ?: 'Framed-User',
                'framedprotocol' => $latestSession->framedprotocol ?: 'PPP',
                'start_formatted' => $start,
                'update_formatted' => $update,
                'stop_formatted' => $stop,
                'formatted_duration' => $latestSession->formatted_session_time,
                'download_formatted' => Radacct::formatBytes($dlLatest),
                'upload_formatted' => Radacct::formatBytes($ulLatest),
                'total_formatted' => Radacct::formatBytes($totalLatest),
                'acctterminatecause' => $latestSession->acctterminatecause ?: ($latestSession->is_online ? 'Sedang Aktif' : 'Tidak Tercatat'),
            ];
        }

        // Format history list (tanpa Status dan Alasan Putus, dengan Total DL/UL)
        $historyList = $sessions->map(function ($s) {
            $dl = (int) ($s->acctoutputoctets ?? 0);
            $ul = (int) ($s->acctinputoctets ?? 0);
            $total = $dl + $ul;

            $start = $s->acctstarttime ? Carbon::parse($s->acctstarttime)->setTimezone(config('app.timezone', 'Asia/Jakarta'))->format('d/m/Y H:i:s') : '-';
            $stop = $s->acctstoptime ? Carbon::parse($s->acctstoptime)->setTimezone(config('app.timezone', 'Asia/Jakarta'))->format('d/m/Y H:i:s') : ($s->is_online ? 'Sedang Aktif' : '-');

            return [
                'radacctid' => $s->radacctid,
                'is_online' => $s->is_online,
                'start' => $start,
                'stop' => $stop,
                'duration' => $s->formatted_session_time,
                'download' => Radacct::formatBytes($dl),
                'upload' => Radacct::formatBytes($ul),
                'total_traffic' => Radacct::formatBytes($total),
                'ip' => $s->framedipaddress ?: '-',
            ];
        });

        return response()->json([
            'success' => true,
            'username' => $username,
            'customer' => $customer ? [
                'nama_lengkap' => $customer->nama_lengkap,
                'no_whatsapp' => $customer->no_whatsapp,
                'paket' => $customer->paket->nama_paket ?? ($pppoe->paket->nama_paket ?? '-'),
                'id_pelanggan' => $customer->id_pelanggan ?? '-',
            ] : ($pppoe ? [
                'nama_lengkap' => $pppoe->keterangan ?: 'Akun Mandiri',
                'no_whatsapp' => '-',
                'paket' => $pppoe->paket->nama_paket ?? '-',
                'id_pelanggan' => '-',
            ] : null),
            'pppoe' => $pppoe ? [
                'ip_address' => $pppoe->ip_address,
                'router' => $pppoe->router,
            ] : null,
            'latest_session' => $latestData,
            'history' => $historyList,
            'total_history' => $sessions->count(),
        ]);
    }

    /**
     * Ambil seluruh riwayat sesi FreeRADIUS (backward compatibility).
     */
    public function userHistory(Request $request)
    {
        return $this->userDetail($request);
    }

    /**
     * Ambil data lengkap satu sesi accounting untuk modal detail parameter.
     */
    public function show($id)
    {
        $session = Radacct::findOrFail($id);

        $cleanUsername = explode('@', $session->username)[0];
        $customer = Customer::where('pppoe_username', $session->username)
            ->orWhere('pppoe_username', $cleanUsername)
            ->with('paket')
            ->first();

        $pppoe = Pppoe::where('username', $session->username)
            ->orWhere('username', $cleanUsername)
            ->with(['paket', 'customer'])
            ->first();

        $sessionData = $session->toArray();
        $sessionData['is_online'] = $session->is_online;
        $sessionData['formatted_duration'] = $session->formatted_session_time;
        $sessionData['download_formatted'] = Radacct::formatBytes($session->acctoutputoctets ?? 0);
        $sessionData['upload_formatted'] = Radacct::formatBytes($session->acctinputoctets ?? 0);

        $start = $session->acctstarttime ? Carbon::parse($session->acctstarttime)->setTimezone(config('app.timezone', 'Asia/Jakarta'))->format('d/m/Y H:i:s') . ' WIB' : '-';
        $update = $session->acctupdatetime ? Carbon::parse($session->acctupdatetime)->setTimezone(config('app.timezone', 'Asia/Jakarta'))->format('d/m/Y H:i:s') . ' WIB' : '-';
        $stop = $session->acctstoptime ? Carbon::parse($session->acctstoptime)->setTimezone(config('app.timezone', 'Asia/Jakarta'))->format('d/m/Y H:i:s') . ' WIB' : ($session->is_online ? 'Sedang Aktif (Online)' : '-');

        $sessionData['start_formatted'] = $start;
        $sessionData['update_formatted'] = $update;
        $sessionData['stop_formatted'] = $stop;

        return response()->json([
            'success' => true,
            'session' => $sessionData,
            'customer' => $customer ? [
                'id' => $customer->id,
                'id_pelanggan' => $customer->id_pelanggan ?? '-',
                'nama_lengkap' => $customer->nama_lengkap,
                'no_whatsapp' => $customer->no_whatsapp,
                'alamat_lengkap' => $customer->alamat_lengkap,
                'paket' => $customer->paket->nama_paket ?? ($pppoe->paket->nama_paket ?? '-'),
            ] : null,
            'pppoe' => $pppoe ? [
                'id' => $pppoe->id,
                'router' => $pppoe->router,
                'paket' => $pppoe->paket->nama_paket ?? '-',
                'is_isolir' => $pppoe->is_isolir,
            ] : null,
        ]);
    }
}
