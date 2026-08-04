<!DOCTYPE html>
<html>

<head>
    <title>Laporan Pelanggan Isolir</title>
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
        <h2>Laporan Pelanggan Isolir</h2>
        <p>ERLANGGA NETWORK | www.erlangga.net</p>
        <p>office@erlangga.net</p>
        <p>0851 1742 4407</p>
    </div>

    <table class="info-table">
        <tr>
            <td width="15%">Nama Sales</td>
            <td width="2%">:</td>
            <td>{{ $sales ? $sales->nama_lengkap : 'Semua Sales' }}</td>
        </tr>
        <tr>
            <td>Tanggal Cetak</td>
            <td>:</td>
            <td>{{ now()->format('d/m/Y H:i') }}</td>
        </tr>
    </table>

    @if(!empty($salesSummary))
    <div style="margin-bottom: 20px;">
        <div style="font-weight: bold; margin-bottom: 5px; color: #2c3e50;">Rekapitulasi Tagihan per Sales:</div>
        <table class="table" style="width: 50%;">
            <thead>
                <tr>
                    <th width="70%" style="text-align: left; padding-left: 10px;">Nama Sales</th>
                    <th width="30%">Total Isolir</th>
                </tr>
            </thead>
            <tbody>
                @foreach($salesSummary as $summary)
                <tr>
                    <td style="padding-left: 10px;">{{ $summary['nama'] }}</td>
                    <td class="text-center fw-bold text-danger" style="color: #c0392b;">{{ $summary['total'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <table class="table">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="25%">Nama Pelanggan</th>
                <th width="20%">Username PPPoE</th>
                <th width="20%">Nama Sales</th>
                <th width="15%">No. WhatsApp</th>
                <th width="15%">Paket Internet</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($pelangganIsolir as $isolir)
                <tr>
                    <td class="text-center" style="vertical-align: middle">{{ $loop->iteration }}</td>
                    <td>{{ $isolir->nama_lengkap }}</td>
                    <td class="text-center" style="font-family: monospace">{{ $isolir->pppoe_username }}</td>
                    <td class="text-center">{{ $isolir->sales->nama_lengkap ?? '-' }}</td>
                    <td class="text-center">{{ $isolir->no_whatsapp }}</td>
                    <td class="text-center">{{ $isolir->paket ? $isolir->paket->nama_paket : '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center">Tidak ada pelanggan isolir.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="summary">
        Total Pelanggan Isolir: {{ $total }} Data
    </div>

    <div class="footer">
        Dicetak otomatis oleh Sistem pada {{ now()->format('d/m/Y H:i:s') }} - Powered: PT. Baraya Bareng Sadulur
    </div>
</body>

</html>
