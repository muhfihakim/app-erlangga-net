<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Invoice {{ $invoice->invoice_number }} - Erlangga Net</title>
    <style>
        @page {
            margin: 30px 40px;
        }

        * {
            font-family: Arial, Helvetica, sans-serif;
            box-sizing: border-box;
        }

        body {
            font-size: 11.5px;
            color: #1f2937;
            line-height: 1.45;
            margin: 0;
            padding: 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .text-right {
            text-align: right !important;
        }

        .text-center {
            text-align: center !important;
        }

        /* ===== KOP PERUSAHAAN ===== */
        .kop-table {
            border-bottom: 3px double #1e3a5f;
            padding-bottom: 10px;
            margin-bottom: 4px;
        }

        .kop-table td {
            vertical-align: middle;
        }

        .kop-logo {
            max-height: 70px;
            max-width: 120px;
        }

        .kop-company {
            font-size: 22px;
            font-weight: bold;
            color: #1e3a5f;
            letter-spacing: 1px;
            text-transform: uppercase;
            margin: 0;
        }

        .kop-tagline {
            font-size: 10.5px;
            color: #4b5563;
            letter-spacing: 0.8px;
            text-transform: uppercase;
            margin-top: 2px;
        }

        .kop-contact {
            font-size: 10px;
            color: #4b5563;
            margin-top: 5px;
            line-height: 1.5;
        }

        .kop-spacer {
            height: 14px;
        }

        /* ===== JUDUL DOKUMEN ===== */
        .doc-title-wrap {
            text-align: center;
            margin: 14px 0 16px 0;
        }

        .doc-title {
            font-size: 16px;
            font-weight: bold;
            color: #111827;
            letter-spacing: 3px;
            text-transform: uppercase;
            border-bottom: 1px solid #111827;
            display: inline-block;
            padding-bottom: 3px;
        }

        .doc-number {
            font-size: 12px;
            color: #374151;
            margin-top: 5px;
        }

        .doc-number strong {
            color: #1e3a5f;
        }

        /* ===== INFO PELANGGAN & FAKTUR ===== */
        .info-table {
            margin-bottom: 18px;
        }

        .info-table>tbody>tr>td {
            width: 50%;
            vertical-align: top;
        }

        .info-heading {
            font-size: 10.5px;
            font-weight: bold;
            color: #ffffff;
            background: #1e3a5f;
            padding: 5px 10px;
            text-transform: uppercase;
            letter-spacing: 0.6px;
        }

        .info-body {
            border: 1px solid #cbd5e1;
            border-top: none;
            padding: 8px 10px;
        }

        .info-inner td {
            padding: 2.5px 0;
            font-size: 11.5px;
            vertical-align: top;
        }

        .info-inner .lbl {
            width: 36%;
            color: #4b5563;
        }

        .info-inner .sep {
            width: 3%;
            color: #4b5563;
        }

        .info-inner .val {
            color: #111827;
            font-weight: bold;
        }

        /* ===== STATUS ===== */
        .badge {
            display: inline-block;
            padding: 2px 8px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .badge-paid {
            color: #166534;
            border: 1px solid #166534;
            background: #f0fdf4;
        }

        .badge-unpaid {
            color: #991b1b;
            border: 1px solid #991b1b;
            background: #fef2f2;
        }

        /* ===== TABEL ITEM ===== */
        .items-table {
            margin-bottom: 14px;
            border: 1px solid #94a3b8;
        }

        .items-table th {
            background: #1e3a5f;
            color: #ffffff;
            padding: 8px 10px;
            font-size: 10.5px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            text-align: left;
            border: 1px solid #1e3a5f;
        }

        .items-table td {
            padding: 9px 10px;
            border: 1px solid #cbd5e1;
            font-size: 11.5px;
            vertical-align: top;
        }

        .item-name {
            font-weight: bold;
            color: #111827;
        }

        .item-desc {
            font-size: 10.5px;
            color: #6b7280;
            margin-top: 2px;
        }

        /* ===== RINGKASAN TOTAL ===== */
        .summary-container {
            width: 100%;
            margin-bottom: 20px;
        }

        .summary-table {
            width: 46%;
            float: right;
            border: 1px solid #cbd5e1;
        }

        .summary-table td {
            padding: 6px 10px;
            font-size: 11.5px;
            border-bottom: 1px solid #e5e7eb;
        }

        .summary-lbl {
            color: #374151;
        }

        .summary-val {
            font-weight: bold;
            color: #111827;
        }

        .total-row td {
            background: #1e3a5f;
            border-bottom: none;
            padding: 9px 10px !important;
        }

        .total-title {
            font-size: 12px;
            font-weight: bold;
            color: #ffffff;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .total-amount {
            font-size: 14px;
            font-weight: bold;
            color: #ffffff;
        }

        /* ===== CATATAN & PENGESAHAN ===== */
        .clear {
            clear: both;
        }

        .note-box {
            border: 1px solid #cbd5e1;
            border-left: 3px solid #1e3a5f;
            padding: 8px 12px;
            font-size: 10.5px;
            color: #4b5563;
            margin-top: 10px;
        }

        .note-title {
            font-weight: bold;
            color: #1e3a5f;
            text-transform: uppercase;
            font-size: 10px;
            letter-spacing: 0.5px;
            margin-bottom: 3px;
        }

        .sign-table {
            margin-top: 24px;
        }

        .sign-table td {
            width: 50%;
            vertical-align: top;
            font-size: 11px;
        }

        .sign-line {
            margin-top: 50px;
            border-top: 1px solid #111827;
            width: 170px;
            padding-top: 3px;
            font-weight: bold;
            color: #111827;
        }

        /* ===== FOOTER ===== */
        .footer-note {
            margin-top: 28px;
            padding-top: 8px;
            border-top: 1px solid #94a3b8;
            text-align: center;
            font-size: 9.5px;
            color: #6b7280;
            line-height: 1.5;
        }
    </style>
</head>

<body>
    @php
        $logoPath = public_path('dist/assets/img/logo.png');
        $logoBase64 = file_exists($logoPath)
            ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath))
            : null;

        // ====== DATA KOP PERUSAHAAN (silakan sesuaikan) ======
        $companyName = 'Erlangga Net';
        $companyTagline = 'High Speed Internet Service Provider';
        $companyAddress = 'Alamat lengkap perusahaan Anda di sini';
        $companyPhone = '0812-0000-0000';
        $companyEmail = 'cs@erlanggank.net';
        $companyWebsite = 'www.erlanggank.net';
    @endphp

    <!-- ================= KOP PERUSAHAAN ================= -->
    <table class="kop-table">
        <tr>
            <td style="width: 18%;">
                @if ($logoBase64)
                    <img src="{{ $logoBase64 }}" class="kop-logo" alt="{{ $companyName }}">
                @endif
            </td>
            <td style="width: 64%; text-align: center;">
                <div class="kop-company">{{ $companyName }}</div>
                <div class="kop-tagline">{{ $companyTagline }}</div>
                <div class="kop-contact">
                    {{ $companyAddress }}<br>
                    Telp/WA: {{ $companyPhone }} &nbsp;|&nbsp; Email: {{ $companyEmail }} &nbsp;|&nbsp;
                    {{ $companyWebsite }}
                </div>
            </td>
            <td style="width: 18%;"></td>
        </tr>
    </table>
    <div class="kop-spacer"></div>

    <!-- ================= JUDUL DOKUMEN ================= -->
    <div class="doc-title-wrap">
        <div class="doc-title">Faktur Tagihan</div>
        <div class="doc-number">
            Nomor: <strong>{{ $invoice->invoice_number }}</strong>
        </div>
        <div style="margin-top: 6px;">
            @if ($invoice->status == 'paid')
                <span class="badge badge-paid">Lunas / Terbayar</span>
            @else
                <span class="badge badge-unpaid">Belum Lunas</span>
            @endif
        </div>
    </div>

    <!-- ================= INFO PELANGGAN & FAKTUR ================= -->
    <table class="info-table">
        <tbody>
            <tr>
                <td style="padding-right: 8px;">
                    <div class="info-heading">Ditujukan Kepada</div>
                    <div class="info-body">
                        <table class="info-inner">
                            <tr>
                                <td class="lbl">Nama Pelanggan</td>
                                <td class="sep">:</td>
                                <td class="val">{{ $invoice->pppoe->preOrder->nama_lengkap ?? 'Pelanggan' }}</td>
                            </tr>
                            <tr>
                                <td class="lbl">Username PPPoE</td>
                                <td class="sep">:</td>
                                <td class="val">{{ $invoice->pppoe->username }}</td>
                            </tr>
                            <tr>
                                <td class="lbl">No. WhatsApp</td>
                                <td class="sep">:</td>
                                <td class="val">{{ $invoice->pppoe->preOrder->no_whatsapp ?? '-' }}</td>
                            </tr>
                        </table>
                    </div>
                </td>
                <td style="padding-left: 8px;">
                    <div class="info-heading">Detail Faktur</div>
                    <div class="info-body">
                        <table class="info-inner">
                            <tr>
                                <td class="lbl">Tanggal Terbit</td>
                                <td class="sep">:</td>
                                <td class="val">{{ $invoice->created_at->format('d/m/Y') }}</td>
                            </tr>
                            <tr>
                                <td class="lbl">Jatuh Tempo</td>
                                <td class="sep">:</td>
                                <td class="val"
                                    style="color: {{ $invoice->status == 'unpaid' ? '#b91c1c' : '#111827' }};">
                                    {{ $invoice->due_date ? \Carbon\Carbon::parse($invoice->due_date)->format('d/m/Y') : '-' }}
                                </td>
                            </tr>
                            <tr>
                                <td class="lbl">Metode Bayar</td>
                                <td class="sep">:</td>
                                <td class="val">
                                    {{ strtoupper($invoice->payment_method ?? 'Gateway Online / Tunai') }}</td>
                            </tr>
                        </table>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>

    <!-- ================= TABEL ITEM ================= -->
    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 35px;" class="text-center">No</th>
                <th>Deskripsi Layanan</th>
                <th style="width: 120px;" class="text-center">Periode Tagihan</th>
                <th style="width: 130px;" class="text-right">Jumlah (Rp)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="text-center">1</td>
                <td>
                    <div class="item-name">Langganan Internet -
                        {{ $invoice->pppoe->paket->nama_paket ?? 'Paket Broadband' }}</div>
                    @if (!empty($invoice->pppoe->paket->rate_limit))
                        <div class="item-desc">Kecepatan Akses: {{ $invoice->pppoe->paket->rate_limit }}</div>
                    @endif
                </td>
                <td class="text-center">{{ $invoice->created_at->translatedFormat('F Y') }}</td>
                <td class="text-right" style="font-weight: bold;">
                    Rp {{ number_format($invoice->amount, 0, ',', '.') }}
                </td>
            </tr>
            @if ((float) ($invoice->penyesuaian ?? 0) != 0)
                <tr>
                    <td class="text-center">2</td>
                    <td>
                        <div class="item-name">Penyesuaian Layanan / Biaya Tambahan</div>
                        <div class="item-desc">
                            {{ $invoice->catatan_penyesuaian ?: 'Penyesuaian Layanan / Biaya Tagihan' }}</div>
                    </td>
                    <td class="text-center">{{ $invoice->created_at->translatedFormat('F Y') }}</td>
                    <td class="text-right"
                        style="font-weight: bold; color: {{ $invoice->penyesuaian > 0 ? '#111827' : '#166534' }};">
                        {{ $invoice->penyesuaian > 0 ? '+ ' : '- ' }}Rp
                        {{ number_format(abs($invoice->penyesuaian), 0, ',', '.') }}
                    </td>
                </tr>
            @endif
        </tbody>
    </table>

    <!-- ================= RINGKASAN TOTAL ================= -->
    <div class="summary-container">
        <table class="summary-table">
            <tr>
                <td class="summary-lbl">Harga Pokok</td>
                <td class="text-right summary-val">Rp {{ number_format($invoice->amount, 0, ',', '.') }}</td>
            </tr>
            @if ((float) ($invoice->penyesuaian ?? 0) != 0)
                <tr>
                    <td class="summary-lbl">Penyesuaian {{ $invoice->penyesuaian > 0 ? '(Tambahan)' : '(Potongan)' }}
                    </td>
                    <td class="text-right summary-val"
                        style="color: {{ $invoice->penyesuaian > 0 ? '#111827' : '#166534' }};">
                        {{ $invoice->penyesuaian > 0 ? '+ ' : '- ' }}Rp
                        {{ number_format(abs($invoice->penyesuaian), 0, ',', '.') }}
                    </td>
                </tr>
            @endif
            @if ($invoice->tax > 0)
                <tr>
                    <td class="summary-lbl">Pajak / PPN / Fee</td>
                    <td class="text-right summary-val">+ Rp {{ number_format($invoice->tax, 0, ',', '.') }}</td>
                </tr>
            @endif
            @if ($invoice->discount > 0)
                <tr>
                    <td class="summary-lbl">Potongan Diskon</td>
                    <td class="text-right summary-val" style="color: #166534;">- Rp
                        {{ number_format($invoice->discount, 0, ',', '.') }}</td>
                </tr>
            @endif
            <tr class="total-row">
                <td class="total-title">Total Tagihan</td>
                <td class="text-right total-amount">Rp {{ number_format($invoice->total, 0, ',', '.') }}</td>
            </tr>
        </table>
    </div>

    <div class="clear"></div>

    <!-- ================= CATATAN ================= -->
    <div class="note-box">
        <div class="note-title">Catatan</div>
        Mohon lakukan pembayaran sebelum tanggal jatuh tempo agar layanan internet Anda tetap aktif
        dan tidak mengalami pembatasan. Simpan dokumen ini sebagai bukti tagihan.
    </div>

    <!-- ================= FOOTER ================= -->
    <div class="footer-note">
        Dokumen ini merupakan bukti tagihan resmi dari <strong>{{ $companyName }}</strong> yang diterbitkan secara
        elektronik
        dan sah tanpa tanda tangan basah.<br>
        Untuk konfirmasi atau bantuan, silakan hubungi Customer Support kami di {{ $companyPhone }} atau
        {{ $companyEmail }}.
    </div>
</body>

</html>
