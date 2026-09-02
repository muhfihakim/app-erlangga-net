<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $invoice->invoice_number }} - Erlangga Net</title>
    <style>
        @page {
            margin: 25px 30px;
        }
        * {
            font-family: Arial, Helvetica, sans-serif;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
            color: #334155;
            line-height: 1.4;
            margin: 0;
            padding: 0;
        }
        .container {
            padding: 5px 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        .header-table {
            margin-bottom: 20px;
            border-bottom: 2px solid #0284c7;
            padding-bottom: 15px;
        }
        .header-table td {
            vertical-align: middle;
        }
        .brand-logo {
            max-height: 50px;
            max-width: 160px;
        }
        .brand-title {
            font-size: 20px;
            font-weight: bold;
            color: #0284c7;
            margin: 0;
        }
        .brand-subtitle {
            font-size: 10.5px;
            color: #64748b;
            font-weight: normal;
            margin-top: 2px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .invoice-main-title {
            text-align: right;
            font-size: 20px;
            font-weight: bold;
            color: #0f172a;
            letter-spacing: 0.5px;
            margin: 0;
        }
        .invoice-num {
            font-size: 13px;
            font-weight: bold;
            color: #0284c7;
            margin-top: 3px;
        }
        .info-card-table {
            margin-bottom: 20px;
        }
        .info-card-table td {
            width: 50%;
            vertical-align: top;
        }
        .info-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 12px 14px;
        }
        .info-box-title {
            font-size: 10.5px;
            font-weight: bold;
            color: #0284c7;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 1px dashed #cbd5e1;
            padding-bottom: 4px;
            margin-bottom: 7px;
        }
        .info-row {
            margin-bottom: 4px;
            font-size: 11.5px;
        }
        .info-label {
            color: #64748b;
            font-weight: bold;
            display: inline-block;
            width: 100px;
        }
        .info-val {
            color: #0f172a;
            font-weight: bold;
        }
        .items-table {
            margin-bottom: 20px;
            border-radius: 4px;
            overflow: hidden;
        }
        .items-table th {
            background: #0f172a;
            color: #ffffff;
            padding: 9px 12px;
            font-size: 10.5px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            text-align: left;
        }
        .items-table td {
            padding: 10px 12px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 11.5px;
        }
        .text-right {
            text-align: right !important;
        }
        .text-center {
            text-align: center !important;
        }
        .summary-container {
            width: 100%;
            margin-bottom: 25px;
        }
        .summary-table {
            width: 48%;
            float: right;
            border-collapse: collapse;
        }
        .summary-table td {
            padding: 5px 10px;
            font-size: 11.5px;
        }
        .summary-lbl {
            font-weight: bold;
            color: #64748b;
        }
        .summary-val {
            font-weight: bold;
            color: #1e293b;
        }
        .total-box {
            background: #f0f9ff;
            border: 1px solid #bae6fd;
        }
        .total-box td {
            padding: 9px 10px !important;
        }
        .total-title {
            font-size: 13px;
            font-weight: bold;
            color: #0369a1;
            text-transform: uppercase;
        }
        .total-amount {
            font-size: 16px;
            font-weight: bold;
            color: #0284c7;
        }
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .badge-paid {
            background: #dcfce7;
            color: #15803d;
            border: 1px solid #86efac;
        }
        .badge-unpaid {
            background: #fee2e2;
            color: #b91c1c;
            border: 1px solid #fca5a5;
        }
        .footer-note {
            clear: both;
            margin-top: 50px;
            padding-top: 12px;
            border-top: 1px solid #e2e8f0;
            text-align: center;
            font-size: 10px;
            color: #94a3b8;
            line-height: 1.4;
        }
    </style>
</head>
<body>
    @php
        $logoPath = public_path('dist/assets/img/logo.png');
        $logoBase64 = file_exists($logoPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath)) : null;
    @endphp

    <div class="container">
        <!-- Header -->
        <table class="header-table">
            <tr>
                <td style="width: 55%;">
                    @if($logoBase64)
                        <img src="{{ $logoBase64 }}" class="brand-logo" alt="Erlangga Net">
                    @else
                        <div class="brand-title">Erlangga Net</div>
                    @endif
                    <div class="brand-subtitle">High Speed Internet Service Provider</div>
                </td>
                <td style="width: 45%; text-align: right;">
                    <div class="invoice-main-title">FAKTUR TAGIHAN</div>
                    <div class="invoice-num">{{ $invoice->invoice_number }}</div>
                    <div style="margin-top: 6px;">
                        @if($invoice->status == 'paid')
                            <span class="badge badge-paid">LUNAS / TERBAYAR</span>
                        @else
                            <span class="badge badge-unpaid">BELUM LUNAS</span>
                        @endif
                    </div>
                </td>
            </tr>
        </table>

        <!-- Customer & Invoice Info (2 Columns, Clean, No Address, No Manual Norek) -->
        <table class="info-card-table">
            <tr>
                <td style="padding-right: 8px;">
                    <div class="info-box">
                        <div class="info-box-title">Ditujukan Kepada</div>
                        <div class="info-row">
                            <span class="info-label">Nama Pelanggan</span>: 
                            <span class="info-val">{{ $invoice->pppoe->preOrder->nama_lengkap ?? 'Pelanggan' }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Username PPPoE</span>: 
                            <span class="info-val" style="color: #0284c7;">{{ $invoice->pppoe->username }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">No. WhatsApp</span>: 
                            <span class="info-val">{{ $invoice->pppoe->preOrder->no_whatsapp ?? '-' }}</span>
                        </div>
                    </div>
                </td>
                <td style="padding-left: 8px;">
                    <div class="info-box">
                        <div class="info-box-title">Detail Faktur & Waktu</div>
                        <div class="info-row">
                            <span class="info-label">Tanggal Terbit</span>: 
                            <span class="info-val">{{ $invoice->created_at->format('d/m/Y') }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Jatuh Tempo</span>: 
                            <span class="info-val" style="color: {{ $invoice->status == 'unpaid' ? '#e11d48' : '#0f172a' }};">
                                {{ $invoice->due_date ? \Carbon\Carbon::parse($invoice->due_date)->format('d/m/Y') : '-' }}
                            </span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Metode Bayar</span>: 
                            <span class="info-val">{{ strtoupper($invoice->payment_method ?? 'Gateway Online / Tunai') }}</span>
                        </div>
                    </div>
                </td>
            </tr>
        </table>

        <!-- Line Items Table -->
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 35px;" class="text-center">#</th>
                    <th>Deskripsi Layanan</th>
                    <th style="width: 130px;" class="text-center">Periode Tagihan</th>
                    <th style="width: 140px;" class="text-right">Jumlah (Rp)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="text-center" style="font-weight: bold; color: #94a3b8;">1</td>
                    <td>
                        <strong style="color: #0f172a; font-size: 12px;">Langganan Internet - {{ $invoice->pppoe->paket->nama_paket ?? 'Paket Broadband' }}</strong>
                        @if(!empty($invoice->pppoe->paket->rate_limit))
                            <div style="font-size: 10.5px; color: #64748b; margin-top: 2px;">Kecepatan Akses: {{ $invoice->pppoe->paket->rate_limit }}</div>
                        @endif
                    </td>
                    <td class="text-center" style="font-weight: bold; color: #475569;">
                        {{ $invoice->created_at->translatedFormat('F Y') }}
                    </td>
                    <td class="text-right" style="font-weight: bold; color: #0f172a;">
                        Rp {{ number_format($invoice->amount, 0, ',', '.') }}
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- Totals Summary Table -->
        <div class="summary-container">
            <table class="summary-table">
                <tr>
                    <td class="summary-lbl">Harga Pokok</td>
                    <td class="text-right summary-val">Rp {{ number_format($invoice->amount, 0, ',', '.') }}</td>
                </tr>
                @if($invoice->tax > 0)
                <tr>
                    <td class="summary-lbl">Pajak / PPN / Fee</td>
                    <td class="text-right summary-val" style="color: #e11d48;">+ Rp {{ number_format($invoice->tax, 0, ',', '.') }}</td>
                </tr>
                @endif
                @if($invoice->discount > 0)
                <tr>
                    <td class="summary-lbl">Potongan Diskon</td>
                    <td class="text-right summary-val" style="color: #16a34a;">- Rp {{ number_format($invoice->discount, 0, ',', '.') }}</td>
                </tr>
                @endif
                <tr class="total-box">
                    <td class="total-title">Total Tagihan</td>
                    <td class="text-right total-amount">Rp {{ number_format($invoice->total, 0, ',', '.') }}</td>
                </tr>
            </table>
        </div>

        <!-- Footer -->
        <div class="footer-note">
            Dokumen ini merupakan bukti tagihan resmi dari <strong>Erlangga Net</strong> yang diterbitkan secara elektronik.<br>
            Untuk konfirmasi atau informasi bantuan, silakan hubungi Customer Support resmi kami.
        </div>
    </div>
</body>
</html>
