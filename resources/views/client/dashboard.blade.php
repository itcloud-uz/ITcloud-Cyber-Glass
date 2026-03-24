<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ITcloud | Mijozlar Boshqaruv Paneli</title>
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
            overflow: hidden;
            position: relative;
        }

        .ambient-blob { position: absolute; border-radius: 50%; filter: blur(120px); z-index: -1; opacity: 0.3; }
        .blob-1 { width: 600px; height: 600px; background: var(--neon-purple); top: -200px; left: -100px; }
        .blob-2 { width: 500px; height: 500px; background: var(--neon-cyan); bottom: -100px; right: -50px; }

        .sidebar {
            width: 300px;
            margin: 20px;
            background: var(--glass-bg);
            backdrop-filter: var(--glass-blur);
            border: 1px solid var(--glass-border);
            border-radius: 40px;
            padding: 40px 20px;
            display: flex;
            flex-direction: column;
            z-index: 10;
        }

        .brand-logo {
            font-size: 24px;
            font-weight: 800;
            margin-bottom: 50px;
            text-align: center;
        }
        .brand-logo span { color: var(--neon-cyan); text-shadow: 0 0 15px rgba(0,255,204,0.5); }

        .nav-item {
            padding: 16px 25px;
            border-radius: 20px;
            margin-bottom: 12px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 15px;
            font-size: 15px;
            font-weight: 600;
            color: var(--text-muted);
            transition: 0.3s;
            text-decoration: none;
        }
        .nav-item:hover { background: rgba(255,255,255,0.05); color: white; }
        .nav-item.active { background: rgba(0, 255, 204, 0.1); border: 1px solid rgba(0, 255, 204, 0.2); color: var(--neon-cyan); }

        .main-content {
            flex: 1;
            padding: 40px;
            overflow-y: auto;
            z-index: 10;
        }

        .welcome-header { margin-bottom: 40px; }
        .welcome-header h1 { font-size: 32px; font-weight: 800; margin-bottom: 10px; }
        .welcome-header p { color: var(--text-muted); font-size: 16px; }

        .grid-header { font-size: 18px; font-weight: 700; margin-bottom: 25px; display: flex; align-items: center; gap: 12px; }

        .projects-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 25px;
            margin-bottom: 50px;
        }

        .project-card {
            background: var(--glass-bg);
            backdrop-filter: var(--glass-blur);
            border: 1px solid var(--glass-border);
            border-radius: 30px;
            padding: 30px;
            position: relative;
            transition: 0.4s;
            overflow: hidden;
        }
        .project-card:hover { border-color: var(--neon-cyan); transform: translateY(-5px); box-shadow: 0 15px 40px rgba(0,0,0,0.4); }
        .project-card::before { content: ''; position: absolute; top: 0; left: 0; width: 4px; height: 100%; background: var(--neon-cyan); }

        .project-name { font-size: 20px; font-weight: 800; margin-bottom: 8px; }
        .project-domain { color: var(--text-muted); font-size: 14px; margin-bottom: 20px; }
        
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            background: rgba(0, 255, 204, 0.1);
            border: 1px solid var(--neon-cyan);
            color: var(--neon-cyan);
            border-radius: 12px;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
        }

        .btn-sso {
            width: 100%;
            margin-top: 25px;
            padding: 12px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--glass-border);
            border-radius: 14px;
            color: white;
            font-weight: 700;
            font-size: 14px;
            cursor: pointer;
            transition: 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            text-decoration: none;
        }
        .btn-sso:hover { background: var(--neon-cyan); color: #000; border-color: var(--neon-cyan); box-shadow: 0 0 20px rgba(0, 255, 204, 0.3); }

        .market-card {
            background: linear-gradient(135deg, rgba(176, 38, 255, 0.1), rgba(0, 255, 204, 0.1));
            border-radius: 30px;
            padding: 30px;
            border: 1px solid var(--glass-border);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .market-info h3 { font-size: 22px; font-weight: 800; margin-bottom: 10px; }
        .market-info p { color: var(--text-muted); font-size: 14px; }
    </style>
</head>
<body>

    <div class="ambient-blob blob-1"></div>
    <div class="ambient-blob blob-2"></div>

    <aside class="sidebar">
        <div class="brand-logo">IT<span>cloud</span> Hub</div>
        
            <div class="grid-header">
                <i class="fa-solid fa-rocket" style="color: var(--neon-cyan);"></i> {{ __('Dashboard') }}
            </div>
            <a href="{{ route('client.dashboard') }}" class="nav-item active">
                <i class="fa-solid fa-rocket"></i> {{ __('Dashboard') }}
            </a>
            <a href="#" class="nav-item">
                <i class="fa-solid fa-cart-shopping"></i> {{ __('ITcloud Shop') }}
            </a>
            <a href="{{ route('client.security') }}" class="nav-item">
                <i class="fa-solid fa-shield-halved"></i> {{ __('Security & Profile') }}
            </a>

            <div style="flex: 1;"></div>

            <form action="{{ route('client.logout') }}" method="POST">
                @csrf
                <button type="submit" class="nav-item" style="width: 100%; background: transparent; border: none; font-size: 15px; color: var(--neon-pink);">
                    <i class="fa-solid fa-power-off"></i> {{ __('Logout') }}
                </button>
            </form>
    </aside>

    <main class="main-content">
        <header class="welcome-header">
            <h1>Xush kelibsiz, {{ Auth::user()->name }}!</h1>
            <p>ITcloud platformasidagi loyihalaringiz va shaxsiy kabinetingiz nazorat markazi.</p>
        </header>

        <section>
            <div class="grid-header">
                <i class="fa-solid fa-cubes-stacked" style="color: var(--neon-cyan);"></i> Mening Loyihalarim
            </div>

            <div class="projects-grid">
                @forelse($projects as $p)
                <div class="project-card">
                    <div class="project-name">{{ $p->company_name }}</div>
                    <div class="project-domain">{{ $p->domain }}</div>
                    <div class="status-badge">
                        <i class="fa-solid fa-circle-check"></i> Faol
                    </div>
                    <a href="{{ route('client.sso', $p->id) }}" class="btn-sso">
                        Tizimga kirish (SSO) <i class="fa-solid fa-arrow-right-to-bracket"></i>
                    </a>
                </div>
                @empty
                <div class="project-card" style="grid-column: 1/-1; text-align: center; opacity: 0.6;">
                    {{ __('No active projects found.') }}
                </div>
                @endforelse
            </div>
        </section>

        <section>
            <div class="grid-header">
                <i class="fa-solid fa-store" style="color: var(--neon-purple);"></i> ITcloud Do'kon
            </div>
            <div class="market-card">
                <div class="market-info">
                    <h3>Yangi loyiha boshlaysizmi?</h3>
                    <p>Bizning do'konimizdan tayyor shablonlarni tanlang va 10 daqiqada o'z biznesingizni onlayn qiling.</p>
                </div>
                <button class="btn-sso" style="width: auto; padding: 15px 40px; background: var(--neon-purple); border: none;">
                    Do'konni ko'rish
                </button>
            </div>
        </section>
    </main>

</body>
</html>
