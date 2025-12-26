<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Setup 2FA</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="bg-light">

    <noscript>
        <div class="container mt-5 alert alert-danger text-center">
            JavaScript diperlukan untuk menampilkan QR Code.
        </div>
    </noscript>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                title: 'Aktifkan Two-Factor Authentication',
                html: `
            <div class="text-center">
                <p class="mb-3">Scan QR Code di bawah ini menggunakan aplikasi <strong>Google Authenticator</strong>:</p>
                <div class="d-inline-block bg-white border rounded p-3 mb-3">
                    {!! addslashes($qrCodeSvg) !!}
                </div>
                <p class="mb-2">Atau masukkan kode berikut secara manual:</p>
                <code class="bg-light border rounded px-3 py-2 d-inline-block text-dark">{{ $secret }}</code>
            </div>
        `,
                showConfirmButton: true,
                confirmButtonText: 'Saya sudah scan, lanjut verifikasi',
                allowOutsideClick: false,
                allowEscapeKey: false,
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = "{{ route('2fa.redirect') }}";
                }
            });
        });
    </script>

</body>

</html>
