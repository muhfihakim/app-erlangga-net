<!DOCTYPE html>
<html>

<head>
    <title>Laporan Pendapatan Teknisi</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            font-size: 11px;
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
            text-transform: uppercase;
        }

        .table tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .summary {
            margin-top: 10px;
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

        .text-right {
            text-align: right;
        }

        .fw-bold {
            font-weight: bold;
        }
    </style>
</head>

<body>
    <div class="header">
        <h2>Laporan Pendapatan Teknisi</h2>
        <p>ERLANGGA NETWORK | www.erlangga.net</p>
        <p>office@erlangga.net</p>
        <p>0851 1742 4407</p>
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
            <td>{{ now()->format('d/m/Y H:i') }}</td>
        </tr>
    </table>

    {{-- Stats Tim --}}
    <table class="table" style="width: 60%; margin-bottom: 25px;">
        <thead>
            <tr>
                <th colspan="2">Rekap Jumlah Tim</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td width="70%">Aktivasi Sendiri (1 Orang)</td>
                <td class="text-center fw-bold">{{ $stats['solo'] }} Kali</td>
            </tr>
            <tr>
                <td>Aktivasi Berdua (2 Orang)</td>
                <td class="text-center fw-bold">{{ $stats['pair'] }} Kali</td>
            </tr>
            <tr>
                <td>Aktivasi Bertiga (3 Orang)</td>
                <td class="text-center fw-bold">{{ $stats['triple'] }} Kali</td>
            </tr>
            <tr>
                <td>Aktivasi Berempat atau Lebih</td>
                <td class="text-center fw-bold">{{ $stats['quad'] + $stats['more'] }} Kali</td>
            </tr>
        </tbody>
    </table>

    <table class="table">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="15%">Tgl. Aktivasi</th>
                <th width="25%">Nama Pelanggan (ID)</th>
                <th width="20%">Tim Teknisi</th>
                <th width="15%">Jml. Tim</th>
                <th width="20%">Fee (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($customers as $c)
                <tr>
                    <td class="text-center">{{ $loop->iteration }}</td>
                    <td class="text-center">{{ \Carbon\Carbon::parse($c->created_at)->format('d/m/Y') }}</td>
                    <td>
                        <div class="fw-bold">{{ strtoupper($c->nama_lengkap) }}</div>
                        <div style="font-size: 9px; color: #666;">ID: {{ $c->id_pelanggan }}</div>
                    </td>
                    <td>
                        <div class="fw-bold">{{ $c->teknisiUtama->nama_lengkap ?? 'N/A' }}</div>
                        @if($c->partner)
                            <div style="font-size: 9px; color: #666;">Partner: {{ $c->partner }}</div>
                        @endif
                    </td>
                    <td class="text-center">{{ $c->total_techs }} Orang</td>
                    <td class="text-center fw-bold">Rp {{ number_format($c->calculated_fee, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="background-color: #ecf0f1;">
                <td colspan="5" class="text-right fw-bold">TOTAL ESTIMASI PENDAPATAN</td>
                <td class="text-center fw-bold" style="color: #2c3e50;">Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    @if(isset($kasbons) && count($kasbons) > 0)
    <h3 style="margin-top: 30px; margin-bottom: 10px; color: #2c3e50; text-transform: uppercase; font-size: 12px;">Rincian Kasbon</h3>
    <table class="table">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="20%">Tanggal</th>
                <th width="55%">Keterangan</th>
                <th width="20%">Nominal (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($kasbons as $k)
                <tr>
                    <td class="text-center">{{ $loop->iteration }}</td>
                    <td class="text-center">{{ \Carbon\Carbon::parse($k->tanggal)->format('d/m/Y') }}</td>
                    <td>{{ $k->keterangan ?? '-' }}</td>
                    <td class="text-center fw-bold" style="color: #c0392b;">Rp {{ number_format($k->nominal, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="background-color: #ecf0f1;">
                <td colspan="3" class="text-right fw-bold">TOTAL KASBON</td>
                <td class="text-center fw-bold" style="color: #c0392b;">Rp {{ number_format($totalKasbon, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>
    @endif

    <table class="table" style="width: 100%; margin-top: 20px;">
        <tr style="background-color: #2c3e50; color: white;">
            <td class="fw-bold" style="padding: 10px; width: 75%; text-align: right;">PENDAPATAN BERSIH (TOTAL ESTIMASI - TOTAL KASBON)</td>
            <td class="text-center fw-bold" style="padding: 10px; width: 25%;">Rp {{ number_format($totalPendapatan - ($totalKasbon ?? 0), 0, ',', '.') }}</td>
        </tr>
    </table>

    <div class="summary">
        Total Aktivasi: {{ $customers->count() }} Data
    </div>

    <div class="footer">
        Dicetak otomatis oleh Sistem pada {{ now()->format('d/m/Y H:i:s') }} - Powered: PT. Baraya Bareng Sadulur
    </div>
</body>

</html>
