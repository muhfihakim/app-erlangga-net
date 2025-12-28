<!DOCTYPE html>
<html>

<head>
    <title>Laporan Maintenance Expired</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 11px;
            color: #333;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #c0392b;
            padding-bottom: 10px;
        }

        .header h2 {
            margin: 0;
            text-transform: uppercase;
            color: #c0392b;
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
            text-align: left;
        }

        .table th {
            background-color: #fdedec;
            color: #c0392b;
            font-weight: bold;
            text-align: center;
        }

        .table tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .summary {
            margin-top: 20px;
            padding: 10px;
            background-color: #f8d7da;
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
        <h2>Laporan Maintenance Expired (Locked)</h2>
        <p>Sistem Manajemen Pemeliharaan Jaringan</p>
    </div>

    <table class="info-table">
        <tr>
            <td width="20%">Periode Laporan</td>
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
                <th width="18%">Waktu Terdeteksi</th>
                <th width="20%">Teknisi Penanggung Jawab</th>
                <th width="37%">Detail Maintenance (ID - Pelanggan)</th>
                <th width="20%">Waktu Delegasi Awal</th>
            </tr>
        </thead>
        <tbody>
            @forelse($logs as $log)
                @php
                    // Decode data JSON jika tersimpan sebagai string
                    $data = is_array($log->data) ? $log->data : json_decode($log->data, true);
                @endphp
                <tr>
                    <td class="text-center">{{ $loop->iteration }}</td>
                    <td class="text-center">{{ $log->logged_at->format('d/m/Y H:i') }}</td>
                    <td class="fw-bold">{{ $log->user->nama_lengkap ?? 'N/A' }}</td>
                    <td>
                        ID: {{ $data['maintenance_id'] ?? '-' }} <br>
                        Pelanggan: {{ $data['customer_name'] ?? '-' }}
                    </td>
                    <td class="text-center">
                        {{ isset($data['delegated_at']) ? \Carbon\Carbon::parse($data['delegated_at'])->format('d/m/Y H:i') : '-' }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center">Tidak ada data maintenance expired ditemukan.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="summary">
        Total Maintenance Expired: {{ $logs->count() }} Data
    </div>

    <div class="footer">
        Dicetak otomatis oleh Sistem pada {{ now()->format('d/m/Y H:i:s') }}
    </div>
</body>

</html>
