<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Memuat ulang</title>
    <script>
        (function () {
            // CSRF / page expired: refresh silently without "sesi habis" messaging.
            try {
                if (!sessionStorage.getItem('err419_reloaded')) {
                    sessionStorage.setItem('err419_reloaded', '1');
                    window.location.reload();
                    return;
                }
                sessionStorage.removeItem('err419_reloaded');
                window.location.href = {{ json_encode(url('/admin')) }};
            } catch (e) {
                window.location.href = {{ json_encode(url('/admin')) }};
            }
        })();
    </script>
</head>
<body></body>
</html>
