<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 14px;
            color: #333;
            line-height: 1.5;
            margin: 0;
            padding: 0;
        }
        .container {
            padding: 30px;
        }
        .header {
            width: 100%;
            margin-bottom: 40px;
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 20px;
        }
        .header td {
            vertical-align: top;
        }
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #1e40af;
        }
        .company-info {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }
        .invoice-title {
            text-align: right;
            font-size: 28px;
            font-weight: bold;
            color: #333;
            text-transform: uppercase;
        }
        .invoice-meta {
            text-align: right;
            font-size: 12px;
            margin-top: 10px;
        }
        .info-section {
            width: 100%;
            margin-bottom: 30px;
        }
        .info-section td {
            width: 50%;
            vertical-align: top;
        }
        .info-box {
            background: #f8fafc;
            padding: 15px;
            border-radius: 8px;
        }
        .info-label {
            font-size: 11px;
            color: #64748b;
            text-transform: uppercase;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .info-value {
            font-size: 14px;
            font-weight: bold;
            color: #0f172a;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .items-table th {
            background: #1e293b;
            color: white;
            padding: 12px;
            text-align: left;
            font-size: 12px;
            text-transform: uppercase;
        }
        .items-table td {
            padding: 12px;
            border-bottom: 1px solid #e2e8f0;
        }
        .text-right {
            text-align: right !important;
        }
        .text-center {
            text-align: center !important;
        }
        .summary-table {
            width: 40%;
            float: right;
            border-collapse: collapse;
        }
        .summary-table td {
            padding: 8px 12px;
        }
        .summary-label {
            font-weight: bold;
            color: #64748b;
        }
        .summary-total {
            font-size: 18px;
            font-weight: bold;
            color: #0f172a;
            border-top: 2px solid #e2e8f0;
            padding-top: 12px !important;
        }
        .footer {
            clear: both;
            margin-top: 80px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
            text-align: center;
            font-size: 11px;
            color: #94a3b8;
        }
        .status-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status-paid {
            background: #dcfce7;
            color: #166534;
        }
        .status-unpaid {
            background: #fee2e2;
            color: #991b1b;
        }
    </style>
</head>
<body>
    <div class="container">
        <table class="header">
            <tr>
                <td>
                    <div class="logo">Erlangga Net</div>
                    <div class="company-info">
                        Layanan Internet Cepat & Stabil<br>
                        Jl. Raya Subang No. 123<br>
                        Telp: 0812-3456-7890
                    </div>
                </td>
                <td>
                    <div class="invoice-title">INVOICE</div>
                    <div class="invoice-meta">
                        <strong>No. Invoice:</strong> {{ $invoice->invoice_number }}<br>
                        <strong>Tanggal:</strong> {{ $invoice->created_at->format('d/m/Y') }}<br>
                        <strong>Jatuh Tempo:</strong> {{ $invoice->due_date ? \Carbon\Carbon::parse($invoice->due_date)->format('d/m/Y') : '-' }}<br>
                        <br>
                        <span class="status-badge {{ $invoice->status == 'paid' ? 'status-paid' : 'status-unpaid' }}">
                            {{ $invoice->status == 'paid' ? 'LUNAS' : 'BELUM LUNAS' }}
                        </span>
                    </div>
                </td>
            </tr>
        </table>

        <table class="info-section">
            <tr>
                <td style="padding-right: 15px;">
                    <div class="info-label">Tagihan Kepada:</div>
                    <div class="info-value">{{ $invoice->pppoe->preOrder->nama_lengkap ?? 'Pelanggan' }}</div>
                    <div style="font-size: 12px; color: #475569; margin-top: 5px;">
                        Alamat: {{ $invoice->pppoe->preOrder->alamat ?? '-' }}<br>
                        Telepon: {{ $invoice->pppoe->preOrder->no_whatsapp ?? '-' }}<br>
                        Username PPPoE: {{ $invoice->pppoe->username }}
                    </div>
                </td>
                <td style="padding-left: 15px;">
                    <div class="info-box">
                        <div class="info-label">Metode Pembayaran</div>
                        <div class="info-value">{{ strtoupper($invoice->payment_method ?? 'Transfer Bank') }}</div>
                        @if($invoice->status == 'unpaid')
                        <div style="font-size: 11px; margin-top: 10px;">
                            BCA: 1234567890 a.n Erlangga<br>
                            Mandiri: 0987654321 a.n Erlangga
                        </div>
                        @endif
                    </div>
                </td>
            </tr>
        </table>

        <table class="items-table">
            <thead>
                <tr>
                    <th>Deskripsi Layanan</th>
                    <th class="text-center">Periode</th>
                    <th class="text-right">Harga</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <strong>Paket {{ $invoice->pppoe->paket->nama_paket ?? '-' }}</strong><br>
                        <span style="font-size: 11px; color: #64748b;">Langganan Internet Bulanan</span>
                    </td>
                    <td class="text-center">
                        {{ $invoice->created_at->format('M Y') }}
                    </td>
                    <td class="text-right">Rp {{ number_format($invoice->amount, 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>

        <table class="summary-table">
            <tr>
                <td class="summary-label">Subtotal</td>
                <td class="text-right">Rp {{ number_format($invoice->amount, 0, ',', '.') }}</td>
            </tr>
            @if($invoice->tax > 0)
            <tr>
                <td class="summary-label">PPN</td>
                <td class="text-right">Rp {{ number_format($invoice->tax, 0, ',', '.') }}</td>
            </tr>
            @endif
            @if($invoice->discount > 0)
            <tr>
                <td class="summary-label">Diskon</td>
                <td class="text-right">- Rp {{ number_format($invoice->discount, 0, ',', '.') }}</td>
            </tr>
            @endif
            <tr>
                <td class="summary-label summary-total">Total Tagihan</td>
                <td class="text-right summary-total">Rp {{ number_format($invoice->total, 0, ',', '.') }}</td>
            </tr>
        </table>

        <div class="footer">
            Terima kasih telah menggunakan layanan Erlangga Net.<br>
            Untuk pertanyaan terkait tagihan ini, silakan hubungi customer service kami.
        </div>
    </div>
</body>
</html>
