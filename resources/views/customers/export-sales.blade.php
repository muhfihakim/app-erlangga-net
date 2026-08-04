<!DOCTYPE html>
<html>

<head>
    <title>Laporan Aktivasi Sales</title>
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
        <h2>Laporan Aktivasi Pelanggan</h2>
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
                <th width="15%">Tgl. Aktivasi</th>
                <th width="20%">Nama Pelanggan</th>
                <th width="15%">Paket</th>
                <th width="18%">Sales</th>
                <th width="17%">Teknisi Utama</th>
                <th width="10%">ID Billing</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($customers as $c)
                <tr>
                    <td style="text-align: center; vertical-align: middle">{{ $loop->iteration }}</td>
                    <td style="text-align: center; vertical-align: middle">{{ $c->created_at->format('d/m/Y H:i') }}
                    </td>
                    <td>{{ $c->nama_lengkap }}</td>
                    <td>{{ $c->paket ? $c->paket->nama_paket : '-' }}</td>
                    <td>{{ $c->sales->nama_lengkap ?? 'N/A' }}</td>
                    <td>{{ $c->teknisiUtama->nama_lengkap ?? 'N/A' }}</td>
                    <td style="text-align: center; vertical-align: middle">{{ $c->id_pelanggan }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="summary">
        Total Aktivasi: {{ $total }} Data
    </div>

    <div class="footer">
        Dicetak otomatis oleh Sistem pada {{ now()->format('d/m/Y H:i:s') }} - Powered: PT. Baraya Bareng Sadulur
    </div>
</body>

</html>
