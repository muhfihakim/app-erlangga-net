<!DOCTYPE html>
<html>

<head>
    <title>Laporan Dismantle</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 11px;
            color: #333;
        }

        .header {
            text-align: center;
            margin-bottom: 25px;
            border-bottom: 2px solid #444;
            padding-bottom: 10px;
        }

        .header h2 {
            margin: 0;
            text-transform: uppercase;
            color: #2c3e50;
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
            background-color: #f8f9fa;
            color: #2c3e50;
            font-weight: bold;
            text-align: center;
        }

        .table tr:nth-child(even) {
            background-color: #fcfcfc;
        }

        .summary {
            margin-top: 20px;
            padding: 10px;
            background-color: #e3f2fd;
            border: 1px solid #bbdefb;
            color: #0d47a1;
            font-weight: bold;
            display: inline-block;
        }

        .footer {
            margin-top: 40px;
            text-align: right;
            font-style: italic;
            font-size: 9px;
            color: #95a5a6;
        }

        .text-center {
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="header">
        <h2>Laporan Pencabutan Perangkat (Dismantle)</h2>
        <p>Sistem Operasional Jaringan - Erlangga Net</p>
    </div>

    <table class="info-table">
        <tr>
            <td width="20%">Periode Laporan</td>
            <td width="2%">:</td>
            <td>{{ \Carbon\Carbon::parse($start)->format('d F Y') }} s/d
                {{ \Carbon\Carbon::parse($end)->format('d F Y') }}</td>
        </tr>
        <tr>
            <td>Filter Teknisi</td>
            <td>:</td>
            <td>{{ $teknisi ? $teknisi->nama_lengkap : 'Semua Teknisi' }}</td>
        </tr>
        <tr>
            <td>Waktu Cetak</td>
            <td>:</td>
            <td>{{ now()->format('d/m/Y H:i') }} WIB</td>
        </tr>
    </table>

    <table class="table">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="15%">Tgl. Selesai</th>
                <th width="25%">Nama Pelanggan</th>
                <th width="15%">ID Pelanggan</th>
                <th width="20%">Teknisi Eksekutor</th>
                <th width="20%">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($dismantles as $d)
                <tr>
                    <td class="text-center">{{ $loop->iteration }}</td>
                    <td class="text-center">{{ $d->completed_at->format('d/m/Y H:i') }}</td>
                    <td>{{ $d->customer->nama_lengkap ?? 'Data Terhapus' }}</td>
                    <td class="text-center">{{ $d->customer->id_pelanggan ?? '-' }}</td>
                    <td>{{ $d->teknisiEksekutor->nama_lengkap ?? 'N/A' }}</td>
                    <td class="text-center">Selesai Dicabut</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center">Tidak ada data pengerjaan pada periode ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="summary">
        Total Pengerjaan Dismantle: {{ $total }} Kali
    </div>

    <div class="footer">
        Dokumen ini dihasilkan secara otomatis oleh sistem pada {{ now()->format('d/m/Y H:i:s') }}
    </div>
</body>

</html>
