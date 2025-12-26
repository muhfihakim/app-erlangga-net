<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Laporan Aktivitas</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 6px 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        h2,
        p {
            text-align: center;
            margin: 0;
            padding: 5px;
        }
    </style>
</head>

<body>
    <h2>Laporan Aktivitas Pengguna</h2>
    <p>Periode: {{ \Carbon\Carbon::parse($start)->format('d-m-Y') }} s/d
        {{ \Carbon\Carbon::parse($end)->format('d-m-Y') }}</p>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Pengguna</th>
                <th>Aktivitas</th>
                <th>Keterangan</th>
                <th>Waktu</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($logs as $log)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $log->user->nama_lengkap ?? 'N/A' }}</td>
                    <td>{{ ucfirst(str_replace('_', ' ', $log->activity_type)) }}</td>
                    <td>{{ $log->description }}</td>
                    <td>{{ \Carbon\Carbon::parse($log->logged_at)->format('d-m-Y H:i:s') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align: center;">Tidak ada data aktivitas.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>

</html>
