<!DOCTYPE html>
<html>

<head>
    <title>Laporan Data Pre-Order</title>
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
            background-color: #ecf0f1;
            color: #2c3e50;
            font-weight: bold;
            text-align: center;
        }

        .table tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .summary {
            margin-top: 20px;
            padding: 10px;
            background-color: #d1ecf1;
            border: 1px solid #bee5eb;
            color: #0c5460;
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
        <h2>Laporan Pre-Order Pelanggan</h2>
        <p>ERLANGGA NETWORK | www.erlangga.net</p>
        <p>office@erlangga.net</p>
        <p>0851 1742 4407</p>
    </div>

    <table class="info-table">
        <tr>
            <td width="15%">Periode Laporan</td>
            <td width="2%">:</td>
            <td>{{ \Carbon\Carbon::parse($start)->format('d F Y') }} s/d
                {{ \Carbon\Carbon::parse($end)->format('d F Y') }}</td>
        </tr>
        <tr>
            <td>Nama Sales</td>
            <td>:</td>
            <td>{{ $sales ? $sales->nama_lengkap : 'Semua Sales' }}</td>
        </tr>
        <tr>
            <td>Tanggal Cetak</td>
            <td>:</td>
            <td>{{ now()->format('d/m/Y H:i') }}</td>
        </tr>
    </table>

    <table class="table">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="15%">Tanggal</th>
                <th width="20%">Nama Sales</th>
                <th width="25%">Nama Pelanggan</th>
                <th width="15%">Paket</th>
                <th width="20%">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($preorders as $po)
                <tr>
                    <td class="text-center">{{ $loop->iteration }}</td>
                    <td class="text-center">{{ $po->created_at->format('d/m/Y') }}</td>
                    <td>{{ $po->sales->nama_lengkap ?? $po->nama_sales }}</td>
                    <td>{{ $po->nama_lengkap }}</td>
                    <td class="text-center">{{ $po->paket_internet }}</td>
                    <td class="text-center">
                        {{ $po->id_teknisi_delegasi ? 'Terdelegasi' : 'Pending' }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="summary">
        Total Pre-Order: {{ $total }} Data
    </div>

    <div class="footer">
        Dicetak otomatis oleh Sistem pada {{ now()->format('d/m/Y H:i:s') }} - Powered: PT. Baraya Bareng Sadulur
    </div>
</body>

</html>
