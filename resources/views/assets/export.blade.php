<!DOCTYPE html>
<html>

<head>
    <title>Laporan Mutasi Barang</title>
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

        .badge {
            padding: 3px 8px;
            border-radius: 4px;
            font-weight: bold;
            font-size: 10px;
            color: white;
            text-transform: uppercase;
        }
        .badge-masuk { background-color: #27ae60; }
        .badge-keluar { background-color: #e74c3c; }
    </style>
</head>

<body>
    <div class="header">
        <h2>Laporan Mutasi Stok Barang</h2>
        <p>ERLANGGA NETWORK | www.erlangga.net</p>
        <p>office@erlangga.net</p>
        <p>0851 1742 4407</p>
    </div>

    <table class="info-table">
        <tr>
            <td width="20%">Periode Laporan</td>
            <td width="2%">:</td>
            <td>{{ \Carbon\Carbon::parse($start)->format('d F Y') }} s/d {{ \Carbon\Carbon::parse($end)->format('d F Y') }}</td>
        </tr>
        <tr>
            <td>Jenis Transaksi</td>
            <td>:</td>
            <td>{{ $type ?: 'Semua Jenis' }}</td>
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
                <th width="25%">Nama Barang</th>
                <th width="10%">Tipe</th>
                <th width="10%">Jumlah</th>
                <th width="15%">Penanggung Jawab</th>
                <th width="20%">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($transactions as $t)
                <tr>
                    <td class="text-center">{{ $loop->iteration }}</td>
                    <td class="text-center">{{ \Carbon\Carbon::parse($t->date)->format('d/m/Y') }}</td>
                    <td class="fw-bold">{{ $t->asset->name }}</td>
                    <td class="text-center">
                        <span class="badge {{ $t->type === 'Masuk' ? 'badge-masuk' : 'badge-keluar' }}">
                            {{ $t->type }}
                        </span>
                    </td>
                    <td class="text-center">
                        {{ $t->type === 'Masuk' ? '+' : '-' }}{{ number_format($t->quantity) }} {{ $t->asset->unit }}
                    </td>
                    <td>{{ $t->user ? $t->user->nama_lengkap : ($t->external_person_name ?: '-') }}</td>
                    <td>{{ $t->description }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Dicetak otomatis oleh Sistem pada {{ now()->format('d/m/Y H:i:s') }} - Powered: PT. Baraya Bareng Sadulur
    </div>
</body>

</html>
