<!DOCTYPE html>
<html>
<head>
    <title>Laporan Perbantuan BBSNET</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; font-weight: bold; }
        .header { text-align: center; margin-bottom: 30px; }
        .footer { position: fixed; bottom: 0; width: 100%; text-align: right; font-size: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <h2 style="margin-bottom: 5px;">LAPORAN PERBANTUAN BBSNET</h2>
        <p style="margin-top: 0;">Tanggal Cetak: {{ date('d/m/Y H:i') }}</p>
    </div>

    <table>
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
            @foreach($activities as $index => $act)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $act->activity_date->format('d/m/Y') }}</td>
                <td>{{ $act->activity_type }}</td>
                <td>{{ $act->mainTechnician->nama_lengkap ?? '-' }}</td>
                <td>
                    @php
                        $partners = \App\Models\User::whereIn('id', $act->partner_ids ?? [])->pluck('nama_lengkap');
                    @endphp
                    @foreach($partners as $p)
                        • {{ $p }}<br>
                    @endforeach
                </td>
                <td>{{ $act->description ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Halaman 1 dari 1
    </div>
</body>
</html>
