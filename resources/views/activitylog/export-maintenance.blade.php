<!DOCTYPE html>
<html>

<head>
    <title>Laporan Maintenance</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 11px;
            color: #333;
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #444;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #ccc;
            padding: 6px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        .summary {
            margin-top: 15px;
            font-weight: bold;
            background: #e8f5e9;
            padding: 10px;
            display: inline-block;
        }
    </style>
</head>

<body>
    <div class="header">
        <h2>LAPORAN PENGERJAAN MAINTENANCE</h2>
        <p>Periode: {{ $start }} s/d {{ $end }} | Teknisi: {{ $teknisi->nama_lengkap ?? 'Semua' }}</p>
    </div>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Tgl Selesai</th>
                <th>Pelanggan</th>
                <th>Teknisi Utama</th>
                <th>Partner</th>
                <th>Solusi</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($maintenances as $m)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $m->completed_at->format('d/m/Y H:i') }}</td>
                    <td>{{ $m->customer->nama_lengkap }}</td>
                    <td>{{ $m->teknisiEksekutor->nama_lengkap }}</td>
                    <td>{{ $m->partner ?: '-' }}</td>
                    <td>{{ $m->solusi_kendala }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <div class="summary">Total Pengerjaan Selesai: {{ $total }} Data</div>
</body>

</html>
