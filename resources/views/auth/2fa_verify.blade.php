<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi 2FA</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="bg-light">

    <!-- Form untuk submit kode (tidak terlihat) -->
    <form id="2fa-form" method="POST" action="{{ route('2fa.verify') }}">
        @csrf
        <input type="hidden" name="code" id="code-input">
    </form>

    <!-- Optional: Message jika JavaScript tidak aktif -->
    <noscript>
        <div class="container mt-5 alert alert-danger text-center">
            JavaScript diperlukan untuk melanjutkan verifikasi 2FA.
        </div>
    </noscript>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                title: 'Verifikasi 2FA',
                text: 'Masukkan 6 digit kode dari Google Authenticator Anda',
                input: 'text',
                inputLabel: 'Kode 2FA',
                inputAttributes: {
                    inputmode: 'numeric',
                    maxlength: 6,
                    autofocus: 'autofocus', // Otomatis fokus saat muncul
                    pattern: '[0-9]*',
                    class: 'form-control text-center'
                },
                inputPlaceholder: '123456',
                showConfirmButton: false, // Sembunyikan tombol karena akan otomatis
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: () => {
                    const input = Swal.getInput();

                    // Pantau setiap perubahan input
                    input.addEventListener('input', (e) => {
                        const val = e.target.value;

                        // Jika sudah mencapai 6 digit, langsung submit
                        if (val.length === 6) {
                            // Jalankan validasi sederhana
                            if (/^\d+$/.test(val)) {
                                // Tampilkan loading sebentar sebelum submit (opsional agar user tidak kaget)
                                Swal.showLoading();

                                document.getElementById('code-input').value = val;
                                document.getElementById('2fa-form').submit();
                            } else {
                                Swal.showValidationMessage('Kode harus berupa angka.');
                                e.target.value = ''; // Reset jika bukan angka
                            }
                        }
                    });
                }
            });
        });
    </script>

</body>

</html>
