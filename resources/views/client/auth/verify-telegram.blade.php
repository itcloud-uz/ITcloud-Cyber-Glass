<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ITcloud | Telegram Tasdiqlash</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-dark: #05050a;
            --neon-blue: #0088cc;
            --neon-cyan: #00ffcc;
            --glass-bg: rgba(20, 20, 35, 0.4);
            --glass-border: rgba(255, 255, 255, 0.08);
            --glass-blur: blur(24px);
        }

        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Outfit', sans-serif; }

        body {
            background-color: var(--bg-dark);
            color: white;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            position: relative;
        }

        .ambient-blob { position: absolute; border-radius: 50%; filter: blur(120px); z-index: -1; opacity: 0.4; background: var(--neon-blue); width: 600px; height: 600px; top: -100px; left: -100px; }

        .verify-card {
            width: 100%;
            max-width: 500px;
            padding: 60px;
            background: var(--glass-bg);
            backdrop-filter: var(--glass-blur);
            border: 1px solid var(--glass-border);
            border-radius: 40px;
            text-align: center;
            box-shadow: 0 25px 50px rgba(0,0,0,0.5);
        }

        .telegram-icon {
            font-size: 80px;
            color: var(--neon-blue);
            margin-bottom: 30px;
            text-shadow: 0 0 30px rgba(0, 136, 204, 0.6);
            animation: pulse 2s infinite;
        }

        @keyframes pulse { 0% { opacity: 0.6; transform: scale(1); } 50% { opacity: 1; transform: scale(1.1); } 100% { opacity: 0.6; transform: scale(1); } }

        .title { font-size: 28px; font-weight: 800; margin-bottom: 15px; }
        .description { color: #8b9bb4; line-height: 1.6; margin-bottom: 40px; font-size: 15px; }

        .btn-telegram {
            display: inline-flex;
            align-items: center;
            gap: 15px;
            padding: 18px 40px;
            background: var(--neon-blue);
            border: none;
            border-radius: 20px;
            color: white;
            font-weight: 800;
            text-decoration: none;
            transition: 0.3s;
            text-transform: uppercase;
            letter-spacing: 1px;
            box-shadow: 0 10px 25px rgba(0, 136, 204, 0.3);
        }
        .btn-telegram:hover { transform: translateY(-3px); box-shadow: 0 15px 35px rgba(0, 136, 204, 0.5); }

        .loader {
            margin-top: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            font-size: 13px;
            color: #8b9bb4;
        }
        .dot { width: 6px; height: 6px; background: var(--neon-cyan); border-radius: 50%; animation: blink 1.4s infinite both; }
        .dot:nth-child(2) { animation-delay: 0.2s; }
        .dot:nth-child(3) { animation-delay: 0.4s; }

        @keyframes blink { 0%, 80%, 100% { opacity: 0; } 40% { opacity: 1; } }
    </style>
</head>
<body>

    <div class="ambient-blob"></div>

    <div class="verify-card">
        <div style="position: absolute; top: 20px; right: 20px; display: flex; gap: 10px;">
            <a href="{{ route('lang.switch', 'uz') }}" style="text-decoration: none; color: white; opacity: {{ App::getLocale() == 'uz' ? '1' : '0.4' }}; font-size: 12px; font-weight: 800;">UZ</a>
            <a href="{{ route('lang.switch', 'tr') }}" style="text-decoration: none; color: white; opacity: {{ App::getLocale() == 'tr' ? '1' : '0.4' }}; font-size: 12px; font-weight: 800;">TR</a>
            <a href="{{ route('lang.switch', 'ru') }}" style="text-decoration: none; color: white; opacity: {{ App::getLocale() == 'ru' ? '1' : '0.4' }}; font-size: 12px; font-weight: 800;">RU</a>
            <a href="{{ route('lang.switch', 'en') }}" style="text-decoration: none; color: white; opacity: {{ App::getLocale() == 'en' ? '1' : '0.4' }}; font-size: 12px; font-weight: 800;">EN</a>
        </div>
        <div class="telegram-icon">
            <i class="fa-brands fa-telegram"></i>
        </div>
        
        <h1 class="title">{{ __('Telegram Verification') }}</h1>
        <p class="description">
            {{ __('To ensure system security, please verify your identity via our Telegram bot. Click the button below and then press "Send Contact".') }}
        </p>

        <a href="https://t.me/Itcloudvertifikatsiya_bot?start={{ $user->verification_hash }}" target="_blank" class="btn-telegram">
            {{ __('Open Telegram Bot') }} <i class="fa-solid fa-arrow-up-right-from-square"></i>
        </a>

        <div class="loader">
            <div class="dot"></div>
            <div class="dot"></div>
            <div class="dot"></div>
            {{ __('Waiting for verification...') }}
        </div>
    </div>

    <script>
        const checkStatus = async () => {
            try {
                const res = await fetch('/api/client/verify/status');
                const data = await res.json();
                
                if (data.is_verified) {
                    window.location.href = '/client/dashboard';
                }
            } catch (err) {
                console.error("Status check failed:", err);
            }
        };

        // Har 3 soniyada tekshirish
        setInterval(checkStatus, 3000);
    </script>

</body>
</html>
