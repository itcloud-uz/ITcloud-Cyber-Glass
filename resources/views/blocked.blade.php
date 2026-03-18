<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ITcloud | Loyiha Bloklangan</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-dark: #05050a;
            --neon-pink: #ff007f;
            --glass-bg: rgba(20, 20, 35, 0.4);
            --glass-border: rgba(255, 255, 255, 0.08);
            --glass-blur: blur(24px);
        }
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Outfit', sans-serif; }
        body {
            background-color: var(--bg-dark);
            color: #fff;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            position: relative;
        }
        /* Ambient blur */
        .ambient-blob { position: absolute; border-radius: 50%; filter: blur(120px); z-index: -1; opacity: 0.3; }
        .blob-1 { width: 500px; height: 500px; background: var(--neon-pink); top: 50%; left: 50%; transform: translate(-50%, -50%); animation: pulse 4s infinite alternate; }

        @keyframes pulse {
            0% { transform: translate(-50%, -50%) scale(1); opacity: 0.2; }
            100% { transform: translate(-50%, -50%) scale(1.2); opacity: 0.5; }
        }

        .glass-panel {
            background: var(--glass-bg);
            backdrop-filter: var(--glass-blur);
            -webkit-backdrop-filter: var(--glass-blur);
            border: 1px solid var(--neon-pink);
            border-radius: 30px;
            padding: 50px;
            text-align: center;
            max-width: 500px;
            box-shadow: 0 0 40px rgba(255, 0, 127, 0.3);
            z-index: 10;
        }

        h1 { font-size: 32px; font-weight: 800; margin-bottom: 20px; color: var(--neon-pink); text-shadow: 0 0 15px var(--neon-pink); }
        p { color: #8b9bb4; font-size: 16px; line-height: 1.6; margin-bottom: 30px; }
        
        .btn-neon {
            padding: 15px 30px;
            border-radius: 14px;
            border: 1px solid var(--neon-pink);
            background: transparent;
            color: var(--neon-pink);
            font-weight: 600;
            font-size: 15px;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }
        .btn-neon:hover {
            background: var(--neon-pink);
            color: #000;
            box-shadow: 0 0 20px var(--neon-pink);
        }
    </style>
</head>
<body>
    <div class="ambient-blob blob-1"></div>
    <div class="glass-panel">
        <h1>ITcloud Block Screen</h1>
        <p>Hurmatli mijoz, sizning obuna vaqtingiz tugagan yoki tizim bloklangan. Iltimos, xizmatni davom ettirish uchun to'lov qiling yoxud texnik yordamga murojaat qiling.</p>
        <a href="#" class="btn-neon">To'lov qilish sahifasi</a>
    </div>
</body>
</html>
