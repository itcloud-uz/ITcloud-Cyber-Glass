<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Academy | Student Login</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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

        .ambient-blob { position: absolute; border-radius: 50%; filter: blur(120px); z-index: -1; opacity: 0.3; }
        .blob-1 { width: 400px; height: 400px; background: var(--neon-purple); top: 10%; left: 20%; animation: float 10s infinite alternate; }
        .blob-2 { width: 400px; height: 400px; background: var(--neon-cyan); bottom: 10%; right: 20%; animation: float 15s infinite alternate-reverse; }
        @keyframes float { 0% { transform: translate(0,0); } 100% { transform: translate(50px, 50px); } }

        .login-card {
            width: 450px;
            padding: 50px;
            background: var(--glass-bg);
            backdrop-filter: var(--glass-blur);
            -webkit-backdrop-filter: var(--glass-blur);
            border: 1px solid var(--glass-border);
            border-radius: 40px;
            text-align: center;
            box-shadow: 0 25px 50px rgba(0,0,0,0.5);
            animation: slideUp 0.6s cubic-bezier(0.23, 1, 0.32, 1);
        }

        @keyframes slideUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }

        .brand-logo {
            font-size: 38px;
            font-weight: 800;
            margin-bottom: 5px;
            color: white;
        }
        .brand-logo span { color: var(--neon-cyan); }
        .subtitle { color: var(--text-muted); font-size: 14px; margin-bottom: 35px; }

        .input-group { margin-bottom: 25px; text-align: left; }
        .input-group label { display: block; font-size: 12px; font-weight: 600; text-transform: uppercase; color: var(--text-muted); margin-bottom: 8px; margin-left: 15px; }
        
        .input-field {
            width: 100%;
            padding: 18px 25px;
            background: rgba(255,255,255,0.03);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            color: white;
            font-size: 15px;
            outline: none;
            transition: 0.3s;
        }
        .input-field:focus { border-color: var(--neon-cyan); background: rgba(255,255,255,0.05); }

        .btn-login {
            width: 100%;
            padding: 18px;
            border-radius: 20px;
            border: none;
            background: linear-gradient(135deg, var(--neon-cyan), var(--neon-purple));
            color: white;
            font-size: 16px;
            font-weight: 800;
            cursor: pointer;
            transition: 0.3s;
            box-shadow: 0 10px 20px rgba(0,255,204,0.2);
            margin-top: 10px;
        }
        .btn-login:hover { transform: translateY(-2px); box-shadow: 0 15px 30px rgba(0,255,204,0.4); }
        .btn-login:active { transform: translateY(0); }

        .footer-links { margin-top: 30px; font-size: 13px; color: var(--text-muted); }
        .footer-links a { color: var(--neon-cyan); text-decoration: none; font-weight: 600; }
    </style>
</head>
<body>

    <div class="ambient-blob blob-1"></div>
    <div class="ambient-blob blob-2"></div>

    <div class="login-card">
        <div class="brand-logo">IT<span>cloud</span></div>
        <div class="subtitle">Academy Student Portal</div>

        <form id="studentLoginForm">
            <div class="input-group">
                <label>Elektron pochta</label>
                <input type="email" name="email" id="email" class="input-field" placeholder="student@itcloud.uz" required>
            </div>

            <div class="input-group">
                <label>Parol</label>
                <input type="password" name="password" id="password" class="input-field" placeholder="••••••••" required>
            </div>

            <button type="submit" class="btn-login" id="submitBtn">KIRISH <i class="fa-solid fa-rocket"></i></button>
        </form>

        <div class="footer-links">
            Hali o'quvchi emasmisiz? <a href="/">Ariza topshirish</a>
        </div>
    </div>

    <script>
        document.getElementById('studentLoginForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const btn = document.getElementById('submitBtn');
            const originalText = btn.innerHTML;
            
            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> KIRILMOQDA...';

            const formData = new FormData(e.target);
            const data = Object.fromEntries(formData);

            try {
                const res = await fetch('/login', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(data)
                });

                const result = await res.json();

                if (res.ok) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Muvaffaqiyatli!',
                        text: 'Tizimga kirildi, yo\'naltirilmoqda...',
                        timer: 1500,
                        showConfirmButton: false,
                        background: '#0a0a1a',
                        color: '#fff'
                    }).then(() => {
                        window.location.href = result.redirect || '/';
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Xato!',
                        text: result.message || 'Login yoki parol xato',
                        background: '#0a0a1a',
                        color: '#fff'
                    });
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                }
            } catch (err) {
                Swal.fire('Xato', 'Server bilan bog\'lanishda xatolik', 'error');
                btn.disabled = false;
                btn.innerHTML = originalText;
            }
        });
    </script>
</body>
</html>
