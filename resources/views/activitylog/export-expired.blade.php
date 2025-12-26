<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Laporan PO Expired</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        th,
        td {
            border: 1px solid #333;
            padding: 6px;
            text-align: center;
        }

        th {
            background-color: #f2f2f2;
        }
    </style>
</head>

<body>
    <h3 style="text-align: center;">Laporan PO Expired Delegasi</h3>
    <p><strong>Periode:</strong> {{ $start }} s/d {{ $end }}</p>
    @if ($teknisi)
        <p><strong>Teknisi:</strong> {{ $teknisi->nama_lengkap }}</p>
    @endif

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Tanggal Log</th>
                <th>Teknisi</th>
                <th>PO ID</th>
                <th>Delegated At</th>
                <th>Expired At</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($logs as $log)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $log->logged_at->format('d-m-Y H:i:s') }}</td>
                    <td>{{ $log->user->nama_lengkap }}</td>
                    <td>{{ $log->data['po_id'] ?? '-' }}</td>
                    <td>{{ \Carbon\Carbon::parse($log->data['delegated_at'])->format('d-m-Y H:i') }}</td>
                    <td>{{ \Carbon\Carbon::parse($log->data['expired_at'])->format('d-m-Y H:i') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>
