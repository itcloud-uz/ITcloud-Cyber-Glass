<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ITcloud | Mijozlar Portali - Kirish</title>
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
            max-width: 450px;
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
            margin-bottom: 40px;
        }
        .brand-logo span { color: var(--neon-cyan); text-shadow: 0 0 15px rgba(0,255,204,0.5); }

        .form-group { margin-bottom: 25px; text-align: left; }
        .form-group label { display: block; margin-bottom: 10px; color: var(--text-muted); font-size: 14px; padding-left: 15px; }
        
        .input-field {
            width: 100%;
            padding: 15px 20px;
            background: rgba(0,0,0,0.4);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            color: white;
            font-size: 16px;
            outline: none;
            transition: 0.3s;
        }
        .input-field:focus { border-color: var(--neon-cyan); box-shadow: 0 0 20px rgba(0, 255, 204, 0.1); }

        .btn-login {
            width: 100%;
            padding: 16px;
            background: linear-gradient(45deg, var(--neon-purple), var(--neon-cyan));
            border: none;
            border-radius: 20px;
            color: white;
            font-weight: 800;
            font-size: 16px;
            cursor: pointer;
            transition: 0.3s;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 10px;
        }
        .btn-login:hover { transform: scale(1.02); box-shadow: 0 0 30px rgba(0, 255, 204, 0.3); }

        .forgot-link {
            display: inline-block;
            margin-top: 30px;
            color: var(--text-muted);
            text-decoration: none;
            font-size: 14px;
            transition: 0.3s;
        }
        .forgot-link span { color: var(--neon-cyan); font-weight: 600; }
        .forgot-link:hover { opacity: 0.8; }

        .support-banner {
            margin-top: 30px;
            background: rgba(0, 255, 204, 0.1);
            border: 1px solid rgba(0, 255, 204, 0.2);
            padding: 15px;
            border-radius: 20px;
            font-size: 13px;
        }

        .alert-error {
            background: rgba(255, 0, 127, 0.1);
            color: #ff007f;
            padding: 12px;
            border-radius: 15px;
            margin-bottom: 20px;
            font-size: 14px;
            border: 1px solid rgba(255, 0, 127, 0.2);
        }

        /* Lang Switcher Naked Style */
        .lang-switcher-premium {
            position: absolute; top: 15px; right: 15px; z-index: 10000;
            background: transparent; border: none; padding: 2px;
            display: flex; align-items: center; gap: 8px; transition: 0.3s; cursor: pointer;
        }
        .lang-choices { display: flex; width: 0; overflow: hidden; transition: 0.3s cubic-bezier(0.4, 0, 0.2, 1); gap: 10px; align-items: center; }
        .lang-switcher-premium:hover .lang-choices { width: 110px; }
        .lang-flag-img { width: 22px; height: 14px; object-fit: cover; border-radius: 2px; transition: 0.2s; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.5)); }
        .lang-flag-link:hover .lang-flag-img { transform: scale(1.2); filter: brightness(1.2); }
        .current-flag-img { border: 1px solid rgba(0, 255, 204, 0.8); }
    </style>
</head>
<body>

    <div class="ambient-blob blob-1"></div>
    <div class="ambient-blob blob-2"></div>

    <div class="login-card">
        <div class="lang-switcher-premium">
            <span class="lang-flag current">
                @php $cur = App::getLocale(); @endphp
                <img src="https://flagcdn.com/w40/{{ $cur == 'en' ? 'gb' : $cur }}.png" class="lang-flag-img current-flag-img">
            </span>
            <div class="lang-choices">
                <a href="{{ route('lang.switch', 'uz') }}" class="lang-flag-link" title="O'zbek"><img src="https://flagcdn.com/w40/uz.png" class="lang-flag-img"></a>
                <a href="{{ route('lang.switch', 'tr') }}" class="lang-flag-link" title="Türkçe"><img src="https://flagcdn.com/w40/tr.png" class="lang-flag-img"></a>
                <a href="{{ route('lang.switch', 'ru') }}" class="lang-flag-link" title="Русский"><img src="https://flagcdn.com/w40/ru.png" class="lang-flag-img"></a>
                <a href="{{ route('lang.switch', 'en') }}" class="lang-flag-link" title="English"><img src="https://flagcdn.com/w40/gb.png" class="lang-flag-img"></a>
            </div>
        </div>
        <div class="brand-logo">IT<span>cloud</span> Client</div>
        
        @if($errors->any())
            <div class="alert-error">
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('client.login') }}" method="POST">
            @csrf
            <div class="form-group">
                <label>E-pochta manzili</label>
                <input type="email" name="email" class="input-field" placeholder="example@mail.com" required>
            </div>
            
            <div class="form-group">
                <label>Maxfiy parol</label>
                <input type="password" name="password" class="input-field" placeholder="••••••••" required>
            </div>

            <button type="submit" class="btn-login">Tizimga Kirish</button>
        </form>

        <a href="{{ route('client.password.request') }}" class="forgot-link">
            Parolingizni unutdingizmi? <span>Texnik yordamga yozish</span>
        </a>

        <div class="support-banner">
            <i class="fa-solid fa-shield-halved" style="color: var(--neon-cyan); margin-right: 8px;"></i>
            Xavfsizlik tizimi faol. Barcha kirish amallar IP bo'yicha nazorat qilinadi.
        </div>
    </div>

</body>
</html>
