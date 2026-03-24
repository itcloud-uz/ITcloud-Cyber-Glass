<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ITcloud | Xavfsizlik Jurnali</title>
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

        body { background-color: var(--bg-dark); color: var(--text-main); height: 100vh; display: flex; overflow: hidden; position: relative; }

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

        .brand-logo { font-size: 24px; font-weight: 800; margin-bottom: 50px; text-align: center; }
        .brand-logo span { color: var(--neon-cyan); text-shadow: 0 0 15px rgba(0,255,204,0.5); }

        .nav-item {
            padding: 16px 25px; border-radius: 20px; margin-bottom: 12px; cursor: pointer;
            display: flex; align-items: center; gap: 15px; font-size: 15px; font-weight: 600; color: var(--text-muted);
            transition: 0.3s; text-decoration: none;
        }
        .nav-item:hover { background: rgba(255,255,255,0.05); color: white; }
        .nav-item.active { background: rgba(0, 255, 204, 0.1); border: 1px solid rgba(0, 255, 204, 0.2); color: var(--neon-cyan); }

        .main-content { flex: 1; padding: 40px; overflow-y: auto; z-index: 10; }

        .header-title { margin-bottom: 40px; position: relative; }
        .header-title h1 { font-size: 32px; font-weight: 800; margin-bottom: 10px; }
        .header-title p { color: var(--text-muted); font-size: 16px; }

        .log-table-container {
            background: var(--glass-bg);
            backdrop-filter: var(--glass-blur);
            border: 1px solid var(--glass-border);
            border-radius: 30px;
            padding: 40px;
            overflow: hidden;
        }

        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; padding: 15px; font-size: 14px; text-transform: uppercase; letter-spacing: 1px; color: var(--text-muted); border-bottom: 1px solid var(--glass-border); }
        td { padding: 18px 15px; font-size: 15px; border-bottom: 1px solid rgba(255,255,255,0.03); }
        
        .action-cell { font-weight: 700; color: var(--neon-cyan); }
        .ip-cell { font-family: monospace; color: var(--text-muted); }
        .time-cell { font-size: 13px; color: var(--text-muted); }

        .profile-card {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid var(--glass-border);
            border-radius: 25px;
            padding: 25px;
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 40px;
        }
        .avatar { width: 60px; height: 60px; background: linear-gradient(45deg, var(--neon-purple), var(--neon-cyan)); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 24px; font-weight: 800; }
        .profile-info h3 { margin-bottom: 5px; font-size: 20px; }
        .profile-info p { font-size: 14px; color: var(--text-muted); }
    </style>
</head>
<body>

    <div class="ambient-blob blob-1"></div>
    <div class="ambient-blob blob-2"></div>

    <aside class="sidebar">
        <div class="brand-logo">IT<span>cloud</span> Hub</div>
        
        <a href="{{ route('client.dashboard') }}" class="nav-item">
            <i class="fa-solid fa-rocket"></i> Dashboard
        </a>
        <a href="#" class="nav-item">
            <i class="fa-solid fa-cart-shopping"></i> ITcloud Do'kon
        </a>
        <a href="{{ route('client.security') }}" class="nav-item active">
            <i class="fa-solid fa-shield-halved"></i> Xavfsizlik va Profil
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
        <header class="header-title">
            <h1>Xavfsizlik va Audit Jurnali</h1>
            <p>Sizning hisobingiz xavfsizligini ta'minlash uchun har bitta harakat nazorat qilinadi.</p>
        </header>

        <section class="profile-card">
            <div class="avatar">{{ substr($user->name, 0, 1) }}</div>
            <div class="profile-info">
                <h3>{{ $user->name }}</h3>
                <p>{{ $user->email }} | Oxirgi kirish IP: {{ $user->last_login_ip ?? 'Nomaʼlum' }}</p>
            </div>
        </section>

        <section class="log-table-container">
            <table>
                <thead>
                    <tr>
                        <th>{{ __('Action') }}</th>
                        <th>{{ __('IP Address') }}</th>
                        <th>{{ __('Device (User Agent)') }}</th>
                        <th>{{ __('Time') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                    <tr>
                        <td class="action-cell">{{ $log->action }}</td>
                        <td class="ip-cell">{{ $log->ip_address }}</td>
                        <td style="font-size: 12px; opacity: 0.6; max-width: 250px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $log->user_agent }}</td>
                        <td class="time-cell">{{ $log->created_at->format('d.m.Y H:i') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" style="text-align: center; padding: 40px; opacity: 0.5;">{{ __('No security logs found yet.') }}</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </section>
    </main>

</body>
</html>
