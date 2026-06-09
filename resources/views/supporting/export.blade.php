<!DOCTYPE html>
<html>
<head>
    <title>Laporan Perbantuan BBSNET</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
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
            font-size: 10px;
        }

        .table tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .footer {
            margin-top: 30px;
            text-align: right;
            font-style: italic;
            font-size: 9px;
            color: #7f8c8d;
        }

        .text-center {
            text-align: center;
        }

        .badge {
            display: inline-block;
            padding: 3px 6px;
            border-radius: 4px;
            background: #f1f5f9;
            color: #475569;
            font-weight: bold;
            font-size: 9px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>Laporan Perbantuan BBSNET</h2>
        <p>ERLANGGA NETWORK | www.erlangga.net</p>
        <p>office@erlangga.net</p>
        <p>0851 1742 4407</p>
        <p style="margin-top: 10px; font-weight: bold; color: #333;">Dicetak pada: {{ date('d/m/Y H:i') }}</p>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th style="width: 30px;">No</th>
                <th style="width: 80px;">Tanggal</th>
                <th style="width: 100px;">Tipe Kegiatan</th>
                <th>Teknisi Utama</th>
                <th>Partner</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($activities as $index => $act)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td class="text-center">{{ $act->activity_date->format('d/m/Y') }}</td>
                <td class="text-center"><span class="badge">{{ $act->activity_type }}</span></td>
                <td style="font-weight: bold;">{{ $act->mainTechnician->nama_lengkap ?? '-' }}</td>
                <td>
                    @php
                        $partners = \App\Models\User::whereIn('id', $act->partner_ids ?? [])->pluck('nama_lengkap');
                    @endphp
                    @foreach($partners as $p)
                        • {{ $p }}<br>
                    @endforeach
                </td>
                <td style="color: #64748b;">{{ $act->description ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Dicetak secara otomatis oleh Sistem ErlanggaNet
    </div>
</body>
</html>
