<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>ITcloud | Obsidian OS v1</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
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
        .sidebar { width: 280px; margin: 20px; padding: 30px 20px; display: flex; flex-direction: column; z-index: 10;}
        .brand { font-size: 26px; font-weight: 800; margin-bottom: 50px; text-align: center; letter-spacing: 1px; }
        .brand span { color: var(--neon-cyan); text-shadow: 0 0 15px rgba(0,255,204,0.5); }
        
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
        <div class="brand">IT<span>cloud</span></div>
        
        <div class="nav-item active" onclick="switchTab(event, 'dashboard')">
            <i class="fa-solid fa-border-all"></i> Dashboard
        </div>
        <div class="nav-item" onclick="switchTab(event, 'tenants')">
            <i class="fa-solid fa-users"></i> CRM Mijozlar
        </div>
        <div class="nav-item" onclick="switchTab(event, 'employees')">
            <i class="fa-solid fa-user-shield"></i> Xodimlar / Admin
        </div>
        <div class="nav-item" onclick="switchTab(event, 'ai-hub')">
            <i class="fa-solid fa-brain"></i> AI Agent Hub
        </div>
        <div class="nav-item" onclick="switchTab(event, 'system-health')">
            <i class="fa-solid fa-server"></i> Server Holati
        </div>
        <div class="nav-item" onclick="switchTab(event, 'security-logs')">
            <i class="fa-solid fa-shield-halved"></i> Xavfsizlik Jurnali
        </div>
        <div class="nav-item" onclick="switchTab(event, 'templates')">
            <i class="fa-solid fa-layer-group"></i> Shablonlar
        </div>
        <div class="nav-item" onclick="switchTab(event, 'bot-manager')">
            <i class="fa-brands fa-telegram"></i> Botlar Manager
        </div>
        <div class="nav-item" onclick="switchTab(event, 'live-chat')">
            <i class="fa-solid fa-headset"></i> Qutqaruv Chati
        </div>
        <div class="nav-item" onclick="switchTab(event, 'finance')">
            <i class="fa-solid fa-file-invoice-dollar"></i> Moliya & Sotuv
        </div>
        
        <form id="logoutForm" action="{{ route('logout') }}" method="POST" style="margin-top: auto;">
            @csrf
            <button type="submit" class="nav-item" style="width: 100%; text-align: left; background: transparent; color: var(--neon-pink);">
                <i class="fa-solid fa-power-off"></i> Tizimdan Chiqish
            </button>
        </form>
    </nav>

    <main class="main-container">
        
        <div id="dashboard" class="view-section active">
            <div class="stats-grid">
                <div class="glass-panel stat-card">
                    <div class="stat-title">Faol Loyihalar</div>
                    <div class="stat-value" id="stats-active-tenants">{{ $activeTenantsCount ?? 0 }}</div>
                </div>
                <div class="glass-panel stat-card" style="--neon-cyan: var(--neon-purple);">
                    <div class="stat-title">AI Sotuvlar (Bu oy)</div>
                    <div class="stat-value" id="stats-ai-sales">{{ $aiSalesCount ?? 0 }}</div>
                </div>
                <div class="glass-panel stat-card" style="--neon-cyan: var(--neon-pink);">
                    <div class="stat-title">Bloklanganlar</div>
                    <div class="stat-value" id="stats-blocked">{{ $blockedTenantsCount ?? 0 }}</div>
                </div>
                <div class="glass-panel stat-card">
                    <div class="stat-title">AI tejagan vaqt</div>
                    <div class="stat-value">{{ $aiSavedTime ?? 0 }} <span style="font-size: 14px; font-weight: 400; color: #8b9bb4;">soat</span></div>
                </div>
            </div>

            <div class="content-row">
                <div class="glass-panel" style="padding: 30px;">
                    <div class="panel-title"><i class="fa-solid fa-chart-line" style="color: var(--neon-cyan);"></i> Daromad & Tizim o'sishi</div>
                    <div style="width: 100%; height: 300px; display: flex; align-items: flex-end; gap: 10px; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 10px;">
                        <div style="flex:1; background: linear-gradient(to top, rgba(0,255,204,0.1), rgba(0,255,204,0.8)); height: 40%; border-radius: 8px 8px 0 0;"></div>
                        <div style="flex:1; background: linear-gradient(to top, rgba(0,255,204,0.1), rgba(0,255,204,0.8)); height: 60%; border-radius: 8px 8px 0 0;"></div>
                        <div style="flex:1; background: linear-gradient(to top, rgba(176,38,255,0.1), rgba(176,38,255,0.8)); height: 30%; border-radius: 8px 8px 0 0;"></div>
                        <div style="flex:1; background: linear-gradient(to top, rgba(0,255,204,0.1), rgba(0,255,204,0.8)); height: 80%; border-radius: 8px 8px 0 0;"></div>
                        <div style="flex:1; background: linear-gradient(to top, rgba(0,255,204,0.1), rgba(0,255,204,0.8)); height: 95%; border-radius: 8px 8px 0 0; box-shadow: 0 0 20px rgba(0,255,204,0.5);"></div>
                    </div>
                </div>

                <div class="glass-panel" style="padding: 30px;">
                    <div class="panel-title"><i class="fa-solid fa-microchip" style="color: var(--neon-purple);"></i> AI Faoliyati (Jonli)</div>
                    <div class="ai-feed" id="ai-feed-container">
                        @if(isset($aiLogs) && $aiLogs->count() > 0)
                            @foreach($aiLogs as $log)
                            <div class="feed-item" style="border-left-color: {{ $log->agent_type == 'sales' ? 'var(--neon-cyan)' : ($log->agent_type == 'support' ? 'var(--neon-purple)' : 'var(--neon-pink)') }};">
                                <div class="feed-time">{{ $log->created_at->format('H:i') }} {{ $log->created_at->isToday() ? 'Bugun' : $log->created_at->format('d M') }}</div>
                                <b>{{ ucfirst($log->agent_type) }} Agenti:</b> {{ $log->action }}. {{ $log->details }}
                            </div>
                            @endforeach
                        @else
                            <div class="feed-item">
                                <div class="feed-time">Hozir</div>
                                <b>Tizim:</b> Hozircha AI harakatlari yo'q.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div id="tenants" class="view-section">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
                <h2>Loyihalar Tarmog'i</h2>
                <button class="btn-ios btn-neon" onclick="promptAddTenant()"><i class="fa-solid fa-plus"></i> Yangi Mijoz</button>
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
                            <div><span class="status-badge status-active"><i class="fa-solid fa-circle" style="font-size: 8px;"></i> Faol</span></div>
                            <div style="color: var(--text-muted); font-size: 14px;"><i class="fa-regular fa-clock"></i> {{ now()->diffInDays($tenant->expires_at, false) }} kun qoldi</div>
                            <div style="display: flex; gap: 10px;">
                                <button class="btn-ios btn-neon" onclick="promptSubscription({{ $tenant->id }})">+ Uzaytirish</button>
                                <button class="btn-ios" style="color: var(--neon-pink); border: 1px solid var(--neon-pink);" onclick="changeTenantStatus({{ $tenant->id }}, 'blocked')">Bloklash</button>
                                <button class="btn-ios" onclick="promptEditTenant({{ $tenant->id }}, '{{ $tenant->company_name }}', '{{ $tenant->domain }}')"><i class="fa-solid fa-pen"></i></button>
                            </div>
                        @else
                            <div><span class="status-badge status-blocked"><i class="fa-solid fa-lock" style="font-size: 10px;"></i> Bloklangan</span></div>
                            <div style="color: var(--neon-pink); font-size: 14px;"><i class="fa-solid fa-triangle-exclamation"></i> Haqdorlik yo'q</div>
                            <div style="display: flex; gap: 10px;">
                                <button class="btn-ios btn-neon" onclick="changeTenantStatus({{ $tenant->id }}, 'active')">Ochish</button>
                                <button class="btn-ios" onclick="promptSubscription({{ $tenant->id }})">+ Uzaytirish</button>
                            </div>
                        @endif
                    </div>
                    @endforeach
                @else
                    <div style="text-align: center; padding: 40px; color: var(--text-muted);">Hozircha loyihalar yo'q.</div>
                @endif
            </div>
        </div>
        
        <div id="employees" class="view-section">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
                <h2>Xodimlar va Master Adminlar</h2>
                <button class="btn-ios btn-neon" onclick="document.getElementById('add-emp-form').style.display='block'"><i class="fa-solid fa-user-plus"></i> Yangi Xodim Qo'shish</button>
            </div>
            
            <div class="glass-panel" id="add-emp-form" style="display:none; margin-bottom: 20px; padding: 30px;">
                <h3 style="margin-bottom: 20px; color: var(--neon-cyan);">Yangi Xodim Sozlamalari (Tizimga Kiritish)</h3>
                <form onsubmit="event.preventDefault(); submitEmployee();" id="empForm">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div>
                            <label style="display:block; margin-bottom:5px; color: var(--text-muted);">To'liq Ismi</label>
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
                        <button type="submit" class="btn-ios btn-neon">Saqlash</button>
                        <button type="button" class="btn-ios" onclick="document.getElementById('add-emp-form').style.display='none'">Bekor qilish</button>
                    </div>
                </form>
            </div>
            
            <div class="glass-panel" style="padding: 30px; text-align: center; color: var(--text-muted);">
                Ayni damda foydalanuvchilar qismi test rejimida ishlayapti. Hozirgi Tizim Egasi: Master Agent.
            </div>
        </div>

        <div id="ai-hub" class="view-section">
            <h2 style="margin-bottom: 25px;">Gemini AI Agentlar Markazi</h2>
            
            <div class="content-row">
                <div class="glass-panel" style="padding: 30px;">
                    <h3 style="margin-bottom: 20px; color: var(--neon-cyan);">Sotuv Agenti Sozlamalari</h3>
                    <p style="color: var(--text-muted); font-size: 14px; margin-bottom: 20px;">
                        Bu agent ijtimoiy tarmoqlar orqali kelgan mijozlar bilan gaplashadi va ularga to'g'ridan-to'g'ri CRM yaratib beradi.
                    </p>
                    
                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 15px 0; border-bottom: 1px solid var(--glass-border);">
                        <div>
                            <b>Avto-Deploy (Serverda papka ochish)</b>
                            <div style="font-size: 12px; color: var(--text-muted);">Mijoz rozi bo'lsa darhol Nginx'da subdomen ochadi</div>
                        </div>
                        <div class="ios-toggle on" onclick="this.classList.toggle('on')"></div>
                    </div>
                    
                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 15px 0;">
                        <div>
                            <b>Chegirma berish huquqi</b>
                            <div style="font-size: 12px; color: var(--text-muted);">Mijoz tortishsa 10% gacha chegirma qila oladi</div>
                        </div>
                        <div class="ios-toggle" onclick="this.classList.toggle('on')"></div>
                    </div>
                    
                    <button class="btn-ios btn-neon" style="width: 100%; margin-top: 20px; padding: 15px;"><i class="fa-solid fa-robot"></i> Agentni Sinab ko'rish</button>
                </div>
            </div>
        </div>

        <div id="billing" class="view-section">
            <h2 style="margin-bottom: 25px;">Kompaniya Moliyasi</h2>
            <div class="glass-panel" style="padding: 50px; text-align: center; color: var(--text-muted);">
                <i class="fa-solid fa-wallet" style="font-size: 40px; margin-bottom: 15px; color: rgba(255,255,255,0.2);"></i>
                <h3>Payme va Click Integratsiyasi</h3>
                <p style="margin-top: 10px;">Bu bo'limda webhook orqali tushgan barcha to'lovlar tarixi ko'rsatiladi. Mijozlar to'lov qilgach avtomatik 'active' holatga o'tadi.</p>
            </div>
            
            <div class="glass-panel" style="padding: 30px; margin-top: 20px;">
                <h3 style="margin-bottom: 20px;">Tranzaksiyalar (Mock)</h3>
                <div class="tenant-row">
                    <div><b>Invoys #1045</b></div>
                    <div style="color: var(--neon-cyan);">150,000 UZS</div>
                    <div>Payme</div>
                    <div>Yangi</div>
                </div>
            </div>
        </div>

        <div id="system-health" class="view-section">
            <h2 style="margin-bottom: 25px;">Server Holati (System Health)</h2>
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
            <h2 style="margin-bottom: 25px;">Xavfsizlik Jurnali (Face ID & Fail2Ban)</h2>
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
            <h2 style="margin-bottom: 25px;">Shablonlar Fabrikasi (Managed Services)</h2>
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
                            <div style="font-size: 11px; color: var(--text-muted); margin-bottom: 8px;">Nima kiritilgan:</div>
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
                                <i class="fa-solid fa-sliders"></i> Boshqarish
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
                    <h3 style="color: var(--neon-cyan); opacity: 0.8;">Yangi Shablon / Sayt</h3>
                    <p style="font-size: 12px; color: var(--text-muted); margin-top: 10px; text-align: center;">Yangi CRM yoki Landing ulaymiz</p>
                </div>
            </div>
        </div>

        <div id="bot-manager" class="view-section">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
                <h2>Telegram Botlar Nazorati</h2>
                <button class="btn-ios btn-neon" onclick="promptAddBot()"><i class="fa-solid fa-plus"></i> Yangi Bot Qo'shish</button>
            </div>
            
            <div class="glass-panel" style="padding: 30px; margin-bottom: 30px;">
                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px;" id="bots-list">
                    @if(isset($telegramBots))
                        @foreach($telegramBots as $bot)
                        <div class="glass-panel" style="padding: 20px; border: 1px solid {{ $bot->is_active ? 'var(--neon-cyan)' : 'var(--neon-pink)' }};">
                            <h4 style="color: var(--neon-cyan); margin-bottom: 5px;">{{ $bot->name }}</h4>
                            <div style="font-size: 11px; color: var(--text-muted); margin-bottom: 15px; font-family: monospace; overflow: hidden; text-overflow: ellipsis;">{{ substr($bot->token, 0, 15) }}...</div>
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                                <span style="font-size: 12px; color: var(--text-muted);">Vazifasi: <b>{{ strtoupper($bot->agent_type) }}</b></span>
                                <div class="ios-toggle {{ $bot->is_active ? 'on' : '' }}" onclick="toggleBot({{ $bot->id }}, {{ $bot->is_active ? 0 : 1 }})"></div>
                            </div>
                            <div style="display: flex; gap: 5px;">
                                <button class="btn-ios" style="flex:1;" onclick="promptEditBot({{ $bot->id }}, '{{ $bot->name }}', '{{ $bot->token }}', '{{ $bot->agent_type }}')"><i class="fa-solid fa-pen"></i></button>
                                <button class="btn-ios" style="flex:1; color: var(--neon-cyan);" onclick="setBotWebhook({{ $bot->id }})" title="Webhookni O'rnatish"><i class="fa-solid fa-link"></i></button>
                                <button class="btn-ios" style="flex:1; color: var(--neon-pink);" onclick="deleteBot({{ $bot->id }})"><i class="fa-solid fa-trash"></i></button>
                            </div>
                        </div>
                        @endforeach
                    @endif
                </div>
            </div>
            
            <div class="glass-panel" style="padding: 30px;">
                <h3 style="margin-bottom: 15px; color: var(--neon-purple);"><i class="fa-solid fa-brain"></i> AI Agentlarni Unifikatsiya Qilish</h3>
                <p style="color: var(--text-muted); font-size: 14px;">Barcha botlarga Gemini AI API biriktirilgan. Yangi bot qo'shganingizda, u avtomatik ravishda tanlangan roli bo'yicha (Sales, Finance, Support) muloqotga kirishadi.</p>
            </div>
        </div>

        <div id="live-chat" class="view-section">
            <h2 style="margin-bottom: 25px;">Qutqaruv Chati (Human Handoff)</h2>
            <div class="content-row">
                <div class="glass-panel" style="padding: 30px; text-align: center; color: var(--text-muted); display: flex; flex-direction: column; align-items: center; justify-content: center; height: 400px;">
                     <i class="fa-brands fa-whatsapp" style="font-size: 50px; color: #25D366; margin-bottom: 20px;"></i>
                     <h3>AI eplay olmagan mijozlar shu yerda chiqadi.</h3>
                     <p style="margin-top: 10px;">Hozircha hamma mijozlarga AI javob berib uddalamoqda. Qutqaruvga hojat yo'q.</p>
                </div>
            </div>
        </div>

        <div id="finance" class="view-section">
            <h2 style="margin-bottom: 25px;">Kompaniya Moliya & Sotuv Markazi</h2>
            
            <!-- Dashboard for Finance -->
            <div class="stats-grid" style="margin-bottom: 30px;">
                <div class="glass-panel stat-card">
                    <div style="font-size: 14px; opacity: 0.7;">Jami Tushum</div>
                    <div style="font-size: 28px; font-weight: bold; color: var(--neon-cyan);">45,200,000 UZS</div>
                    <div style="font-size: 12px; color: #0f0; margin-top: 5px;">+12% o'tgan oydan</div>
                </div>
                <div class="glass-panel stat-card">
                    <div style="font-size: 14px; opacity: 0.7;">Yangi Leadlar (Botdan)</div>
                    <div style="font-size: 28px; font-weight: bold; color: var(--neon-purple);">{{ count($leads ?? []) }}ta</div>
                    <div style="font-size: 12px; opacity: 0.5;">Bugungi o'sish</div>
                </div>
                <div class="glass-panel stat-card">
                    <div style="font-size: 14px; opacity: 0.7;">Faol Shartnomalar</div>
                    <div style="font-size: 28px; font-weight: bold; color: var(--neon-pink);">{{ $activeTenantsCount ?? 0 }}ta</div>
                </div>
            </div>

            <!-- Leads and Sales Section -->
            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px;">
                <!-- Leads Table -->
                <div class="glass-panel" style="padding: 25px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                        <h3><i class="fa-solid fa-bolt" style="color: var(--neon-cyan);"></i> Yangi So'rovlar (Leads)</h3>
                    </div>
                    <div style="overflow-x: auto;">
                        <table style="width: 100%; border-collapse: collapse;">
                            <thead>
                                <tr style="text-align: left; border-bottom: 1px solid var(--glass-border);">
                                    <th style="padding: 15px; font-size: 13px; opacity: 0.6;">Mijoz</th>
                                    <th style="padding: 15px; font-size: 13px; opacity: 0.6;">Telegram / Tel</th>
                                    <th style="padding: 15px; font-size: 13px; opacity: 0.6;">Qiziqish</th>
                                    <th style="padding: 15px; font-size: 13px; opacity: 0.6;">Holat</th>
                                    <th style="padding: 15px; font-size: 13px; opacity: 0.6;">Amal</th>
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
                                            {{ strtoupper($lead->status) }}
                                        </span>
                                    </td>
                                    <td style="padding: 15px;">
                                        <select onchange="updateLeadStatus({{ $lead->id }}, this.value)" style="background: #1a1a1a; border: 1px solid var(--glass-border); color: white; border-radius: 5px; padding: 2px 5px; font-size: 12px;">
                                            <option value="yangi" {{ $lead->status == 'yangi' ? 'selected' : '' }}>Yangi</option>
                                            <option value="jarayonda" {{ $lead->status == 'jarayonda' ? 'selected' : '' }}>Harakatda</option>
                                            <option value="sotildi" {{ $lead->status == 'sotildi' ? 'selected' : '' }}>Sotildi</option>
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
                                <button onclick="openUploadModal({{ $tenant->id }}, 'files')" class="btn-ios" style="padding: 5px 10px; font-size: 11px; flex: 1;">Fayllar</button>
                            </div>
                        </div>
                        @endforeach
                    </div>
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

    <!-- Bot Modal -->
    <div id="botModal" class="modal-overlay" onclick="if(event.target === this) closeBotModal()">
        <div class="glass-modal">
            <div class="modal-title">
                <i class="fa-brands fa-telegram"></i> <span id="botModalHeader">Yangi Telegram Bot</span>
            </div>
            <form id="botForm">
                <input type="hidden" id="edit_bot_id">
                <div class="form-group">
                    <label>Bot Nomi</label>
                    <input type="text" id="bot_name" class="form-control" placeholder="Masalan: ITcloud Sales Bot" required>
                </div>
                <div class="form-group">
                    <label>Telegram API Token</label>
                    <input type="text" id="bot_token" class="form-control" placeholder="123456:ABC-DEF..." required>
                </div>
                <div class="form-group">
                    <label>AI Agent Rollari (Vazifasi)</label>
                    <select id="bot_agent_type" class="form-control">
                        <option value="sales">Sales (Sotuvchi)</option>
                        <option value="finance">Finance (Hisobchi)</option>
                        <option value="support">Support (Texnik Yordam)</option>
                        <option value="custom">Custom (Maxsus)</option>
                    </select>
                </div>
                <div class="modal-actions">
                    <button type="submit" class="btn-ios btn-neon" style="flex: 2;">Botni Faollashtirish</button>
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
                    simulateAIAction(id ? "Mijoz ma'lumotlari yangilandi." : "Yangi loyiha muvaffaqiyatli ishga tushirildi!");
                    closeTenantModal();
                    setTimeout(() => location.reload(), 1500);
                }
            } catch(e) { alert("Xato yuz berdi"); }
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
                    alert("Yangi xodim qo'shildi! Endi ular Face ID rasm orqali ishonchli kira oladilar.");
                    location.reload();
                } else {
                    alert("Pochtada yoki bazada muammo. " + (data.message || ''));
                }
            } catch(e) { alert("Xatolik. Rasm hajmi juda katta bo'lishi mumkin."); }
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
                    simulateAIAction(id ? "Xizmat ma'lumotlari yangilandi!" : "Yangi shablon tizimga muvaffaqiyatli ulandi!");
                    closeTemplateModal();
                    setTimeout(() => location.reload(), 1500);
                }
            } catch(e) { alert("Server bilan aloqa xatosi"); }
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
            if(!confirm("Haqiqatan ham o'chirmoqchimisiz?")) return;
            try {
                await fetch(`${API_PREFIX}/templates/${id}`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
                });
                location.reload();
            } catch(e) { }
        }

        // Bot Management UI
        function openBotModal(id = null, name = '', token = '', type = 'sales') {
            document.getElementById('edit_bot_id').value = id || '';
            document.getElementById('bot_name').value = name;
            document.getElementById('bot_token').value = token;
            document.getElementById('bot_agent_type').value = type;
            document.getElementById('botModalHeader').innerText = id ? "Botni Tahrirlash" : "Yangi AI Bot Qo'shish";
            document.getElementById('botModal').classList.add('active');
        }

        function closeBotModal() {
            document.getElementById('botModal').classList.remove('active');
        }

        document.getElementById('botForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            let id = document.getElementById('edit_bot_id').value;
            let name = document.getElementById('bot_name').value;
            let token = document.getElementById('bot_token').value;
            let agent_type = document.getElementById('bot_agent_type').value;

            let url = id ? `${API_PREFIX}/bots/${id}` : `${API_PREFIX}/bots`;
            let method = id ? 'PUT' : 'POST';

            try {
                let res = await fetch(url, {
                    method: method,
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                    body: JSON.stringify({ name, token, agent_type, is_active: 1 })
                });
                let data = await res.json();
                if(data.status === 'success') {
                    simulateAIAction(id ? "Bot muvaffaqiyatli yangilandi." : "Yangi AI Bot faollashtirildi!");
                    closeBotModal();
                    setTimeout(() => location.reload(), 1500);
                }
            } catch(e) { }
        });

        function promptAddBot() {
            openBotModal();
        }

        function promptEditBot(id, name, token, type) {
            openBotModal(id, name, token, type);
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
            if(!confirm("Botni o'chirib tashlamoqchimisiz?")) return;
            try {
                await fetch(`${API_PREFIX}/bots/${id}`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
                });
                location.reload();
            } catch(e) { }
        }

        // Yon Menyuni (Tablarni) almashtirish mantig'i
        function switchTab(event, tabId) {
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
            if(targetSection) {
                targetSection.classList.add('active');
            }
            
            if(event && event.currentTarget) {
                event.currentTarget.classList.add('active');
            }
        }

        // Dynamic Island (Sun'iy Intelekt xabarnomasi) animatsiyasi
        function simulateAIAction(customText = null) {
            const island = document.getElementById('dynamicIsland');
            const text = document.getElementById('islandText');
            
            island.classList.add('active');
            text.innerHTML = customText || "Agent Gemini: Mijoz bilan suhbat qilinmoqda...";
            text.style.color = "var(--neon-cyan)";
            
            // 3 soniyadan keyin qaytish
            setTimeout(() => {
                if(!customText) {
                    text.innerHTML = "To'lov qabul qilindi!";
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

        window.onload = function() {
            inactivityTime();
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
