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
        
        <div class="nav-item active" onclick="switchTab('dashboard')">
            <i class="fa-solid fa-border-all"></i> Dashboard
        </div>
        <div class="nav-item" onclick="switchTab('tenants')">
            <i class="fa-solid fa-users"></i> CRM Mijozlar
        </div>
        <div class="nav-item" onclick="switchTab('employees')">
            <i class="fa-solid fa-user-shield"></i> Xodimlar / Admin
        </div>
        <div class="nav-item" onclick="switchTab('ai-hub')">
            <i class="fa-solid fa-brain"></i> AI Agent Hub
        </div>
        <div class="nav-item" onclick="switchTab('system-health')">
            <i class="fa-solid fa-server"></i> Server Holati
        </div>
        <div class="nav-item" onclick="switchTab('security-logs')">
            <i class="fa-solid fa-shield-halved"></i> Xavfsizlik Jurnali
        </div>
        <div class="nav-item" onclick="switchTab('templates')">
            <i class="fa-solid fa-layer-group"></i> Shablonlar
        </div>
        <div class="nav-item" onclick="switchTab('live-chat')">
            <i class="fa-solid fa-headset"></i> Qutqaruv Chati
        </div>
        <div class="nav-item" onclick="switchTab('billing')">
            <i class="fa-solid fa-wallet"></i> To'lovlar
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
            <h2 style="margin-bottom: 25px;">Shablonlar Fabrikasi</h2>
            <div class="stats-grid">
                @if(isset($templates))
                    @foreach($templates as $template)
                    <div class="glass-panel stat-card" style="padding: 30px;">
                        <h3 style="color: var(--neon-cyan); margin-bottom: 10px;">{{ $template->name }}</h3>
                        <p style="color: var(--text-muted); font-size: 14px; margin-bottom: 20px; line-height: 1.5;">{{ $template->description }}</p>
                        <div style="font-size: 24px; font-weight: bold; margin-bottom: 20px;">{{ number_format($template->price, 0) }} UZS</div>
                        <div style="display: flex; gap: 10px;">
                            <button class="btn-ios btn-neon" style="flex:1;">Tahrirlash</button>
                            <a href="{{ $template->preview_url }}" target="_blank" class="btn-ios" style="display: flex; align-items: center; justify-content: center; background: rgba(255,255,255,0.1); color: white; border:1px solid #fff;"><i class="fa-solid fa-eye"></i></a>
                        </div>
                    </div>
                    @endforeach
                @endif
                <!-- Add new template card -->
                <div class="glass-panel stat-card" style="display: flex; flex-direction: column; align-items: center; justify-content: center; cursor: pointer; border: 2px dashed var(--glass-border); background: transparent;">
                    <i class="fa-solid fa-plus" style="font-size: 40px; color: var(--text-muted); margin-bottom: 15px;"></i>
                    <h3 style="color: var(--text-muted);">Yangi shablon qo'shish</h3>
                </div>
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

    </main>

    <script>
        const API_PREFIX = '/api';
        
        // Mijoz qo'shish prompt
        async function promptAddTenant() {
            let company = prompt("Yangi kompaniya/mijoz nomini kiriting:");
            if(!company) return;
            let domain = prompt("Mijoz uchun qisqa domen kiriting (masalan: newcrm.itcloud.uz):");
            if(!domain) return;
            
            try {
                let res = await fetch(`${API_PREFIX}/tenants`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                    body: JSON.stringify({ company_name: company, domain: domain })
                });
                let data = await res.json();
                if(data.status === 'success') location.reload();
            } catch(e) { alert("Xatolik"); }
        }

        async function promptEditTenant(id, oldCompany, oldDomain) {
            let company = prompt("Kompaniya nomi:", oldCompany);
            if(!company) return;
            let domain = prompt("Domen nomi:", oldDomain);
            if(!domain) return;
            
            try {
                let res = await fetch(`${API_PREFIX}/tenants/${id}`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                    body: JSON.stringify({ company_name: company, domain: domain })
                });
                let data = await res.json();
                if(data.status === 'success') location.reload();
            } catch(e) { }
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

        async function promptSubscription(id) {
            let plan = prompt("Tarif muddatini tanlang: (Kun yozing, masalan 30, aylikka yoxud 'infinity' cheksiz deb yozing)", "30");
            if(!plan) return;
            try {
                let res = await fetch(`${API_PREFIX}/tenants/${id}/subscription`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                    body: JSON.stringify({ duration: plan, amount: 150000 })
                });
                let data = await res.json();
                if(data.status === 'success') location.reload();
            } catch(e) { }
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
            document.getElementById(tabId).classList.add('active');
            event.currentTarget.classList.add('active');
        }

        // Dynamic Island (Sun'iy Intelekt xabarnomasi) animatsiyasi
        function simulateAIAction() {
            const island = document.getElementById('dynamicIsland');
            const text = document.getElementById('islandText');
            
            island.classList.add('active');
            text.innerHTML = "Agent Gemini: Mijoz bilan suhbat qilinmoqda...";
            text.style.color = "var(--neon-cyan)";
            
            // 3 soniyadan keyin qaytish
            setTimeout(() => {
                text.innerHTML = "To'lov qabul qilindi!";
                text.style.color = "var(--neon-purple)";
                
                setTimeout(() => {
                    island.classList.remove('active');
                    text.innerHTML = "Obsidian OS v1";
                    text.style.color = "var(--text-main)";
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
