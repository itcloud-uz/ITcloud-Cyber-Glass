<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>ITcloud | Obsidian OS v1</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        :root {
            /* Cyberpunk ranglar */
            --bg-dark: #05050a;
            --neon-cyan: #00ffcc;
            --neon-purple: #b026ff;
            --neon-pink: #ff007f;
            
            /* Glassmorphism xususiyatlari */
            --glass-bg: rgba(20, 20, 35, 0.4);
            --glass-border: rgba(255, 255, 255, 0.08);
            --glass-blur: blur(24px);
            
            /* Matnlar */
            --text-main: #ffffff;
            --text-muted: #8b9bb4;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Outfit', sans-serif; }

        body {
            background-color: var(--bg-dark);
            color: var(--text-main);
            height: 100vh;
            overflow: hidden;
            display: flex;
            position: relative;
        }

        /* Ambient Orqa fon (Cyberpunk yorug'liklari) */
        .ambient-blob { position: absolute; border-radius: 50%; filter: blur(120px); z-index: -1; opacity: 0.4; }
        .blob-1 { width: 500px; height: 500px; background: var(--neon-purple); top: -100px; left: -100px; animation: float 10s infinite alternate; }
        .blob-2 { width: 400px; height: 400px; background: var(--neon-cyan); bottom: -100px; right: -50px; animation: float 15s infinite alternate-reverse; }

        @keyframes float { 0% { transform: translate(0,0); } 100% { transform: translate(50px, 50px); } }

        /* Dynamic Island (iOS uslubi) */
        .dynamic-island {
            position: fixed;
            top: 15px;
            left: 50%;
            transform: translateX(-50%);
            background: #000000;
            border: 1px solid var(--glass-border);
            border-radius: 40px;
            padding: 10px 25px;
            display: flex;
            align-items: center;
            gap: 15px;
            z-index: 1000;
            box-shadow: 0 10px 30px rgba(0,0,0,0.8);
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
            overflow: hidden;
            min-width: 200px;
            justify-content: center;
        }
        .dynamic-island.active { min-width: 400px; padding: 15px 25px; border-color: var(--neon-cyan); box-shadow: 0 0 20px rgba(0, 255, 204, 0.2); }
        .island-content { font-size: 14px; font-weight: 600; color: var(--text-main); white-space: nowrap; transition: 0.3s; }
        .island-icon { color: var(--neon-cyan); display: none; }
        .dynamic-island.active .island-icon { display: block; animation: pulse 1.5s infinite; }

        @keyframes pulse { 0% { opacity: 0.5; text-shadow: 0 0 0 var(--neon-cyan); } 50% { opacity: 1; text-shadow: 0 0 15px var(--neon-cyan); } 100% { opacity: 0.5; text-shadow: 0 0 0 var(--neon-cyan); } }

        /* Glass Panel umumiy klassi */
        .glass-panel {
            background: var(--glass-bg);
            backdrop-filter: var(--glass-blur);
            -webkit-backdrop-filter: var(--glass-blur);
            border: 1px solid var(--glass-border);
            border-radius: 30px;
        }

        /* Chap Menyu (Sidebar) */
        .sidebar { 
            width: 280px; 
            margin: 20px; 
            padding: 30px 20px; 
            display: flex; 
            flex-direction: column; 
            z-index: 10;
            height: calc(100vh - 40px);
            overflow-y: auto;
            position: sticky;
            top: 20px;
        }
        .brand { margin-bottom: 40px; text-align: center; }
        .logo-animated {
            width: 140px;
            height: auto;
            filter: drop-shadow(0 0 10px rgba(0, 255, 204, 0.4));
            animation: logoFloat 5s ease-in-out infinite;
            transition: 0.3s;
        }
        .logo-animated:hover { 
            filter: drop-shadow(0 0 25px var(--neon-cyan)); 
            transform: scale(1.1) rotate(2deg); 
        }
        @keyframes logoFloat {
            0%, 100% { transform: translateY(0) rotate(-1deg); }
            50% { transform: translateY(-8px) rotate(1deg); }
        }

        .nav-item {
            padding: 15px 20px; border-radius: 18px; margin-bottom: 10px; cursor: pointer;
            display: flex; align-items: center; gap: 15px; font-size: 15px; font-weight: 600; color: var(--text-muted);
            transition: all 0.3s ease; border: 1px solid transparent;
        }
        .nav-item:hover { background: rgba(255,255,255,0.05); color: var(--text-main); }
        .nav-item.active { background: rgba(0, 255, 204, 0.1); border-color: rgba(0, 255, 204, 0.3); color: var(--neon-cyan); box-shadow: inset 0 0 20px rgba(0, 255, 204, 0.05); }

        /* Asosiy oyna */
        .main-container { flex: 1; padding: 80px 30px 20px 10px; display: flex; flex-direction: column; overflow-y: auto; overflow-x: hidden; z-index: 10; }
        
        .view-section { display: none; animation: fadeIn 0.4s ease forwards; }
        .view-section.active { display: block; }

        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

        /* Dashboard Vidjetlari */
        .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 30px; }
        .stat-card { padding: 25px; border-radius: 24px; position: relative; overflow: hidden; }
        .stat-card::before { content: ''; position: absolute; top: 0; left: 0; width: 4px; height: 100%; background: var(--neon-cyan); box-shadow: 0 0 15px var(--neon-cyan); }
        .stat-title { font-size: 13px; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 10px; }
        .stat-value { font-size: 32px; font-weight: 800; }
        
        /* 2-Ustunli layout (Chart va AI Feed) */
        .content-row { display: grid; grid-template-columns: 2fr 1fr; gap: 20px; }
        .panel-title { font-size: 18px; font-weight: 600; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }
        
        .ai-feed { height: 400px; overflow-y: auto; padding-right: 10px; }
        .feed-item { padding: 15px; background: rgba(0,0,0,0.3); border-radius: 16px; margin-bottom: 12px; border-left: 2px solid var(--neon-purple); font-size: 14px; color: #cbd5e1; }
        .feed-time { font-size: 11px; color: var(--text-muted); margin-bottom: 5px; }

        /* Mijozlar (Tenants) Jadvali */
        .tenant-row { display: grid; grid-template-columns: 2fr 1fr 1fr auto; align-items: center; padding: 20px; background: rgba(0,0,0,0.2); border-radius: 20px; margin-bottom: 15px; border: 1px solid var(--glass-border); transition: 0.3s; }
        .tenant-row:hover { background: rgba(255,255,255,0.03); border-color: var(--neon-cyan); }
        .status-badge { padding: 5px 12px; border-radius: 20px; font-size: 12px; font-weight: 800; display: inline-flex; align-items: center; gap: 6px; }
        .status-active { color: var(--neon-cyan); background: rgba(0, 255, 204, 0.1); border: 1px solid var(--neon-cyan); box-shadow: 0 0 10px rgba(0, 255, 204, 0.2); }
        .status-blocked { color: var(--neon-pink); background: rgba(255, 0, 127, 0.1); border: 1px solid var(--neon-pink); }
        
        .btn-ios { padding: 10px 20px; border-radius: 14px; border: none; cursor: pointer; font-weight: 600; font-size: 13px; transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1); background: rgba(255,255,255,0.1); color: white; }
        .btn-ios:hover { background: rgba(255,255,255,0.2); transform: scale(1.05); }
        .btn-neon { background: transparent; color: var(--neon-cyan); border: 1px solid var(--neon-cyan); }
        .btn-neon:hover { background: var(--neon-cyan); color: #000; box-shadow: 0 0 15px var(--neon-cyan); }

        /* iOS Toggle Switch */
        .ios-toggle { position: relative; width: 50px; height: 28px; background: rgba(255,255,255,0.2); border-radius: 30px; cursor: pointer; transition: 0.3s; }
        .ios-toggle::after { content: ''; position: absolute; top: 2px; left: 2px; width: 24px; height: 24px; background: white; border-radius: 50%; transition: 0.3s; box-shadow: 0 2px 5px rgba(0,0,0,0.5); }
        .ios-toggle.on { background: var(--neon-cyan); box-shadow: 0 0 15px rgba(0,255,204,0.4); }
        .ios-toggle.on::after { left: 24px; }

        /* Skrollbarni yashirish/chiroyli qilish */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.2); border-radius: 10px; }

        /* Mobile Layout */
        @media (max-width: 900px) {
            body { flex-direction: column; overflow: auto; }
            .sidebar { width: auto; margin: 10px; padding: 15px; flex-direction: row; overflow-x: auto; margin-top: 80px; }
            .brand { display: none; }
            .nav-item { margin-bottom: 0; margin-right: 10px; white-space: nowrap; }
            .main-container { padding: 10px; overflow: visible; }
            .dynamic-island { top: 10px; min-width: 150px; padding: 10px 15px; }
            .dynamic-island.active { min-width: 300px; }
            .stats-grid { grid-template-columns: repeat(2, 1fr); gap: 15px; }
            .content-row { grid-template-columns: 1fr; }
            .tenant-row { grid-template-columns: 1fr; gap: 15px; text-align: center; }
            .tenant-row > div { display: flex; flex-direction: column; align-items: center; }
            .tenant-row .btn-ios { width: 100%; margin: 5px 0; }
        }

        @media (max-width: 500px) {
            .stats-grid { grid-template-columns: 1fr; }
            .dynamic-island { width: 90%; }
        }

        /* Lang Switcher Premium */
        .lang-btn {
            text-decoration: none;
            font-size: 12px;
            font-weight: 800;
            color: var(--text-muted);
            width: 35px;
            height: 35px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            background: rgba(255,255,255,0.03);
            border: 1px solid var(--glass-border);
            transition: all 0.3s ease;
        }
        .lang-btn:hover { background: rgba(255,255,255,0.1); color: white; transform: translateY(-2px); }
        .lang-btn.active { background: var(--neon-cyan); color: #000; border-color: var(--neon-cyan); box-shadow: 0 0 15px rgba(0,255,204,0.3); }
    </style>
    <style>
        /* Custom Modal Styles */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.8);
            backdrop-filter: blur(10px);
            z-index: 2000;
            display: none;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        .modal-overlay.active {
            display: flex;
            opacity: 1;
        }
        .glass-modal {
            width: 500px;
            padding: 40px;
            background: rgba(20, 20, 25, 0.7);
            border: 1px solid var(--glass-border);
            box-shadow: 0 25px 50px rgba(0,0,0,0.5);
            border-radius: 20px;
            position: relative;
            transform: translateY(20px);
            transition: transform 0.3s ease;
        }
        .modal-overlay.active .glass-modal {
            transform: translateY(0);
        }
        .modal-title {
            font-size: 24px;
            color: var(--neon-cyan);
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: var(--text-muted);
            font-size: 14px;
        }
        .form-control {
            width: 100%;
            padding: 12px 15px;
            background: rgba(0,0,0,0.4);
            border: 1px solid var(--glass-border);
            border-radius: 12px;
            color: white;
            font-size: 15px;
            outline: none;
            transition: border-color 0.3s;
        }
        .form-control:focus {
            border-color: var(--neon-cyan);
            box-shadow: 0 0 10px rgba(0, 255, 242, 0.1);
        }
        .modal-actions {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }
        .service-link-badge {
            background: rgba(0, 255, 242, 0.1);
            color: var(--neon-cyan);
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 11px;
            border: 1px solid rgba(0, 255, 242, 0.2);
        }
        .iframe-container {
            width: 100%;
            height: 500px;
            border-radius: 15px;
            overflow: hidden;
            border: 1px solid var(--glass-border);
            margin-top: 20px;
            background: #fff;
        }
        .badge {
            padding: 5px 10px;
            border-radius: 8px;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
        }
        /* Gallery Modal Redesign */
        .gallery-modal-content {
            width: 90%;
            max-width: 600px;
            max-height: 80vh;
            background: rgba(15, 15, 20, 0.9);
            backdrop-filter: blur(25px);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            padding: 30px;
            display: flex;
            flex-direction: column;
            box-shadow: 0 25px 50px rgba(0,0,0,0.5);
        }
        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
            gap: 15px;
            margin-top: 20px;
            overflow-y: auto;
            padding-right: 10px;
        }
        .gallery-item {
            position: relative;
            aspect-ratio: 1;
            background: rgba(255,255,255,0.05);
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid var(--glass-border);
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        .gallery-item:hover {
            transform: translateY(-5px);
            border-color: var(--neon-cyan);
            box-shadow: 0 10px 20px rgba(0, 255, 242, 0.2);
        }
        .gallery-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .gallery-item .overlay-down {
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.4);
            display: flex; align-items: center; justify-content: center;
            opacity: 0; transition: 0.3s;
            font-size: 20px; color: white;
        }
        .gallery-item:hover .overlay-down { opacity: 1; }
        
        #gallerySidebar { display: none; } /* Remove old sidebar */
    </style>
