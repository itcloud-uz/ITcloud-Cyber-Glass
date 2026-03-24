<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ITcloud | Cyber-Glass Login</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-dark: #05050a;
            --neon-cyan: #00ffcc;
            --neon-purple: #b026ff;
            --neon-pink: #ff007f;
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
        .blob-1 { width: 400px; height: 400px; background: var(--neon-purple); top: 10%; left: 20%; animation: float 10s infinite alternate; }
        .blob-2 { width: 400px; height: 400px; background: var(--neon-cyan); bottom: 10%; right: 20%; animation: float 15s infinite alternate-reverse; }

        @keyframes float { 0% { transform: translate(0,0); } 100% { transform: translate(50px, 50px); } }

        .glass-panel {
            background: var(--glass-bg);
            backdrop-filter: var(--glass-blur);
            -webkit-backdrop-filter: var(--glass-blur);
            border: 1px solid var(--neon-cyan);
            border-radius: 30px;
            padding: 50px 40px;
            width: 400px;
            text-align: center;
            box-shadow: 0 0 30px rgba(0, 255, 204, 0.2);
            transition: all 0.4s ease;
        }

        .brand { margin-bottom: 30px; text-align: center; }
        .logo-animated {
            width: 120px;
            height: auto;
            filter: drop-shadow(0 0 10px rgba(0, 255, 204, 0.4));
            animation: logoFloat 5s ease-in-out infinite;
        }
        @keyframes logoFloat {
            0%, 100% { transform: translateY(0) rotate(-1deg); }
            50% { transform: translateY(-8px) rotate(1deg); }
        }

        .input-group { margin-bottom: 25px; text-align: left; }
        .input-group label { display: block; font-size: 13px; color: var(--text-muted); margin-bottom: 8px; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; margin-left: 10px; }
        
        .input-field {
            width: 100%;
            padding: 16px 20px;
            background: rgba(0,0,0,0.3);
            border: 1px solid var(--glass-border);
            border-radius: 16px;
            color: white;
            font-size: 15px;
            outline: none;
            transition: 0.3s;
        }
        .input-field:focus { border-color: var(--neon-cyan); box-shadow: 0 0 15px rgba(0,255,204,0.2); }

        .btn-neon {
            width: 100%;
            padding: 16px;
            border-radius: 16px;
            border: 1px solid var(--neon-cyan);
            background: rgba(0,255,204,0.1);
            color: var(--neon-cyan);
            font-weight: 600;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        .btn-neon:hover { background: var(--neon-cyan); color: #000; box-shadow: 0 0 20px var(--neon-cyan); }
        .btn-neon:disabled { opacity: 0.5; cursor: not-allowed; }

        .btn-telegram { border-color: #0088cc; color: #0088cc; background: rgba(0, 136, 204, 0.1); margin-top: 15px; }
        .btn-telegram:hover { background: #0088cc; color: #fff; box-shadow: 0 0 20px rgba(0,136,204,0.5); }

        /* Yuz Skaneri (Face ID) UX */
        #face-id-container { display: none; text-align: center; }
        .scanner-box {
            position: relative; width: 220px; height: 220px; margin: 0 auto 30px; display: flex; align-items: center; justify-content: center;
        }
        .scanner-box::before {
            content: ''; position: absolute; width: 100%; height: 100%; border-radius: 50%; border: 3px dashed var(--neon-cyan); animation: scanSpin 6s linear infinite; box-shadow: 0 0 20px rgba(0,255,204,0.3);
        }
        .scanner-box video { width: 200px; height: 200px; border-radius: 50%; object-fit: cover; z-index: 10; border: 2px solid rgba(0,255,204,0.2); }
        .scan-line { position: absolute; top: 0; left: 0; width: 100%; height: 2px; background: var(--neon-cyan); box-shadow: 0 0 15px var(--neon-cyan); animation: scanMove 2s infinite alternate; }
        
        @keyframes scanMove { 0% { top: 10%; } 100% { top: 90%; } }
        @keyframes scanSpin { 100% { transform: rotate(360deg); } }

        .liveness-text { font-size: 16px; color: var(--neon-purple); font-weight: 600; margin-bottom: 20px; animation: textPulse 1s infinite alternate; }
        @keyframes textPulse { from { opacity: 0.5; } to { opacity: 1; text-shadow: 0 0 10px var(--neon-purple); } }

        .error-msg { color: var(--neon-pink); font-size: 14px; margin-bottom: 15px; display: none; }
    </style>
</head>
<body>

    <div class="ambient-blob blob-1"></div>
    <div class="ambient-blob blob-2"></div>

    <div class="glass-panel" id="login-panel">
        <div style="position: absolute; top: 20px; right: 20px; display: flex; gap: 10px;">
            <a href="{{ route('lang.switch', 'uz') }}" style="text-decoration: none; opacity: {{ App::getLocale() == 'uz' ? '1' : '0.4' }};">🇺🇿</a>
            <a href="{{ route('lang.switch', 'tr') }}" style="text-decoration: none; opacity: {{ App::getLocale() == 'tr' ? '1' : '0.4' }};">🇹🇷</a>
            <a href="{{ route('lang.switch', 'ru') }}" style="text-decoration: none; opacity: {{ App::getLocale() == 'ru' ? '1' : '0.4' }};">🇷🇺</a>
            <a href="{{ route('lang.switch', 'en') }}" style="text-decoration: none; opacity: {{ App::getLocale() == 'en' ? '1' : '0.4' }};">🇺🇸</a>
        </div>
        <div class="brand">
            <div class="logo-animated" style="display: inline-block; font-size: 32px; font-weight: 800; letter-spacing: 1px;">
                IT<span style="color: var(--neon-cyan); text-shadow: 0 0 15px rgba(0,255,204,0.5);">cloud</span> <span style="font-size: 14px; color: var(--text-muted); font-weight: 400;">Master</span>
            </div>
        </div>
        
        <div id="error-alert" class="error-msg"></div>

        <div id="step-1-password">
            <div class="input-group">
                <label>Email / Login</label>
                <input type="email" id="email" class="input-field" placeholder="admin@itcloud.uz" value="admin@itcloud.uz" />
            </div>
            
            <div class="input-group">
                <label>{{ __('Secret Password') }}</label>
                <input type="password" id="password" class="input-field" placeholder="••••••••" value="clone1997" />
            </div>

            <button class="btn-neon" onclick="submitPassword()" id="btn-login">
                <i class="fa-solid fa-arrow-right-to-bracket"></i> {{ __('Login to System') }}
            </button>
        </div>

        <div id="step-2-faceid" id="face-id-container" style="display: none;">
            <div class="scanner-box">
                <video id="video-feed" autoplay playsinline></video>
                <div class="scan-line"></div>
            </div>
            
            <div class="liveness-text" id="liveness-msg">{{ __('Look at the camera and blink...') }}</div>
            
            <button class="btn-neon btn-telegram" onclick="requestTelegramOTP()" id="btn-otp">
                <i class="fa-brands fa-telegram"></i> {{ __('Send code instead of Face ID') }}
            </button>
        </div>

        <div id="step-3-otp" style="display: none;">
            <div class="input-group">
                <label>{{ __('Telegram Code (6 digits)') }}</label>
                <input type="text" id="otp-code" class="input-field" placeholder="123456" />
            </div>
            <button class="btn-neon" onclick="verifyOTP()">{{ __('Confirm Code') }}</button>
        </div>

    </div>

    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        
        function showError(msg) {
            const el = document.getElementById('error-alert');
            el.innerHTML = msg;
            el.style.display = 'block';
            setTimeout(() => { el.style.display = 'none'; }, 5000);
        }

        async function submitPassword() {
            const btn = document.getElementById('btn-login');
            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Tekshirilmoqda...';

            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;

            try {
                const res = await fetch('/login', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                    body: JSON.stringify({ email, password })
                });
                const data = await res.json();

                if (data.status === 'success' && data.step === 'face_id_required') {
                    document.getElementById('step-1-password').style.display = 'none';
                    document.getElementById('step-2-faceid').style.display = 'block';
                    startCamera();
                } else {
                    showError(data.message || 'Xatolik yuz berdi');
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fa-solid fa-arrow-right-to-bracket"></i> Tizimga Kirish';
                }
            } catch (err) {
                showError("Server ulanishida xatolik!");
                btn.disabled = false;
            }
        }

        async function startCamera() {
            const video = document.getElementById('video-feed');
            try {
                const stream = await navigator.mediaDevices.getUserMedia({ video: true });
                video.srcObject = stream;
                
                // Kadr tayyor bo'lishini kutamiz (Metadata yuklangach)
                video.onloadedmetadata = () => {
                    video.play();
                    document.getElementById('liveness-msg').innerHTML = "Yuz tahlil qilinmoqda...";
                    
                    // Kichik delay (Kamera uyg'onishi uchun)
                    setTimeout(() => {
                        // Canvas orqali kadrni olish (Tezlik uchun kichraytiramiz)
                        const canvas = document.createElement('canvas');
                        canvas.width = 400; // Standart o'lcham
                        canvas.height = 300;
                        const ctx = canvas.getContext('2d');
                        ctx.drawImage(video, 0, 0, 400, 300);
                        const imageData = canvas.toDataURL('image/jpeg', 0.7); // Sifatni biroz pasaytiramiz (70%)

                        // Backend orqali Python API ga yuborish
                        sendFaceIDImage(imageData);
                    }, 300);
                };

            } catch (err) {
                showError("Kamerani yoqish imkoni bo'lmadi! Iltimos, Telegram orqali kiring.");
                document.getElementById('liveness-msg').innerHTML = "Kamera bloklangan.";
            }
        }

        let faceRetryCount = 0;
        async function sendFaceIDImage(image) {
            try {
                const res = await fetch('/login/face-id', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                    body: JSON.stringify({ image: image })
                });
                const data = await res.json();
                if (data.status === 'success') {
                    document.getElementById('liveness-msg').innerHTML = "<i class='fa-solid fa-check'></i> {{ __('Success') }}! {{ __('Logging in') }}...";
                    setTimeout(() => window.location.href = data.redirect, 1000);
                } else {
                    if (faceRetryCount < 3) {
                        faceRetryCount++;
                        document.getElementById('liveness-msg').innerHTML = `<i class='fa-solid fa-rotate'></i> {{ __('Retry') }} (${faceRetryCount}/3)...`;
                        setTimeout(() => startCamera(), 2000);
                    } else {
                        showError("Yuz tanilmadi. Iltimos, Telegram orqali kiring.");
                        document.getElementById('liveness-msg').innerHTML = "Tahlil to'xtatildi.";
                        document.getElementById('btn-otp').style.boxShadow = "0 0 20px var(--neon-cyan)";
                    }
                }
            } catch (err) { 
                showError('FaceID serveri bilan bog\'lanishda xato'); 
            }
        }

        async function requestTelegramOTP() {
            try {
                const res = await fetch('/login/otp/send', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                });
                const data = await res.json();
                if (data.status === 'success') {
                    // Kameradagi oqimni to'xtatamiz
                    const video = document.getElementById('video-feed');
                    if(video.srcObject) { video.srcObject.getTracks().forEach(track => track.stop()); }
                    
                    document.getElementById('step-2-faceid').style.display = 'none';
                    document.getElementById('step-3-otp').style.display = 'block';
                    showError("<span style='color:var(--neon-cyan)'>Kamera o'chirildi. Kod yuborildi.</span>");
                }
            } catch(err) { showError('Bot bilan ishlashda xato'); }
        }

        async function verifyOTP() {
            const otpCode = document.getElementById('otp-code').value;
            try {
                const res = await fetch('/login/otp/verify', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                    body: JSON.stringify({ otp: otpCode })
                });
                const data = await res.json();
                if (data.status === 'success') {
                    window.location.href = data.redirect;
                } else {
                    showError(data.message);
                }
            } catch(e) {}
        }
    </script>
</body>
</html>
