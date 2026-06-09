<!DOCTYPE html>
<html>

<head>
    <title>Laporan Perbantuan BBSNET</title>
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
        <h2>Laporan Perbantuan Teknisi BBSNET</h2>
        <p>ERLANGGA NETWORK | www.erlangga.net</p>
        <p>office@erlangga.net</p>
        <p>0851 1742 4407</p>
    </div>

    <table class="info-table">
        <tr>
            <td width="15%">Periode Laporan</td>
            <td width="2%">:</td>
            <td>{{ \Carbon\Carbon::parse(request('start_date'))->format('d F Y') }} s/d
                {{ \Carbon\Carbon::parse(request('end_date'))->format('d F Y') }}</td>
        </tr>
        <tr>
            <td>Nama Teknisi</td>
            <td>:</td>
            <td>{{ request('teknisi_id') ? \App\Models\User::find(request('teknisi_id'))->nama_lengkap ?? 'Semua Teknisi' : 'Semua Teknisi' }}</td>
        </tr>
        <tr>
            <td>Tipe Kegiatan</td>
            <td>:</td>
            <td>{{ request('activity_type') ?: 'Semua Tipe' }}</td>
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
                <th>No</th>
                <th>Tanggal</th>
                <th>Tipe Kegiatan</th>
                <th>Teknisi Utama</th>
                <th>Partner</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($activities as $m)
                <tr>
                    <td style="text-align: center; vertical-align: top;">{{ $loop->iteration }}</td>
                    <td style="text-align: center; vertical-align: top;">{{ $m->activity_date->format('d/m/Y') }}</td>
                    <td style="text-align: center; vertical-align: top;">{{ $m->activity_type }}</td>
                    <td style="vertical-align: top;">{{ $m->mainTechnician->nama_lengkap ?? '-' }}</td>
                    <td style="vertical-align: top;">
                        @php
                            $partners = \App\Models\User::whereIn('id', $m->partner_ids ?? [])->pluck('nama_lengkap');
                        @endphp
                        @if($partners->isEmpty())
                            -
                        @else
                            <ul style="margin:0; padding-left: 15px;">
                                @foreach($partners as $p)
                                    <li>{{ $p }}</li>
                                @endforeach
                            </ul>
                        @endif
                    </td>
                    <td style="vertical-align: top;">{{ $m->description ?: '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="summary">
        Total Perbantuan: {{ $activities->count() }} Data
    </div>

    <div class="footer">
        Dicetak otomatis oleh Sistem pada {{ now()->format('d/m/Y H:i:s') }} - Powered: PT. Baraya Bareng Sadulur
    </div>
</body>

</html>
