<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Status Pembayaran' }} - Erlangga Net</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background-color: #f8fafc;
            color: #1e293b;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .card {
            background: #ffffff;
            border-radius: 20px;
            padding: 36px 28px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.02);
            max-width: 380px;
            width: 100%;
            text-align: center;
            border: 1px solid #f1f5f9;
        }
        .icon-wrapper {
            width: 72px;
            height: 72px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 34px;
        }
        .icon-success {
            background: #dcfce7;
            color: #16a34a;
        }
        .icon-failure {
            background: #fee2e2;
            color: #dc2626;
        }
        h1 {
            font-size: 20px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 10px;
        }
        p {
            font-size: 14px;
            color: #64748b;
            line-height: 1.5;
            margin-bottom: 24px;
        }
        .btn {
            display: inline-block;
            width: 100%;
            padding: 13px 20px;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            border: none;
            transition: all 0.2s ease;
        }
        .btn-success {
            background: #0284c7;
            color: #ffffff;
        }
        .btn-success:hover {
            background: #0369a1;
        }
        .btn-failure {
            background: #64748b;
            color: #ffffff;
        }
        .btn-failure:hover {
            background: #475569;
        }
    </style>
</head>
<body>
    <div class="card">
        @if(!empty($isSuccess))
            <div class="icon-wrapper icon-success">✓</div>
            <h1>{{ $title ?? 'Pembayaran Berhasil!' }}</h1>
            <p>{{ $message ?? 'Terima kasih. Pembayaran tagihan internet Anda telah kami terima dan diverifikasi.' }}</p>
            <button type="button" class="btn btn-success" onclick="closeWebView()">Kembali ke Aplikasi</button>
        @else
            <div class="icon-wrapper icon-failure">✕</div>
            <h1>{{ $title ?? 'Pembayaran Belum Berhasil' }}</h1>
            <p>{{ $message ?? 'Sesi pembayaran telah berakhir, dibatalkan, atau gagal diproses.' }}</p>
            <button type="button" class="btn btn-failure" onclick="closeWebView()">Kembali ke Aplikasi</button>
        @endif
    </div>

    <script>
        function closeWebView() {
            if (window.opener) {
                window.close();
            } else {
                history.back();
            }
        }
    </script>
</body>
</html>
