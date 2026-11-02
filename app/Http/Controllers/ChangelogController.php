<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ChangelogController extends Controller
{
    public function index(Request $request)
    {
        $releases = [
            [
                'version' => 'v1.3.0',
                'commit' => 'b92caa6',
                'date' => '2 November 2026',
                'badge' => 'Rilis Terbaru',
                'badge_color' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                'highlight' => true,
                'title' => 'Standardisasi Tombol Aksi Tabel (Icon-Only), Perapian UI/UX, dan Pembersihan Modul Usang',
                'summary' => 'Penyelarasan menyeluruh kolom aksi tabel di seluruh modul menjadi icon-only seragam, penyeragaman warna tombol hapus dengan hover rose lembut, penghapusan bersih modul monitoring router yang usang, penambahan alur prospek tunggu (waiting list), dan implementasi DataTables server-side performa tinggi.',
                'sections' => [
                    [
                        'name' => 'Penyelarasan Antarmuka & UI/UX',
                        'badge' => 'UI / UX',
                        'badge_color' => 'bg-slate-100 text-slate-700 border-slate-300',
                        'items' => [
                            'Standardisasi seluruh tombol kolom Status/OPSI menjadi format icon-only seragam abu-abu (`topbar-icon-btn !w-8 !h-8 !rounded-lg`) pada modul Pre-Order, Waiting List, Pelanggan, Pemeliharaan, PPPoE, OLT, ODP, GenieACS, Invoices, Transaksi, Kasbon, dan Penarikan.',
                            'Penyeragaman warna tombol hapus destruktif di semua tabel menggunakan warna netral (`text-slate-500`) dan bertransisi ke rose saat di-hover (`hover:text-rose-600 hover:!bg-rose-50`).',
                            'Perampingan lebar kolom status dan opsi pada tabel untuk memaksimalkan ruang baca data pelanggan dan jaringan.',
                            'Penyeragaman tombol CRUD di semua modal form (Simpan & Batal) menggunakan ukuran dan icon FontAwesome yang proporsional.',
                        ]
                    ],
                    [
                        'name' => 'Fitur & Alur Operasional',
                        'badge' => 'Fitur Baru',
                        'badge_color' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                        'items' => [
                            'Modul Prospek Tunggu (`/pre-orders/waiting-list`) untuk menampung calon pelanggan kendala teknis (unspek) yang menunggu perluasan jaringan fiber optik.',
                            'Sinkronisasi form aktivasi teknisi dengan data akun PPPoE yang telah disiapkan admin pada pre-order secara otomatis (autofill).',
                            'Tampilan filter default halaman Pre-Order berbasis calon pelanggan yang belum dibuatkan akun PPPoE.',
                        ]
                    ],
                    [
                        'name' => 'Performa & Pembersihan Sistem',
                        'badge' => 'Optimasi',
                        'badge_color' => 'bg-sky-50 text-sky-700 border-sky-200',
                        'items' => [
                            'Penghapusan tuntas modul Traffic Monitoring Router (`/monitoring`) dan API endpoint yang tidak efisien.',
                            'Pembersihan dependensi WebRTC PTT legacy, konfigurasi audio websocket, dan file build mobile Android yang tidak terpakai.',
                            'Migrasi tabel Pemeliharaan (`/maintenances`) dan PPPoE Aktif (`/pppoe`) ke DataTables Server-Side murni dengan debounce search.',
                        ]
                    ]
                ]
            ],
            [
                'version' => 'v1.0.0-monolith-stable',
                'commit' => '158fa36',
                'date' => '22 Oktober 2026',
                'badge' => 'Monolith Stable',
                'badge_color' => 'bg-blue-50 text-blue-700 border-blue-200',
                'highlight' => false,
                'title' => 'Stabilisasi Arsitektur Blade Monolith, Integrasi FreeRADIUS, Billing Otomatis, & GenieACS TR-069',
                'summary' => 'Konsolidasi arsitektur sistem monolith Blade stabil dengan integrasi lengkap database FreeRADIUS, otomatisasi penagihan dan isolir pelanggan MikroTik, manajemen modem ONT jarak jauh via GenieACS TR-069, serta pencairan dana XenPlatform.',
                'sections' => [
                    [
                        'name' => 'FreeRADIUS & Penagihan (Billing)',
                        'badge' => 'Radius & Billing',
                        'badge_color' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                        'items' => [
                            'Sinkronisasi database FreeRADIUS (`radcheck`, `radreply`, `radusergroup`, `userinfo`) untuk manajemen autentikasi PPPoE berkecepatan tinggi.',
                            'Otomatisasi isolir pelanggan via command `billing:process` dan `billing:isolir` dengan grup `d_isolir` serta pemutusan sesi aktif MikroTik otomatis.',
                            'Integrasi portal isolir mandiri (`/isolir`) untuk pembayaran mandiri pelanggan via payment gateway Xendit.',
                            'Modul Transaksi Masuk (`/transactions`) dan Invoice (`/invoices`) dengan kalkulasi PPN dan bukti bayar.',
                        ]
                    ],
                    [
                        'name' => 'Infrastruktur Jaringan & Hardware',
                        'badge' => 'Infrastruktur',
                        'badge_color' => 'bg-amber-50 text-amber-700 border-amber-200',
                        'items' => [
                            'Integrasi GenieACS TR-069 (`/genieacs`) untuk monitoring modem ONT pelanggan, konfigurasi SSID/password Wi-Fi, dan pembacaan daya optik (Rx/Tx Power).',
                            'Dashboard OLT (`/olt`) dengan terminal SSH/Telnet interaktif dan generator skrip aktivasi modem.',
                            'Peta ODP interaktif (`/odp`) berbasis Google Maps dengan visualisasi kapasitas port dan penanda koordinat.',
                        ]
                    ],
                    [
                        'name' => 'Keuangan & Sistem',
                        'badge' => 'Sistem & Backup',
                        'badge_color' => 'bg-teal-50 text-teal-700 border-teal-200',
                        'items' => [
                            'Modul Penarikan Dana Superadmin (`/withdrawals`) terhubung XenPlatform Disbursement Xendit dengan verifikasi OTP WhatsApp.',
                            'Modul Backup & Restore Database CSV (`/migration`) mandiri dengan validasi role administrator.',
                        ]
                    ]
                ]
            ],
            [
                'version' => 'v1.2.1',
                'commit' => '4d548de',
                'date' => '9 Juli 2026',
                'badge' => 'Perbaikan',
                'badge_color' => 'bg-slate-100 text-slate-700 border-slate-300',
                'highlight' => false,
                'title' => 'Pencatatan Log PPPoE dan Penyesuaian Spacing Modal',
                'summary' => 'Peningkatan pencatatan riwayat sesi PPPoE dan perapian tata letak serta jarak vertikal pada modal dialog.',
                'sections' => [
                    [
                        'name' => 'Perbaikan & Log',
                        'badge' => 'Perbaikan',
                        'badge_color' => 'bg-slate-100 text-slate-700 border-slate-300',
                        'items' => [
                            'Penambahan logging riwayat sesi akun PPPoE untuk kemudahan audit jaringan.',
                            'Penyesuaian spacing, padding vertikal, dan konsistensi tombol tutup pada seluruh modal antarmuka.',
                        ]
                    ]
                ]
            ],
            [
                'version' => 'v1.2.0',
                'commit' => '4580775',
                'date' => '23 Juni 2026',
                'badge' => 'Fitur & Update',
                'badge_color' => 'bg-sky-50 text-sky-700 border-sky-200',
                'highlight' => false,
                'title' => 'Toggle Aktivasi Akun Pengguna, Persistensi Koneksi MikroTik, dan Pembaruan UI',
                'summary' => 'Penambahan fitur kontrol aktivasi akun pengguna langsung dari tabel, optimalisasi koneksi API MikroTik agar persisten untuk menghindari spam log, dan pembersihan aset usang.',
                'sections' => [
                    [
                        'name' => 'Fitur & Optimasi',
                        'badge' => 'Fitur',
                        'badge_color' => 'bg-sky-50 text-sky-700 border-sky-200',
                        'items' => [
                            'Penambahan toggle status aktif/non-aktif akun pengguna dengan modal konfirmasi terstandarisasi.',
                            'Implementasi koneksi persisten tunggal pada MikroTik RouterOS API untuk mencegah log spam dan lonjakan beban CPU router.',
                            'Pembersihan sisa aset CSS dan JavaScript dependensi AdminLTE yang tidak terpakai.',
                        ]
                    ]
                ]
            ],
            [
                'version' => 'v1.1.1',
                'commit' => '57b53d1',
                'date' => '9 Juni 2026',
                'badge' => 'Patch',
                'badge_color' => 'bg-slate-100 text-slate-700 border-slate-300',
                'highlight' => false,
                'title' => 'Sinkronisasi Filter DataTables dan Standardisasi Wording Bahasa Indonesia',
                'summary' => 'Perbaikan sinkronisasi kartu statistik dengan data tabel yang terfilter, serta penerjemahan istilah sistem ke bahasa Indonesia formal.',
                'sections' => [
                    [
                        'name' => 'Perbaikan & Penyelarasan',
                        'badge' => 'Patch',
                        'badge_color' => 'bg-slate-100 text-slate-700 border-slate-300',
                        'items' => [
                            'Sinkronisasi perhitungan angka pada kartu statistik dengan hasil filter tabel DataTables.',
                            'Penerjemahan teks aksi dan log aktivitas ke bahasa Indonesia yang konsisten di seluruh kontroler.',
                        ]
                    ]
                ]
            ],
            [
                'version' => 'v1.1.0',
                'commit' => 'ee2758b',
                'date' => '3 Juni 2026',
                'badge' => 'Major Update',
                'badge_color' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                'highlight' => false,
                'title' => 'Sistem Notifikasi Real-Time Navbar, Penggantian SweetAlert2, dan Modul Aset/Supporting',
                'summary' => 'Penyegaran antarmuka global, sistem notifikasi navbar berbasis log aktivitas, eliminasi library SweetAlert2 digantikan toast dan modal delete custom yang ringan, serta penambahan modul aset dan perbantuan.',
                'sections' => [
                    [
                        'name' => 'Fitur & Standarisasi UI',
                        'badge' => 'Pembaruan',
                        'badge_color' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                        'items' => [
                            'Implementasi sistem notifikasi real-time pada navbar berbasis riwayat log aktivitas pengguna.',
                            'Penggantian library SweetAlert2 dengan komponen global toast dan modal delete custom native yang lebih cepat dan ringan.',
                            'Penambahan modul Manajemen Aset (`/assets`) untuk pencatatan barang gudang dan mutasi stok.',
                            'Penambahan modul Perbantuan Lapangan (`/supporting`) dengan dukungan ekspor PDF dan filter teknisi.',
                            'Peta ODP interaktif Google Maps untuk penandaan distribusi fiber optik di lapangan.',
                        ]
                    ]
                ]
            ],
            [
                'version' => 'v1.0.0',
                'commit' => '7f4138a',
                'date' => '22 Mei 2026',
                'badge' => 'Rilis Awal',
                'badge_color' => 'bg-slate-100 text-slate-700 border-slate-300',
                'highlight' => false,
                'title' => 'Rilis Awal Sistem Erlangga Net Core (Laravel 11)',
                'summary' => 'Pondasi awal aplikasi web ISP Erlangga Net mencakup alur registrasi calon pelanggan (Pre-Order), daftar pelanggan aktif, manajemen teknisi lapangan, dan koneksi router NAS.',
                'sections' => [
                    [
                        'name' => 'Fondasi Sistem',
                        'badge' => 'Rilis Perdana',
                        'badge_color' => 'bg-slate-100 text-slate-700 border-slate-300',
                        'items' => [
                            'Inisialisasi arsitektur Laravel 11 dengan autentikasi multi-role (Administrator, Teknisi, dan Sales).',
                            'Modul Manajemen Pre-Order calon pelanggan dan verifikasi lapangan.',
                            'Modul Pelanggan Aktif dengan pencatatan paket langganan dan integrasi router.',
                            'Manajemen Router NAS dan integrasi dasar MikroTik RouterOS.',
                        ]
                    ]
                ]
            ]
        ];

        return view('changelogs.index', compact('releases'));
    }
}
