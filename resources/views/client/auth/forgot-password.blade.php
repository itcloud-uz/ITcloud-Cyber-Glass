<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ITcloud | Parolni Tiklash</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-dark: #05050a;
            --neon-cyan: #00ffcc;
            --neon-purple: #b026ff;
            --glass-bg: rgba(20, 20, 35, 0.4);
            --glass-border: rgba(255, 255, 255, 0.08);
            --glass-blur: blur(24px);
            --text-main: #ffffff;
            --text-muted: #8b9bb4;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Outfit', sans-serif; }

        body {
            background-color: var(--bg-dark);
            color: var(--text-main);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            position: relative;
        }

        .ambient-blob { position: absolute; border-radius: 50%; filter: blur(120px); z-index: -1; opacity: 0.4; }
        .blob-1 { width: 500px; height: 500px; background: var(--neon-purple); top: -100px; left: -100px; }
        .blob-2 { width: 400px; height: 400px; background: var(--neon-cyan); bottom: -100px; right: -50px; }

        .login-card {
            width: 100%;
            max-width: 500px;
            padding: 50px;
            background: var(--glass-bg);
            backdrop-filter: var(--glass-blur);
            border: 1px solid var(--glass-border);
            border-radius: 40px;
            text-align: center;
            box-shadow: 0 25px 50px rgba(0,0,0,0.5);
            animation: fadeIn 0.8s ease;
        }

        @keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }

        .brand-logo {
            font-size: 32px;
            font-weight: 800;
            margin-bottom: 30px;
        }
        .brand-logo span { color: var(--neon-cyan); text-shadow: 0 0 15px rgba(0,255,204,0.5); }

        .info-text {
            color: var(--text-muted);
            line-height: 1.6;
            margin-bottom: 35px;
            font-size: 15px;
        }

        .btn-support {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            padding: 16px 30px;
            background: rgba(0, 136, 204, 0.15);
            border: 1px solid rgba(0, 136, 204, 0.4);
            border-radius: 20px;
            color: #0088cc;
            font-weight: 700;
            text-decoration: none;
            transition: 0.3s;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .btn-support:hover {
            background: rgba(0, 136, 204, 0.25);
            transform: scale(1.05);
            box-shadow: 0 0 30px rgba(0, 136, 204, 0.2);
        }

        .btn-back {
            display: block;
            margin-top: 40px;
            color: var(--text-muted);
            text-decoration: none;
            font-size: 14px;
            transition: 0.3s;
        }
        .btn-back:hover { color: white; }

        .security-badge {
            margin-bottom: 25px;
            font-size: 48px;
            color: var(--neon-cyan);
            filter: drop-shadow(0 0 15px var(--neon-cyan));
        }
    </style>
</head>
<body>

    <div class="ambient-blob blob-1"></div>
    <div class="ambient-blob blob-2"></div>

    <div class="login-card">
        <div class="security-badge">
            <i class="fa-solid fa-user-shield"></i>
        </div>
        
        <div class="brand-logo">IT<span>cloud</span> Security</div>
        
        <div class="info-text">
            Xavfsizlik qoidalari sababli, ITcloud tizimida parolni avtomatik tiklash imkoniyati o'chirilgan. <br><br>
            Parolingizni yangilash yoki hisobingizga kirishni tiklash uchun, iltimos, <b>Texnik Yordam</b> botiga yoki shaxsiy menedjeringizga murojaat qiling.
        </div>

        <a href="https://t.me/itcloud_support" class="btn-support" target="_blank">
            <i class="fa-brands fa-telegram"></i> Texnik Yordam (Support)
        </a>

        <a href="{{ route('client.login') }}" class="btn-back">
            <i class="fa-solid fa-arrow-left" style="margin-right: 8px;"></i> Kirish oynasiga qaytish
        </a>
    </div>

</body>
</html>