</head>
<body>

    <div class="ambient-blob blob-1"></div>
    <div class="ambient-blob blob-2"></div>

    <div class="dynamic-island" id="dynamicIsland" onclick="simulateAIAction()">
        <i class="fa-solid fa-sparkles island-icon"></i>
        <span class="island-content" id="islandText">Obsidian OS v1</span>
    </div>

    <nav class="sidebar glass-panel">
        <div class="brand">
            <div class="logo-animated" style="display: inline-block; font-size: 26px; font-weight: 800; letter-spacing: 1px;">
                IT<span style="color: var(--neon-cyan); text-shadow: 0 0 15px rgba(0,255,204,0.5);">cloud</span>
            </div>
        </div>
        
        <div class="nav-item active" onclick="switchTab('dashboard')">
            <i class="fa-solid fa-border-all"></i> {{ __('Dashboard') }}
        </div>
        <div class="nav-item" onclick="switchTab('finance')">
            <i class="fa-solid fa-file-invoice-dollar"></i> {{ __('Pricing') }}
        </div>
        <div class="nav-item" onclick="switchTab('tenants')">
            <i class="fa-solid fa-users"></i> {{ __('Clients') }}
        </div>
        <div class="nav-item" onclick="switchTab('employees')">
            <i class="fa-solid fa-user-shield"></i> {{ __('Employees') }}
        </div>
        <div class="nav-item" onclick="switchTab('settings')">
            <i class="fa-solid fa-gears"></i> {{ __('Settings') }}
        </div>
        <div class="nav-item" onclick="switchTab('ai-hub')">
            <i class="fa-solid fa-brain"></i> {{ __('AI Agents') }}
        </div>
        <div class="nav-item" onclick="switchTab('system-health')">
            <i class="fa-solid fa-server"></i> {{ __('Server Health') }}
        </div>
        <div class="nav-item" onclick="switchTab('security-logs')">
            <i class="fa-solid fa-shield-halved"></i> {{ __('Security Logs') }}
        </div>
        <div class="nav-item" onclick="switchTab('templates')">
            <i class="fa-solid fa-layer-group"></i> {{ __('Templates') }}
        </div>
        <div class="nav-item" onclick="switchTab('ai-developer')">
            <i class="fa-solid fa-code"></i> {{ __('Developer Portal') }}
        </div>
        <div class="nav-item" onclick="switchTab('bot-manager')">
            <i class="fa-solid fa-tower-cell"></i> {{ __('Bot Manager') }}
        </div>
        <div class="nav-item" onclick="switchTab('live-chat')">
            <i class="fa-solid fa-headset"></i> {{ __('Human Handoff') }}
        </div>
        
        <div style="margin-top: auto; padding: 20px 10px; background: rgba(0,0,0,0.3); border-radius: 20px; border: 1px solid var(--glass-border); margin-bottom: 10px;">
            <div style="display: flex; justify-content: space-around; align-items: center; gap: 5px;">
                <a href="{{ route('lang.switch', 'uz') }}" class="lang-btn {{ App::getLocale() == 'uz' ? 'active' : '' }}" title="O'zbek">UZ</a>
                <a href="{{ route('lang.switch', 'tr') }}" class="lang-btn {{ App::getLocale() == 'tr' ? 'active' : '' }}" title="Türkçe">TR</a>
                <a href="{{ route('lang.switch', 'ru') }}" class="lang-btn {{ App::getLocale() == 'ru' ? 'active' : '' }}" title="Русский">RU</a>
                <a href="{{ route('lang.switch', 'en') }}" class="lang-btn {{ App::getLocale() == 'en' ? 'active' : '' }}" title="English">EN</a>
            </div>
        </div>

        <form id="logoutForm" action="{{ route('logout') }}" method="POST" style="margin-top: 20px;">
            @csrf
            <button type="submit" class="nav-item" style="width: 100%; text-align: left; background: transparent; color: var(--neon-pink);">
                <i class="fa-solid fa-power-off"></i> {{ __('Logout') }}
            </button>
        </form>
    </nav>

    <main class="main-container">
        
        <div id="dashboard" class="view-section active">
            <div class="stats-grid">
                <div class="glass-panel stat-card">
                    <div class="stat-title">{{ __('Total Revenue') }}</div>
                    <div class="stat-value" id="stats-total-revenue">0</div>
                </div>
                <div class="glass-panel stat-card" style="--neon-cyan: var(--neon-purple);">
                    <div class="stat-title">{{ __('Active Clients') }}</div>
                    <div class="stat-value" id="stats-active-tenants">{{ $activeTenantsCount ?? 0 }}</div>
                </div>
                <div class="glass-panel stat-card" style="--neon-cyan: var(--neon-pink);">
                    <div class="stat-title">{{ __("Today's Leads") }}</div>
                    <div class="stat-value" id="stats-new-leads">0</div>
                </div>
                <div class="glass-panel stat-card">
                    <div class="stat-title">{{ __('Total Agents') }}</div>
                    <div class="stat-value" id="stats-total-bots">{{ count($telegramBots ?? []) }}</div>
                </div>
            </div>

            <div class="content-row">
                <div class="glass-panel" style="padding: 25px; flex: 1.5;">
                    <div class="panel-title"><i class="fa-solid fa-chart-line" style="color: var(--neon-cyan);"></i> {{ __('Company Growth Dynamics') }}</div>
                    <div style="height: 300px; width: 100%;">
                        <canvas id="dashboardChart"></canvas>
                    </div>
                </div>

                <div class="glass-panel" style="padding: 30px;">
                    <div class="panel-title"><i class="fa-solid fa-microchip" style="color: var(--neon-purple);"></i> {{ __('AI Activity (Live)') }}</div>
                    <div class="ai-feed" id="ai-feed-container">
                        @if(isset($aiLogs) && $aiLogs->count() > 0)
                            @foreach($aiLogs as $log)
                            <div class="feed-item" style="border-left-color: {{ $log->agent_type == 'sales' ? 'var(--neon-cyan)' : ($log->agent_type == 'support' ? 'var(--neon-purple)' : 'var(--neon-pink)') }};">
                                <div class="feed-time">{{ $log->created_at->format('H:i') }} {{ $log->created_at->isToday() ? __('Today') : $log->created_at->format('d M') }}</div>
                                <b>{{ ucfirst($log->agent_type) }} {{ __('AI Agents') }}:</b> {{ $log->action }}. {{ $log->details }}
                            </div>
                            @endforeach
                        @else
                            <div class="feed-item">
                                <div class="feed-time">{{ __('Now') }}</div>
                                <b>{{ __('Status') }}:</b> {{ __('System: No AI movements yet.') }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div id="finance" class="view-section">
            <h2 style="margin-bottom: 25px;">{{ __('Company Finance & Sales Center') }}</h2>
            
            <div class="stats-grid" style="margin-bottom: 30px;">
                <div class="glass-panel stat-card">
                    <div style="font-size: 14px; opacity: 0.7;">{{ __('Total Revenue') }}</div>
                    <div style="font-size: 28px; font-weight: bold; color: var(--neon-cyan);">45,200,000 UZS</div>
                    <div style="font-size: 12px; color: #0f0; margin-top: 5px;">+12%</div>
                </div>
                <div class="glass-panel stat-card">
                    <div style="font-size: 14px; opacity: 0.7;">{{ __('New Leads (From Bot)') }}</div>
                    <div style="font-size: 28px; font-weight: bold; color: var(--neon-purple);">{{ count($leads ?? []) }}</div>
                    <div style="font-size: 12px; opacity: 0.5;">{{ __('Today') }}</div>
                </div>
                <div class="glass-panel stat-card">
                    <div style="font-size: 14px; opacity: 0.7;">{{ __('Active Contracts') }}</div>
                    <div style="font-size: 28px; font-weight: bold; color: var(--neon-pink);">{{ $activeTenantsCount ?? 0 }}</div>
                </div>
            </div>

            <!-- Leads and Sales Section -->
            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px;">
                <!-- Leads Table -->
                <div class="glass-panel" style="padding: 25px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                        <h3><i class="fa-solid fa-bolt" style="color: var(--neon-cyan);"></i> {{ __('New Requests (Leads)') }}</h3>
                    </div>
                    <div style="overflow-x: auto;">
                        <table style="width: 100%; border-collapse: collapse;">
                            <thead>
                                <tr style="text-align: left; border-bottom: 1px solid var(--glass-border);">
                                    <th style="padding: 15px; font-size: 13px; opacity: 0.6;">{{ __('Client') }}</th>
                                    <th style="padding: 15px; font-size: 13px; opacity: 0.6;">{{ __('Telegram / Phone') }}</th>
                                    <th style="padding: 15px; font-size: 13px; opacity: 0.6;">{{ __('Interest') }}</th>
                                    <th style="padding: 15px; font-size: 13px; opacity: 0.6;">{{ __('Status') }}</th>
                                    <th style="padding: 15px; font-size: 13px; opacity: 0.6;">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($leads ?? [] as $lead)
                                <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                                    <td style="padding: 15px;">{{ $lead->customer_name }}</td>
                                    <td style="padding: 15px;">{{ $lead->phone }}</td>
                                    <td style="padding: 15px; font-size: 13px;">{{ $lead->details }}</td>
                                    <td style="padding: 15px;">
                                        <span class="badge" style="background: {{ $lead->status == 'yangi' ? 'rgba(0, 255, 242, 0.1)' : 'rgba(255, 255, 255, 0.1)' }}; color: {{ $lead->status == 'yangi' ? 'var(--neon-cyan)' : 'white' }};">
                                            {{ __($lead->status) }}
                                        </span>
                                    </td>
                                    <td style="padding: 15px;">
                                        <select onchange="updateLeadStatus({{ $lead->id }}, this.value)" style="background: #1a1a1a; border: 1px solid var(--glass-border); color: white; border-radius: 5px; padding: 2px 5px; font-size: 12px;">
                                            <option value="yangi" {{ $lead->status == 'yangi' ? 'selected' : '' }}>{{ __('New') }}</option>
                                            <option value="jarayonda" {{ $lead->status == 'jarayonda' ? 'selected' : '' }}>{{ __('In Progress') }}</option>
                                            <option value="sotildi" {{ $lead->status == 'sotildi' ? 'selected' : '' }}>{{ __('Sold') }}</option>
                                        </select>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Client Files & Contracts -->
                <div class="glass-panel" style="padding: 25px;">
                    <h3><i class="fa-solid fa-file-signature"></i> Shartnomalar</h3>
                    <div style="margin-top: 15px;">
                        @foreach($tenants as $tenant)
                        <div style="background: rgba(255,255,255,0.03); border: 1px solid var(--glass-border); border-radius: 12px; padding: 15px; margin-bottom: 10px;">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <div style="font-weight: bold; font-size: 14px;">{{ $tenant->company_name }}</div>
                                @if($tenant->contract_path)
                                    <a href="/storage/{{ $tenant->contract_path }}" target="_blank" style="color: var(--neon-cyan); font-size: 12px;"><i class="fa-solid fa-download"></i> PDF</a>
                                @else
                                    <span style="font-size: 10px; opacity: 0.5;">Shartnoma yo'q</span>
                                @endif
                            </div>
                            <div style="display: flex; gap: 5px; margin-top: 10px;">
                                <button onclick="openUploadModal({{ $tenant->id }}, 'contract')" class="btn-ios" style="padding: 5px 10px; font-size: 11px; flex: 1;">Biriktirish</button>
                                <button onclick="openFileGallery({{ $tenant->id }}, '{{ $tenant->company_name }}', @json($tenant->files ?? []))" class="btn-ios" style="padding: 5px 10px; font-size: 11px; flex: 1;">Fayllar</button>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div id="tenants" class="view-section">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
                <h2>{{ __('Project Network') }}</h2>
                <button class="btn-ios btn-neon" onclick="promptAddTenant()"><i class="fa-solid fa-plus"></i> {{ __('New Client') }}</button>
            </div>
            
            <div id="tenants-container">
                @if(isset($tenants) && $tenants->count() > 0)
                    @foreach($tenants as $tenant)
                    <div class="tenant-row">
                        <div>
                            <h3 style="margin-bottom: 5px;">{{ $tenant->company_name }}</h3>
                            <div style="color: var(--text-muted); font-size: 13px; font-family: monospace;">{{ $tenant->domain }}</div>
                        </div>
                        @if($tenant->status === 'active')
                            <div><span class="status-badge status-active"><i class="fa-solid fa-circle" style="font-size: 8px;"></i> {{ __('Active') }}</span></div>
                            <div style="color: var(--text-muted); font-size: 14px;"><i class="fa-regular fa-clock"></i> {{ now()->diffInDays($tenant->expires_at, false) }} {{ __('days left') }}</div>
                            <div style="display: flex; gap: 10px;">
                                <button class="btn-ios btn-neon" onclick="promptSubscription({{ $tenant->id }})">+ {{ __('Extend') }}</button>
                                <button class="btn-ios" style="color: var(--neon-pink); border: 1px solid var(--neon-pink);" onclick="changeTenantStatus({{ $tenant->id }}, 'blocked')">{{ __('Block') }}</button>
                                <button class="btn-ios" onclick="promptEditTenant({{ $tenant->id }}, '{{ $tenant->company_name }}', '{{ $tenant->domain }}')"><i class="fa-solid fa-pen"></i></button>
                                <button class="btn-ios" style="color: #ff3b30;" onclick="deleteTenant({{ $tenant->id }})"><i class="fa-solid fa-trash"></i></button>
                            </div>
                        @else
                            <div><span class="status-badge status-blocked"><i class="fa-solid fa-lock" style="font-size: 10px;"></i> {{ __('Blocked') }}</span></div>
                            <div style="color: var(--neon-pink); font-size: 14px;"><i class="fa-solid fa-triangle-exclamation"></i> {{ __('No Ownership') }}</div>
                            <div style="display: flex; gap: 10px;">
                                <button class="btn-ios btn-neon" onclick="changeTenantStatus({{ $tenant->id }}, 'active')">{{ __('Open') }}</button>
                                <button class="btn-ios" onclick="promptSubscription({{ $tenant->id }})">+ {{ __('Extend') }}</button>
                                <button class="btn-ios" style="color: #ff3b30;" onclick="deleteTenant({{ $tenant->id }})"><i class="fa-solid fa-trash"></i></button>
                            </div>
                        @endif
                    </div>
                    @endforeach
                @else
                    <div style="text-align: center; padding: 40px; color: var(--text-muted);">{{ __('No projects found yet.') }}</div>
                @endif
            </div>
        </div>
        
        <div id="ai-developer" class="view-section">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
                <h2>Antigravity Pipeline 🛸 <small style="font-size: 0.5em; color: var(--neon-cyan);">Prompt-Driven Factory</small></h2>
                <button class="btn-ios btn-neon" onclick="document.getElementById('ai-project-modal').classList.add('active')"><i class="fa-solid fa-wand-magic-sparkles"></i> {{ __('Build New Project Architecture') }}</button>
            </div>

            <div class="glass-panel" style="padding: 20px;">
                <h3 style="margin-bottom: 15px;"><i class="fa-solid fa-list-check"></i> {{ __('Projects Conveyor') }}</h3>
                <div id="ai-projects-list">
                    <!-- Dinamik ravishda to'ldiriladi -->
                    <div style="text-align:center; padding: 40px; color: var(--text-muted);">
                        {{ __('No projects created via AI yet. Start the Pipeline!') }}
                    </div>
                </div>
            </div>
        </div>

        <div id="employees" class="view-section">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
                <h2>{{ __('Employees and Master Admins') }}</h2>
                <button class="btn-ios btn-neon" onclick="document.getElementById('add-emp-form').style.display='block'"><i class="fa-solid fa-user-plus"></i> {{ __('Add New Employee') }}</button>
            </div>
            
            <div class="glass-panel" id="add-emp-form" style="display:none; margin-bottom: 20px; padding: 30px;">
                <h3 id="empModalHeader" style="margin-bottom: 20px; color: var(--neon-cyan);">{{ __('New Employee Settings') }}</h3>
                <form onsubmit="event.preventDefault(); submitEmployee();" id="empForm">
                    <input type="hidden" id="edit_emp_id">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div>
                            <label style="display:block; margin-bottom:5px; color: var(--text-muted);">{{ __('Full Name') }}</label>
                            <input type="text" id="emp_name" required style="width:100%; padding:10px; border-radius:10px; background:rgba(0,0,0,0.3); border:1px solid var(--glass-border); color:white;">
                        </div>
                        <div>
                            <label style="display:block; margin-bottom:5px; color: var(--text-muted);">Email / Login</label>
                            <input type="email" id="emp_email" required style="width:100%; padding:10px; border-radius:10px; background:rgba(0,0,0,0.3); border:1px solid var(--glass-border); color:white;">
                        </div>
                        <div>
                            <label style="display:block; margin-bottom:5px; color: var(--text-muted);">Parol</label>
                            <input type="password" id="emp_password" required minlength="6" style="width:100%; padding:10px; border-radius:10px; background:rgba(0,0,0,0.3); border:1px solid var(--glass-border); color:white;">
                        </div>
                        <div>
                            <label style="display:block; margin-bottom:5px; color: var(--text-muted);">Pasport Raqami (JSHSHR)</label>
                            <input type="text" id="emp_passport" placeholder="AA1234567" style="width:100%; padding:10px; border-radius:10px; background:rgba(0,0,0,0.3); border:1px solid var(--glass-border); color:white;">
                        </div>
                        <div>
                            <label style="display:block; margin-bottom:5px; color: var(--text-muted);">Roli</label>
                            <select id="emp_role" style="width:100%; padding:10px; border-radius:10px; background:rgba(0,0,0,0.3); border:1px solid var(--glass-border); color:white;">
                                <option value="admin">Admin</option>
                                <option value="master">Master Admin (Root)</option>
                                <option value="operator">Operator / Sotuvchi</option>
                            </select>
                        </div>
                        <div>
                            <label style="display:block; margin-bottom:5px; color: var(--text-muted);">Yuz qiyofasi (Face ID Base64 yoki Rasm)</label>
                            <input type="file" id="emp_face_photo" accept="image/*" style="width:100%; padding:10px; border-radius:10px; background:rgba(0,0,0,0.3); border:1px solid var(--glass-border); color:white;">
                        </div>
                    </div>
                    <div style="margin-top: 20px; display:flex; gap: 10px;">
                        <button type="submit" class="btn-ios btn-neon">{{ __('Save') }}</button>
                        <button type="button" class="btn-ios" onclick="closeEmpForm()">{{ __('Cancel') }}</button>
                    </div>
                </form>
            </div>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px;">
                @foreach($employees ?? [] as $emp)
                <div class="glass-panel" style="padding: 20px; border-left: 4px solid {{ $emp->role === 'master' ? 'var(--neon-purple)' : 'var(--neon-cyan)' }};">
                    <div style="display: flex; align-items: center; gap: 15px;">
                        <div style="width: 50px; height: 50px; border-radius: 50%; background: rgba(255,255,255,0.05); display: flex; align-items: center; justify-content: center; position: relative; overflow: hidden; border: 1px solid rgba(255,255,255,0.1);">
                            @if($emp->face_id_photo)
                                <img src="/storage/{{ $emp->face_id_photo }}" style="width: 100%; height: 100%; object-fit: cover;">
                            @else
                                <i class="fa-solid fa-user-shield" style="font-size: 20px; opacity: 0.3;"></i>
                            @endif
                        </div>
                        <div style="flex: 1;">
                            <h4 style="margin: 0; color: white; font-size: 15px;">{{ $emp->name }}</h4>
                            <div style="font-size: 11px; color: var(--text-muted); margin-top: 3px;">{{ $emp->email }}</div>
                            <div style="margin-top: 8px; display: flex; justify-content: space-between; align-items: center;">
                                <div style="display: flex; gap: 5px;">
                                    <span class="badge" style="background: rgba(255,255,255,0.05); font-size: 9px; padding: 2px 8px;">{{ strtoupper($emp->role) }}</span>
                                    @if($emp->passport_number)
                                    <span class="badge" style="background: rgba(0, 255, 204, 0.1); color: var(--neon-cyan); font-size: 9px; padding: 2px 8px;"><i class="fa-solid fa-id-card"></i> Verified</span>
                                    @endif
                                </div>
                                <div style="display: flex; gap: 10px;">
                                    <button data-emp="{{ json_encode($emp) }}" onclick="openEditEmpModal(JSON.parse(this.dataset.emp))" style="background:none; border:none; color:var(--neon-cyan); cursor:pointer; font-size:12px;"><i class="fa-solid fa-pen"></i></button>
                                    <button onclick="deleteEmployee({{ $emp->id }})" style="background:none; border:none; color:var(--neon-pink); cursor:pointer; font-size:12px;"><i class="fa-solid fa-user-xmark"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            @if(count($employees ?? []) === 0)
            <div class="glass-panel" style="padding: 30px; text-align: center; color: var(--text-muted);">
                {{ __('No employees found in the system. Use the button above to add one.') }}
            </div>
            @endif
        </div>

        <div id="ai-hub" class="view-section">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
                <h2 style="margin: 0;">{{ __('Gemini AI Agents Center') }}</h2>
                <button type="button" class="btn-ios btn-neon" onclick="manualPrBotTrigger()"><i class="fa-solid fa-paper-plane" style="margin-right:8px;"></i> Barcha PR Botlarni Test Qilish</button>
            </div>
            
            <div class="glass-panel" style="padding: 25px; margin-bottom: 25px;">
                <p style="color: var(--text-muted); font-size: 14px;">Ushbu bo'limda siz AI agentlarga biznesingizning turli bo'limlari (Sotuv, Moliya, Texnik yordam, PR) bo'yicha maxsus vazifalar bera olasiz va yangi qirralarni integratsiya qilasiz.</p>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px;">
                @foreach($telegramBots as $bot)
                <div class="glass-panel" style="padding: 25px; border-top: 4px solid {{ $bot->agent_type == 'sales' ? 'var(--neon-cyan)' : ($bot->agent_type == 'finance' ? 'var(--neon-purple)' : 'var(--neon-pink)') }};">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 15px;">
                        <div>
                            <h3 style="color: white; margin-bottom: 5px;">{{ $bot->name }}</h3>
                            <div style="display: flex; gap: 5px;">
                                <span class="badge" style="background: rgba(255,255,255,0.05); font-size: 9px;">{{ strtoupper($bot->agent_type) }}</span>
                                @if($bot->token)
                                    <span class="badge" style="background: rgba(0, 136, 204, 0.2); color: #0088cc; font-size: 9px;"><i class="fa-brands fa-telegram"></i> TG BOT</span>
                                @else
                                    <span class="badge" style="background: rgba(0, 255, 242, 0.1); color: var(--neon-cyan); font-size: 9px;"><i class="fa-solid fa-brain"></i> INTERNAL AI</span>
                                @endif
                            </div>
                        </div>
                        <div class="status-badge {{ $bot->is_active ? 'status-active' : 'status-blocked' }}" style="font-size: 10px;">{{ $bot->is_active ? 'ONLINE' : 'OFFLINE' }}</div>
                    </div>
                    
                    <div style="margin-bottom: 15px; font-size: 13px; color: rgba(255,255,255,0.7); min-height: 40px; background: rgba(0,0,0,0.2); padding: 10px; border-radius: 8px;">
                        @if($bot->agent_type === 'pr_channel')
                        <span style="opacity: 0.5;">PR Strategiya:</span> {{ Str::limit($bot->custom_prompt ?? 'Standart post.', 60) }}<br/>
                        <span style="opacity: 0.5;"><i class="fa-solid fa-clock"></i> Reja:</span> Har kuni {{ $bot->schedule_time }}
                        @else
                        <span style="opacity: 0.5;">Missiya:</span> {{ $bot->current_task ?? 'Standart boshqaruv rejimi.' }}
                        @endif
                    </div>

                    @if($bot->agent_type === 'pr_channel')
                    <div style="display: grid; grid-template-columns: 1fr; gap: 10px; margin-bottom: 10px;">
                        <button data-bot="{{ json_encode($bot) }}" onclick="openBotModal(JSON.parse(this.dataset.bot))" class="btn-ios" style="background: rgba(0,255,204,0.1); border-color: rgba(0,255,204,0.3); color: var(--neon-cyan);"><i class="fa-solid fa-cog"></i> Sozlamalarni o'zgartirish</button>
                    </div>
                    @else
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 10px;">
                        <button onclick="openTaskModal({{ $bot->id }}, '{{ $bot->name }}', '{{ $bot->current_task }}')" class="btn-ios" style="background: rgba(176,38,255,0.1); border-color: rgba(176,38,255,0.3); color: var(--neon-purple);"><i class="fa-solid fa-list-check"></i> Missiya</button>
                        <button onclick="openAiChat({{ $bot->id }}, '{{ $bot->name }}')" class="btn-ios btn-neon"><i class="fa-solid fa-comments"></i> Chat</button>
                    </div>
                    @endif
                    
                    @if($bot->agent_type !== 'pr_channel')
                    <button onclick="openKnowledgeModal({{ $bot->id }}, '{{ $bot->name }}')" class="btn-ios" style="width: 100%; background: rgba(0,255,204,0.05); border-color: rgba(0,255,204,0.2); color: var(--neon-cyan);"><i class="fa-solid fa-book"></i> Bilimlar Bazasi (RAG)</button>
                    @endif
                </div>
                @endforeach

                <div class="glass-panel" onclick="openBotModal()" style="display: flex; flex-direction: column; align-items: center; justify-content: center; cursor: pointer; border: 2px dashed rgba(255,255,255,0.1); background: rgba(255,255,255,0.02); min-height: 200px;">
                    <i class="fa-solid fa-plus-circle" style="font-size: 30px; opacity: 0.3; margin-bottom: 10px;"></i>
                    <div style="color: var(--text-muted);">{{ __('Add New Agent') }}</div>
                </div>
            </div>

            <div class="glass-panel" style="margin-top: 30px; padding: 25px;">
                <h3 style="margin-bottom: 20px;"><i class="fa-solid fa-eye" style="color: var(--neon-cyan);"></i> Jonli Monitoring (Human-in-the-loop)</h3>
                <div id="active-chats-list" style="display: flex; flex-direction: column; gap: 10px;">
                    <div style="text-align: center; padding: 20px; opacity: 0.5;">Aktiv suhbatlar yuklanmoqda...</div>
                </div>
            </div>
        </div>

        <div id="billing" class="view-section">
            <h2 style="margin-bottom: 25px;">{{ __('Company Finance') }}</h2>
            <div class="glass-panel" style="padding: 50px; text-align: center; color: var(--text-muted);">
                <i class="fa-solid fa-wallet" style="font-size: 40px; margin-bottom: 15px; color: rgba(255,255,255,0.2);"></i>
                <h3>{{ __('Payme and Click Integration') }}</h3>
                <p style="margin-top: 10px;">{{ __('This section displays all payment history via webhook. Clients are automatically activated after payment.') }}</p>
            </div>
            
            <div class="glass-panel" style="padding: 30px; margin-top: 20px;">
                <h3 style="margin-bottom: 20px;">Oxirgi To'lovlar va Invoyslar</h3>
                @foreach($subscriptions ?? [] as $sub)
                <div class="tenant-row" style="margin-bottom:10px;">
                    <div>
                        <b>Invoys #{{ $sub->id }}</b><br>
                        <small style="opacity: 0.5;">{{ $sub->tenant->company_name ?? 'Noma\'lum' }}</small>
                    </div>
                    <div>{{ number_format($sub->amount_paid, 0) }} UZS</div>
                    <div><span class="badge" style="background: rgba(0,255,100,0.1); color: #0f0;">Muvaffaqiyatli</span></div>
                    <div style="font-size: 13px; opacity: 0.7;">{{ $sub->paid_at ? $sub->paid_at->format('d.m.Y') : $sub->created_at->format('d.m.Y') }}</div>
                    <a href="/api/subscriptions/{{ $sub->id }}/invoice" class="btn-ios" style="text-decoration: none; color: var(--neon-cyan); border: 1px solid var(--neon-cyan);"><i class="fa-solid fa-file-pdf"></i> PDF</a>
                </div>
                @endforeach
            </div>
        </div>

        <div id="system-health" class="view-section">
            <h2 style="margin-bottom: 25px;">{{ __('Server Health') }}</h2>
            <div class="stats-grid">
                <div class="glass-panel stat-card" style="--neon-cyan: var(--neon-cyan);">
                    <div class="stat-title">CPU Yuklanishi</div>
                    <div class="stat-value">12% <span style="font-size: 14px; color: var(--text-muted);"><i class="fa-solid fa-arrow-trend-down"></i> Muqobil</span></div>
                </div>
                <div class="glass-panel stat-card" style="--neon-cyan: var(--neon-purple);">
                    <div class="stat-title">RAM Sarfi</div>
                    <div class="stat-value">2.4 / 16 GB</div>
                </div>
                <div class="glass-panel stat-card" style="--neon-cyan: var(--neon-pink);">
                    <div class="stat-title">Bo'sh Joy (Disk)</div>
                    <div class="stat-value">145 GB</div>
                </div>
                <div class="glass-panel stat-card" style="--neon-cyan: var(--neon-cyan);">
                    <div class="stat-title">DB Avto-Zaxira</div>
                    <div class="stat-value" style="font-size: 20px; color: var(--neon-cyan);"><i class="fa-solid fa-cloud-arrow-up"></i> Oxirgi zaxira: 03:00 am</div>
                </div>
            </div>
        </div>

        <div id="security-logs" class="view-section">
            <h2 style="margin-bottom: 25px;">{{ __('Security Logs') }}</h2>
            <div class="glass-panel" style="padding: 20px;">
                @if(isset($securityLogs) && $securityLogs->count() > 0)
                    <table style="width: 100%; border-collapse: collapse; text-align: left;">
                        <tr style="border-bottom: 1px solid var(--glass-border);">
                            <th style="padding: 10px;">ID / IP Manzil</th>
                            <th style="padding: 10px;">Hodisa Tipi</th>
                            <th style="padding: 10px;">Tafsilotlar</th>
                            <th style="padding: 10px;">Vaqt</th>
                        </tr>
                        @foreach($securityLogs as $log)
                        <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                            <td style="padding: 15px; font-family: monospace;">{{ $log->ip_address }}</td>
                            <td style="padding: 15px;">
                                <span class="status-badge {{ $log->event_type == 'FACE_ID_SPOOF' ? 'status-blocked' : 'status-active' }}" style="border-color: var(--neon-pink); color: var(--neon-pink);">
                                    {{ $log->event_type }}
                                </span>
                            </td>
                            <td style="padding: 15px; color: var(--text-muted); font-size: 14px;">{{ $log->details }}</td>
                            <td style="padding: 15px; font-size: 12px; color: var(--text-muted);">{{ $log->created_at->diffForHumans() }}</td>
                        </tr>
                        @endforeach
                    </table>
                @else
                    <div style="text-align: center; padding: 40px; color: var(--text-muted);">Hozircha xavfsizlik tahdidlari yo'q.</div>
                @endif
            </div>
        </div>

        <div id="templates" class="view-section">
            <h2 style="margin-bottom: 25px;">{{ __('Templates Factory (Managed Services)') }}</h2>
            <div class="stats-grid" id="templates-grid">
                @if(isset($templates))
                    @foreach($templates as $template)
                    <div class="glass-panel stat-card" style="padding: 30px; position: relative; overflow: hidden; height: auto;">
                        <div style="position: absolute; top: 0; left: 0; width: 4px; height: 100%; background: var(--neon-cyan);"></div>
                        
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 15px;">
                            <div>
                                <h3 style="color: var(--neon-cyan);">{{ $template->name }}</h3>
                                <div style="font-size: 10px; text-transform: uppercase; opacity: 0.5; margin-top: 4px;">
                                    {{ $template->service_type }} | {{ $template->payment_type }}
                                </div>
                            </div>
                            <div style="display: flex; gap: 5px;">
                                <button onclick='openTemplateModal({{ $template->id }}, @json($template))' class="btn-icon" style="color: var(--neon-cyan); opacity: 0.6;"><i class="fa-solid fa-pen-to-square"></i></button>
                                <button onclick="deleteTemplate({{ $template->id }})" class="btn-icon" style="color: var(--neon-pink); opacity: 0.6;"><i class="fa-solid fa-trash-can"></i></button>
                            </div>
                        </div>

                        <p style="color: var(--text-muted); font-size: 14px; margin-bottom: 20px; min-height: 42px;">{{ $template->description }}</p>
                        
                        <div style="margin-bottom: 15px;">
                            <div style="font-size: 11px; color: var(--text-muted); margin-bottom: 8px;">{{ __("What's included:") }}</div>
                            <div style="display: flex; flex-wrap: wrap; gap: 5px;">
                                @foreach($template->includes ?? [] as $inc)
                                    <span style="font-size: 10px; background: rgba(255,255,255,0.05); padding: 2px 6px; border-radius: 4px; border: 1px solid rgba(255,255,255,0.1);">{{ $inc }}</span>
                                @endforeach
                            </div>
                        </div>

                        <div style="margin-bottom: 20px; display: flex; align-items: center; justify-content: space-between;">
                            <span class="service-link-badge"><i class="fa-solid fa-link"></i> {{ parse_url($template->preview_url, PHP_URL_HOST) }}</span>
                            <span style="font-weight: bold; font-size: 18px; color: var(--neon-pink);">{{ number_format($template->price, 0) }} UZS</span>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 45px; gap: 10px;">
                            <button class="btn-ios btn-neon" onclick="manageTemplate({{ $template->id }}, '{{ $template->preview_url }}')">
                                <i class="fa-solid fa-sliders"></i> {{ __('Manage') }}
                            </button>
                            <a href="{{ $template->preview_url }}" target="_blank" class="btn-ios" style="display: flex; align-items: center; justify-content: center; background: rgba(255,255,255,0.05); border: 1px solid var(--glass-border);">
                                <i class="fa-solid fa-arrow-up-right-from-square"></i>
                            </a>
                        </div>
                    </div>
                    @endforeach
                @endif
                
                <div class="glass-panel stat-card" onclick="openTemplateModal()" style="display: flex; flex-direction: column; align-items: center; justify-content: center; cursor: pointer; border: 2px dashed rgba(0, 255, 242, 0.3); background: rgba(0, 255, 242, 0.02); min-height: 250px;">
                    <div style="width: 60px; height: 60px; border-radius: 50%; background: rgba(0, 255, 242, 0.1); display: flex; align-items: center; justify-content: center; margin-bottom: 20px;">
                        <i class="fa-solid fa-plus-circle" style="font-size: 30px; color: var(--neon-cyan);"></i>
                    </div>
                    <h3 style="color: var(--neon-cyan); opacity: 0.8;">{{ __('New Template / Site') }}</h3>
                    <p style="font-size: 12px; color: var(--text-muted); margin-top: 10px; text-align: center;">{{ __('Connect new CRM or Landing') }}</p>
                </div>
            </div>
        </div>

        <div id="bot-manager" class="view-section">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
                <h2>{{ __('Telegram Bots Control') }}</h2>
                <button class="btn-ios btn-neon" onclick="promptAddBot()"><i class="fa-solid fa-plus"></i> {{ __('Add New Bot') }}</button>
            </div>
            
            <div class="glass-panel" style="padding: 30px; margin-bottom: 30px;">
                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px;" id="bots-list">
                    @if(isset($telegramBots))
                        @foreach($telegramBots as $bot)
                        <div class="glass-panel" style="padding: 20px; border: 1px solid {{ $bot->is_active ? 'var(--neon-cyan)' : 'var(--neon-pink)' }};">
                            <div style="display:flex; justify-content: space-between; align-items: center; margin-bottom:10px;">
                                <h4 style="color: var(--neon-cyan);">{{ $bot->name }}</h4>
                                <i class="fa-{{ $bot->channel_type === 'telegram' ? 'brands fa-telegram' : ($bot->channel_type === 'whatsapp' ? 'brands fa-whatsapp' : ($bot->channel_type === 'instagram' ? 'brands fa-instagram' : 'solid fa-brain')) }}" 
                                   style="color: {{ $bot->channel_type === 'whatsapp' ? '#25D366' : ($bot->channel_type === 'instagram' ? '#E1306C' : 'var(--neon-cyan)') }}; font-size: 18px;"></i>
                            </div>
                            <div style="font-size: 11px; color: var(--text-muted); margin-bottom: 15px; font-family: monospace; overflow: hidden; text-overflow: ellipsis;">
                                {{ $bot->channel_type === 'telegram' ? 'TG: ' . substr($bot->token, 0, 10) . '...' : ($bot->channel_type === 'whatsapp' ? 'WA: ' . $bot->phone_number_id : ($bot->channel_type === 'instagram' ? 'IG: ' . $bot->instagram_account_id : 'Internal AI')) }}
                            </div>
                            @if($bot->channel_type === 'whatsapp' || $bot->channel_type === 'instagram')
                            <div style="background: rgba(0,0,0,0.3); padding: 8px; border-radius: 6px; font-size: 10px; margin-bottom: 10px; border: 1px solid rgba(255,255,255,0.05);">
                                <span style="opacity: 0.5;">Webhook URL:</span><br>
                                <code style="color: var(--neon-cyan);">{{ url("/") }}/webhook/meta/{{ $bot->id }}</code>
                            </div>
                            @endif
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                                <span style="font-size: 12px; color: var(--text-muted);">Vazifasi: <b>{{ strtoupper($bot->agent_type) }}</b></span>
                                <div class="ios-toggle {{ $bot->is_active ? 'on' : '' }}" onclick="toggleBot({{ $bot->id }}, {{ $bot->is_active ? 0 : 1 }})"></div>
                            </div>
                            <div style="display: flex; gap: 5px;">
                                <button class="btn-ios" style="flex:1;" onclick='openBotModal({!! json_encode($bot) !!})'><i class="fa-solid fa-pen"></i> Tahrirlash</button>
                                <button class="btn-ios" style="flex:1; color: var(--neon-pink);" onclick="deleteBot({{ $bot->id }})"><i class="fa-solid fa-trash"></i></button>
                            </div>
                        </div>
                        @endforeach
                    @endif
                </div>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="glass-panel" style="padding: 30px;">
                    <h3 style="margin-bottom: 15px; color: var(--neon-purple);"><i class="fa-solid fa-brain"></i> {{ __('AI Agents Unification') }}</h3>
                    <p style="color: var(--text-muted); font-size: 14px;">Barcha botlarga Gemini AI API biriktirilgan. Yangi bot qo'shganingizda, u avtomatik ravishda tanlangan roli bo'yicha (Sales, Finance, Support) muloqotga kirishadi.</p>
                </div>
                <div class="glass-panel" style="padding: 30px; border-left: 4px solid #25D366;">
                    <h3 style="margin-bottom: 15px; color: #25D366;"><i class="fa-brands fa-whatsapp"></i> Multi-Channel Connect</h3>
                    <div style="display: flex; gap: 10px;">
                        <button onclick="alert('WhatsApp Business API ulanmoqda...')" class="btn-ios" style="color: #25D366; border-color: #25D366; flex: 1;"><i class="fa-brands fa-whatsapp"></i> WhatsApp</button>
                        <button onclick="alert('Instagram Direct ulanmoqda...')" class="btn-ios" style="color: #E1306C; border-color: #E1306C; flex: 1;"><i class="fa-brands fa-instagram"></i> Instagram</button>
                    </div>
                </div>
            </div>
        </div>

        <div id="live-chat" class="view-section">
            <h2 style="margin-bottom: 25px;">{{ __('Live Chat') }}</h2>
            <div class="content-row">
                </div>
            </div>
        </div>

        <div id="settings" class="view-section">
            <h2 style="margin-bottom: 25px;">{{ __('System and Company Settings') }}</h2>
            
            <div style="display: flex; gap: 10px; margin-bottom: 30px;">
                <button class="btn-ios active" onclick="switchSettingsTab(this, 's-general')"><i class="fa-solid fa-gear"></i> {{ __('General') }}</button>
                <button class="btn-ios" onclick="switchSettingsTab(this, 's-social-proof')"><i class="fa-solid fa-star"></i> {{ __('Social Proof') }}</button>
                <button class="btn-ios" onclick="switchSettingsTab(this, 's-calculator')"><i class="fa-solid fa-calculator"></i> {{ __('Calculator') }}</button>
            </div>

            <div id="s-general" class="settings-tab-content" style="display: block;">
                <div class="glass-panel" style="padding: 40px;">
                    <form id="globalSettingsForm">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
                        <div>
                            <h3 style="margin-bottom: 20px; color: var(--neon-cyan);"><i class="fa-solid fa-address-book"></i> {{ __('Contact Information') }}</h3>
                            <div style="margin-bottom: 20px;">
                                <label style="display:block; margin-bottom:10px; color: var(--text-muted); font-size: 13px;">{{ __('Email (Support)') }}</label>
                                <input type="email" name="contact_email" value="{{ $settings['contact_email'] ?? '' }}" class="input-field" style="width:100%; padding:15px; border-radius:15px; background:rgba(0,0,0,0.3); border:1px solid var(--glass-border); color:white;">
                            </div>
                            <div style="margin-bottom: 20px;">
                                <label style="display:block; margin-bottom:10px; color: var(--text-muted); font-size: 13px;">{{ __('Phone Number') }}</label>
                                <input type="text" name="contact_phone" value="{{ $settings['contact_phone'] ?? '' }}" class="input-field" style="width:100%; padding:15px; border-radius:15px; background:rgba(0,0,0,0.3); border:1px solid var(--glass-border); color:white;">
                            </div>
                            <div style="margin-bottom: 20px;">
                                <label style="display:block; margin-bottom:10px; color: var(--text-muted); font-size: 13px;">{{ __('Office Address') }}</label>
                                <textarea name="contact_address" class="input-field" style="width:100%; padding:15px; border-radius:15px; background:rgba(0,0,0,0.3); border:1px solid var(--glass-border); color:white; height: 100px;">{{ $settings['contact_address'] ?? '' }}</textarea>
                            </div>
                        </div>
                        <div>
                            <h3 style="margin-bottom: 20px; color: var(--neon-purple);"><i class="fa-solid fa-share-nodes"></i> {{ __('Social Networks') }}</h3>
                            <div style="margin-bottom: 20px;">
                                <label style="display:block; margin-bottom:10px; color: var(--text-muted); font-size: 13px;">Telegram Kanal / Bot</label>
                                <input type="text" name="social_telegram" value="{{ $settings['social_telegram'] ?? '' }}" class="input-field" style="width:100%; padding:15px; border-radius:15px; background:rgba(0,0,0,0.3); border:1px solid var(--glass-border); color:white;">
                            </div>
                            <div style="margin-bottom: 20px;">
                                <label style="display:block; margin-bottom:10px; color: var(--text-muted); font-size: 13px;">Instagram Link</label>
                                <input type="text" name="social_instagram" value="{{ $settings['social_instagram'] ?? '' }}" class="input-field" style="width:100%; padding:15px; border-radius:15px; background:rgba(0,0,0,0.3); border:1px solid var(--glass-border); color:white;">
                            </div>
                            <div style="margin-bottom: 20px;">
                                <label style="display:block; margin-bottom:10px; color: var(--text-muted); font-size: 13px;">LinkedIn Link</label>
                                <input type="text" name="social_linkedin" value="{{ $settings['social_linkedin'] ?? '' }}" class="input-field" style="width:100%; padding:15px; border-radius:15px; background:rgba(0,0,0,0.3); border:1px solid var(--glass-border); color:white;">
                            </div>
                        </div>
                    </div>
                    <div style="margin-top: 30px; border-top: 1px solid var(--glass-border); padding-top: 30px; text-align: right;">
                        <button type="submit" class="btn-ios btn-neon" style="padding: 15px 40px;"><i class="fa-solid fa-floppy-disk"></i> {{ __('Save Data') }}</button>
                    </div>
                </form>
            </div>
        </div>

        <div id="s-social-proof" class="settings-tab-content" style="display: none;">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 25px;">
                <div class="glass-panel" style="padding: 30px;">
                    <h3 style="margin-bottom: 20px; color: var(--neon-cyan);"><i class="fa-solid fa-users"></i> {{ __('Client Logos') }}</h3>
                    <div id="logo-list" style="margin-bottom: 20px; display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px;">
                        <!-- Logo management logic -->
                        <div style="border: 1px dashed var(--glass-border); padding: 20px; text-align: center; border-radius: 12px; cursor: pointer;">
                            <i class="fa-solid fa-plus"></i><br><small>{{ __('Add Logo') }}</small>
                        </div>
                    </div>
                    <p style="font-size: 11px; color: var(--text-muted);">{{ __('These logos will appear in the "Infinite Carousel" section of the site.') }}</p>
                </div>
                <div class="glass-panel" style="padding: 30px;">
                    <h3 style="margin-bottom: 20px; color: var(--neon-purple);"><i class="fa-solid fa-comment-dots"></i> {{ __('Testimonials (Client Reviews)') }}</h3>
                    <button class="btn-ios" style="width: 100%; margin-bottom: 15px;"><i class="fa-solid fa-plus"></i> {{ __('Add New Review') }}</button>
                    <div style="opacity: 0.5; text-align: center; padding: 40px;">{{ __('No reviews yet.') }}</div>
                </div>
            </div>
        </div>

        <div id="s-calculator" class="settings-tab-content" style="display: none;">
            <div class="glass-panel" style="padding: 30px;">
                <h3 style="margin-bottom: 20px; color: var(--neon-pink);"><i class="fa-solid fa-hand-holding-dollar"></i> {{ __('Services Prices') }}</h3>
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="border-bottom: 1px solid var(--glass-border); color: var(--text-muted); font-size: 12px; text-align: left;">
                            <th style="padding: 10px;">{{ __('Service Name') }}</th>
                            <th style="padding: 10px;">{{ __('Base Price ($)') }}</th>
                            <th style="padding: 10px;">{{ __('Duration (Days)') }}</th>
                            <th style="padding: 10px;">{{ __('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($priceServices as $ps)
                        <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                            <td style="padding: 15px;">
                                <i class="fa-solid {{ $ps->icon }}" style="margin-right: 10px; color: var(--neon-cyan);"></i>
                                {{ $ps->name }}
                            </td>
                            <td style="padding: 15px;"><b>${{ number_format($ps->base_price) }}</b> <small style="opacity: 0.4;">- ${{ number_format($ps->max_price) }}</small></td>
                            <td style="padding: 15px;">{{ $ps->min_days }}+ kun</td>
                            <td style="padding: 15px;">
                                <button class="btn-ios" style="padding: 5px 12px;" onclick="openServiceModal({{ $ps->id }}, '{{ addslashes($ps->name) }}', {{ $ps->base_price }}, {{ $ps->max_price }}, {{ $ps->min_days }})">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>



    </main>

    <!-- UI MODALS -->
    <div id="templateModal" class="modal-overlay" onclick="if(event.target === this) closeTemplateModal()">
        <div class="glass-modal">
            <div class="modal-title">
                <i class="fa-solid fa-cube"></i> <span id="modalHeaderText">Yangi Shablon Sozlamalari</span>
            </div>
            <form id="templateForm">
                <input type="hidden" id="edit_tpl_id">
                <div class="form-group">
                    <label>Shablon / Sayt nomi</label>
                    <input type="text" id="tpl_name" class="form-control" placeholder="Masalan: Delta CRM v2" required>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div class="form-group">
                        <label>Xizmat Turi</label>
                        <select id="tpl_service_type" class="form-control">
                            <option value="software">Dasturiy ta'minot</option>
                            <option value="service" selected>Xizmat ko'rsatish</option>
                            <option value="hybrid">Gibrid</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>To'lov Turi</label>
                        <select id="tpl_payment_type" class="form-control">
                            <option value="one-time">Bir martalik</option>
                            <option value="monthly" selected>Oylik obuna</option>
                            <option value="yearly">Yillik obuna</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label>Tafsilotlar (AI uchun asosiy tavsif)</label>
                    <textarea id="tpl_desc" class="form-control" style="height: 60px;" placeholder="Tizim nimalar qila olishi haqida..." required></textarea>
                </div>
                <div class="form-group">
                    <label>Afzalliklari (Har bir qatorga bittadan)</label>
                    <textarea id="tpl_advantages" class="form-control" style="height: 60px;" placeholder="Tezkorlik, 24/7 yordam..."></textarea>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div class="form-group">
                        <label>Narx ichidagi funksiyalar (vergul bilan)</label>
                        <input type="text" id="tpl_includes" class="form-control" placeholder="Admin panel, Telegram bot...">
                    </div>
                    <div class="form-group">
                        <label>Qo'shimcha xizmatlar (vergul bilan)</label>
                        <input type="text" id="tpl_extras" class="form-control" placeholder="SEO, Dizayn...">
                    </div>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div class="form-group">
                        <label>Sotuv Narxi (UZS)</label>
                        <input type="number" id="tpl_price" class="form-control" placeholder="150000" required>
                    </div>
                    <div class="form-group">
                        <label>Preview URL (Havola)</label>
                        <input type="url" id="tpl_preview" class="form-control" placeholder="https://..." required>
                    </div>
                </div>
                <div class="modal-actions">
                    <button type="submit" class="btn-ios btn-neon" style="flex: 2;">Saqlash va Tasdiqlash</button>
                    <button type="button" class="btn-ios" onclick="closeTemplateModal()" style="flex: 1;">Bekor qilish</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Management Modal -->
    <div id="manageModal" class="modal-overlay" onclick="if(event.target === this) closeManageModal()">
        <div class="glass-modal" style="width: 80%; max-width: 1000px;">
            <div class="modal-title" style="justify-content: space-between;">
                <div style="display: flex; align-items: center; gap: 15px;">
                    <i class="fa-solid fa-gauge-high"></i> <span id="manageTitle">Xizmatni Boshqarish</span>
                </div>
                <button onclick="closeManageModal()" style="background:none; border:none; color:white; cursor:pointer;"><i class="fa-solid fa-times"></i></button>
            </div>
            <p id="manageSubtitle" style="color: var(--text-muted); font-size: 13px;"></p>
            <div class="iframe-container">
                <iframe id="manageIframe" src="" style="width:100%; height:100%; border:none;"></iframe>
            </div>
            <div style="margin-top: 20px; display: flex; justify-content: flex-end; gap: 10px;">
                <button class="btn-ios" onclick="window.open(document.getElementById('manageIframe').src, '_blank')"><i class="fa-solid fa-external-link"></i> Alohida oynada ochish</button>
                <button class="btn-ios btn-neon" onclick="closeManageModal()">Yopish</button>
            </div>
        </div>
    </div>

    <!-- Knowledge Base RAG Modal -->
    <div id="knowledgeModal" class="modal-overlay" onclick="if(event.target === this) closeKnowledgeModal()">
        <div class="glass-modal" style="max-width: 500px;">
            <div class="modal-title">Bilimlar Bazasi: <span id="knowledgeBotName">...</span></div>
            <p style="color: var(--text-muted); font-size: 13px; margin-bottom: 20px;">PDF yoki Word hujjatlarini yuklang. Agent ushbu hujjatlar asosida mijozlarga javob beradi.</p>
            
            <form onsubmit="event.preventDefault(); uploadKnowledge();" id="knowledgeForm">
                <input type="hidden" id="kb_bot_id">
                <div style="margin-bottom: 20px;">
                    <label style="display:block; margin-bottom:10px;">Hujjat (PDF/DOCX/TXT)</label>
                    <input type="file" id="kb_file" required style="width:100%; padding:15px; background: rgba(0,0,0,0.3); border: 1px dashed var(--glass-border); border-radius: 12px; color: white;">
                </div>
                <div style="display:flex; gap:10px;">
                    <button type="button" onclick="closeKnowledgeModal()" class="btn-ios" style="flex:1;">Bekor qilish</button>
                    <button type="submit" class="btn-ios btn-neon" style="flex:2;">Yuklash va O'rgatish</button>
                </div>
            </form>

            <div id="kb-list" style="margin-top: 20px; border-top: 1px solid var(--glass-border); padding-top: 15px;">
                <!-- List of uploaded files will appear here -->
            </div>
        </div>
    </div>

    <!-- AI Agent / Bot Modal -->
    <div id="botModal" class="modal-overlay" onclick="if(event.target === this) closeBotModal()">
        <div class="glass-modal">
            <div class="modal-title" id="botModalHeader">Yangi AI Agent Qo'shish</div>
            <form id="botForm">
                <input type="hidden" id="edit_bot_id">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div class="form-group">
                        <label>Agent Nomi</label>
                        <input type="text" id="bot_name" class="form-control" placeholder="Agent nomi..." required>
                    </div>
                    <div class="form-group">
                        <label>Agent Roli</label>
                        <select id="bot_agent_type" class="form-control" onchange="toggleAgentRoleFields(this.value)">
                            <option value="sales">Sotuv Menejeri</option>
                            <option value="finance">Moliya Nazorati</option>
                            <option value="support">Texnik Yordam</option>
                            <option value="pr_channel">Avto PR (Kanal Boti)</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>Kanal Turi</label>
                    <select id="bot_channel_type" class="form-control" onchange="toggleChannelFields(this.value)">
                        <option value="internal">Internal AI (Dashboard faqat)</option>
                        <option value="telegram">Telegram Bot</option>
                        <option value="whatsapp">WhatsApp Business (Meta)</option>
                        <option value="instagram">Instagram Direct (Meta)</option>
                    </select>
                </div>

                <!-- Dynamic Fields -->
                <div id="field_token" class="form-group" style="display:none;">
                    <label id="label_token">Access Token / Bot Token</label>
                    <input type="text" id="bot_token" class="form-control" placeholder="Kiriting...">
                </div>

                <div id="field_meta" style="display:none;">
                    <div class="form-group">
                        <label>Webhook Verify Token (Meta uchun o'zingiz o'ylab toping)</label>
                        <input type="text" id="bot_verify_token" class="form-control" placeholder="Masalan: itcloud_secret_2026">
                    </div>
                    <div class="form-group" id="field_wa_id" style="display:none;">
                        <label>WhatsApp Phone Number ID</label>
                        <input type="text" id="bot_wa_id" class="form-control" placeholder="1059518...243">
                    </div>
                    <div class="form-group" id="field_ig_id" style="display:none;">
                        <label>Instagram Account ID</label>
                        <input type="text" id="bot_ig_id" class="form-control" placeholder="178414...332">
                    </div>
                </div>

                <div id="field_pr_channel" style="display:none; margin-top:15px; padding-top:15px; border-top: 1px dashed rgba(255,255,255,0.2);">
                    <h4 style="color:var(--neon-cyan); margin-bottom:15px;"><i class="fa-solid fa-bullhorn"></i> PR Kanal Sozlamalari</h4>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div class="form-group">
                            <label>Kanal/Chat ID</label>
                            <input type="text" id="bot_channel_id" class="form-control" placeholder="Masalan: -10012345678">
                        </div>
                        <div class="form-group">
                            <label>Harkungi Yuborish Vaqti</label>
                            <input type="time" id="bot_schedule_time" class="form-control" value="09:00">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Rasm Temasi</label>
                        <select id="bot_theme" class="form-control">
                            <option value="cyberpunk">Cyberpunk (Neon, 2077)</option>
                            <option value="corporate">Corporate (Professional)</option>
                            <option value="minimal">Minimalist (Oq va qora)</option>
                            <option value="medical_ai">Medical AI (Tibbiy Futuristik)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Qo'shimcha Prompt</label>
                        <textarea id="bot_custom_prompt" class="form-control" style="height:60px;" placeholder="AI qo'shishi kerak bo'lgan ma'lumotlar..."></textarea>
                    </div>
                </div>


                <div class="modal-actions">
                    <button type="submit" class="btn-ios btn-neon" style="flex: 2;">Saqlash</button>
                    <button type="button" class="btn-ios" onclick="closeBotModal()" style="flex: 1;">Bekor qilish</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Tenant Modal -->
    <div id="tenantModal" class="modal-overlay" onclick="if(event.target === this) closeTenantModal()">
        <div class="glass-modal">
            <div class="modal-title">
                <i class="fa-solid fa-briefcase"></i> <span id="tenantModalHeader">Mijoz Sozlamalari</span>
            </div>
            <form id="tenantForm">
                <input type="hidden" id="edit_tenant_id">
                <div class="form-group">
                    <label>Kompaniya Nomi</label>
                    <input type="text" id="tenant_company" class="form-control" placeholder="Masalan: Delta CRM" required>
                </div>
                <div class="form-group">
                    <label>Subdomen / Domain</label>
                    <input type="text" id="tenant_domain" class="form-control" placeholder="delta.itcloud.uz" required>
                </div>
                <div class="modal-actions">
                    <button type="submit" class="btn-ios btn-neon" style="flex: 2;">Saqlash</button>
                    <button type="button" class="btn-ios" onclick="closeTenantModal()" style="flex: 1;">Orqaga</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Subscription Modal -->
    <div id="subModal" class="modal-overlay" onclick="if(event.target === this) closeSubModal()">
        <div class="glass-modal">
            <div class="modal-title">
                <i class="fa-solid fa-calendar-check"></i> Obunani Uzaytirish
            </div>
            <form id="subForm">
                <input type="hidden" id="sub_tenant_id">
                <div class="form-group">
                    <label>Muddat (kunlarda)</label>
                    <select id="sub_duration" class="form-control">
                        <option value="30">30 Kun (1 Oy)</option>
                        <option value="90">90 Kun (3 Oy)</option>
                        <option value="365">365 Kun (1 Yil)</option>
                        <option value="infinity">Cheksiz (Infinity)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>To'lov Summasi (UZS)</label>
                    <input type="number" id="sub_amount" class="form-control" value="150000">
                </div>
                <div class="modal-actions">
                    <button type="submit" class="btn-ios btn-neon" style="flex: 2;">Tasdiqlash</button>
                    <button type="button" class="btn-ios" onclick="closeSubModal()" style="flex: 1;">Bekor qilish</button>
                </div>
            </form>
        </div>
    </div>

    <!-- AI Project Constructor Modal -->
    <div id="ai-project-modal" class="modal-overlay" onclick="if(event.target === this) this.classList.remove('active')">
        <div class="glass-modal" style="max-width: 700px;">
            <div class="modal-title">
                <i class="fa-solid fa-wand-magic-sparkles"></i> Antigravity Arxitektor
            </div>
            <form id="aiProjectForm" onsubmit="event.preventDefault(); submitAiProject();">
                <div class="form-group">
                    <label>Loyiha Nomi</label>
                    <input type="text" id="ai_p_name" class="form-control" placeholder="Masalan: Milliy Ta'lim CRM" required>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div class="form-group">
                        <label>Soha / Kategoriya</label>
                        <select id="ai_p_category" class="form-control">
                            <option value="edu">O'quv Markazi</option>
                            <option value="medical">Klinika / Tibbiyot</option>
                            <option value="retail">Savdo / Magaza</option>
                            <option value="service" selected>Xizmat Ko'rsatish</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Subdomen</label>
                        <input type="text" id="ai_p_domain" class="form-control" placeholder="edu-test" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Kerakli Jadvallar (Database Schema)</label>
                    <textarea id="ai_p_tables" class="form-control" style="height: 80px;" placeholder="Masalan: students (ism, tel, guruh), courses (nomi, narxi)..."></textarea>
                </div>

                <div class="form-group">
                    <label>Qo'shimcha Xususiyatlar</label>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; padding: 10px; background: rgba(0,0,0,0.2); border-radius: 10px;">
                        <label style="display:flex; align-items:center; gap:8px;"><input type="checkbox" name="feat" value="tg_bot"> Telegram Bot</label>
                        <label style="display:flex; align-items:center; gap:8px;"><input type="checkbox" name="feat" value="sms"> SMS Xabarnoma</label>
                        <label style="display:flex; align-items:center; gap:8px;"><input type="checkbox" name="feat" value="payme"> Payme/Click Integratsiya</label>
                        <label style="display:flex; align-items:center; gap:8px;"><input type="checkbox" name="feat" value="stats"> Grafik Analitika</label>
                    </div>
                </div>

                <div class="modal-actions">
                    <button type="submit" class="btn-ios btn-neon" style="flex: 2;">🚀 Pipeline'ni Ishga Tushirish</button>
                    <button type="button" class="btn-ios" onclick="document.getElementById('ai-project-modal').classList.remove('active')" style="flex: 1;">Orqaga</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Upload Modal -->
    <div id="uploadModal" class="modal-overlay" onclick="if(event.target === this) closeUploadModal()">
        <div class="glass-modal">
            <div class="modal-title">
                <i class="fa-solid fa-cloud-arrow-up"></i> <span id="uploadModalHeader">Fayl Biriktirish</span>
            </div>
            <form id="uploadForm">
                <input type="hidden" id="upload_tenant_id">
                <input type="hidden" id="upload_type">
                <div class="form-group">
                    <label>Faylni tanlang (PDF, JPG, PNG)</label>
                    <input type="file" id="upload_file" class="form-control" required>
                </div>
                <div class="modal-actions">
                    <button type="submit" class="btn-ios btn-neon" style="flex: 2;">Yuklash</button>
                    <button type="button" class="btn-ios" onclick="closeUploadModal()" style="flex: 1;">Bekor qilish</button>
                </div>
            </form>
        </div>
    </div>

    <!-- AI Task Modal -->
    <div id="aiTaskModal" class="modal-overlay" onclick="if(event.target === this) closeTaskModal()">
        <div class="glass-modal">
            <div class="modal-title">
                <i class="fa-solid fa-clipboard-list"></i> <span id="taskAgentName">Agent</span> uchun yangi vazifa
            </div>
            <form id="aiTaskForm">
                <input type="hidden" id="task_bot_id">
                <div class="form-group">
                    <label>Agent nima qilishi kerak? (Muntazam vazifa)</label>
                    <textarea id="task_text" class="form-control" style="height: 120px;" placeholder="Masalan: Faqat Toshkent shahri mijozlariga xizmat ko'rsat. Agar mijoz viloyatdan bo'lsa, ularni operatorga yo'naltir." required></textarea>
                </div>
                <div class="modal-actions">
                    <button type="submit" class="btn-ios btn-neon" style="flex: 2;">Vazifani topshirish</button>
                    <button type="button" class="btn-ios" onclick="closeTaskModal()" style="flex: 1;">Bekor qilish</button>
                </div>
            </form>
        </div>
    </div>

    <!-- AI Chat Modal (Individual Chat) -->
    <div id="aiChatModal" class="modal-overlay" onclick="if(event.target === this) closeAiChat()">
        <div class="glass-modal" style="max-width: 500px; height: 600px; display: flex; flex-direction: column;">
            <div class="modal-title" style="display: flex; justify-content: space-between;">
                <span><i class="fa-solid fa-comments"></i> Chat: <span id="chatAgentName">Agent</span></span>
                <button onclick="closeAiChat()" style="background:none; border:none; color:white; cursor:pointer;"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div id="chatMessages" style="flex: 1; overflow-y: auto; padding: 15px; display: flex; flex-direction: column; gap: 10px; background: rgba(0,0,0,0.2); border-radius: 10px; margin: 10px 0;">
                <!-- Messages -->
                <div style="background: rgba(0,255,242,0.1); padding: 10px; border-radius: 10px 10px 10px 0; align-self: flex-start; max-width: 80%; font-size: 14px;">
                    Salom! Men tayyorman. Menga har qanday savol bering yoki buyruq bering.
                </div>
            </div>
            <div style="display: flex; gap: 10px;">
                <input type="hidden" id="chat_bot_id">
                <input type="text" id="chatInput" class="form-control" placeholder="Xabaringizni yozing..." style="flex: 1;" onkeypress="if(event.key==='Enter') sendAiMessage()">
                <button onclick="sendAiMessage()" class="btn-ios btn-neon" style="width: 50px;"><i class="fa-solid fa-paper-plane"></i></button>
            </div>
        </div>
    </div>

    <!-- AI Monitor Modal (Intervention) -->
    <div id="aiMonitorModal" class="modal-overlay" onclick="if(event.target === this) closeAiMonitor()">
        <div class="glass-modal" style="max-width: 600px; height: 700px; display: flex; flex-direction: column;">
            <div class="modal-title" style="display:flex; justify-content: space-between;">
                <span><i class="fa-solid fa-tower-broadcast"></i> Jonli Kuzatuv: <span id="monitorChatId">...</span></span>
                <button onclick="closeAiMonitor()" style="background:none; border:none; color:white; cursor:pointer;"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div id="monitorMessages" style="flex: 1; overflow-y: auto; padding: 15px; display: flex; flex-direction: column; gap: 12px; background: rgba(0,0,0,0.3); border-radius: 12px; margin: 15px 0;">
                <!-- Conversation history -->
            </div>
            <div style="display: flex; gap: 10px; padding-top: 10px; border-top: 1px solid var(--glass-border);">
                <button onclick="takeControl()" class="btn-ios" style="background: var(--neon-pink); border-color: var(--neon-pink); color: white; flex: 1;"><i class="fa-solid fa-hand-stop"></i> AI ni To'xtatish</button>
                <button onclick="sendOperatorMsg()" class="btn-ios btn-neon" style="flex: 2;"><i class="fa-solid fa-paper-plane"></i> Operator xabari</button>
            </div>
        </div>
    </div>

    <!-- Gallery Modal Viewer (Redesigned) -->
    <div id="galleryModal" class="modal-overlay" onclick="if(event.target === this) closeFileGallery()">
        <div class="gallery-modal-content">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h3 id="galleryTitle"><i class="fa-solid fa-images"></i> Mijoz Galereyasi</h3>
                <div style="display: flex; gap: 10px;">
                    <button onclick="document.getElementById('gallery_file_input').click()" class="btn-ios" style="padding: 8px 15px; background: var(--neon-purple); border-color: var(--neon-purple);"><i class="fa-solid fa-plus"></i></button>
                    <button onclick="closeFileGallery()" style="background:none; border:none; color:white; cursor:pointer; font-size: 20px;"><i class="fa-solid fa-xmark"></i></button>
                </div>
                <input type="file" id="gallery_file_input" style="display:none;" onchange="handleGalleryUpload(this)">
                <input type="hidden" id="gallery_tenant_id">
            </div>
            <p id="galleryInfo" style="font-size: 11px; opacity: 0.5; margin-bottom: 15px;">Fayllar ustiga bossangiz yuklab olinadi.</p>
            <div id="galleryContent" class="gallery-grid">
                <!-- Loaded via JS -->
            </div>
        </div>
    </div>

    <!-- Price Service Modal -->
    <div id="serviceModal" class="modal-overlay" onclick="if(event.target === this) closeServiceModal()">
        <div class="glass-modal">
            <div class="modal-title"><i class="fa-solid fa-pen-fancy"></i> Xizmat Narxini Sozlash</div>
            <form id="serviceForm">
                <input type="hidden" id="edit_service_id">
                <div class="form-group">
                    <label>Xizmat Nomi</label>
                    <input type="text" id="service_name" class="form-control" readonly>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div class="form-group">
                        <label>Boshlang'ich Narx ($)</label>
                        <input type="number" id="service_base_price" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Maksimal Narx ($)</label>
                        <input type="number" id="service_max_price" class="form-control" required>
                    </div>
                </div>
                <div class="form-group">
                    <label>Minimal Muddat (kun)</label>
                    <input type="number" id="service_days" class="form-control" required>
                </div>
                <div class="modal-actions">
                    <button type="submit" class="btn-ios btn-neon" style="flex: 2;">{{ __('Save') }}</button>
                    <button type="button" class="btn-ios" onclick="closeServiceModal()" style="flex: 1;">{{ __('Back') }}</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const API_PREFIX = '/api';
        
        // Mijoz (Tenant) Modal
        function openTenantModal(id = null, company = '', domain = '') {
            document.getElementById('edit_tenant_id').value = id || '';
            document.getElementById('tenant_company').value = company;
            document.getElementById('tenant_domain').value = domain;
            document.getElementById('tenantModalHeader').innerText = id ? "Mijozni Tahrirlash" : "Yangi Mijoz Qo'shish";
            document.getElementById('tenantModal').classList.add('active');
        }

        function closeTenantModal() {
            document.getElementById('tenantModal').classList.remove('active');
        }

        document.getElementById('tenantForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            let id = document.getElementById('edit_tenant_id').value;
            let company_name = document.getElementById('tenant_company').value;
            let domain = document.getElementById('tenant_domain').value;
            
            let url = id ? `${API_PREFIX}/tenants/${id}` : `${API_PREFIX}/tenants`;
            let method = id ? 'PUT' : 'POST';

            try {
                let res = await fetch(url, {
                    method: method,
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                    body: JSON.stringify({ company_name, domain })
                });
                let data = await res.json();
                if(data.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: "Muvaffaqiyatli!",
                        text: id ? "Mijoz ma'lumotlari yangilandi." : "Yangi loyiha muvaffaqiyatli ishga tushirildi!",
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => location.reload());
                }
            } catch(e) { Swal.fire("Xato", "Ma'lumotni saqlashda xatolik", 'error'); }
        });

        function promptAddTenant() {
            openTenantModal();
        }

        function promptEditTenant(id, company, domain) {
            openTenantModal(id, company, domain);
        }

        async function changeTenantStatus(id, newStatus) {
            try {
                let res = await fetch(`${API_PREFIX}/tenants/${id}/status`, {
                    method: 'PATCH',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                    body: JSON.stringify({ status: newStatus })
                });
                let data = await res.json();
                if(data.status === 'success') location.reload();
            } catch(e) { }
        }

        // Leads
        async function updateLeadStatus(id, status) {
            try {
                let res = await fetch(`${API_PREFIX}/leads/${id}/status`, {
                    method: 'PATCH',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                    body: JSON.stringify({ status })
                });
                if(res.ok) simulateAIAction("Lead holati yangilandi.");
            } catch(e) {}
        }

        // Upload
        function openUploadModal(tenantId, type) {
            document.getElementById('upload_tenant_id').value = tenantId;
            document.getElementById('upload_type').value = type;
            document.getElementById('uploadModalHeader').innerText = type === 'contract' ? "Shartnomani Yuklash" : "Mijoz Fayllari";
            document.getElementById('uploadModal').classList.add('active');
        }

        function closeUploadModal() {
            document.getElementById('uploadModal').classList.remove('active');
        }

        document.getElementById('uploadForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            let fd = new FormData();
            fd.append('file', document.getElementById('upload_file').files[0]);
            fd.append('type', document.getElementById('upload_type').value);
            
            let tenantId = document.getElementById('upload_tenant_id').value;
            
            try {
                let res = await fetch(`${API_PREFIX}/tenants/${tenantId}/upload`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                    body: fd
                });
                if(res.ok) {
                    simulateAIAction("Fayl muvaffaqiyatli saqlandi.");
                    closeUploadModal();
                    setTimeout(() => location.reload(), 1500);
                }
            } catch(e) { }
        });

        // Subscriptions
        function openSubModal(id) {
            document.getElementById('sub_tenant_id').value = id;
            document.getElementById('subModal').classList.add('active');
        }

        function closeSubModal() {
            document.getElementById('subModal').classList.remove('active');
        }

        document.getElementById('subForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            let id = document.getElementById('sub_tenant_id').value;
            let duration = document.getElementById('sub_duration').value;
            let amount = document.getElementById('sub_amount').value;

            try {
                let res = await fetch(`${API_PREFIX}/tenants/${id}/subscription`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                    body: JSON.stringify({ duration, amount })
                });
                let data = await res.json();
                if(data.status === 'success') {
                    simulateAIAction("Obuna muddati uzaytirildi. Xizmat faol!");
                    closeSubModal();
                    setTimeout(() => location.reload(), 1500);
                }
            } catch(e) { }
        });

        function promptSubscription(id) {
            openSubModal(id);
        }

        async function submitEmployee() {
            let fd = new FormData();
            fd.append('name', document.getElementById('emp_name').value);
            fd.append('email', document.getElementById('emp_email').value);
            fd.append('password', document.getElementById('emp_password').value);
            fd.append('role', document.getElementById('emp_role').value);
            fd.append('passport_number', document.getElementById('emp_passport').value);
            fd.append('is_face_id_enabled', 1);
            
            let photoInput = document.getElementById('emp_face_photo');
            if(photoInput.files[0]) {
                fd.append('face_id_photo', photoInput.files[0]);
            }
            
            try {
                let res = await fetch(`${API_PREFIX}/employees`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                    body: fd
                });
                let data = await res.json();
                if(data.status === 'success') {
                    Swal.fire("Muvaffaqiyatli", "Yangi xodim qo'shildi! Endi ular Face ID rasm orqali ishonchli kira oladilar.", 'success')
                        .then(() => location.reload());
                } else {
                    Swal.fire("Xato", "Pochtada yoki bazada muammo. " + (data.message || ''), 'error');
                }
            } catch(e) { Swal.fire("Xato", "Xodimni kiritishda xatolik. Fayl hajmini tekshiring.", 'error'); }
        }
        async function submitAiProject() {
            const name = document.getElementById('ai_p_name').value;
            const category = document.getElementById('ai_p_category').value;
            const domain = document.getElementById('ai_p_domain').value;
            const tables = document.getElementById('ai_p_tables').value;
            const features = Array.from(document.querySelectorAll('input[name="feat"]:checked')).map(cb => cb.value);

            simulateAIAction("Pipeline ishga tushirildi. Antigravity Arxitektor loyihani tahlil qilmoqda...");
            document.getElementById('ai-project-modal').classList.remove('active');

            try {
                const res = await fetch(`${API_PREFIX}/ai-projects`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                    body: JSON.stringify({ name, category, domain, tables, features })
                });
                const data = await res.json();
                
                if (data.status === 'success') {
                    simulateAIAction("Loyiha qurilmoqda. Antigravity AI Senior dasturchilar kodi yozishni boshladi...");
                    setTimeout(() => location.reload(), 3000);
                }
            } catch(e) {
                console.error(e);
            }
        }

        // Templates Management UI
        function openTemplateModal(id = null, data = null) {
            document.getElementById('edit_tpl_id').value = id || '';
            document.getElementById('modalHeaderText').innerText = id ? "Xizmatni Tahrirlash" : "Yangi Xizmat / Shablon";
            
            if(data) {
                document.getElementById('tpl_name').value = data.name || '';
                document.getElementById('tpl_desc').value = data.description || '';
                document.getElementById('tpl_price').value = data.price || 0;
                document.getElementById('tpl_preview').value = data.preview_url || '';
                document.getElementById('tpl_service_type').value = data.service_type || 'service';
                document.getElementById('tpl_payment_type').value = data.payment_type || 'monthly';
                document.getElementById('tpl_advantages').value = data.advantages || '';
                document.getElementById('tpl_includes').value = Array.isArray(data.includes) ? data.includes.join(', ') : '';
                document.getElementById('tpl_extras').value = Array.isArray(data.extra_services) ? data.extra_services.join(', ') : '';
            } else {
                document.getElementById('templateForm').reset();
            }
            document.getElementById('templateModal').classList.add('active');
        }

        function closeTemplateModal() {
            document.getElementById('templateModal').classList.remove('active');
        }

        document.getElementById('templateForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            let id = document.getElementById('edit_tpl_id').value;
            let payload = {
                name: document.getElementById('tpl_name').value,
                description: document.getElementById('tpl_desc').value,
                price: document.getElementById('tpl_price').value,
                preview_url: document.getElementById('tpl_preview').value,
                service_type: document.getElementById('tpl_service_type').value,
                payment_type: document.getElementById('tpl_payment_type').value,
                advantages: document.getElementById('tpl_advantages').value,
                includes: document.getElementById('tpl_includes').value.split(',').map(s => s.trim()).filter(s => s !== ""),
                extra_services: document.getElementById('tpl_extras').value.split(',').map(s => s.trim()).filter(s => s !== "")
            };

            let url = id ? `${API_PREFIX}/templates/${id}` : `${API_PREFIX}/templates`;
            let method = id ? 'PUT' : 'POST';

            try {
                let res = await fetch(url, {
                    method: method,
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                    body: JSON.stringify(payload)
                });
                let data = await res.json();
                if(data.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: id ? "Yangilandi!" : "Qo'shildi!",
                        text: id ? "Xizmat ma'lumotlari muvaffaqiyatli yangilandi." : "Yangi shablon tizimga muvaffaqiyatli ulandi!",
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => location.reload());
                }
            } catch(e) { Swal.fire("Xato", "Server bilan aloqa xatosi", 'error'); }
        });

        function manageTemplate(id, url) {
            simulateAIAction("Loyiha boshqaruv paneliga ulanmoqda...");
            document.getElementById('manageTitle').innerText = "Live Preview & Management";
            document.getElementById('manageSubtitle').innerText = "Hozirda ulanayotgan manzil: " + url;
            document.getElementById('manageIframe').src = url;
            document.getElementById('manageModal').classList.add('active');
        }

        function closeManageModal() {
            document.getElementById('manageModal').classList.remove('active');
            document.getElementById('manageIframe').src = "";
        }

        async function deleteTemplate(id) {
            const result = await Swal.fire({
                title: "O'chirishni tasdiqlaysizmi?",
                text: "Bu shablonni qayta tiklab bo'lmaydi!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ff1493',
                cancelButtonColor: '#888',
                confirmButtonText: 'Ha, o\'chirilsin!',
                cancelButtonText: 'Bekor qilish'
            });
            
            if(!result.isConfirmed) return;
            
            try {
                await fetch(`${API_PREFIX}/templates/${id}`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
                });
                location.reload();
            } catch(e) { Swal.fire("Xato", "O'chirishda xatolik", 'error'); }
        }

        async function deleteTenant(id) {
            const result = await Swal.fire({
                title: "Mijozni o'chirib tashlash?",
                text: "Ushbu kompaniyaga tegishli barcha ma'lumotlar o'chadi!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ff1493',
                confirmButtonText: 'Ha, o\'chirilsin!',
                cancelButtonText: 'Yo\'q'
            });
            
            if(!result.isConfirmed) return;
            
            try {
                let res = await fetch(`${API_PREFIX}/tenants/${id}`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
                });
                let data = await res.json();
                if(data.status === 'success') {
                    Swal.fire("O'chirildi!", "Mijoz tizimdan muvaffaqiyatli chiqarib yuborildi.", 'success')
                        .then(() => location.reload());
                }
            } catch(e) { Swal.fire("Xato", "Server xatosi", 'error'); }
        }

        // Bot Management UI
        function toggleAgentRoleFields(role) {
            document.getElementById('field_pr_channel').style.display = (role === 'pr_channel') ? 'block' : 'none';
        }

        function toggleChannelFields(type) {
            document.getElementById('field_token').style.display = (type === 'internal') ? 'none' : 'block';
            document.getElementById('field_meta').style.display = (type === 'whatsapp' || type === 'instagram') ? 'block' : 'none';
            document.getElementById('field_wa_id').style.display = (type === 'whatsapp') ? 'block' : 'none';
            document.getElementById('field_ig_id').style.display = (type === 'instagram') ? 'block' : 'none';
            
            if(type === 'telegram') document.getElementById('label_token').innerText = "Telegram Bot Token";
            else if(type === 'whatsapp' || type === 'instagram') document.getElementById('label_token').innerText = "Meta Permanent Access Token";
        }

        function openBotModal(bot = null) {
            if (bot) {
                document.getElementById('edit_bot_id').value = bot.id;
                document.getElementById('bot_name').value = bot.name;
                document.getElementById('bot_agent_type').value = bot.agent_type;
                document.getElementById('bot_channel_type').value = bot.channel_type || (bot.token ? 'telegram' : 'internal');
                document.getElementById('bot_token').value = bot.token || '';
                document.getElementById('bot_verify_token').value = bot.meta_verify_token || '';
                document.getElementById('bot_wa_id').value = bot.phone_number_id || '';
                document.getElementById('bot_ig_id').value = bot.instagram_account_id || '';
                
                // PR fields
                document.getElementById('bot_channel_id').value = bot.channel_id || '';
                document.getElementById('bot_schedule_time').value = bot.schedule_time || '09:00';
                document.getElementById('bot_theme').value = bot.theme || 'cyberpunk';
                document.getElementById('bot_custom_prompt').value = bot.custom_prompt || '';
                
                toggleChannelFields(document.getElementById('bot_channel_type').value);
                toggleAgentRoleFields(bot.agent_type);
                document.getElementById('botModalHeader').innerText = "Agentni Tahrirlash";
            } else {
                document.getElementById('botForm').reset();
                document.getElementById('edit_bot_id').value = '';
                toggleChannelFields('internal');
                toggleAgentRoleFields('sales');
                document.getElementById('botModalHeader').innerText = "Yangi AI Agent Qo'shish";
            }
            document.getElementById('botModal').classList.add('active');
        }

        function closeBotModal() {
            document.getElementById('botModal').classList.remove('active');
        }

        document.getElementById('botForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            let id = document.getElementById('edit_bot_id').value;
            let payload = {
                name: document.getElementById('bot_name').value,
                agent_type: document.getElementById('bot_agent_type').value,
                channel_type: document.getElementById('bot_channel_type').value,
                token: document.getElementById('bot_token').value,
                meta_verify_token: document.getElementById('bot_verify_token').value,
                phone_number_id: document.getElementById('bot_wa_id').value,
                instagram_account_id: document.getElementById('bot_ig_id').value,
                channel_id: document.getElementById('bot_channel_id').value,
                schedule_time: document.getElementById('bot_schedule_time').value,
                theme: document.getElementById('bot_theme').value,
                custom_prompt: document.getElementById('bot_custom_prompt').value,
                is_active: 1
            };

            let url = id ? `${API_PREFIX}/bots/${id}` : `${API_PREFIX}/bots`;
            let method = id ? 'PUT' : 'POST';

            try {
                let res = await fetch(url, {
                    method: method,
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                    body: JSON.stringify(payload)
                });
                let data = await res.json();
                if(data.status === 'success') {
                    closeBotModal();
                    Swal.fire({
                        icon: 'success',
                        title: "Saqlandi!",
                        text: id ? "AI Agent sozlamalari yangilandi." : "Yangi AI agent muvaffaqiyatli ulandi!",
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => location.reload());
                }
            } catch(e) { 
                closeBotModal();
                Swal.fire("Xato", "Agentni saqlashda xatolik yuz berdi", 'error'); 
            }
        });

        function promptAddBot() {
            openBotModal();
        }

        function promptEditBot(id, name, token, type) {
            openBotModal(id, name, token, type);
        }

        // Knowledge Base (RAG) UI Logic
        async function openKnowledgeModal(botId, botName) {
            document.getElementById('kb_bot_id').value = botId;
            document.getElementById('knowledgeBotName').innerText = botName;
            document.getElementById('knowledgeModal').classList.add('active');
            
            // Fetch existing documents
            const res = await fetch(`${API_PREFIX}/bots/${botId}/knowledge`);
            const data = await res.json();
            const list = document.getElementById('kb-list');
            if (data.length === 0) {
                list.innerHTML = '<div style="opacity: 0.5; font-size: 12px; text-align: center;">Hozircha bilimlar yuklanmagan.</div>';
            } else {
                list.innerHTML = '<h4 style="font-size: 14px; margin-bottom: 10px;">Yuklangan Bilimlar:</h4>' + 
                    data.map(f => `
                        <div style="background: rgba(255,255,255,0.05); padding: 8px 12px; border-radius: 8px; margin-bottom: 5px; font-size: 12px; display: flex; justify-content: space-between;">
                            <span><i class="fa-solid fa-file-pdf"></i> ${f.file_name}</span>
                            <span style="color: var(--neon-cyan);">O'qildi <i class="fa-solid fa-check-double"></i></span>
                        </div>
                    `).join('');
            }
        }

        function closeKnowledgeModal() {
            document.getElementById('knowledgeModal').classList.remove('active');
        }

        async function uploadKnowledge() {
            const botId = document.getElementById('kb_bot_id').value;
            const fileInput = document.getElementById('kb_file');
            const formData = new FormData();
            formData.append('file', fileInput.files[0]);
            formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);

            simulateAIAction("Hujjat tahlil qilinmoqda va AI miyasiga yuklanmoqda...");
            
            try {
                const res = await fetch(`${API_PREFIX}/bots/${botId}/knowledge`, {
                    method: 'POST',
                    body: formData
                });
                const data = await res.json();
                if (data.status === 'success') {
                    simulateAIAction("Loyiha bilimlar bazasi yangilandi!");
                    closeKnowledgeModal();
                }
            } catch(e) { alert("Yuklashda xatolik"); }
        }

        async function toggleBot(id, status) {
            try {
                await fetch(`${API_PREFIX}/bots/${id}`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                    body: JSON.stringify({ is_active: status })
                });
                location.reload();
            } catch(e) { }
        }

        // File Gallery & Smart Download
        function openFileGallery(tenantId, companyName, files) {
            const modal = document.getElementById('galleryModal');
            const content = document.getElementById('galleryContent');
            document.getElementById('galleryTitle').innerText = companyName;
            document.getElementById('gallery_tenant_id').value = tenantId;
            
            content.innerHTML = '';
            
            if(!files || files.length === 0) {
                content.innerHTML = '<div style="grid-column: 1/-1; text-align: center; color: var(--text-muted); padding: 50px 0;">Hali fayllar yuklanmagan</div>';
            } else {
                files.forEach((file) => {
                    const isImg = file.match(/\.(jpg|jpeg|png|gif|webp)$/i);
                    const item = document.createElement('div');
                    item.className = 'gallery-item';
                    
                    const fileName = file.split('/').pop();
                    const taggedName = `ID-${tenantId}-${companyName}-${fileName}`;

                    item.onclick = () => smartDownload(`/storage/${file}`, taggedName);
                    
                    if(isImg) {
                        item.innerHTML = `<img src="/storage/${file}"><div class="overlay-down"><i class="fa-solid fa-download"></i></div>`;
                    } else {
                        item.innerHTML = `<div style="display:flex; flex-direction:column; align-items:center; justify-content:center; height:100%; padding: 10px; text-align:center;">
                            <i class="fa-solid fa-file-invoice" style="font-size: 30px; opacity:0.3; margin-bottom:5px;"></i>
                            <div style="font-size:9px; overflow:hidden; width:100%;">${fileName}</div>
                        </div><div class="overlay-down"><i class="fa-solid fa-download"></i></div>`;
                    }
                    content.appendChild(item);
                });
            }
            
            modal.classList.add('active');
        }

        function closeFileGallery() {
            document.getElementById('galleryModal').classList.remove('active');
        }

        async function handleGalleryUpload(input) {
            if(!input.files.length) return;
            const tenantId = document.getElementById('gallery_tenant_id').value;
            const file = input.files[0];
            
            let fd = new FormData();
            fd.append('file', file);
            fd.append('type', 'files');

            simulateAIAction("Yangi fayl galerayaga yuklanmoqda...");
            
            try {
                let res = await fetch(`${API_PREFIX}/tenants/${tenantId}/upload`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                    body: fd
                });
                if(res.ok) {
                    simulateAIAction("Yuklandi! Galereya yangilanmoqda...");
                    setTimeout(() => location.reload(), 1500);
                }
            } catch(e) { }
        }

        async function smartDownload(url, filename) {
            simulateAIAction("Yuklab olinmoqda...");
            try {
                const response = await fetch(url);
                const blob = await response.blob();
                const link = document.createElement('a');
                link.href = window.URL.createObjectURL(blob);
                link.download = filename;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                simulateAIAction("Saqlandi: " + filename);
            } catch(e) { }
        }

        async function setBotWebhook(id) {
            simulateAIAction("Webhook o'rnatilmoqda...");
            try {
                let res = await fetch(`${API_PREFIX}/bots/${id}/set-webhook`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
                });
                let data = await res.json();
                if(data.ok) simulateAIAction("Muvaffaqiyatli! Bot endi javob beradi.");
                else simulateAIAction("Xato: " + (data.description || "Ulanib bo'lmadi"));
            } catch(e) { }
        }

        async function deleteBot(id) {
            const result = await Swal.fire({
                title: "Botni o'chirishni tasdiqlaysizmi?",
                text: "Bu amalni qayta tiklab bo'lmaydi!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ff1493',
                confirmButtonText: 'Ha, o\'chirilsin!',
                cancelButtonText: 'Bekor qilish'
            });
            
            if(!result.isConfirmed) return;

            try {
                await fetch(`${API_PREFIX}/bots/${id}`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
                });
                location.reload();
            } catch(e) { Swal.fire("Xato", "Botni o'chirishda xatolik", 'error'); }
        }

        // Yon Menyuni (Tablarni) almashtirish mantig'i
        function switchTab(tabId) {
            // Hamma sectionlarni yashirish
            document.querySelectorAll('.view-section').forEach(el => {
                el.classList.remove('active');
            });
            // Hamma menyu aktivligini olib tashlash
            document.querySelectorAll('.nav-item').forEach(el => {
                el.classList.remove('active');
            });
            
            // Tanlanganini ko'rsatish
            const targetSection = document.getElementById(tabId);
            const navItems = document.querySelectorAll('.nav-item');
            
            if(targetSection) targetSection.classList.add('active');
            
            navItems.forEach(item => {
                const onClickAttr = item.getAttribute('onclick');
                if(onClickAttr && onClickAttr.includes(`'${tabId}'`)) {
                    item.classList.add('active');
                }
            });

            if(tabId === 'ai-developer') loadAiProjects();

            // Save to storage
            localStorage.setItem('activeTab', tabId);
        }

        async function loadAiProjects() {
            try {
                const res = await fetch(`${API_PREFIX}/ai-projects`);
                const projects = await res.json();
                const list = document.getElementById('ai-projects-list');
                
                if (projects.length === 0) {
                    list.innerHTML = `<div style="text-align:center; padding: 40px; color: var(--text-muted);">Hozircha AI loyihalar yo'q. Pipeline'ni ishga tushiring!</div>`;
                    return;
                }

                list.innerHTML = projects.map(p => `
                    <div class="tenant-row" style="margin-bottom: 12px; align-items: center;">
                        <div>
                            <b><i class="fa-solid fa-microchip" style="color:var(--neon-cyan)"></i> ${p.name}</b><br>
                            <small style="opacity: 0.5;">Domain: ${p.tenant.domain}.itcloud.uz</small>
                        </div>
                        <div style="flex: 1; padding: 0 20px;">
                            <div style="font-size: 10px; margin-bottom: 4px; display:flex; justify-content:space-between;">
                                <span>Status: ${p.status.toUpperCase()}</span>
                                <span>${p.progress}%</span>
                            </div>
                            <div style="width: 100%; height: 6px; background: rgba(255,255,255,0.1); border-radius: 10px; overflow: hidden;">
                                <div style="width: ${p.progress}%; height: 100%; background: linear-gradient(90deg, var(--neon-cyan), var(--neon-purple)); box-shadow: 0 0 10px var(--neon-cyan);"></div>
                            </div>
                        </div>
                        <div style="width: 120px; text-align: right;">
                             <span class="status-badge status-${p.status === 'deployed' ? 'active' : 'pending'}">${p.status}</span>
                        </div>
                    </div>
                `).join('');
            } catch(e) {}
        }

        // AI Agent Hub Features
        function openTaskModal(botId, name, currentTask) {
            document.getElementById('task_bot_id').value = botId;
            document.getElementById('taskAgentName').innerText = name;
            document.getElementById('task_text').value = currentTask !== 'null' ? currentTask : '';
            document.getElementById('aiTaskModal').classList.add('active');
        }

        function closeTaskModal() {
            document.getElementById('aiTaskModal').classList.remove('active');
        }

        document.getElementById('aiTaskForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const botId = document.getElementById('task_bot_id').value;
            const task = document.getElementById('task_text').value;

            try {
                let res = await fetch(`${API_PREFIX}/bots/${botId}/task`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                    body: JSON.stringify({ task })
                });
                if((await res.json()).status === 'success') {
                    simulateAIAction("{{ __('Agent accepted the new task!') }}");
                    closeTaskModal();
                    setTimeout(() => location.reload(), 1500);
                }
            } catch(e) { }
        });

        function openAiChat(botId, name) {
            document.getElementById('chat_bot_id').value = botId;
            document.getElementById('chatAgentName').innerText = name;
            document.getElementById('chatMessages').innerHTML = `<div style="background: rgba(0,255,242,0.1); padding: 10px; border-radius: 10px 10px 10px 0; align-self: flex-start; max-width: 80%; font-size: 14px;">{{ __('Hello! I am') }} ${name}. {{ __('How can I help you?') }}</div>`;
            document.getElementById('aiChatModal').classList.add('active');
        }

        function closeAiChat() {
            document.getElementById('aiChatModal').classList.remove('active');
        }

        function openEditEmpModal(emp) {
            document.getElementById('edit_emp_id').value = emp.id;
            document.getElementById('emp_name').value = emp.name;
            document.getElementById('emp_email').value = emp.email;
            document.getElementById('emp_passport').value = emp.passport_number || '';
            document.getElementById('emp_role').value = emp.role;
            document.getElementById('emp_password').required = false;
            document.getElementById('empModalHeader').innerText = "Xodim Ma'lumotlarini Tahrirlash";
            document.getElementById('add-emp-form').style.display = 'block';
            document.getElementById('add-emp-form').scrollIntoView({ behavior: 'smooth' });
        }

        function closeEmpForm() {
            document.getElementById('empForm').reset();
            document.getElementById('edit_emp_id').value = '';
            document.getElementById('emp_password').required = true;
            document.getElementById('add-emp-form').style.display = 'none';
        }

        async function submitEmployee() {
            let id = document.getElementById('edit_emp_id').value;
            let fd = new FormData();
            fd.append('name', document.getElementById('emp_name').value);
            fd.append('email', document.getElementById('emp_email').value);
            fd.append('role', document.getElementById('emp_role').value);
            fd.append('passport_number', document.getElementById('emp_passport').value);
            
            let pass = document.getElementById('emp_password').value;
            if(pass) fd.append('password', pass);
            
            let photo = document.getElementById('emp_face_photo').files[0];
            if(photo) fd.append('face_id_photo', photo);
            
            // Laravel Method Spoofing for PUT
            if(id) fd.append('_method', 'PUT');

            Swal.fire({ title: "Saqlanmoqda...", didOpen: () => Swal.showLoading() });

            try {
                let url = id ? `${API_PREFIX}/employees/${id}` : `${API_PREFIX}/employees`;
                let res = await fetch(url, {
                    method: 'POST', // Always POST for FormData, Laravel uses _method for PUT
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                    body: fd
                });
                let data = await res.json();
                if(data.status === 'success') {
                    Swal.fire("Muvaffaqiyatli", id ? "Ma'lumotlar yangilandi" : "Yangi xodim qo'shildi", 'success')
                        .then(() => location.reload());
                } else {
                    Swal.fire("Xato", data.message || "Xatolik yuz berdi", 'error');
                }
            } catch(e) { Swal.fire("Xato", "Server xatosi", 'error'); }
        }

        async function deleteEmployee(id) {
            const result = await Swal.fire({
                title: "Xodimni ishdan bo'shatish?",
                text: "Ushbu xodim tizimga kira olmaydi!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ff1493',
                confirmButtonText: 'Ha, bo\'shatilsin!',
                cancelButtonText: 'Bekor qilish'
            });

            if(!result.isConfirmed) return;

            try {
                let res = await fetch(`${API_PREFIX}/employees/${id}`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
                });
                let data = await res.json();
                if(data.status === 'success') {
                    Swal.fire("Bajarildi", "Xodim muvaffaqiyatli ishdan bo'shatildi", 'success')
                        .then(() => location.reload());
                } else {
                    Swal.fire("Xato", data.message || "O'chirishning iloji bo'lmadi", 'error');
                }
            } catch(e) { Swal.fire("Xato", "Server bilan aloqa uzildi", 'error'); }
        }

        async function sendAiMessage() {
            const input = document.getElementById('chatInput');
            const msg = input.value.trim();
            const botId = document.getElementById('chat_bot_id').value;
            if(!msg) return;

            const chatMessages = document.getElementById('chatMessages');
            chatMessages.innerHTML += `<div style="background: rgba(255,255,255,0.05); padding: 10px; border-radius: 10px 10px 0 10px; align-self: flex-end; max-width: 80%; font-size: 14px;">${msg}</div>`;
            input.value = '';
            chatMessages.scrollTop = chatMessages.scrollHeight;

            // Loading state
            const loadingId = 'ai_loading_' + Date.now();
            chatMessages.innerHTML += `<div id="${loadingId}" style="background: rgba(0,255,242,0.05); padding: 10px; border-radius: 10px 10px 10px 0; align-self: flex-start; max-width: 80%; font-size: 12px; font-style: italic;">{{ __('Agent Gemini is responding...') }}</div>`;
            chatMessages.scrollTop = chatMessages.scrollHeight;

            try {
                let res = await fetch(`${API_PREFIX}/ai/chat`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                    body: JSON.stringify({ bot_id: botId, message: msg })
                });
                let data = await res.json();
                document.getElementById(loadingId).remove();
                chatMessages.innerHTML += `<div style="background: rgba(0,255,242,0.1); padding: 10px; border-radius: 10px 10px 10px 0; align-self: flex-start; max-width: 80%; font-size: 14px;">${data.reply}</div>`;
                chatMessages.scrollTop = chatMessages.scrollHeight;
            } catch(e) { 
                document.getElementById(loadingId).remove();
                chatMessages.innerHTML += `<div style="color: var(--neon-pink); font-size: 12px;">{{ __('Error: Could not connect.') }}</div>`;
            }
        }

        // AI Live Monitoring Logic
        async function updateActiveChats() {
            try {
                const res = await fetch(`${API_PREFIX}/ai/active-chats`, {
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
                });
                const chats = await res.json();
                const container = document.getElementById('active-chats-list');
                
                if (chats.length === 0) {
                    container.innerHTML = '<div style="text-align: center; padding: 20px; opacity: 0.5;">{{ __("No active conversations yet.") }}</div>';
                    return;
                }

                container.innerHTML = chats.map(c => `
                    <div style="background: rgba(255,255,255,0.03); border: 1px solid var(--glass-border); padding: 12px 20px; border-radius: 12px; display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <span style="color: var(--neon-cyan); font-weight: bold;">ID: ${c.chat_id}</span>
                            <span style="margin-left: 10px; font-size: 11px; opacity: 0.6;">Kanal: ${c.agent_type.toUpperCase()}</span>
                        </div>
                        <div>
                            <span style="font-size: 12px; opacity: 0.4; margin-right: 15px;">${new Date(c.last_time).toLocaleTimeString()}</span>
                            <button onclick="openAiMonitor('${c.chat_id}')" class="btn-ios" style="padding: 5px 15px; font-size: 12px;">{{ __('Monitor & Intervene') }}</button>
                        </div>
                    </div>
                `).join('');
            } catch(e) { }
        }

        async function openAiMonitor(chatId) {
            document.getElementById('monitorChatId').innerText = chatId;
            document.getElementById('aiMonitorModal').classList.add('active');
            
            try {
                const res = await fetch(`${API_PREFIX}/ai/conversation/${chatId}`, {
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
                });
                const history = await res.json();
                const box = document.getElementById('monitorMessages');
                
                box.innerHTML = history.map(h => `
                    <div style="margin-bottom: 10px;">
                        <div style="font-size: 10px; opacity: 0.5; margin-bottom: 4px;">User (${chatId}):</div>
                        <div style="background: rgba(255,255,255,0.05); padding: 10px; border-radius: 10px 10px 0 10px; align-self: flex-end; font-size: 13px;">${h.user_message}</div>
                    </div>
                    <div style="margin-bottom: 10px;">
                        <div style="font-size: 10px; color: var(--neon-cyan); margin-bottom: 4px;">AI (${h.agent_type}):</div>
                        <div style="background: rgba(0,255,204,0.1); padding: 10px; border-radius: 10px 10px 10px 0; align-self: flex-start; font-size: 13px;">${h.bot_response}</div>
                    </div>
                `).join('');
                box.scrollTop = box.scrollHeight;
            } catch(e) { }
        }

        function closeAiMonitor() {
            document.getElementById('aiMonitorModal').classList.remove('active');
        }

        // Send dummy actions for intervention (future implementation)
        function takeControl() { simulateAIAction("{{ __('AI has been temporarily disabled for this chat. Now only the operator will respond.') }}"); }
        function sendOperatorMsg() { alert("{{ __('Operator message sent (Simulation)') }}"); }

        // Refresh loop
        setInterval(updateActiveChats, 10000); // 10 sekunda yangilab turadi
        function simulateAIAction(customText = null) {
            const island = document.getElementById('dynamicIsland');
            const text = document.getElementById('islandText');
            
            island.classList.add('active');
            text.innerHTML = customText || "{{ __('Agent Gemini: Conversing with client...') }}";
            text.style.color = "var(--neon-cyan)";
            
            // 3 soniyadan keyin qaytish
            setTimeout(() => {
                if(!customText) {
                    text.innerHTML = "{{ __('Payment accepted!') }}";
                    text.style.color = "var(--neon-purple)";
                }
                
                setTimeout(() => {
                    island.classList.remove('active');
                    setTimeout(() => {
                        text.innerHTML = "Obsidian OS v1";
                        text.style.color = "var(--text-main)";
                    }, 300);
                }, 2000);
            }, 3000);
        }

        // Sessiya nazorati (Auto-Logout) - 15 daqiqa harakatsizlik
        let inactivityTime = function () {
            let time;
            document.onmousemove = resetTimer;
            document.onkeypress = resetTimer;
            
            function logout() {
                // Xavfsizlik uchun tizimdan chiqazib tashlash
                fetch('/logout', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').content : '' }
                }).then(() => {
                    window.location.href = '/login';
                });
            }
            
            function resetTimer() {
                clearTimeout(time);
                // 15 daqiqa (900,000 millisekund)
                time = setTimeout(logout, 900000);
            }
        };

        // Dashboard Analytics Hydration
        async function initDashboardAnalytics() {
            try {
                const res = await fetch(`${API_PREFIX}/dashboard/analytics`, {
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
                });
                const data = await res.json();

                // Update Stats
                document.getElementById('stats-total-revenue').innerText = new Intl.NumberFormat('uz-UZ').format(data.stats.total_revenue) + " UZS";
                document.getElementById('stats-active-tenants').innerText = data.stats.active_tenants;
                document.getElementById('stats-new-leads').innerText = data.stats.new_leads_today;
                document.getElementById('stats-total-bots').innerText = data.stats.total_bots;

                // Render Chart
                const ctx = document.getElementById('dashboardChart').getContext('2d');
                
                // Helper to map monthly totals
                const mapMonthly = (dbData) => {
                    const months = ["01","02","03","04","05","06","07","08","09","10","11","12"];
                    const currentMonths = [];
                    for(let i=5; i>=0; i--) {
                        let d = new Date();
                        d.setMonth(d.getMonth() - i);
                        currentMonths.push(months[d.getMonth()]);
                    }
                    return currentMonths.map(m => {
                        let find = dbData.find(d => d.month == m);
                        return find ? find.total : 0;
                    });
                }

                const revenueValues = mapMonthly(data.revenue);
                const leadsValues = mapMonthly(data.leads);

                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: data.months,
                        datasets: [
                            {
                                label: 'Daromad (UZS)',
                                data: revenueValues,
                                borderColor: '#00ffcc',
                                backgroundColor: 'rgba(0, 255, 204, 0.1)',
                                fill: true,
                                tension: 0.4,
                                yAxisID: 'y'
                            },
                            {
                                label: 'Yangi Leadlar',
                                data: leadsValues,
                                borderColor: '#b026ff',
                                backgroundColor: 'rgba(176, 38, 255, 0.1)',
                                fill: true,
                                tension: 0.4,
                                yAxisID: 'y1'
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: true, labels: { color: 'white' } }
                        },
                        scales: {
                            x: { grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: 'rgba(255,255,255,0.5)' } },
                            y: { position: 'left', grid: { color: 'rgba(255,255,255,0.1)' }, ticks: { color: '#00ffcc' } },
                            y1: { position: 'right', grid: { display: false }, ticks: { color: '#b026ff' } }
                        }
                    }
                });
            } catch (e) { console.error("Analytics Error:", e); }
        }

        // Settings Update Logic
        document.getElementById('globalSettingsForm')?.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            const dataHash = Object.fromEntries(formData);

            Swal.fire({
                title: "{{ __('Saving...') }}",
                html: "{{ __('Updating system settings...') }}",
                didOpen: () => { Swal.showLoading(); }
            });

            try {
                const res = await fetch(`${API_PREFIX}/settings`, {
                    method: 'POST',
                    headers: { 
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content 
                    },
                    body: JSON.stringify(dataHash)
                });
                const result = await res.json();
                if(result.status === 'success') {
                    Swal.fire("{{ __('Success!') }}", result.message, 'success');
                }
            } catch (err) {
                Swal.fire("{{ __('Error!') }}", "{{ __('There was a problem saving the settings.') }}", 'error');
            }
        });


        // PR Bot Settings Logic
        document.getElementById('prBotSettingsForm')?.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            const dataHash = Object.fromEntries(formData);

            Swal.fire({
                title: "{{ __('Saving...') }}",
                html: "{{ __('Updating PR Bot settings...') }}",
                didOpen: () => { Swal.showLoading(); }
            });

            try {
                const res = await fetch(`${API_PREFIX}/settings`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                    body: JSON.stringify(dataHash)
                });
                const result = await res.json();
                if(result.status === 'success') {
                    Swal.fire("Muvaffaqiyatli", "Avto-PR Bot vaqti va dizayni bazaga yozildi. Ajoyib!", 'success');
                }
            } catch (err) {
                Swal.fire("Xatolik", "Serverga bog'lanib bo'lmadi", 'error');
            }
        });

        async function manualPrBotTrigger() {
            Swal.fire({
                title: "AI O'ylamoqda...",
                html: "Ma'lumotlar yig'ilmoqda va post generatsiya qilinib Telegramga yuborilmoqda. Iltimos 10-15 soniya kuting.",
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });

            try {
                const res = await fetch(`${API_PREFIX}/pr-bot/trigger`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
                });
                
                if (res.ok) {
                    Swal.fire("Bajarildi!", "Post muvaffaqiyatli tarzda telegram kanalga tushdi!", 'success');
                } else {
                    Swal.fire("Xatolik", "Kanalga post tashlashda backend muammosi kuzatildi.", 'error');
                }
            } catch(e) {
                Swal.fire("Xato", "Ulanish uzildi.", 'error');
            }
        }

        function switchSettingsTab(btn, tabId) {
            document.querySelectorAll('.settings-tab-content').forEach(el => el.style.display = 'none');
            document.querySelectorAll('#settings .btn-ios').forEach(el => el.classList.remove('active'));
            document.getElementById(tabId).style.display = 'block';
            btn.classList.add('active');
        }

        // Calculator Service Logic
        function openServiceModal(id, name, base_price, max_price, min_days) {
            document.getElementById('edit_service_id').value = id;
            document.getElementById('service_name').value = name;
            document.getElementById('service_base_price').value = base_price;
            document.getElementById('service_max_price').value = max_price;
            document.getElementById('service_days').value = min_days;
            document.getElementById('serviceModal').classList.add('active');
        }

        function closeServiceModal() {
            document.getElementById('serviceModal').classList.remove('active');
        }

        document.getElementById('serviceForm')?.addEventListener('submit', async (e) => {
            e.preventDefault();
            const id = document.getElementById('edit_service_id').value;
            const payload = {
                base_price: document.getElementById('service_base_price').value,
                max_price: document.getElementById('service_max_price').value,
                min_days: document.getElementById('service_days').value
            };

            Swal.fire({ title: "{{ __('Updating...') }}", didOpen: () => Swal.showLoading() });

            try {
                const res = await fetch(`${API_PREFIX}/price-services/${id}`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                    body: JSON.stringify(payload)
                });
                if(res.ok) {
                    Swal.fire("{{ __('Success!') }}", "{{ __('Service prices updated and will apply real-time to all calculators.') }}", 'success')
                        .then(() => location.reload());
                }
            } catch(e) { Swal.fire("{{ __('Error!') }}", "{{ __('Connection lost.') }}", 'error'); }
        });

        window.onload = function() {
            inactivityTime();
            const savedTab = localStorage.getItem('activeTab') || 'dashboard';
            switchTab(savedTab);
            initDashboardAnalytics();
        }

        // Tasodifiy ravishda AI xabar berib turishi (Realistik effekt uchun)
        setInterval(() => {
            if(Math.random() > 0.7 && !document.getElementById('dynamicIsland').classList.contains('active')) {
                simulateAIAction();
            }
        }, 15000);
    </script>
</body>
</html>
