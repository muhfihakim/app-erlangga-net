<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Pppoe;
use App\Models\Radacct;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Yajra\DataTables\Facades\DataTables;

class PppoeSessionController extends Controller
{
    /**
     * Tampilkan riwayat dan sesi aktif PPPoE dari FreeRADIUS accounting (radacct).
     */
    public function index(Request $request)
    {
        $statusFilter = $request->input('status', '');
        $causeFilter = $request->input('cause', '');
        $dateFilter = $request->input('date_range', '');
        $usernameFilter = $request->input('username', '');

        if ($request->ajax()) {
            $query = Radacct::query();

            // Filter Status
            if ($statusFilter === 'online') {
                $query->whereNull('acctstoptime');
            } elseif ($statusFilter === 'offline') {
                $query->whereNotNull('acctstoptime');
            }

            // Filter Disconnect Cause
            if (!empty($causeFilter)) {
                $query->where('acctterminatecause', $causeFilter);
            }

            // Filter Username
            if (!empty($usernameFilter)) {
                $query->where('username', 'like', '%' . trim($usernameFilter) . '%');
            }

            // Filter Date Range
            if ($dateFilter === 'today') {
                $query->where('acctstarttime', '>=', now()->startOfDay());
            } elseif ($dateFilter === '7d') {
                $query->where('acctstarttime', '>=', now()->subDays(7)->startOfDay());
            } elseif ($dateFilter === '30d') {
                $query->where('acctstarttime', '>=', now()->subDays(30)->startOfDay());
            }

            // Search box filter
            if ($search = $request->input('search.value')) {
                $cleanSearch = trim($search);
                $query->where(function ($q) use ($cleanSearch) {
                    $q->where('username', 'like', "%{$cleanSearch}%")
                        ->orWhere('framedipaddress', 'like', "%{$cleanSearch}%")
                        ->orWhere('callingstationid', 'like', "%{$cleanSearch}%")
                        ->orWhere('acctterminatecause', 'like', "%{$cleanSearch}%");
                });
            }

            // Default sorting by radacctid DESC
            if (!$request->has('order')) {
                $query->orderByDesc('radacctid');
            }

            $customerCache = [];

            return DataTables::eloquent($query)
                ->addColumn('no', function () {
                    return ''; // Diisi oleh render DataTables
                })
                ->addColumn('pelanggan', function ($radacct) use (&$customerCache) {
                    $rawUsername = $radacct->username;
                    if (!isset($customerCache[$rawUsername])) {
                        $cleanUsername = explode('@', $rawUsername)[0];
                        $cust = Customer::where('pppoe_username', $rawUsername)
                            ->orWhere('pppoe_username', $cleanUsername)
                            ->with('paket')
                            ->first();

                        if (!$cust) {
                            $pppoe = Pppoe::where('username', $rawUsername)
                                ->orWhere('username', $cleanUsername)
                                ->with(['paket', 'customer'])
                                ->first();

                            if ($pppoe && $pppoe->customer) {
                                $cust = $pppoe->customer;
                            }
                        }

                        $customerCache[$rawUsername] = $cust;
                    }

                    $customer = $customerCache[$rawUsername];

                    $nameHtml = '';
                    if ($customer && !empty($customer->nama_lengkap)) {
                        $nameHtml = '<div style="font-weight: 600; color: #1e293b; font-size: 12.5px; text-transform: none !important;">'
                            . e($customer->nama_lengkap) . '</div>'
                            . ($customer->no_whatsapp ? '<div style="font-size: 11px; color: #64748b; font-weight: 500;">' . e($customer->no_whatsapp) . '</div>' : '');
                    } else {
                        $nameHtml = '<div style="font-weight: 600; color: #475569; font-size: 12px; font-style: italic;">Profil Belum Ditautkan</div>';
                    }

                    $usernameHtml = '<div class="flex items-center gap-1.5 mt-0.5">'
                        . '<span class="font-mono text-xs font-semibold text-slate-800 bg-slate-100 px-1.5 py-0.5 rounded border border-slate-200" title="' . e($rawUsername) . '">'
                        . e($rawUsername)
                        . '</span>'
                        . '</div>';

                    return '<div>' . $nameHtml . $usernameHtml . '</div>';
                })
                ->addColumn('status_badge', function ($radacct) {
                    if ($radacct->is_online) {
                        return '<div class="text-center">'
                            . '<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">'
                            . '<span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>'
                            . 'ONLINE'
                            . '</span>'
                            . '</div>';
                    }

                    return '<div class="text-center">'
                        . '<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-600 border border-slate-200">'
                        . '<i class="fa-solid fa-circle-stop text-[10px] text-slate-400"></i>'
                        . 'OFFLINE'
                        . '</span>'
                        . '</div>';
                })
                ->addColumn('ip_mac', function ($radacct) {
                    $ip = $radacct->framedipaddress ?: '-';
                    $mac = $radacct->callingstationid ?: '-';

                    return '<div class="text-center">'
                        . '<div class="font-mono text-xs font-bold text-slate-800">' . e($ip) . '</div>'
                        . '<div class="font-mono text-[11px] text-slate-500 mt-0.5" title="MAC ONT / Router">' . e($mac) . '</div>'
                        . '</div>';
                })
                ->addColumn('waktu_mulai', function ($radacct) {
                    if (!$radacct->acctstarttime) {
                        return '<div class="text-center text-slate-400">-</div>';
                    }
                    $start = Carbon::parse($radacct->acctstarttime)->setTimezone(config('app.timezone', 'Asia/Jakarta'));
                    return '<div class="text-center">'
                        . '<div class="text-xs font-semibold text-slate-800">' . $start->format('d M Y') . '</div>'
                        . '<div class="text-[11px] text-slate-500 font-mono">' . $start->format('H:i:s') . ' WIB</div>'
                        . '</div>';
                })
                ->addColumn('waktu_selesai', function ($radacct) {
                    if ($radacct->is_online) {
                        return '<div class="text-center">'
                            . '<span class="text-xs font-semibold text-emerald-600 italic">Sedang Aktif</span>'
                            . '</div>';
                    }
                    if (!$radacct->acctstoptime) {
                        return '<div class="text-center text-slate-400">-</div>';
                    }
                    $stop = Carbon::parse($radacct->acctstoptime)->setTimezone(config('app.timezone', 'Asia/Jakarta'));
                    return '<div class="text-center">'
                        . '<div class="text-xs font-semibold text-slate-700">' . $stop->format('d M Y') . '</div>'
                        . '<div class="text-[11px] text-slate-500 font-mono">' . $stop->format('H:i:s') . ' WIB</div>'
                        . '</div>';
                })
                ->addColumn('durasi', function ($radacct) {
                    $seconds = (int) $radacct->acctsessiontime;
                    if ($radacct->is_online && $radacct->acctstarttime) {
                        $seconds = max($seconds, Carbon::parse($radacct->acctstarttime)->diffInSeconds(now()));
                    }

                    $days = floor($seconds / 86400);
                    $hours = floor(($seconds % 86400) / 3600);
                    $mins = floor(($seconds % 3600) / 60);
                    $secs = $seconds % 60;

                    $durationParts = [];
                    if ($days > 0) $durationParts[] = "{$days}h";
                    if ($hours > 0) $durationParts[] = "{$hours}j";
                    if ($mins > 0) $durationParts[] = "{$mins}m";
                    if (empty($durationParts) || ($days == 0 && count($durationParts) < 2)) {
                        $durationParts[] = "{$secs}d";
                    }

                    $badgeClass = $radacct->is_online
                        ? 'bg-emerald-50 text-emerald-800 border-emerald-200'
                        : 'bg-slate-50 text-slate-700 border-slate-200';

                    return '<div class="text-center">'
                        . '<span class="inline-block px-2 py-0.5 rounded border text-xs font-semibold font-mono ' . $badgeClass . '">'
                        . implode(' ', $durationParts)
                        . '</span>'
                        . '</div>';
                })
                ->addColumn('traffic', function ($radacct) {
                    // acctoutputoctets = download to client, acctinputoctets = upload from client
                    $download = Radacct::formatBytes($radacct->acctoutputoctets ?? 0);
                    $upload = Radacct::formatBytes($radacct->acctinputoctets ?? 0);

                    return '<div class="text-center font-mono text-[11px] leading-tight">'
                        . '<div class="text-emerald-600 font-semibold" title="Download"><i class="fa-solid fa-arrow-down text-[10px] mr-1"></i>' . $download . '</div>'
                        . '<div class="text-sky-600 font-semibold mt-0.5" title="Upload"><i class="fa-solid fa-arrow-up text-[10px] mr-1"></i>' . $upload . '</div>'
                        . '</div>';
                })
                ->addColumn('alasan_putus', function ($radacct) {
                    if ($radacct->is_online) {
                        return '<div class="text-center">'
                            . '<span class="inline-block px-2 py-0.5 rounded text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">'
                            . '<i class="fa-solid fa-bolt mr-1"></i> Terhubung'
                            . '</span>'
                            . '</div>';
                    }

                    $cause = $radacct->acctterminatecause ?: 'Unknown';
                    $style = match ($cause) {
                        'User-Request' => ['bg' => '#eff6ff', 'text' => '#1d4ed8', 'border' => '#bfdbfe', 'icon' => 'fa-power-off', 'label' => 'User Request (Modem Off)'],
                        'Lost-Carrier' => ['bg' => '#fef2f2', 'text' => '#b91c1c', 'border' => '#fecaca', 'icon' => 'fa-triangle-exclamation', 'label' => 'Lost Carrier (LOS/Kabel Putus)'],
                        'NAS-Reboot' => ['bg' => '#fffbeb', 'text' => '#b45309', 'border' => '#fde68a', 'icon' => 'fa-rotate-right', 'label' => 'NAS Reboot'],
                        'NAS-Request' => ['bg' => '#f8fafc', 'text' => '#475569', 'border' => '#cbd5e1', 'icon' => 'fa-shield-halved', 'label' => 'Kicked by NAS / Admin'],
                        'Admin-Reboot', 'Admin-Reset' => ['bg' => '#faf5ff', 'text' => '#7e22ce', 'border' => '#e9d5ff', 'icon' => 'fa-user-gear', 'label' => 'Admin Reset'],
                        default => ['bg' => '#f8fafc', 'text' => '#64748b', 'border' => '#e2e8f0', 'icon' => 'fa-circle-info', 'label' => $cause],
                    };

                    return '<div class="text-center">'
                        . '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-semibold border" style="background:' . $style['bg'] . '; color:' . $style['text'] . '; border-color:' . $style['border'] . ';" title="' . e($cause) . '">'
                        . '<i class="fa-solid ' . $style['icon'] . ' text-[10px]"></i> ' . e($style['label'])
                        . '</span>'
                        . '</div>';
                })
                ->addColumn('opsi', function ($radacct) {
                    return '<div class="flex items-center justify-center">'
                        . '<button type="button" class="topbar-icon-btn !w-8 !h-8 !rounded-lg hover:!bg-sky-50 hover:!border-sky-300" onclick="showSessionDetail(' . $radacct->radacctid . ')" title="Lihat Detail Sesi Accounting">'
                        . '<i class="fa-solid fa-circle-info text-sky-600"></i>'
                        . '</button>'
                        . '</div>';
                })
                ->rawColumns(['pelanggan', 'status_badge', 'ip_mac', 'waktu_mulai', 'waktu_selesai', 'durasi', 'traffic', 'alasan_putus', 'opsi'])
                ->make(true);
        }

        // Cache statistical queries
        $totalSessions = Cache::remember('radacct_total_count', 60, function () {
            return Radacct::count();
        });

        $onlineSessions = Radacct::whereNull('acctstoptime')->count();
        $offlineSessions = max(0, $totalSessions - $onlineSessions);

        $active24h = Cache::remember('radacct_active_24h_count', 60, function () {
            return Radacct::where('acctupdatetime', '>=', now()->subHours(24))
                ->orWhere('acctstarttime', '>=', now()->subHours(24))
                ->count();
        });

        $causes = Cache::remember('radacct_distinct_causes', 300, function () {
            return Radacct::whereNotNull('acctterminatecause')
                ->where('acctterminatecause', '!=', '')
                ->distinct()
                ->pluck('acctterminatecause')
                ->filter()
                ->values()
                ->all();
        });

        $stats = [
            'total' => $totalSessions,
            'online' => $onlineSessions,
            'offline' => $offlineSessions,
            'active24h' => $active24h,
        ];

        return view('pppoe.sessions', compact('stats', 'causes', 'statusFilter', 'causeFilter', 'usernameFilter'));
    }

    /**
     * Ambil data lengkap satu sesi accounting untuk modal detail.
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

        $start = $session->acctstarttime ? Carbon::parse($session->acctstarttime)->setTimezone(config('app.timezone', 'Asia/Jakarta'))->format('d M Y, H:i:s') . ' WIB' : '-';
        $update = $session->acctupdatetime ? Carbon::parse($session->acctupdatetime)->setTimezone(config('app.timezone', 'Asia/Jakarta'))->format('d M Y, H:i:s') . ' WIB' : '-';
        $stop = $session->acctstoptime ? Carbon::parse($session->acctstoptime)->setTimezone(config('app.timezone', 'Asia/Jakarta'))->format('d M Y, H:i:s') . ' WIB' : ($session->is_online ? 'Sedang Aktif (Online)' : '-');

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
