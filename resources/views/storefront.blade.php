<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ITcloud Cyber-Glass | Kelajak CRM Tizimi</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700&family=Rajdhani:wght@300;500;700&display=swap" rel="stylesheet">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- PWA Basic -->
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#030014">
    <style>
        :root {
            --primary: #00f2fe;
            --secondary: #4facfe;
            --accent: #f093fb;
            --bg-dark: #030014;
            --glass: rgba(255, 255, 255, 0.03);
            --glass-border: rgba(255, 255, 255, 0.1);
        }

        * { margin:0; padding:0; box-sizing: border-box; }
        body { font-family: 'Rajdhani', sans-serif; background: var(--bg-dark); color: white; overflow-x: hidden; }

        .blob {
            position: fixed; top: -100px; left: -100px;
            width: 400px; height: 400px;
            background: radial-gradient(circle, var(--secondary), transparent 70%);
            opacity: 0.15; filter: blur(50px); z-index: -1; animation: float 10s infinite;
        }

        @keyframes float { 0%, 100% { transform: translate(0,0); } 50% { transform: translate(50px, 50px); } }

        nav {
            padding: 20px 10%; display: flex; justify-content: space-between; align-items: center;
            background: rgba(3, 0, 20, 0.8); backdrop-filter: blur(10px); position: sticky; top: 0; z-index: 100;
        }

        .logo { font-family: 'Orbitron', sans-serif; font-size: 1.5rem; font-weight: 700; color: var(--primary); display: flex; align-items: center; gap: 10px; }
        .logo i { filter: drop-shadow(0 0 10px var(--primary)); }

        .hero {
            height: 90vh; display: flex; flex-direction: column; justify-content: center; align-items: center; text-align: center;
            padding: 0 10%; background: radial-gradient(circle at center, rgba(79, 172, 254, 0.1), transparent);
        }

        h1 { font-family: 'Orbitron', sans-serif; font-size: 4rem; margin-bottom: 20px; line-height: 1.1; }
        h1 span { background: linear-gradient(90deg, var(--primary), var(--accent)); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        p.lead { font-size: 1.2rem; color: #aaa; max-width: 600px; margin-bottom: 40px; }

        .btn {
            padding: 15px 40px; border-radius: 50px; text-decoration: none; font-weight: 700; font-family: 'Orbitron', sans-serif;
            transition: 0.3s; cursor: pointer; display: inline-block; border: none;
        }
        .btn-primary { background: linear-gradient(90deg, var(--primary), var(--secondary)); color: black; box-shadow: 0 0 20px rgba(0, 242, 254, 0.4); }
        .btn-primary:hover { transform: scale(1.05); box-shadow: 0 0 30px rgba(0, 242, 254, 0.6); }

        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px; padding: 100px 10%; }

        .card {
            background: var(--glass); border: 1px solid var(--glass-border); padding: 30px; border-radius: 20px;
            backdrop-filter: blur(5px); transition: 0.3s;
        }
        .card:hover { transform: translateY(-10px); border-color: var(--primary); box-shadow: 0 0 20px rgba(0,242,254,0.1); }
        .card h3 { font-family: 'Orbitron', sans-serif; margin-bottom: 15px; color: var(--primary); }
        .card .price { font-size: 2rem; font-weight: 700; margin: 20px 0; }
        .card ul { list-style: none; color: #888; margin-bottom: 30px; }
        .card ul li { margin-bottom: 10px; }
        .card ul li i { color: var(--primary); margin-right: 10px; }

        .pwa-install {
            background: var(--primary); color: black; padding: 10px 20px; position: fixed; bottom: 20px; right: 20px;
            border-radius: 10px; display: none; cursor: pointer; font-weight: 700; align-items: center; gap: 10px; z-index: 1000;
        }

        footer { text-align: center; padding: 50px; color: #555; border-top: 1px solid var(--glass-border); }
    </style>
</head>
<body>
    <div class="blob"></div>
    
    <nav>
        <div class="logo"><i class="fas fa-microchip"></i> ITcloud</div>
        <div class="menu">
            <a href="{{ route('login') }}" class="btn" style="color:var(--primary)">Login</a>
        </div>
    </nav>

    <header class="hero">
        <h1>Biznesingiz Uchun <span>Cyber-Glass</span> CRM</h1>
        <p class="lead">AI asosidagi to'liq avtomatlashgan, FaceID xavfsizlik tizimi va Ovozli boshqaruvga ega yangi avlod ekotizimi.</p>
        <div class="actions">
            <a href="#plans" class="btn btn-primary">Hozir Boshlash</a>
        </div>
    </header>

    <section id="plans" class="grid">
        @foreach($templates as $tpl)
        <div class="card">
            <h3>{{ $tpl->name }}</h3>
            <div class="price">{{ number_format($tpl->price, 0, ',', ' ') }} <small>UZS</small></div>
            <ul>
                @if(is_array($tpl->includes))
                    @foreach($tpl->includes as $inc)
                    <li><i class="fas fa-check"></i> {{ $inc }}</li>
                    @endforeach
                @else
                    <li><i class="fas fa-check"></i> To'liq integratsiya</li>
                @endif
            </ul>
            <a href="#" class="btn btn-primary" style="width:100%; text-align:center">Buyurtma Berish</a>
        </div>
        @endforeach
    </section>

    <div class="pwa-install" id="installBtn">
        <i class="fas fa-download"></i> Ilovani O'rnatish
    </div>

    <footer>
        &copy; 2026 ITcloud Cyber-Glass. All Rights Reserved.
    </footer>

    <script>
        // PWA Install Logic
        let deferredPrompt;
        const installBtn = document.getElementById('installBtn');

        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            deferredPrompt = e;
            installBtn.style.display = 'flex';
        });

        installBtn.addEventListener('click', (e) => {
            installBtn.style.display = 'none';
            deferredPrompt.prompt();
            deferredPrompt.userChoice.then((choiceResult) => {
                if (choiceResult.outcome === 'accepted') {
                    console.log('User accepted the PWA prompt');
                }
                deferredPrompt = null;
            });
        });

        // Service Worker registration
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
                navigator.serviceWorker.register('/sw.js').then(function(registration) {
                    console.log('SW registration successful');
                }, function(err) {
                    console.log('SW registration failed: ', err);
                });
            });
        }
    </script>
</body>
</html>
