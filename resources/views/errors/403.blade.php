<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 - Akses Ditolak</title>
    <meta name="robots" content="noindex, nofollow">
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            font-family: Arial, sans-serif;
            background: #f8fafc;
            color: #1e293b;
        }

        .error-card {
            width: 100%;
            max-width: 460px;
            padding: 40px 32px;
            text-align: center;
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.06);
        }

        .error-code {
            margin: 0 0 12px;
            font-size: 64px;
            font-weight: 800;
            color: #dc2626;
        }

        h1 {
            margin: 0 0 12px;
            font-size: 24px;
        }

        p {
            color: #64748b;
            line-height: 1.6;
        }

        .countdown {
            margin: 24px 0;
            font-weight: 600;
            color: #475569;
        }

        .back-button {
            display: inline-block;
            padding: 12px 24px;
            border-radius: 8px;
            background: #0f766e;
            color: white;
            text-decoration: none;
            font-weight: 600;
        }

        .back-button:hover {
            background: #115e59;
        }


        @media (prefers-color-scheme: dark) {
            body {
                background: #0f172a;
                color: #f1f5f9;
            }

            .error-card {
                background: #1e293b;
                border-color: #334155;
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.25);
            }

            .error-code {
                color: #f87171;
            }

            p {
                color: #cbd5e1;
            }

            .countdown {
                color: #e2e8f0;
            }

            .back-button {
                background: #0d9488;
            }

            .back-button:hover {
                background: #0f766e;
            }
        }
    </style>
</head>

<body>
    <main class="error-card">
        <div class="error-code">403</div>
        <h1>Akses Ditolak</h1>

        <p>
            Maaf, Anda tidak memiliki izin untuk mengakses halaman ini.
        </p>

        <p class="countdown">
            Anda akan dikembalikan ke halaman sebelumnya dalam
            <span id="countdown">3</span> detik.
        </p>

        <a href="javascript:history.back()" class="back-button">
            Kembali Sekarang
        </a>
    </main>

    <script>
        let remaining = 3;
        const countdown = document.getElementById('countdown');

        const timer = setInterval(() => {
            remaining--;
            countdown.textContent = remaining;

            if (remaining <= 0) {
                clearInterval(timer);

                if (window.history.length > 1) {
                    window.history.back();
                } else {
                    window.location.href = '/admin';
                }
            }
        }, 1000);
    </script>
</body>

</html>