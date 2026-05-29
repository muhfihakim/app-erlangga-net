<!DOCTYPE html>
<html>
<head>
    <title>Laporan Pendapatan Teknisi</title>
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
            text-transform: uppercase;
        }

        .table td {
            text-transform: uppercase;
        }

        .table tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .summary {
            margin-top: 20px;
            padding: 10px;
            background-color: #dcfce7;
            border: 1px solid #15803d;
            color: #15803d;
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
        <h2>Laporan Pendapatan Teknisi (Aktivasi)</h2>
        <p>ERLANGGA NETWORK | www.erlangga.net</p>
        <p>office@erlangga.net</p>
        <p>0851 1742 4407</p>
    </div>

    <table class="info-table">
        <tr>
            <td width="15%" class="fw-bold">Periode</td>
            <td width="85%">: 
                {{ $start ? \Carbon\Carbon::parse($start)->format('d/m/Y') : '-' }} s.d 
                {{ $end ? \Carbon\Carbon::parse($end)->format('d/m/Y') : '-' }}
            </td>
        </tr>
        <tr>
            <td class="fw-bold">Teknisi</td>
            <td>: {{ $teknisi ? $teknisi->nama_lengkap : 'Semua Teknisi' }}</td>
        </tr>
        <tr>
            <td class="fw-bold">Total Aktivasi</td>
            <td>: {{ $customers->count() }} Aktivasi</td>
        </tr>
        <tr>
            <td class="fw-bold">Tarif Dasar</td>
            <td>: Rp {{ number_format($baseFee, 0, ',', '.') }} / Aktivasi</td>
        </tr>
    </table>

    <table class="table">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="15%">Tanggal</th>
                <th width="25%">Pelanggan (ID)</th>
                <th width="25%">Tim Teknisi</th>
                <th width="10%">Bagi Hasil</th>
                <th width="20%">Pendapatan</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($customers as $index => $c)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-center">{{ \Carbon\Carbon::parse($c->created_at)->format('d/m/Y') }}</td>
                    <td>
                        <div class="fw-bold">{{ $c->nama_lengkap }}</div>
                        <div style="font-size: 10px;">ID: {{ $c->id_pelanggan }}</div>
                    </td>
                    <td>
                        <div class="fw-bold">{{ $c->teknisiUtama->nama_lengkap ?? 'Unknown' }}</div>
                        @php
                            $partners = is_array($c->partner_ids) ? $c->partner_ids : json_decode($c->partner_ids, true);
                            $partnerNames = [];
                            if(is_array($partners) && count($partners) > 0) {
                                foreach($partners as $pid) {
                                    $partnerUser = \App\Models\User::find($pid);
                                    if($partnerUser) $partnerNames[] = $partnerUser->nama_lengkap;
                                }
                            }
                        @endphp
                        @if(count($partnerNames) > 0)
                            <div style="font-size: 10px;">Partner: {{ implode(', ', $partnerNames) }}</div>
                        @endif
                    </td>
                    <td class="text-center">{{ $c->total_techs }} Orang</td>
                    <td class="text-right fw-bold">
                        @if($c->calculated_fee > 0)
                            Rp {{ number_format($c->calculated_fee, 0, ',', '.') }}
                        @else
                            -
                        @endif
                    </td>
                </tr>
            @endforeach
            @if($customers->isEmpty())
                <tr>
                    <td colspan="6" class="text-center">Tidak ada data aktivasi pada periode ini.</td>
                </tr>
            @endif
        </tbody>
        @if(!$customers->isEmpty())
        <tfoot>
            <tr>
                <th colspan="5" class="text-right">TOTAL PENDAPATAN ESTIMASI</th>
                <th class="text-right fw-bold">Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</th>
            </tr>
        </tfoot>
        @endif
    </table>

    <div class="summary">
        Total Estimasi Pendapatan: Rp {{ number_format($totalPendapatan, 0, ',', '.') }}
    </div>

    <div class="footer">
        Dicetak pada: {{ \Carbon\Carbon::now()->format('d/m/Y H:i:s') }} oleh Sistem ErlanggaNet
    </div>
</body>
</html>
