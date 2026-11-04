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
        $filterStatus = $request->input('status', '');
        $filterCause = $request->input('cause', '');
        $filterDate = $request->input('date_range', '');
        $filterUsername = $request->input('username', '');

        if ($request->ajax()) {
            $query = Radacct::query();

            // Filter Status
            if ($filterStatus === 'online') {
                $query->whereNull('acctstoptime');
            } elseif ($filterStatus === 'offline') {
                $query->whereNotNull('acctstoptime');
            }

            // Filter Disconnect Cause
            if (!empty($filterCause)) {
                $query->where('acctterminatecause', $filterCause);
            }

            // Filter Username
            if (!empty($filterUsername)) {
                $query->where('username', 'like', '%' . trim($filterUsername) . '%');
            }

            // Filter Date Range
            if ($filterDate === 'today') {
                $query->where('acctstarttime', '>=', now()->startOfDay());
            } elseif ($filterDate === '7d') {
                $query->where('acctstarttime', '>=', now()->subDays(7)->startOfDay());
            } elseif ($filterDate === '30d') {
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

                    if ($customer && !empty($customer->nama_lengkap)) {
                        return '<div><div style="font-weight: 600; color: #1e293b; font-size: 12.5px; text-transform: none !important;">'
                            . e($customer->nama_lengkap) . '</div>'
                            . ($customer->no_whatsapp ? '<span style="font-size: 11px; color: #64748b; font-weight: 500;">' . e($customer->no_whatsapp) . '</span>' : '') . '</div>';
                    }

                    return '<div><span style="font-size: 11px; color: #94a3b8; font-style: italic; font-weight: 500;">Akun Mandiri / Luar</span></div>';
                })
                ->addColumn('username_col', function ($radacct) {
                    return '<div class="pppoe-username"><div class="pppoe-username-val" style="font-weight: 600; color: #0f172a; font-family: monospace; font-size: 12px; text-transform: none !important;">'
                        . e($radacct->username)
                        . '</div></div>';
                })
                ->addColumn('status_badge', function ($radacct) {
                    if ($radacct->is_online) {
                        return '<div style="text-align: center;"><span class="badge" style="background:#dcfce7; color:#15803d; border:1px solid #bbf7d0; font-size:11px; padding:2px 8px; border-radius:6px; font-weight:700;"><i class="fa-solid fa-circle text-[7px] mr-1 text-emerald-500"></i> ONLINE</span></div>';
                    }

                    return '<div style="text-align: center;"><span class="badge" style="background:#f1f5f9; color:#64748b; border:1px solid #e2e8f0; font-size:11px; padding:2px 8px; border-radius:6px; font-weight:600;">OFFLINE</span></div>';
                })
                ->addColumn('ip_address', function ($radacct) {
                    return '<div style="text-align: center; font-family: monospace; font-size: 11.5px; color: #334155; font-weight: 600;">' . e($radacct->framedipaddress ?: '-') . '</div>';
                })
                ->addColumn('mac_address', function ($radacct) {
                    return '<div style="text-align: center; font-family: monospace; font-size: 11px; color: #64748b;">' . e($radacct->callingstationid ?: '-') . '</div>';
                })
                ->addColumn('waktu_mulai', function ($radacct) {
                    if (!$radacct->acctstarttime) {
                        return '<div style="text-align: center; color: #94a3b8;">-</div>';
                    }
                    $start = Carbon::parse($radacct->acctstarttime)->setTimezone(config('app.timezone', 'Asia/Jakarta'));
                    return '<div style="text-align: center;"><div style="font-size: 11.5px; font-weight: 600; color: #334155;">' . $start->format('d/m/Y') . '</div><div style="font-size: 10.5px; color: #64748b; font-family: monospace;">' . $start->format('H:i:s') . '</div></div>';
                })
                ->addColumn('waktu_selesai', function ($radacct) {
                    if ($radacct->is_online) {
                        return '<div style="text-align: center;"><span style="font-size: 11px; color: #16a34a; font-weight: 600; font-style: italic;">Sedang Aktif</span></div>';
                    }
                    if (!$radacct->acctstoptime) {
                        return '<div style="text-align: center; color: #94a3b8;">-</div>';
                    }
                    $stop = Carbon::parse($radacct->acctstoptime)->setTimezone(config('app.timezone', 'Asia/Jakarta'));
                    return '<div style="text-align: center;"><div style="font-size: 11.5px; font-weight: 600; color: #475569;">' . $stop->format('d/m/Y') . '</div><div style="font-size: 10.5px; color: #64748b; font-family: monospace;">' . $stop->format('H:i:s') . '</div></div>';
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

                    return '<div style="text-align: center;"><span style="font-family: monospace; font-size: 11px; font-weight: 600; color: #475569; background: #f8fafc; border: 1px solid #e2e8f0; padding: 2px 6px; border-radius: 4px;">'
                        . implode(' ', $durationParts)
                        . '</span></div>';
                })
                ->addColumn('traffic', function ($radacct) {
                    $download = Radacct::formatBytes($radacct->acctoutputoctets ?? 0);
                    $upload = Radacct::formatBytes($radacct->acctinputoctets ?? 0);

                    return '<div style="text-align: center; font-family: monospace; font-size: 11px; line-height: 1.3;">'
                        . '<div style="color: #166534;"><i class="fa-solid fa-arrow-down text-[9px]"></i> ' . $download . '</div>'
                        . '<div style="color: #0369a1;"><i class="fa-solid fa-arrow-up text-[9px]"></i> ' . $upload . '</div>'
                        . '</div>';
                })
                ->addColumn('alasan_putus', function ($radacct) {
                    if ($radacct->is_online) {
                        return '<div style="text-align: center;"><span class="badge" style="background:#f0fdf4; color:#166534; border:1px solid #bbf7d0; font-size:10.5px; padding:2px 7px; border-radius:5px; font-weight:600;"><i class="fa-solid fa-bolt text-[9px] mr-1"></i> Terhubung</span></div>';
                    }

                    $cause = $radacct->acctterminatecause ?: 'Unknown';
                    $badge = match ($cause) {
                        'User-Request' => '<span class="badge" style="background:#eff6ff; color:#1e40af; border:1px solid #bfdbfe; font-size:10.5px; padding:2px 7px; border-radius:5px; font-weight:600;" title="User Request / Modem Off"><i class="fa-solid fa-power-off text-[9px] mr-1"></i> User-Request</span>',
                        'Lost-Carrier' => '<span class="badge" style="background:#fef2f2; color:#be123c; border:1px solid #fecdd3; font-size:10.5px; padding:2px 7px; border-radius:5px; font-weight:600;" title="Kabel Putus / LOS Merah"><i class="fa-solid fa-triangle-exclamation text-[9px] mr-1"></i> Lost-Carrier</span>',
                        'NAS-Reboot' => '<span class="badge" style="background:#fffbeb; color:#b45309; border:1px solid #fde68a; font-size:10.5px; padding:2px 7px; border-radius:5px; font-weight:600;"><i class="fa-solid fa-rotate text-[9px] mr-1"></i> NAS-Reboot</span>',
                        'NAS-Request' => '<span class="badge" style="background:#f8fafc; color:#475569; border:1px solid #cbd5e1; font-size:10.5px; padding:2px 7px; border-radius:5px; font-weight:600;"><i class="fa-solid fa-shield-halved text-[9px] mr-1"></i> NAS-Request</span>',
                        'Admin-Reset', 'Admin-Reboot' => '<span class="badge" style="background:#faf5ff; color:#7e22ce; border:1px solid #e9d5ff; font-size:10.5px; padding:2px 7px; border-radius:5px; font-weight:600;">Admin-Reset</span>',
                        default => '<span class="badge" style="background:#f8fafc; color:#64748b; border:1px solid #e2e8f0; font-size:10.5px; padding:2px 7px; border-radius:5px; font-weight:600;">' . e($cause) . '</span>',
                    };

                    return '<div style="text-align: center;">' . $badge . '</div>';
                })
                ->addColumn('opsi', function ($radacct) {
                    return '<div class="flex items-center justify-center">'
                        . '<button type="button" class="topbar-icon-btn !w-8 !h-8 !rounded-lg" onclick="showSessionDetail(' . $radacct->radacctid . ')" title="Detail Sesi">'
                        . '<i class="fa-solid fa-circle-info text-slate-500"></i>'
                        . '</button>'
                        . '</div>';
                })
                ->rawColumns(['pelanggan', 'username_col', 'status_badge', 'ip_address', 'mac_address', 'waktu_mulai', 'waktu_selesai', 'durasi', 'traffic', 'alasan_putus', 'opsi'])
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

        return view('pppoe.sessions', compact('stats', 'causes', 'filterStatus', 'filterCause', 'filterDate', 'filterUsername'));
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
