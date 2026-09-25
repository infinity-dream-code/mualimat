<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Server Error</title>
    <script>
        (function () {
            try {
                if (!sessionStorage.getItem('err500_reloaded')) {
                    sessionStorage.setItem('err500_reloaded', '1');
                    window.location.reload();
                    return;
                }
                sessionStorage.removeItem('err500_reloaded');
            } catch (e) { /* ignore */ }
        })();
    </script>
</head>
<body style="font-family: system-ui, sans-serif; display:flex; align-items:center; justify-content:center; min-height:100vh; margin:0; color:#6b7280;">
    <div style="text-align:center;">
        <div style="font-size:1.25rem; letter-spacing:.05em;">500 — Server Error</div>
        <p style="margin-top:1rem;">Silakan muat ulang halaman jika masalah berlanjut.</p>
    </div>
</body>
</html>
