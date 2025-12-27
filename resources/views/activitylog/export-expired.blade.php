<!DOCTYPE html>
<html>

<head>
    <title>Laporan PO Expired Delegasi</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 12px;
            color: #333;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #444;
            padding-bottom: 10px;
        }

        .header h2 {
            margin: 0;
            text-transform: uppercase;
            color: #c0392b;
            /* Warna merah gelap untuk indikasi expired */
        }

        .header p {
            margin: 5px 0;
            color: #7f8c8d;
        }

        .info-table {
            width: 100%;
            margin-bottom: 20px;
        }

        .info-table td {
            padding: 3px 0;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .table th,
        .table td {
            border: 1px solid #bdc3c7;
            padding: 8px;
        }

        .table th {
            background-color: #f2f2f2;
            color: #2c3e50;
            font-weight: bold;
            text-align: center;
        }

        .table td {
            text-align: left;
        }

        .table tr:nth-child(even) {
            background-color: #fcfcfc;
        }

        .summary {
            margin-top: 20px;
            padding: 10px;
            background-color: #f8d7da;
            /* Warna merah muda transparan */
            border: 1px solid #f5c6cb;
            color: #721c24;
            font-weight: bold;
            display: inline-block;
        }

        .footer {
            margin-top: 50px;
            text-align: right;
            font-style: italic;
            font-size: 10px;
        }

        .text-center {
            text-align: center;
        }

        .fw-bold {
            font-weight: bold;
        }
    </style>
</head>

<body>
    <div class="header">
        <h2>Laporan PO Expired Delegasi</h2>
        <p>Sistem Manajemen Monitoring Pengerjaan Teknisi</p>
    </div>

    <table class="info-table">
        <tr>
            <td width="18%">Periode Laporan</td>
            <td width="2%">:</td>
            <td>{{ \Carbon\Carbon::parse($start)->format('d F Y') }} s/d
                {{ \Carbon\Carbon::parse($end)->format('d F Y') }}</td>
        </tr>
        <tr>
            <td>Nama Teknisi</td>
            <td>:</td>
            <td>{{ $teknisi ? $teknisi->nama_lengkap : 'Semua Teknisi' }}</td>
        </tr>
        <tr>
            <td>Tanggal Cetak</td>
            <td>:</td>
            <td>{{ now()->format('d/m/Y H:i') }} WIB</td>
        </tr>
    </table>

    <table class="table">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="18%">Waktu Log</th>
                <th width="18%">Nama Teknisi</th>
                <th width="10%">PO ID</th>
                <th width="24%">Waktu Delegasi</th>
                <th width="25%">Waktu Expired (>3 Hari)</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($logs as $log)
                <tr>
                    <td class="text-center">{{ $loop->iteration }}</td>
                    <td class="text-center">{{ $log->logged_at->format('d/m/Y H:i') }}</td>
                    <td class="fw-bold">{{ $log->user->nama_lengkap ?? 'N/A' }}</td>
                    <td class="text-center">{{ $log->data['po_id'] ?? '-' }}</td>
                    <td class="text-center">
                        {{ isset($log->data['delegated_at']) ? \Carbon\Carbon::parse($log->data['delegated_at'])->format('d/m/Y H:i') : '-' }}
                    </td>
                    <td class="text-center" style="color: #c0392b;">
                        {{ isset($log->data['expired_at']) ? \Carbon\Carbon::parse($log->data['expired_at'])->format('d/m/Y H:i') : '-' }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center">Tidak ada data penugasan expired pada periode ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="summary">
        Total Tugas Expired: {{ $logs->count() }} Data
    </div>

    <div class="footer">
        Dokumen ini dihasilkan secara otomatis oleh sistem pada {{ now()->format('d/m/Y H:i:s') }}
    </div>
</body>

</html>
