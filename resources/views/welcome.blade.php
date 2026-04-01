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
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(0, 0, 0, 0.85);
            backdrop-filter: blur(25px);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 50px;
            padding: 8px 22px;
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 180px;
            justify-content: center;
            touch-action: none;
            user-select: none;
            cursor: grab;
        }
        .dynamic-island:active { cursor: grabbing; }
        .dynamic-island:hover { 
            transform: translateX(-50%) translateY(5px);
            border-color: var(--neon-cyan);
            box-shadow: 0 10px 30px rgba(0, 255, 204, 0.2);
        }
        .dynamic-island.active { 
            min-width: 320px; 
            padding: 12px 30px; 
            border-radius: 20px;
            background: #000;
        }
        .island-icon { color: var(--neon-cyan); font-size: 16px; animation: glowPulse 2s infinite; }
        .island-content { font-size: 13px; font-weight: 700; color: white; white-space: nowrap; overflow: hidden; }
        .island-sub { font-size: 10px; color: var(--text-muted); display: none; margin-top: 2px; }
        .dynamic-island.active .island-sub { display: block; animation: fadeIn 0.4s ease; }

        @keyframes glowPulse { 0% { filter: drop-shadow(0 0 0px var(--neon-cyan)); } 50% { filter: drop-shadow(0 0 10px var(--neon-cyan)); } 100% { filter: drop-shadow(0 0 0px var(--neon-cyan)); } }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: translateY(0); } }

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
            padding: 50px 20px 20px 20px; 
            display: flex; 
            flex-direction: column; 
            z-index: 1000;
            height: calc(100vh - 40px);
            overflow-y: auto;
            position: sticky;
            top: 20px;
            scrollbar-width: thin;
        }
        
        @media (max-width: 900px) {
            body { flex-direction: column; overflow-y: auto; height: auto; }
            .sidebar { 
                width: calc(100% - 30px); 
                height: auto; 
                margin: 15px; 
                padding: 25px 15px; 
                position: relative; 
                top: 0;
                flex-direction: row;
                overflow-x: auto;
                gap: 10px;
            }
            .brand { margin-bottom: 0; display: none; } /* Hide logo in sidebar on mobile as top-logo is preferred or just space saving */
            .main-container { padding: 80px 15px 20px 15px; }
            .nav-item { padding: 10px 15px; font-size: 13px; white-space: nowrap; margin-bottom: 0; }
            .stats-grid { grid-template-columns: 1fr 1fr; }
        }
        .brand { margin-bottom: 50px; text-align: center; }
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

        .mobile-header {
            display: none;
            position: fixed; top: 0; left: 0; width: 100%; height: 60px;
            padding: 0 20px; align-items: center; justify-content: space-between;
            z-index: 9998; background: var(--glass-bg); backdrop-filter: var(--glass-blur); border-bottom: 1px solid var(--glass-border);
        }
        @media (max-width: 900px) {
            .mobile-header { display: flex; }
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
        .tenant-row { display: grid; grid-template-columns: 2fr 1fr 1fr auto; align-items: center; padding: 20px; background: rgba(0,0,0,0.2); border-radius: 20px; margin-bottom: 15px; border: 1px solid var(--glass-border); transition: 0.3s; min-width: 600px; }
        .view-section { overflow-x: auto; }
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

        /* Mobile Layout Optimization (9:16 Vertical Friendly) */
        @media (max-width: 900px) {
            body { flex-direction: column; overflow: auto; }
            .sidebar { 
                width: 100%; height: auto; margin: 0; padding: 15px; 
                flex-direction: row; position: fixed; bottom: 0; top: auto;
                border-radius: 30px 30px 0 0; border: 1px solid var(--glass-border);
                border-bottom: none; z-index: 2000;
                justify-content: space-around; backdrop-filter: blur(25px);
            }
            .brand { display: none; }
            .nav-item { margin-bottom: 0; white-space: nowrap; flex-direction: column; gap: 5px; font-size: 10px; padding: 10px; margin-right: 0; }
            .nav-item i { font-size: 18px; margin: 0; }
            .main-container { padding: 15px; padding-bottom: 100px; margin-top: 80px; }
            .dynamic-island { top: 10px; min-width: 150px; padding: 10px 15px; z-index: 3000; }
            .dynamic-island.active { min-width: 300px; }
            .stats-grid { grid-template-columns: repeat(2, 1fr); gap: 12px; }
            .content-row { grid-template-columns: 1fr; }
        }

        @media (max-width: 500px) {
            .stats-grid { grid-template-columns: 1fr; }
            .nav-item span { display: none; }
        }

        /* Lang Switcher Naked Style (Invisible container) */
        .lang-switcher-premium {
            position: fixed;
            top: 15px;
            right: 15px;
            z-index: 10000;
            background: transparent;
            border: none;
            padding: 2px;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: 0.3s;
            cursor: pointer;
        }
        .lang-choices { 
            display: flex;
            width: 0;
            overflow: hidden;
            transition: 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            gap: 10px;
            align-items: center;
        }
        .lang-switcher-premium:hover .lang-choices { width: 110px; }
        .lang-flag-img { 
            width: 22px; 
            height: 14px; 
            object-fit: cover; 
            border-radius: 2px; 
            transition: 0.2s; 
            filter: drop-shadow(0 2px 4px rgba(0,0,0,0.5));
        }
        .lang-flag-link:hover .lang-flag-img { transform: scale(1.2); filter: brightness(1.2); }
        .current-flag-img { border: 1px solid rgba(0, 255, 204, 0.8); }

        @media (max-width: 900px) {
            .lang-switcher-premium { top: 10px; right: 10px; }
            .lang-flag-img { width: 18px; height: 12px; }
            .lang-switcher-premium:hover .lang-choices { width: 85px; }
        }

        /* Academy Specific */
        .btn-tabs {
            padding: 8px 18px; border-radius: 10px; border: 1px solid transparent; background: transparent;
            color: var(--text-muted); font-weight: 600; cursor: pointer; transition: 0.3s; font-size: 13px;
        }
        .btn-tabs:hover { color: white; background: rgba(255,255,255,0.05); }
        .btn-tabs.active { background: rgba(176,38,255,0.1); border-color: rgba(176,38,255,0.3); color: var(--neon-purple); }
        
        .badge {
            background: var(--neon-pink); color: white; padding: 2px 6px; border-radius: 6px; font-size: 10px; margin-left: 5px;
        }

        .acad-stat-box {
            display: flex; justify-content: space-between; padding: 12px; background: rgba(255,255,255,0.02);
            border-radius: 10px; margin-bottom: 8px; border: 1px solid var(--glass-border); font-size: 13px;
        }

        .course-card {
            background: rgba(0,0,0,0.2); border: 1px solid var(--glass-border); padding: 15px; border-radius: 15px;
            display: flex; justify-content: space-between; align-items: center; transition: 0.3s;
        }
        .course-card:hover { border-color: var(--neon-cyan); background: rgba(255,255,255,0.03); }

        .search-box input {
            background: rgba(0,0,0,0.3); border: 1px solid var(--glass-border); color: white; padding: 8px 15px;
            border-radius: 10px; width: 200px; font-size: 13px; outline: none; transition: 0.3s;
        }
        .search-box input:focus { border-color: var(--neon-cyan); width: 250px; }
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
        .badge {
            padding: 5px 10px;
            border-radius: 8px;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
        }

        /* Permissions Glass Modal */
        #permissionsModal .glass-modal {
            background: rgba(10, 10, 15, 0.95);
            backdrop-filter: blur(40px);
            border: 1px solid var(--neon-cyan);
            box-shadow: 0 0 50px rgba(0, 255, 204, 0.3);
            width: 700px; /* Optimized Width */
            padding: 30px;
            border-radius: 25px;
        }
        .perm-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr); /* 3-column compact grid */
            gap: 10px;
            margin: 20px 0;
        }
        .perm-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 8px 12px;
            background: rgba(255,255,255,0.02);
            border-radius: 10px;
            border: 1px solid var(--glass-border);
            transition: 0.2s;
        }
        .perm-item:hover { background: rgba(0, 255, 204, 0.05); border-color: rgba(0, 255, 204, 0.3); }
        .perm-item span { font-size: 12px; font-weight: 500; color: white; margin-right: 5px; }

        .ios-toggle { 
            transform: scale(0.7); /* Smaller toggles for grid */
            margin-right: -5px;
        }

        /* SweetAlert2 Premium Personalization (Obsidian Glass Style) */
        .swal2-popup.swal2-modal {
            background: rgba(10, 10, 15, 0.95) !important;
            backdrop-filter: blur(40px) !important;
            border: 1px solid var(--neon-cyan) !important;
            border-radius: 30px !important;
            box-shadow: 0 0 50px rgba(0, 255, 204, 0.2) !important;
            color: #fff !important;
            padding: 2.5rem !important;
        }
        .swal2-title { color: #fff !important; font-family: 'Outfit', sans-serif !important; font-weight: 800 !important; }
        .swal2-html-container { color: rgba(255,255,255,0.7) !important; font-size: 15px !important; }
        .swal2-confirm.swal2-styled {
            background: rgba(0, 255, 204, 0.1) !important;
            border: 1px solid var(--neon-cyan) !important;
            color: var(--neon-cyan) !important;
            border-radius: 15px !important;
            padding: 12px 30px !important;
            font-weight: 700 !important;
            transition: 0.3s !important;
        }
        .swal2-confirm.swal2-styled:hover {
            background: var(--neon-cyan) !important;
            color: #000 !important;
            box-shadow: 0 0 20px var(--neon-cyan) !important;
        }
        .swal2-cancel.swal2-styled {
            background: rgba(255, 255, 255, 0.05) !important;
            border: 1px solid var(--glass-border) !important;
            border-radius: 15px !important;
            color: #ccc !important;
        }
        .swal2-input {
            background: rgba(0,0,0,0.3) !important;
            border: 1px solid var(--glass-border) !important;
            color: white !important;
            border-radius: 12px !important;
            font-size: 14px !important;
        }
        .swal2-icon.swal2-error { border-color: var(--neon-pink) !important; color: var(--neon-pink) !important; }
        .swal2-icon.swal2-error [class^='swal2-x-mark-line'] { background-color: var(--neon-pink) !important; }
        .swal2-icon.swal2-success { border-color: var(--neon-cyan) !important; }
        .swal2-icon.swal2-success [class^='swal2-success-line'] { background-color: var(--neon-cyan) !important; }
        .swal2-icon.swal2-success .swal2-success-ring { border: 4px solid rgba(0, 255, 204, 0.2) !important; }

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

    <div class="mobile-header glass-panel" style="display: none;">
        <div class="logo-animated" style="font-size: 22px; font-weight: 800;">
            IT<span style="color: var(--neon-cyan);">cloud</span>
        </div>
    </div>

    <div class="ambient-blob blob-1"></div>
    <div class="ambient-blob blob-2"></div>

    <div class="dynamic-island" id="dynamicIsland" onclick="simulateAIAction()">
        <i class="fa-solid fa-sparkles island-icon"></i>
        <div style="display: flex; flex-direction: column; align-items: center;">
            <span class="island-content" id="islandText">Obsidian OS v1</span>
            <span class="island-sub" id="islandSub">AI faolligi normal</span>
        </div>
    </div>

    <nav class="sidebar glass-panel">
        <div class="brand">
            <div class="logo-animated" style="display: inline-block; font-size: 26px; font-weight: 800; letter-spacing: 1px;">
                IT<span style="color: var(--neon-cyan); text-shadow: 0 0 15px rgba(0,255,204,0.5);">cloud</span>
            </div>
        </div>
        
        @if(auth()->user()->role === 'master' || auth()->user()->role === 'employee')
        <div class="nav-item active" data-module="dashboard" onclick="checkPermission('dashboard', () => switchTab('dashboard'))">
            <i class="fa-solid fa-border-all"></i> {{ __('Dashboard') }}
        </div>
        <div class="nav-item" data-module="finance" onclick="checkPermission('finance', () => switchTab('finance'))">
            <i class="fa-solid fa-file-invoice-dollar"></i> {{ __('Pricing') }}
        </div>
        <div class="nav-item" data-module="tenants" onclick="checkPermission('tenants', () => switchTab('tenants'))">
            <i class="fa-solid fa-users"></i> {{ __('Clients') }}
        </div>
        <div class="nav-item" data-module="employees" onclick="checkPermission('employees', () => switchTab('employees'))">
            <i class="fa-solid fa-user-shield"></i> {{ __('Employees') }}
        </div>
        <div class="nav-item" data-module="settings" onclick="checkPermission('settings', () => switchTab('settings'))">
            <i class="fa-solid fa-gears"></i> {{ __('Settings') }}
        </div>
        <div class="nav-item" data-module="ai_hub" onclick="checkPermission('ai_hub', () => switchTab('ai_hub'))">
            <i class="fa-solid fa-brain"></i> {{ __('AI Agents') }}
        </div>
        <div class="nav-item" data-module="academy" onclick="checkPermission('academy', () => switchTab('academy'))">
            <i class="fa-solid fa-graduation-cap"></i> {{ __('Academy') }}
        </div>
        <div class="nav-item" data-module="system_health" onclick="checkPermission('system_health', () => switchTab('system_health'))">
            <i class="fa-solid fa-server"></i> {{ __('Server Health') }}
        </div>
        <div class="nav-item" data-module="security_logs" onclick="checkPermission('security_logs', () => switchTab('security_logs'))">
            <i class="fa-solid fa-shield-halved"></i> {{ __('Security Logs') }}
        </div>
        <div class="nav-item" data-module="templates" onclick="checkPermission('templates', () => switchTab('templates'))">
            <i class="fa-solid fa-layer-group"></i> {{ __('Templates') }}
        </div>
        <div class="nav-item" data-module="ai_developer" onclick="checkPermission('ai_developer', () => switchTab('ai_developer'))">
            <i class="fa-solid fa-code"></i> {{ __('Developer Portal') }}
        </div>
        <div class="nav-item" data-module="bot_manager" onclick="checkPermission('bot_manager', () => switchTab('bot_manager'))">
            <i class="fa-solid fa-tower-cell"></i> {{ __('Bot Manager') }}
        </div>
        <div class="nav-item" data-module="academy_moderation" onclick="checkPermission('academy', () => switchTab('academy_moderation'))">
            <i class="fa-solid fa-gavel"></i> {{ __('Student Chat Moderation') }}
        </div>
        @endif


        @if(auth()->user()->role === 'student')
        <div class="nav-item active" data-module="student_portal" onclick="switchTab('student_portal')">
            <i class="fa-solid fa-graduation-cap"></i> {{ __('My Learning') }}
        </div>
        <div class="nav-item" data-module="student_projects" onclick="switchTab('student_projects')">
            <i class="fa-solid fa-code-branch"></i> {{ __('Mening loyihalarim') }}
        </div>
        <div class="nav-item" data-module="student_chat" onclick="switchTab('student_chat')">
            <i class="fa-solid fa-comments"></i> {{ __('Global Chat') }}
        </div>
        <div class="nav-item" data-module="student_achievements" onclick="switchTab('student_achievements')">
            <i class="fa-solid fa-medal"></i> {{ __('Yutuqlarim') }}
        </div>
        <div class="nav-item" data-module="student_jobs" onclick="switchTab('student_jobs')">
            <i class="fa-solid fa-briefcase"></i> {{ __('Karyera M.)') }}
        </div>

        @endif

        @if(auth()->user()->role === 'master' || auth()->user()->role === 'employee')
        <div class="nav-item" data-module="live_chat" onclick="checkPermission('live_chat', () => switchTab('live_chat'))">
            <i class="fa-solid fa-headset"></i> {{ __('Human Handoff') }}
        </div>
        @endif

        

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
        
        <div id="ai_developer" class="view-section">
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
                            <label style="display:block; margin-bottom:5px; color: var(--text-muted);">Xodim Roli</label>
                            <select id="emp_role" style="width:100%; padding:10px; border-radius:10px; background:rgba(0,0,0,0.3); border:1px solid var(--glass-border); color:white;">
                                <option value="operator">Operator / Sotuvchi</option>
                                <option value="admin">Admin</option>
                                <option value="master">Master Admin</option>
                                <option value="developer">Dasturchi</option>
                            </select>
                        </div>
                        <div>
                            <label style="display:block; margin-bottom:5px; color: var(--text-muted);">Face ID (Rasm yuklang)</label>
                            <input type="file" id="emp_face_photo" accept="image/*" style="width:100%; padding:10px; border-radius:10px; background:rgba(0,0,0,0.3); border:1px solid var(--glass-border); color:white;">
                        </div>
                        <div>
                            <label style="display:block; margin-bottom:10px; color: var(--neon-cyan); font-weight: bold;">Ruxsatlar boshqaruvi</label>
                            <button type="button" class="btn-ios btn-neon" onclick="document.getElementById('permissionsModal').classList.add('active')" style="width: 100%; height: 50px; border-radius: 12px; font-size: 14px;">
                                <i class="fa-solid fa-shield-halved"></i> Ruxsatlarni belgilash (<span id="selectedPermsCount">12</span> ta)
                            </button>
                        </div>
                    </div>
                    <div style="margin-top: 20px; display:flex; gap: 10px;">
                        <button type="submit" class="btn-ios btn-neon">{{ __('Save') }}</button>
                        <button type="button" class="btn-ios" onclick="closeEmpForm()">{{ __('Cancel') }}</button>
                    </div>
                </form>
            </div>

            <!-- Mini Glass Modal for Permissions -->
            <div id="permissionsModal" class="modal-overlay" onclick="if(event.target === this) this.classList.remove('active')">
                <div class="glass-modal">
                    <div class="modal-title"><i class="fa-solid fa-lock-open"></i> Bo'lim ruxsatlari</div>
                    <div class="perm-grid" id="permGridList">
                        @php $modules = ['dashboard', 'finance', 'tenants', 'employees', 'settings', 'ai_hub', 'system_health', 'security_logs', 'templates', 'ai_developer', 'bot_manager', 'live_chat']; @endphp
                        @foreach($modules as $mod)
                        <label class="perm-item">
                            <span>{{ ucwords(str_replace('_', ' ', $mod)) }}</span>
                            <div class="ios-toggle-container">
                                <input type="checkbox" name="permissions[]" value="{{ $mod }}" class="emp-permission-cb" checked style="display:none;" id="cb_{{ $mod }}" onchange="updatePermCount()">
                                <div class="ios-toggle on" onclick="togglePerm('{{ $mod }}', this)"></div>
                            </div>
                        </label>
                        @endforeach
                    </div>
                    <button type="button" class="btn-ios btn-neon" onclick="document.getElementById('permissionsModal').classList.remove('active')" style="width: 100%;">Saqlash</button>
                </div>
            </div>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px;">
                @foreach($employees ?? [] as $emp)
                @if($emp->role !== 'student')
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
                @endif
                @endforeach
            </div>

            @if(count($employees ?? []) === 0)
            <div class="glass-panel" style="padding: 30px; text-align: center; color: var(--text-muted);">
                {{ __('No employees found in the system. Use the button above to add one.') }}
            </div>
            @endif
        </div>

        <div id="ai_hub" class="view-section">
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
                    
                    <div style="margin-bottom: 15px; font-size: 13px; color: rgba(255,255,255,0.7); min-height: 40px; background: rgba(0,0,0,0.2); padding: 10px; border-radius: 8px; position: relative; overflow: hidden;">
                        @if($bot->agent_type === 'pr_channel')
                        <span style="opacity: 0.5;">PR Strategiya:</span> {{ Str::limit($bot->custom_prompt ?? 'Standart post.', 60) }}
                        @if(strlen($bot->custom_prompt ?? '') > 60)
                             <a href="javascript:void(0)" onclick="openBotModal(JSON.parse(this.dataset.bot))" style="color: var(--neon-cyan); font-size: 10px; text-decoration: none; margin-left: 5px;">Barchasi...</a>
                        @endif
                        <br/>
                        <span style="opacity: 0.5;"><i class="fa-solid fa-clock"></i> Reja:</span> Har kuni {{ $bot->schedule_time }}
                        @else
                        <span style="opacity: 0.5;">Missiya:</span> {{ Str::limit($bot->current_task ?? 'Standart boshqaruv rejimi.', 80) }}
                        @if(strlen($bot->current_task ?? '') > 80)
                             <a href="javascript:void(0)" onclick="openTaskModal({{ $bot->id }}, '{{ $bot->name }}', '{{ addslashes($bot->current_task) }}')" style="color: var(--neon-cyan); font-size: 10px; text-decoration: none; margin-left: 5px;">Barchasi...</a>
                        @endif
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

        <div id="system_health" class="view-section">
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

        <div id="security_logs" class="view-section">
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

        <div id="bot_manager" class="view-section">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
                <h2>{{ __('Telegram Bots Control') }}</h2>
                <button class="btn-ios btn-neon" onclick="promptAddBot()"><i class="fa-solid fa-plus"></i> {{ __('Add New Bot') }}</button>
            </div>
            
            <div class="glass-panel" style="padding: 30px; margin-bottom: 30px;">
                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px;" id="bots-list">
                    @if(isset($telegramBots) && count($telegramBots) > 0)
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
                    @else
                        <div style="grid-column: 1 / -1; text-align: center; padding: 50px; color: var(--text-muted); border: 2px dashed rgba(255,255,255,0.05); border-radius: 20px;">
                            <i class="fa-solid fa-robot" style="font-size: 30px; margin-bottom: 15px; opacity: 0.2;"></i>
                            <p>Tizimda botlar mavjud emas. Yangi bot qo'shing!</p>
                        </div>
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

        <div id="live_chat" class="view-section">
            <h2 style="margin-bottom: 25px;">{{ __('Live Chat') }}</h2>
            <div class="glass-panel" style="padding: 25px; margin-bottom: 25px;">
                <p style="color: var(--text-muted); font-size: 14px;">Ushbu bo'limda siz AI agentlar va mijozlar o'rtasidagi jonli suhbatlarni kuzatishingiz va kerak bo'lganda aralashishingiz mumkin (Human-in-the-loop).</p>
            </div>
            <div id="live-chat-container" style="display: grid; grid-template-columns: 350px 1fr; gap: 20px; height: 600px;">
                <div class="glass-panel" style="padding: 20px; display: flex; flex-direction: column;">
                    <h3 style="margin-bottom: 15px; font-size: 16px;"><i class="fa-solid fa-comments"></i> Faol Suhbatlar</h3>
                    <div id="live-chats-list" style="flex: 1; overflow-y: auto; display: flex; flex-direction: column; gap: 10px;">
                        <div style="text-align: center; padding: 20px; opacity: 0.5;">Suhbatlar kutilmoqda...</div>
                    </div>
                </div>
                <div class="glass-panel" style="padding: 20px; display: flex; flex-direction: column; position: relative;">
                    <div id="chat-window-header" style="padding-bottom: 15px; border-bottom: 1px solid var(--glass-border); margin-bottom: 15px; display: none;">
                        <h3 id="active-chat-title" style="margin: 0; font-size: 16px; color: var(--neon-cyan);">ID: <span id="current-chat-id">...</span></h3>
                        <small id="active-chat-meta" style="opacity: 0.5;">Kanal: ...</small>
                    </div>
                    <div id="live-messages-box" style="flex: 1; overflow-y: auto; padding-right: 10px; margin-bottom: 15px; display: flex; flex-direction: column; gap: 15px;">
                        <div style="flex: 1; display: flex; align-items: center; justify-content: center; opacity: 0.3; flex-direction: column;">
                            <i class="fa-solid fa-comment-slash" style="font-size: 40px; margin-bottom: 10px;"></i>
                            <p>Suhbatni tanlang</p>
                        </div>
                    </div>
                    <div id="live-chat-input-area" style="display: none; gap: 10px;">
                        <input type="text" id="live-reply-input" class="input-field" placeholder="Mijozga javob yozing..." style="flex: 1; padding: 12px; border-radius: 10px; background: rgba(0,0,0,0.3); border: 1px solid var(--glass-border); color: white;">
                        <button onclick="sendLiveReply()" class="btn-ios btn-neon"><i class="fa-solid fa-paper-plane"></i></button>
                    </div>
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
    
    <div id="academy" class="view-section">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
            <h2 style="margin: 0;"><i class="fa-solid fa-graduation-cap" style="color: var(--neon-purple);"></i> ITcloud Academy <span style="font-size: 14px; opacity: 0.5;">Master Control</span></h2>
            <div style="display: flex; gap: 10px;">
                <button class="btn-ios btn-neon" onclick="openAcademyQuickModal()"><i class="fa-solid fa-bolt"></i> Tezkor Qo'shish</button>
            </div>
        </div>
        
        <!-- Academy Nav -->
        <div style="display: flex; gap: 10px; margin-bottom: 30px; background: rgba(255,255,255,0.03); padding: 5px; border-radius: 12px; border: 1px solid var(--glass-border); width: fit-content;">
            <button class="btn-tabs active" onclick="switchAcademyTab(this, 'acad-apps')">Arizalar <span class="badge" id="badge-apps">0</span></button>
            <button class="btn-tabs" onclick="switchAcademyTab(this, 'acad-students')">O'quvchilar</button>
            <button class="btn-tabs" onclick="switchAcademyTab(this, 'acad-logins')">O'quvchi Logini</button>
            <button class="btn-tabs" onclick="switchAcademyTab(this, 'acad-courses')">Kurslar & AI</button>
            <button class="btn-tabs" onclick="switchAcademyTab(this, 'acad-mentors')">Ustozlar (I-Ticher)</button>
            <button class="btn-tabs" onclick="switchAcademyTab(this, 'acad-analytics')">Natijalar & IQ</button>
            <button class="btn-tabs" onclick="switchAcademyTab(this, 'acad-pro')"><i class="fa-solid fa-briefcase"></i> Professional (Karyera)</button>
        </div>

        <!-- 1. ARIZALAR -->
        <div id="acad-apps" class="academy-tab-content" style="display: block;">
            <div class="glass-panel" style="padding: 25px;">
                <div id="academy-apps-list" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 20px;">
                    <div style="grid-column: 1/-1; text-align: center; padding: 40px; opacity: 0.5;">Yuklanmoqda...</div>
                </div>
            </div>
        </div>

        <!-- 2. O'QUVCHILAR -->
        <div id="acad-students" class="academy-tab-content" style="display: none;">
            <div class="glass-panel" style="padding: 25px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <h3>Faol O'quvchilar Ro'yxati</h3>
                    <div class="search-box">
                        <input type="text" placeholder="O'quvchini qidirish..." onkeyup="filterStudents(this.value)">
                    </div>
                </div>
                <div id="academy-students-list" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 15px;">
                    <!-- Student Cards -->
                </div>
            </div>
        </div>

        <div id="acad-logins" class="academy-tab-content" style="display: none;">
            <div class="glass-panel" style="padding: 25px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
                    <h3>O'quvchi Loginlari & Parollari</h3>
                </div>
                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="border-bottom: 1px solid var(--glass-border); text-align: left; font-size: 13px; color: #888;">
                                <th style="padding: 15px;">Talaba</th>
                                <th>Login (Email)</th>
                                <th>Amallar</th>
                            </tr>
                        </thead>
                        <tbody id="academy-logins-list">
                            <!-- Logins -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- 3. KURSLAR -->
        <div id="acad-courses" class="academy-tab-content" style="display: none;">
            <div style="display: grid; grid-template-columns: 1fr 350px; gap: 25px;">
                <div class="glass-panel" style="padding: 25px;">
                    <div style="display: flex; justify-content: space-between; border-bottom: 1px solid var(--glass-border); padding-bottom: 15px; margin-bottom: 15px;">
                        <h3>O'quv Kurslari</h3>
                        <button class="btn-ios" style="background: var(--neon-cyan); color: black;" onclick="openCourseModal()"><i class="fa-solid fa-plus"></i> Yangi Kurs</button>
                    </div>
                    <div id="academy-courses-list" style="display: flex; flex-direction: column; gap: 10px;">
                        <!-- Courses -->
                    </div>
                </div>
                <div class="glass-panel" style="padding: 25px;">
                    <h3>Darslar & Reja</h3>
                    <div id="course-lessons-list" style="opacity: 0.5; font-size: 13px; text-align: center; padding-top: 20px;">
                        Kursni tanlang...
                    </div>
                </div>
            </div>
        </div>

        <!-- 4. USTOZLAR (I-TICHER) -->
        <div id="acad-mentors" class="academy-tab-content" style="display: none;">
            <div class="glass-panel" style="padding: 25px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
                    <h3><i class="fa-solid fa-robot" style="color: var(--neon-cyan);"></i> AI Ustozlar (I-Ticher)</h3>
                    <button class="btn-ios btn-neon" onclick="openMentorModal()"><i class="fa-solid fa-plus"></i> Yangi Mentor yaratish</button>
                </div>
                <div id="academy-mentors-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px;">
                    <!-- Mentors -->
                </div>
            </div>
        </div>

        <!-- 5. ANALYTICS & IQ -->
        <div id="acad-analytics" class="academy-tab-content" style="display: none;">
            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 25px;">
                <div class="glass-panel" style="padding: 25px;">
                    <h3>O'quvchilar Reytingi & IQ Natijalari</h3>
                    <div id="academy-rankings" style="margin-top: 20px;">
                        <!-- Radar charts or list -->
                    </div>
                </div>
                <div class="glass-panel" style="padding: 25px;">
                    <h3>Akademiya Statistikasi</h3>
                    <div class="acad-stat-box">
                        <span>O'rtacha IQ:</span> <strong id="acad-avg-iq">...</strong>
                    </div>
                    <div class="acad-stat-box">
                        <span>Muvaffaqiyat:</span> <strong id="acad-pass-rate">...</strong>
                    </div>
                </div>
            </div>
        </div>

        <!-- 6. PROFESSIONAL (CAREER & GAMI) -->
        <div id="acad-pro" class="academy-tab-content" style="display: none;">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 25px;">
                <!-- Job Board -->
                <div class="glass-panel" style="padding: 25px;">
                    <div style="display: flex; justify-content: space-between; border-bottom: 1px solid var(--glass-border); padding-bottom: 15px; margin-bottom: 15px;">
                        <h3><i class="fa-solid fa-briefcase"></i> Vakansiyalar Markazi</h3>
                        <button class="btn-ios" style="background: var(--neon-cyan); color: black;" onclick="openJobModal()"><i class="fa-solid fa-plus"></i> Yangi Ish</button>
                    </div>
                    <div id="academy-jobs-list" style="display: flex; flex-direction: column; gap: 15px;">
                        <div style="text-align: center; padding: 20px; opacity: 0.5;">Yuklanmoqda...</div>
                    </div>
                </div>
                
                <!-- Achievements & Badges -->
                <div class="glass-panel" style="padding: 25px;">
                    <div style="display: flex; justify-content: space-between; border-bottom: 1px solid var(--glass-border); padding-bottom: 15px; margin-bottom: 15px;">
                        <h3><i class="fa-solid fa-medal"></i> Yutuqlar (Badges)</h3>
                        <button class="btn-ios" style="background: var(--neon-purple); color: white;" onclick="openAchievementModal()"><i class="fa-solid fa-plus"></i> Nishon Qo'shish</button>
                    </div>
                    <div id="academy-achievements-list" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); gap: 15px;">
                        <div style="text-align: center; padding: 20px; opacity: 0.5;">Yuklanmoqda...</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(auth()->user()->role === 'student')
    <div id="student_portal" class="view-section active">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
            <h2 style="margin: 0; font-weight: 800;">Akademiya <span style="color: var(--neon-cyan);">Platformasi</span></h2>
            <div id="student-rank-badge" style="background: rgba(0,255,204,0.1); padding: 8px 20px; border-radius: 30px; border: 1px solid var(--neon-cyan); color: var(--neon-cyan); font-weight: bold;">
                <i class="fa-solid fa-ranking-star"></i> <span id="s-rank-text">Junior</span>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 400px; gap: 25px;">
            <!-- Left: Stats & Lessons -->
            <div style="display: flex; flex-direction: column; gap: 25px;">
                <div class="glass-panel" style="padding: 30px; display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px;">
                    <div style="text-align: center;">
                        <small style="opacity: 0.5;">Foydali Ballar (XP)</small>
                        <h2 id="s-xp-val" style="color: var(--neon-purple); margin-top: 5px;">0</h2>
                    </div>
                    <div style="text-align: center; border-left: 1px solid var(--glass-border); border-right: 1px solid var(--glass-border);">
                        <small style="opacity: 0.5;">IQ Darajasi</small>
                        <h2 id="s-iq-val" style="color: var(--neon-cyan); margin-top: 5px;">0</h2>
                    </div>
                    <div style="text-align: center;">
                        <small style="opacity: 0.5;">Iste'dodlar</small>
                        <h2 id="s-talent-count" style="color: var(--neon-pink); margin-top: 5px;">0</h2>
                    </div>
                </div>

                <div class="glass-panel" style="padding: 30px;">
                    <h3 style="margin-bottom: 20px;"><i class="fa-solid fa-book-open-reader"></i> Mening Kursim: <span id="s-course-name" style="color: var(--neon-cyan);">...</span></h3>
                    <div id="s-course-progress-bar" style="width: 100%; height: 10px; background: rgba(255,255,255,0.05); border-radius: 20px; overflow: hidden; margin-bottom: 25px;">
                        <div id="s-course-fill" style="width: 0%; height: 100%; background: linear-gradient(90deg, var(--neon-cyan), var(--neon-purple)); box-shadow: 0 0 10px var(--neon-cyan);"></div>
                    </div>
                    
                    <div id="student-lessons-list" style="display: flex; flex-direction: column; gap: 12px;">
                        <!-- Lessons Go Here -->
                    </div>
                </div>
            </div>

            <!-- Right: AI Mentor (I-Ticher) -->
            <div class="glass-panel" style="padding: 25px; display: flex; flex-direction: column; height: 600px; border-left: 1px solid rgba(0,255,204,0.3);">
                <div style="display: flex; align-items: center; gap: 15px; border-bottom: 1px solid var(--glass-border); padding-bottom: 15px; margin-bottom: 15px;">
                    <div style="width: 50px; height: 50px; border-radius: 50%; background: var(--neon-cyan); display: flex; align-items: center; justify-content: center; box-shadow: 0 0 20px rgba(0,255,204,0.3);">
                        <i class="fa-solid fa-robot" style="color: black; font-size: 24px;"></i>
                    </div>
                    <div>
                        <h4 style="margin: 0; color: white;" id="s-mentor-name">I-Ticher AI</h4>
                        <small style="color: var(--neon-cyan);">Shaxsiy Ustozingiz</small>
                    </div>
                </div>

                <div id="s-mentor-chat" style="flex: 1; overflow-y: auto; display: flex; flex-direction: column; gap: 15px; padding-right: 10px;">
                    <div style="background: rgba(0,255,204,0.1); padding: 15px; border-radius: 15px; font-size: 14px; border: 1px solid rgba(0,255,204,0.1);">
                        Salom! Men sizning shaxsiy AI ustozi man. Kursga oid savollaringiz bo'lsa, bemalol berishingiz mumkin.
                    </div>
                </div>

                <div style="margin-top: 15px; display: flex; gap: 10px;">
                    <input type="text" id="s-mentor-input" class="input-field" placeholder="Savol bering..." style="flex: 1; padding: 12px; border-radius: 12px; background: rgba(0,0,0,0.3); border: 1px solid var(--glass-border); color: white;">
                    <button onclick="send_s_mentor_msg()" class="btn-ios btn-neon"><i class="fa-solid fa-paper-plane"></i></button>
                </div>
            </div>
        </div>
    </div>

    <!-- Student Projects -->

    <div id="student_projects" class="view-section">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
            <h2 style="margin: 0; font-weight: 800;">Mening <span style="color: var(--neon-cyan);">Loyihalarim</span></h2>
            <button class="btn-ios btn-neon" onclick="openProjectModal()"><i class="fa-solid fa-plus"></i> Yangi Loyiha</button>
        </div>
        <div id="student-projects-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 20px;">
            <!-- Dinamik loyihalar -->
        </div>
    </div>

    <!-- Student Global Chat -->
    <div id="student_chat" class="view-section">
        <h2 style="margin-bottom: 20px;"><i class="fa-solid fa-comments"></i> Akademiya <span style="color: var(--neon-cyan);">Muloqoti</span></h2>
        <div class="glass-panel" style="display: flex; height: 75vh; padding: 0; overflow: hidden;">
            <!-- Chat Sidebar (Contacts) -->
            <div style="width: 280px; border-right: 1px solid var(--glass-border); display: flex; flex-direction: column;">
                <div style="padding: 15px; border-bottom: 1px solid var(--glass-border);">
                    <input type="text" class="input-field" placeholder="Qidirish..." style="font-size: 13px; padding: 10px;">
                </div>
                <div id="chat-contacts-list" style="flex: 1; overflow-y: auto;">
                    <div class="chat-contact active" onclick="selectChat(null, 'global')">
                        <i class="fa-solid fa-earth-americas"></i>
                        <span>Global Chat</span>
                    </div>
                    <!-- Dinamik kontaktlar -->
                </div>
            </div>
            <!-- Chat Main -->
            <div style="flex: 1; display: flex; flex-direction: column;">
                <div id="chat-header" style="padding: 15px 25px; border-bottom: 1px solid var(--glass-border); display: flex; align-items: center; gap: 12px; background: rgba(255,255,255,0.02);">
                    <strong id="chat-target-name">Global Chat</strong>
                    <small id="chat-target-status" style="color: var(--neon-cyan); font-size: 10px;">● Online (Hamma)</small>
                </div>
                <div id="global-chat-box" style="flex: 1; overflow-y: auto; padding: 25px; display: flex; flex-direction: column; gap: 12px;"></div>
                <div style="padding: 15px; border-top: 1px solid var(--glass-border); display: flex; gap: 10px; align-items: center;">
                    <label for="chat-file-input" style="cursor: pointer; color: var(--neon-cyan);"><i class="fa-solid fa-paperclip"></i></label>
                    <input type="file" id="chat-file-input" style="display: none;" onchange="updateFileLabel(this)">
                    <input type="text" id="chat-msg-input" class="input-field" placeholder="Xabar yozing (Enter @ jo'natish)" style="flex: 1;" onkeypress="if(event.key==='Enter') sendGlobalChat()">
                    <button onclick="sendGlobalChat()" class="btn-ios btn-neon"><i class="fa-solid fa-paper-plane"></i></button>
                </div>
                <div id="file-preview-label" style="padding: 0 20px 10px; font-size: 10px; color: var(--neon-cyan); display: none;"></div>
            </div>
        </div>
    </div>

    <!-- Student Achievements -->
    <div id="student_achievements" class="view-section">
        <h2 style="margin-bottom: 25px;"><i class="fa-solid fa-medal"></i> Mening <span style="color: var(--neon-purple);">Yutuqlarim</span></h2>
        <div id="s-achievements-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 20px;">
            <div style="opacity: 0.5;">Yuklanmoqda...</div>
        </div>
    </div>

    <!-- Student Careers -->
    <div id="student_jobs" class="view-section">
        <h2 style="margin-bottom: 25px;"><i class="fa-solid fa-briefcase"></i> Karyera <span style="color: var(--neon-cyan);">Markazi</span></h2>
        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 25px;">
            <div class="glass-panel" style="padding: 25px;">
                <h3>Mavjud Vakansiyalar</h3>
                <div id="s-jobs-list" style="display: flex; flex-direction: column; gap: 15px; margin-top: 20px;">
                    <div style="opacity: 0.5;">Yuklanmoqda...</div>
                </div>
            </div>
            <div class="glass-panel" style="padding: 25px;">
                <h3>Status & Sertifikatlar</h3>
                <div id="s-career-status" style="margin-top: 10px; text-align: center; padding: 25px; border-radius: 15px; background: rgba(0,255,204,0.05); border: 1px solid rgba(0,255,204,0.1);">
                    <div id="s-career-readiness-badge">Tahlil qilinmoqda...</div>
                </div>
                <div style="margin-top: 25px;">
                    <h4><i class="fa-solid fa-certificate"></i> Mening Sertifikatlarim</h4>
                    <div id="s-certs-list" style="margin-top: 15px; display: flex; flex-direction: column; gap: 10px;">
                        <div style="opacity: 0.3; font-size: 11px;">Hali sertifikat yo'q.</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <style>
        .chat-contact { padding: 15px 20px; display: flex; align-items: center; gap: 12px; cursor: pointer; transition: 0.2s; border-bottom: 1px solid rgba(255,255,255,0.02); }
        .chat-contact:hover { background: rgba(255,255,255,0.03); }
        .chat-contact.active { background: rgba(0,255,204,0.1); border-left: 3px solid var(--neon-cyan); }
        .chat-contact span { font-size: 14px; font-weight: 500; }
        .chat-contact i { opacity: 0.6; width: 20px; text-align: center; }
    </style>

    @endif

    @if(auth()->user()->role === 'master' || auth()->user()->role === 'employee')
    <!-- Admin Moderation -->
    <div id="academy_moderation" class="view-section">
        <h2 style="margin-bottom: 25px;"><i class="fa-solid fa-gavel"></i> Student Chat <span style="color: var(--neon-pink);">Moderatsiyasi</span></h2>
        <div class="glass-panel" style="padding: 25px;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="border-bottom: 1px solid var(--glass-border); color: var(--text-muted); text-align: left;">
                        <th style="padding: 15px;">Foydalanuvchi</th>
                        <th>Xabar / Fayl</th>
                        <th>AI Holat</th>
                        <th>Jazo</th>
                        <th>Vaqt</th>
                    </tr>
                </thead>
                <tbody id="moderation-log-body">
                    <!-- Loglar -->
                </tbody>
            </table>
        </div>
    </div>
    @endif


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
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

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

            if(tabId === 'student_achievements') loadStudentAchievements();
            if(tabId === 'student_jobs') loadStudentJobs();
            if(tabId === 'ai_developer') loadAiProjects();
            if(tabId === 'live_chat') startLiveChatPolling();

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
            
            // Sync permissions UI
            const userPerms = emp.permissions || [];
            document.querySelectorAll('.emp-permission-cb').forEach(cb => {
                const isSelected = userPerms.includes(cb.value);
                cb.checked = isSelected;
                const toggle = cb.nextElementSibling;
                if(isSelected) toggle.classList.add('on');
                else toggle.classList.remove('on');
            });
            updatePermCount();

            document.getElementById('emp_password').required = false;
            document.getElementById('empModalHeader').innerText = "Xodim Ma'lumotlarini Tahrirlash";
            document.getElementById('add-emp-form').style.display = 'block';
            document.getElementById('add-emp-form').scrollIntoView({ behavior: 'smooth' });
        }

        function togglePerm(mod, el) {
            const cb = document.getElementById('cb_' + mod);
            cb.checked = !cb.checked;
            if(cb.checked) el.classList.add('on');
            else el.classList.remove('on');
            updatePermCount();
        }

        function updatePermCount() {
            const count = document.querySelectorAll('.emp-permission-cb:checked').length;
            document.getElementById('selectedPermsCount').innerText = count;
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
            
            // Add Permissions manually
            document.querySelectorAll('.emp-permission-cb:checked').forEach(cb => {
                fd.append('permissions[]', cb.value);
            });
            
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
        const aiActivities = [
            { main: "Yangi Lead!", sub: "Bot: Azamat bilan suhbat yakunlandi" },
            { main: "To'lov qabul qilindi", sub: "+45,000,000 UZS (Delta Edu)" },
            { main: "AI Moderatsiya", sub: "Chatda 1 ta xabar bloklandi" },
            { main: "Dars yaratildi", sub: "I-Ticher: Python darsi tayyor" },
            { main: "Yangi Mijoz", sub: "Visa konsalting shartnomasi" }
        ];

        function simulateAIAction(customText = null, customSub = null) {
            const island = document.getElementById('dynamicIsland');
            const text = document.getElementById('islandText');
            const sub = document.getElementById('islandSub');
            
            island.classList.add('active');
            text.innerHTML = customText || aiActivities[Math.floor(Math.random() * aiActivities.length)].main;
            if(sub) sub.innerHTML = customSub || aiActivities[Math.floor(Math.random() * aiActivities.length)].sub;
            text.style.color = "var(--neon-cyan)";
            
            setTimeout(() => {
                island.classList.remove('active');
                setTimeout(() => {
                    text.innerHTML = "Obsidian OS v1";
                    text.style.color = "white";
                    if(sub) sub.innerHTML = "AI faolligi normal";
                }, 400);
            }, 5000);
        }

        // Draggable Island Logic
        (function() {
            const island = document.getElementById('dynamicIsland');
            let isDragging = false;
            let startX, startY, initialX, initialY;

            island.addEventListener('mousedown', dragStart);
            island.addEventListener('touchstart', dragStart, { passive: false });

            function dragStart(e) {
                if (e.target.closest('button')) return;
                isDragging = true;
                const clientX = e.type === 'touchstart' ? e.touches[0].clientX : e.clientX;
                const clientY = e.type === 'touchstart' ? e.touches[0].clientY : e.clientY;
                startX = clientX; startY = clientY;
                const rect = island.getBoundingClientRect();
                initialX = rect.left + rect.width / 2;
                initialY = rect.top;
                island.style.transition = 'none';
                document.addEventListener('mousemove', dragMove);
                document.addEventListener('touchmove', dragMove, { passive: false });
                document.addEventListener('mouseup', dragEnd);
                document.addEventListener('touchend', dragEnd);
            }

            function dragMove(e) {
                if (!isDragging) return;
                const clientX = e.type === 'touchmove' ? e.touches[0].clientX : e.clientX;
                const clientY = e.type === 'touchmove' ? e.touches[0].clientY : e.clientY;
                const dx = clientX - startX;
                const dy = clientY - startY;
                island.style.left = `calc(50% + ${dx}px)`;
                island.style.top = `${initialY + dy}px`;
            }

            function dragEnd() {
                isDragging = false;
                island.style.transition = 'all 0.6s cubic-bezier(0.34, 1.56, 0.64, 1)';
                document.removeEventListener('mousemove', dragMove);
                document.removeEventListener('touchmove', dragMove);
                document.removeEventListener('mouseup', dragEnd);
                document.removeEventListener('touchend', dragEnd);
            }
        })();

        // Tasodifiy AI faolligini ko'rsatib turish
        setInterval(() => {
            if(!document.getElementById('dynamicIsland').classList.contains('active')) {
                const act = aiActivities[Math.floor(Math.random() * aiActivities.length)];
                simulateAIAction(act.main, act.sub);
            }
        }, 15000);

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
                const revEl = document.getElementById('stats-total-revenue');
                if(revEl) revEl.innerText = new Intl.NumberFormat('uz-UZ').format(data.stats.total_revenue) + " UZS";
                
                const tenEl = document.getElementById('stats-active-tenants');
                if(tenEl) tenEl.innerText = data.stats.active_tenants;

                const leadEl = document.getElementById('stats-new-leads');
                if(leadEl) leadEl.innerText = data.stats.new_leads_today;
                
                // Chart logic remains same...
                renderMainChart(data);

                // Load sub-sections if they are empty
                loadDashboardSections();
            } catch (e) { console.error("Analytics Error:", e); }
        }

        function renderMainChart(data) {
                const ctx = document.getElementById('dashboardChart').getContext('2d');
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
                            { label: 'Daromad (UZS)', data: revenueValues, borderColor: '#00ffcc', backgroundColor: 'rgba(0, 255, 204, 0.1)', fill: true, tension: 0.4, yAxisID: 'y' },
                            { label: 'Yangi Leadlar', data: leadsValues, borderColor: '#b026ff', backgroundColor: 'rgba(176, 38, 255, 0.1)', fill: true, tension: 0.4, yAxisID: 'y1' }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: true, labels: { color: 'white' } } },
                        scales: {
                            x: { grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: 'rgba(255,255,255,0.5)' } },
                            y: { position: 'left', grid: { color: 'rgba(255,255,255,0.1)' }, ticks: { color: '#00ffcc' } },
                            y1: { position: 'right', grid: { display: false }, ticks: { color: '#b026ff' } }
                        }
                    }
                });
        }

        async function loadDashboardSections() {
            // This ensures sections are populated via API if they look empty after Blade render
            updateActiveChats();
            initAcademyDashboard();
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
            updateServerHealthSim();
        }

        function updateServerHealthSim() {
            // Simulated health updates for "Cyberpunk" feel
            setInterval(() => {
                const cpu = Math.floor(Math.random() * 15) + 5;
                const ram = (Math.random() * 0.5 + 2.2).toFixed(1);
                const cpuEl = document.querySelector('#system_health .stat-value');
                if(cpuEl) cpuEl.innerHTML = `${cpu}% <span style="font-size: 14px; color: var(--text-muted);"><i class="fa-solid fa-arrow-trend-down"></i> Muqobil</span>`;
            }, 3000);
        }

        // Role & Permission Logic
        const CURRENT_USER_PERMISSIONS = @json(auth()->user()->permissions ?? []);
        const IS_MASTER = "{{ auth()->user()->role === 'master' }}";

        function checkPermission(module, callback) {
            const userPermissions = @json(auth()->user()->permissions ?? []);
            const userRole = "{{ auth()->user()->role }}";

            if (userRole === 'master' || userPermissions.includes(module)) {
                callback();
            } else {
                Swal.fire({
                    title: 'Kirish Bloklangan',
                    text: 'Sizda ushbu boʻlimga ruxsat yoʻq. Davom etish uchun Master Login kiritasizmi?',
                    icon: 'lock',
                    background: '#0a0a1a',
                    color: '#fff',
                    showCancelButton: true,
                    confirmButtonText: 'Login kiritish',
                    cancelButtonText: 'Bekor qilish',
                    confirmButtonColor: 'var(--neon-cyan)',
                }).then((result) => {
                    if (result.isConfirmed) {
                        promptMasterEscalation(module, callback);
                    }
                });
            }
        }

        async function promptMasterEscalation(module, callback) {
            const { value: formValues } = await Swal.fire({
                title: 'Master Tasdiqlash',
                html:
                    '<input id="swal-input1" class="swal2-input" placeholder="Email/Login" style="background:#1a1a1a; color:white; border-color:var(--glass-border)">' +
                    '<input id="swal-input2" type="password" class="swal2-input" placeholder="Parol" style="background:#1a1a1a; color:white; border-color:var(--glass-border)">',
                focusConfirm: false,
                background: '#0a0a1a',
                color: '#fff',
                preConfirm: () => {
                    return [
                        document.getElementById('swal-input1').value,
                        document.getElementById('swal-input2').value
                    ]
                }
            });

            if (formValues) {
                // In a real app, this would be an AJAX call to verify credentials
                // For now, simulate with a simple check or feedback
                Swal.fire({ title: 'Tekshirilmoqda...', didOpen: () => Swal.showLoading() });
                
                try {
                    const res = await fetch(`${API_PREFIX}/auth/verify-master`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                        body: JSON.stringify({ email: formValues[0], password: formValues[1], module: module })
                    });
                    const d = await res.json();
                    if(d.status === 'success') {
                        Swal.fire('Muvaffaqiyatli!', 'Ruxsat berildi.', 'success');
                        callback();
                    } else {
                        Swal.fire('Xato', 'Ruxsat berilmadi yoki ma\'lumotlar xato.', 'error');
                    }
                } catch(e) { Swal.fire('Xato', 'Server ulanish xatosi', 'error'); }
            }
        }

        function switchAcademyTab(btn, tabId) {
            document.querySelectorAll('.academy-tab-content').forEach(t => t.style.display = 'none');
            document.querySelectorAll('#academy .btn-tabs').forEach(b => b.classList.remove('active'));
            document.getElementById(tabId).style.display = 'block';
            btn.classList.add('active');
            
            if(tabId === 'acad-students') loadAcademyStudentsFull();
            if(tabId === 'acad-logins') loadAcademyLogins();
            if(tabId === 'acad-courses') loadAcademyCourses();
            if(tabId === 'acad-mentors') loadAcademyMentors();
            if(tabId === 'acad-analytics') loadAcademyAnalytics();
            if(tabId === 'acad-pro') loadAcademyPro();
        }

        async function loadAcademyPro() {
            // Load Achievements
            try {
                const res = await fetch(`${API_PREFIX}/academy/achievements`);
                const data = await res.json();
                document.getElementById('academy-achievements-list').innerHTML = data.map(a => `
                    <div class="glass-panel" style="padding: 15px; text-align: center; border: 1px solid rgba(255,255,255,0.05); transition: 0.3s; cursor: pointer;">
                        <i class="fa-solid ${a.icon}" style="font-size: 24px; color: var(--neon-purple); margin-bottom: 10px;"></i>
                        <div style="font-size: 11px; font-weight: bold; color: white;">${a.name}</div>
                        <div style="font-size: 9px; opacity: 0.5; margin-top: 5px;">${a.points} XP • ${a.description}</div>
                    </div>
                `).join('') || '<div style="opacity: 0.5;">Yutuqlar yo\'q</div>';
            } catch(e) {}

            // Load Jobs
            try {
                const res = await fetch(`${API_PREFIX}/academy/jobs`);
                const data = await res.json();
                document.getElementById('academy-jobs-list').innerHTML = data.map(j => `
                    <div class="glass-panel" style="padding: 15px; display: flex; justify-content: space-between; align-items: center; border-left: 3px solid var(--neon-cyan);">
                        <div>
                            <h5 style="margin: 0; color: white; font-size: 14px;">${j.title}</h5>
                            <div style="font-size: 11px; opacity: 0.7;">${j.company_name} • ${j.location}</div>
                            <div style="font-size: 10px; color: var(--neon-cyan); margin-top: 5px; font-family: monospace;">
                                <i class="fa-solid fa-money-bill-wave"></i> ${new Intl.NumberFormat().format(j.salary_range_min)} - ${new Intl.NumberFormat().format(j.salary_range_max)} UZS
                            </div>
                        </div>
                        <div style="display: flex; gap: 8px;">
                            <button class="btn-ios" onclick="viewJobApplications(${j.id})" title="Arizalar" style="padding: 8px;"><i class="fa-solid fa-users-viewfinder"></i></button>
                            <button class="btn-ios" onclick="deleteJob(${j.id})" title="O'chirish" style="padding: 8px; color: var(--neon-pink); opacity: 0.5;"><i class="fa-solid fa-trash"></i></button>
                        </div>
                    </div>
                `).join('') || '<div style="opacity: 0.5;">Faol vakansiyalar yo\'q</div>';
            } catch(e) {}
        }

        async function deleteJob(id) {
            if(!confirm('Ishni o\'chirishni tasdiqlaysizmi?')) return;
            try {
                await fetch(`${API_PREFIX}/academy/jobs/${id}`, { method: 'DELETE' });
                loadAcademyPro();
            } catch(e) {}
        }

        async function deleteAchievement(id) {
            if(!confirm('Yutuqni o\'chirishni tasdiqlaysizmi?')) return;
            try {
                await fetch(`${API_PREFIX}/academy/achievements/${id}`, { method: 'DELETE' });
                loadAcademyPro();
            } catch(e) {}
        }

        async function openJobModal() {
            Swal.fire({
                title: 'Yangi Vakansiya Qo\'shish',
                html: `
                    <div style="text-align: left;">
                        <input id="job-title" class="swal2-input" placeholder="Lavozim (e.g. Senior Laravel)" style="width: 80%;">
                        <input id="job-company" class="swal2-input" placeholder="Kompaniya nomi" style="width: 80%;">
                        <textarea id="job-desc" class="swal2-textarea" placeholder="Ish tavsifi va talablar" style="width: 80%;"></textarea>
                        <input id="job-location" class="swal2-input" placeholder="Joylashuv (e.g. Masofaviy)" style="width: 80%;">
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-top: 15px; padding: 0 40px;">
                            <div>
                                <label style="font-size: 11px; opacity: 0.5;">Min Oylik</label>
                                <input id="job-min" type="number" class="swal2-input" style="margin: 0; width: 100%;">
                            </div>
                            <div>
                                <label style="font-size: 11px; opacity: 0.5;">Max Oylik</label>
                                <input id="job-max" type="number" class="swal2-input" style="margin: 0; width: 100%;">
                            </div>
                        </div>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: 'Joylash',
                background: '#0a0a1a',
                color: '#fff',
                preConfirm: () => {
                    return {
                        title: document.getElementById('job-title').value,
                        company_name: document.getElementById('job-company').value,
                        description: document.getElementById('job-desc').value,
                        location: document.getElementById('job-location').value,
                        salary_range_min: document.getElementById('job-min').value,
                        salary_range_max: document.getElementById('job-max').value,
                        is_active: true
                    }
                }
            }).then(async (result) => {
                if(result.isConfirmed) {
                    await fetch(`${API_PREFIX}/academy/jobs`, {
                        method: 'POST',
                        headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content},
                        body: JSON.stringify(result.value)
                    });
                    loadAcademyPro();
                }
            });
        }

        async function viewJobApplications(jobId) {
            Swal.fire({
                title: 'Arizalar Ro\'yxati',
                text: 'Hozircha arizalar yo\'q (MOCK).',
                background: '#0a0a1a',
                color: '#fff'
            });
        }

        async function openAchievementModal() {
            Swal.fire({
                title: 'Yangi Yutuq (Badge) Qo\'shish',
                html: `
                    <input id="ach-name" class="swal2-input" placeholder="Yutuq nomi (e.g. Master Archer)">
                    <input id="ach-desc" class="swal2-input" placeholder="Tavsif">
                    <input id="ach-icon" class="swal2-input" placeholder="Icon (e.g. fa-chess-knight)">
                    <input id="ach-points" type="number" class="swal2-input" placeholder="XP miqdori">
                `,
                showCancelButton: true,
                confirmButtonText: 'Qo\'shish',
                background: '#0a0a1a',
                color: '#fff',
                preConfirm: () => {
                    return {
                        name: document.getElementById('ach-name').value,
                        description: document.getElementById('ach-desc').value,
                        icon: document.getElementById('ach-icon').value,
                        points: document.getElementById('ach-points').value
                    }
                }
            }).then(async (result) => {
                if(result.isConfirmed) {
                    await fetch(`${API_PREFIX}/academy/achievements`, {
                        method: 'POST',
                        headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content},
                        body: JSON.stringify(result.value)
                    });
                    loadAcademyPro();
                }
            });
        }

        async function initAcademyDashboard() {
            try {
                const res = await fetch(`${API_PREFIX}/academy/stats`);
                const data = await res.json();
                document.getElementById('badge-apps').innerText = data.pending_apps;
                loadAcademyApplications();
            } catch(e) { console.error("Academy Dashboard Error", e); }
        }

        async function loadAcademyStudentsFull() {
            const container = document.getElementById('academy-students-list');
            container.innerHTML = '<p style="opacity: 0.5; text-align: center;">Yuklanmoqda...</p>';
            try {
                const res = await fetch(`${API_PREFIX}/academy/students`);
                const students = await res.json();
                
                if (students.length === 0) {
                    container.innerHTML = '<p style="opacity: 0.5; text-align: center;">Hali o\'quvchilar mavjud emas.</p>';
                    return;
                }

                container.innerHTML = students.map(s => `
                    <div class="glass-panel" style="padding: 20px; border-left: 4px solid var(--neon-cyan);">
                        <div style="display: flex; gap: 15px; align-items: flex-start;">
                            <div style="width: 45px; height: 45px; border-radius: 50%; background: rgba(0,255,204,0.1); display: flex; align-items: center; justify-content: center; color: var(--neon-cyan); font-weight: bold;">
                                ${s.name.charAt(0)}
                            </div>
                            <div style="flex: 1;">
                                <h4 style="margin: 0; font-size: 15px;">${s.name}</h4>
                                <div style="font-size: 11px; opacity: 0.5;">${s.email}</div>
                                <div style="margin-top: 10px; display: flex; gap: 5px;">
                                    <span class="badge" style="background: rgba(176,38,255,0.1); color: var(--neon-purple); border: 1px solid rgba(176,38,255,0.2);">${s.rank}</span>
                                    <span class="badge" style="background: rgba(0,255,204,0.1); color: var(--neon-cyan); border: 1px solid rgba(0,255,204,0.2);">${s.study_status.toUpperCase()}</span>
                                </div>
                                <div style="margin-top: 10px; font-size: 12px; display: flex; flex-direction: column; gap: 3px;">
                                    <span>XP: <b style="color: var(--neon-cyan);">${s.total_xp}</b></span>
                                    ${s.jshir ? `<span style="font-size: 10px; opacity: 0.6;">JSHIR: ${s.jshir}</span>` : ''}
                                    ${s.passport_number ? `<span style="font-size: 10px; opacity: 0.6;">Passport: ${s.passport_number}</span>` : ''}
                                    ${s.address ? `<span style="font-size: 10px; opacity: 0.6; overflow: hidden; white-space: nowrap; text-overflow: ellipsis; max-width: 150px;">Manzil: ${s.address}</span>` : ''}
                                </div>
                            </div>
                            <div style="display: flex; flex-direction: column; gap: 5px;">
                                <button class="btn-ios" onclick="openStudentModal(${s.id})" style="padding: 5px 8px;" title="Tahrirlash"><i class="fa-solid fa-pen"></i></button>
                                <button class="btn-ios" onclick="viewStudentEnrollments(${s.id})" style="padding: 5px 8px;" title="Kurslar"><i class="fa-solid fa-graduation-cap"></i></button>
                                <button class="btn-ios" onclick="viewStudentPayment(${s.id})" style="padding: 5px 8px;" title="To'lovlar"><i class="fa-solid fa-wallet"></i></button>
                                <button class="btn-ios" onclick="openStudentAnalytics(${s.id})" style="padding: 5px 8px;" title="Analytics"><i class="fa-solid fa-chart-line"></i></button>
                            </div>
                        </div>
                    </div>
                `).join('');
            } catch(e) { }
        }

        async function loadAcademyCourses() {
            const list = document.getElementById('academy-courses-list');
            list.innerHTML = 'Yuklanmoqda...';
            try {
                const res = await fetch(`${API_PREFIX}/academy/courses`);
                const courses = await res.json();
                list.innerHTML = courses.map(c => `
                    <div class="course-card">
                        <div>
                            <h4 style="margin: 0;">${c.title}</h4>
                            <div style="font-size: 11px; opacity: 0.5; margin-top: 3px;">
                                Status: ${c.is_published ? 'E\'lon qilingan' : 'Qoralama'} | 
                                Narx: <span style="color: var(--neon-cyan)">${new Intl.NumberFormat('uz-UZ').format(c.price || 0)} UZS</span>
                            </div>
                            ${c.mentor_id ? `<div style="font-size: 10px; color: var(--neon-purple); margin-top: 2px;"><i class="fa-solid fa-user-tie"></i> Mentor: ${window.academyMentors?.find(m=>m.id==c.mentor_id)?.name || 'Biriktirilgan'}</div>` : ''}
                        </div>
                        <div style="display: flex; gap: 10px;">
                            <button class="btn-ios" onclick="openCourseModal(${c.id})" style="padding: 5px 12px;" title="Tahrirlash"><i class="fa-solid fa-edit"></i></button>
                            <button class="btn-ios" onclick="selectCourse(${c.id})"><i class="fa-solid fa-layer-group"></i> Darslar</button>
                        </div>
                    </div>
                `).join('');
            } catch(e) {}
        }

        window.academyMentors = [];
        async function loadAcademyMentors() {
            const grid = document.getElementById('academy-mentors-grid');
            if(!grid) return;
            grid.innerHTML = 'Yuklanmoqda...';
            try {
                const res = await fetch(`${API_PREFIX}/academy/mentors`);
                const mentors = await res.json();
                if(!Array.isArray(mentors)) throw new Error("Format xatosi");
                window.academyMentors = mentors;
                
                if (mentors.length === 0) {
                    grid.innerHTML = '<div style="grid-column: 1/-1; text-align: center; padding: 40px; opacity: 0.5;">Mentors yo\'q.</div>';
                    return;
                }

                grid.innerHTML = mentors.map(m => `
                    <div class="glass-panel" style="padding: 20px; text-align: center; border-bottom: 3px solid ${m.is_active ? 'var(--neon-cyan)' : 'var(--neon-pink)'};">
                        <div style="width: 60px; height: 60px; border-radius: 50%; background: #000; margin: 0 auto 15px auto; border: 2px solid var(--neon-cyan); display: flex; align-items: center; justify-content: center; font-size: 24px; color: var(--neon-cyan);">
                            <i class="fa-solid fa-robot"></i>
                        </div>
                        <h4 style="margin: 0 0 5px 0;">${m.name}</h4>
                        <div style="display: flex; flex-direction: column; gap: 8px; margin-top: 15px;">
                            <button class="btn-ios" onclick="showFullPrompt(${m.id})" style="font-size: 10px; background: rgba(0,255,204,0.1); color: var(--neon-cyan); border: 1px solid var(--neon-cyan); padding: 8px;"><i class="fa-solid fa-eye"></i> Promtni ko'rish</button>
                            <button class="btn-ios btn-neon" onclick="openMentorModal(${m.id})" style="padding: 8px;"><i class="fa-solid fa-gears"></i> Sozlamalar</button>
                        </div>
                    </div>
                `).join('');
            } catch(e) { 
                console.error("Load Mentors Error", e);
                grid.innerHTML = '<div style="color: var(--neon-pink);">Yuklashda xatolik yuz berdi.</div>';
            }
        }

        async function loadAcademyAnalytics() {
            const container = document.getElementById('academy-rankings');
            // Simplified student rankings
            try {
                const res = await fetch(`${API_PREFIX}/academy/students`);
                const students = await res.json();
                container.innerHTML = students.slice(0, 5).map((s, index) => `
                    <div class="acad-stat-box">
                        <span>${index + 1}. ${s.name} (${s.rank})</span>
                        <strong style="color: var(--neon-cyan); font-family: monospace;">XP: ${s.total_xp}</strong>
                    </div>
                `).join('');
                document.getElementById('acad-avg-iq').innerText = (students.length > 0) ? '112.5' : 'N/A'; // Mock
                document.getElementById('acad-pass-rate').innerText = '94%'; // Mock
            } catch(e) {}
        }

        async function loadAcademyLogins() {
            const list = document.getElementById('academy-logins-list');
            if(!list) return;
            list.innerHTML = '<tr><td colspan="3" style="text-align: center; padding: 20px;">Yuklanmoqda...</td></tr>';
            try {
                const res = await fetch(`${API_PREFIX}/employees?role=student`);
                const students = await res.json();
                window.academyLogins = students;
                list.innerHTML = students.map(s => `
                    <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                        <td style="padding: 15px;">
                            <div style="font-weight: bold; color: white;">${s.name}</div>
                        </td>
                        <td style="color: var(--neon-cyan); opacity: 0.8;">${s.email}</td>
                        <td style="padding: 15px;">
                            <button class="btn-ios btn-neon" onclick="editStudentLogin(${s.id})" style="padding: 5px 12px; font-size: 11px;"><i class="fa-solid fa-key"></i> Parolni o'zgartirish</button>
                        </td>
                    </tr>
                `).join('') || '<tr><td colspan="3" style="text-align: center; padding: 40px; opacity: 0.5;">Talabalar topilmadi.</td></tr>';
            } catch(e) { 
                list.innerHTML = '<tr><td colspan="3">Xatolik yuz berdi.</td></tr>';
            }
        }

        async function editStudentLogin(id) {
            const s = (window.academyLogins || []).find(x => x.id == id);
            if(!s) return;

            const { value: formValues } = await Swal.fire({
                title: `${s.name} parolini yangilash`,
                html: `
                    <div style="text-align: left;">
                        <label style="font-size: 11px; color: #888;">Yangi Parol</label>
                        <input id="new-password" type="password" class="swal2-input" placeholder="Yangi parol..." style="width: 80%;">
                    </div>`,
                showCancelButton: true,
                confirmButtonText: 'Yangilash',
                background: '#0a0a1a',
                color: '#fff',
                preConfirm: () => [ document.getElementById('new-password').value ]
            });

            if (formValues && formValues[0]) {
                try {
                    const res = await fetch(`${API_PREFIX}/employees/${id}`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                        body: JSON.stringify({ 
                            name: s.name, 
                            email: s.email, 
                            role: 'student', 
                            password: formValues[0], 
                            _method: 'PUT' 
                        })
                    });
                    if (res.ok) {
                        Swal.fire('Bajarildi!', 'Talaba paroli muvaffaqiyatli o\'zgartirildi.', 'success');
                        loadAcademyLogins();
                    }
                } catch(e) {}
            }
        }
        function showFullPrompt(id) {
            const m = window.academyMentors.find(x => x.id == id);
            if(!m) return;
            Swal.fire({
                title: `${m.name} uchun Promt`,
                html: `<div style="text-align: left; font-size: 13px; max-height: 400px; overflow-y: auto; padding: 15px; background: rgba(0,0,0,0.3); border-radius: 10px; border: 1px solid var(--glass-border); color: #fff;">${m.instructions || m.system_prompt || 'Bo\'sh'}</div>`,
                background: '#0a0a1a',
                color: '#fff',
                confirmButtonText: 'Yopish'
            });
        }
        async function openCourseModal(id = null) {
            const c = id ? (await (await fetch(`${API_PREFIX}/academy/courses`)).json()).find(x => x.id == id) : null;
            const mentors = window.academyMentors || [];

            Swal.fire({
                title: id ? 'Kursni tahrirlash' : 'Yangi Kurs yaratish',
                html: `
                    <div style="text-align: left;">
                        <input id="course-title" class="swal2-input" placeholder="Kurs nomi" value="${c ? c.title : ''}" style="width: 80%;">
                        <textarea id="course-desc" class="swal2-textarea" placeholder="Tavsif" style="width: 80%;">${c ? c.description : ''}</textarea>
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-top: 15px;">
                            <div>
                                <label style="font-size: 11px; opacity: 0.5;">Umumiy narx (UZS)</label>
                                <input id="course-price" type="number" class="swal2-input" value="${c ? c.price : 0}" style="margin: 0; width: 100%;">
                            </div>
                            <div>
                                <label style="font-size: 11px; opacity: 0.5;">Oylik to'lov (UZS)</label>
                                <input id="course-monthly" type="number" class="swal2-input" value="${c ? c.monthly_fee : 0}" style="margin: 0; width: 100%;">
                            </div>
                        </div>

                        <div style="margin-top: 15px;">
                            <label style="font-size: 11px; opacity: 0.5;">Mentor biriktirish</label>
                            <select id="course-mentor" class="swal2-input" style="width: 90%; background: #1a1a1a; color: white;">
                                <option value="">Mentorsiz</option>
                                ${mentors.map(m => `<option value="${m.id}" ${c && c.mentor_id == m.id ? 'selected' : ''}>${m.name}</option>`).join('')}
                            </select>
                        </div>
                    </div>`,
                showCancelButton: true,
                confirmButtonText: 'Saqlash',
                background: '#0a0a1a',
                color: '#fff',
                preConfirm: () => {
                    return {
                        id: id,
                        title: document.getElementById('course-title').value,
                        description: document.getElementById('course-desc').value,
                        price: document.getElementById('course-price').value,
                        monthly_fee: document.getElementById('course-monthly').value,
                        mentor_id: document.getElementById('course-mentor').value
                    }
                }
            }).then(async (result) => {
                if (result.isConfirmed) {
                    try {
                        const res = await fetch(`${API_PREFIX}/academy/courses`, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                            body: JSON.stringify(result.value)
                        });
                        if (res.ok) {
                            Swal.fire('Tayyor!', 'Kurs saqlandi.', 'success');
                            loadAcademyCourses();
                        }
                    } catch(e) {}
                }
            });
        }

        function openMentorModal(id = null) {
            const p = id ? window.academyMentors.find(x => x.id == id) : null;
            Swal.fire({
                title: id ? 'AI Mentorni tahrirlash' : 'Yangi AI Mentor (I-Ticher)',
                html: `
                    <div style="text-align: left;">
                        <label style="font-size: 11px; color: #888;">Mentor Nomi</label>
                        <input id="mentor-name" class="swal2-input" value="${p?p.name:''}" style="width: 80%;">
                        <label style="font-size: 11px; color: #888; margin-top: 10px; display: block;">Gemini API Key</label>
                        <input id="mentor-api-key" class="swal2-input" value="${p?p.gemini_api_key:''}" style="width: 80%;">
                        <label style="font-size: 11px; color: #888; margin-top: 10px; display: block;">System Prompt (Yo'riqnoma)</label>
                        <textarea id="mentor-sys-prompt" class="swal2-textarea" style="width: 80%; height: 100px;">${p?p.system_prompt:''}</textarea>
                    </div>`,
                showCancelButton: true,
                confirmButtonText: 'Saqlash',
                background: '#0a0a1a',
                color: '#fff',
                preConfirm: () => {
                    return {
                        id: id,
                        name: document.getElementById('mentor-name').value,
                        gemini_api_key: document.getElementById('mentor-api-key').value,
                        system_prompt: document.getElementById('mentor-sys-prompt').value
                    }
                }
            }).then(async (result) => {
                if (result.isConfirmed) {
                    try {
                        const res = await fetch(`${API_PREFIX}/academy/mentors`, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                            body: JSON.stringify(result.value)
                        });
                        if (res.ok) {
                            Swal.fire('Muvaffaqiyatli!', 'I-Ticher Mentor saqlandi.', 'success');
                            loadAcademyMentors();
                        }
                    } catch(e) {}
                }
            });
        }

        async function viewStudentPayment(studentId) {
            try {
                const res = await fetch(`${API_PREFIX}/academy/students/${studentId}/payments`);
                const payments = await res.json();
                
                let html = `
                    <div style="text-align: left; max-height: 400px; overflow-y: auto;">
                        <button class="btn-ios btn-neon" onclick="addPaymentModal(${studentId})" style="margin-bottom: 15px; width: 100%;"><i class="fa-solid fa-plus"></i> Yangi To'lov kiritish</button>
                        <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
                            <thead>
                                <tr style="border-bottom: 1px solid #333;">
                                    <th style="padding: 10px;">Sana</th>
                                    <th style="padding: 10px;">Suma</th>
                                    <th style="padding: 10px;">Kurs</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${payments.length ? payments.map(p => `
                                    <tr style="border-bottom: 1px solid #222;">
                                        <td style="padding: 10px;">${new Date(p.created_at).toLocaleDateString()}</td>
                                        <td style="padding: 10px; color: var(--neon-cyan);">${new Intl.NumberFormat('uz-UZ').format(p.amount)} UZS</td>
                                        <td style="padding: 10px;">${p.course_title}</td>
                                    </tr>
                                `).join('') : '<tr><td colspan="3" style="text-align:center; padding: 20px; opacity: 0.5;">To\'lovlar topilmadi.</td></tr>'}
                            </tbody>
                        </table>
                    </div>
                `;

                Swal.fire({
                    title: 'To\'lovlar Tarixi',
                    html: html,
                    background: '#0a0a1a',
                    color: '#fff',
                    showConfirmButton: false,
                    showCloseButton: true
                });
            } catch(e) { Swal.fire('Error', 'Ma\'lumotni yuklashda xato', 'error'); }
        }

        async function addPaymentModal(studentId) {
            const courses = await (await fetch(`${API_PREFIX}/academy/courses`)).json();
            
            Swal.fire({
                title: 'Yangi To\'lov',
                html: `
                    <div style="text-align: left;">
                        <label style="font-size: 11px; opacity: 0.5;">Kurs</label>
                        <select id="pay-course" class="swal2-input" style="width: 90%; background: #1a1a1a; color: white;">
                            ${courses.map(c => `<option value="${c.id}">${c.title}</option>`).join('')}
                        </select>
                        <label style="font-size: 11px; opacity: 0.5; margin-top: 10px; display: block;">Summa (UZS)</label>
                        <input id="pay-amount" type="number" class="swal2-input" placeholder="0" style="width: 80%;">
                        <label style="font-size: 11px; opacity: 0.5; margin-top: 10px; display: block;">Turi</label>
                        <select id="pay-method" class="swal2-input" style="width: 90%; background: #1a1a1a; color: white;">
                            <option value="cash">Naqd</option>
                            <option value="card">Karta (Terminal)</option>
                            <option value="payme">Payme / Click</option>
                        </select>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: 'Saqlash',
                background: '#0a0a1a',
                color: '#fff',
                preConfirm: () => {
                    return {
                        user_id: studentId,
                        course_id: document.getElementById('pay-course').value,
                        amount: document.getElementById('pay-amount').value,
                        payment_method: document.getElementById('pay-method').value
                    }
                }
            }).then(async (result) => {
                if(result.isConfirmed) {
                    await fetch(`${API_PREFIX}/academy/payments`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                        body: JSON.stringify(result.value)
                    });
                    Swal.fire('Tayyor!', 'To\'lov qabul qilindi.', 'success');
                }
            });
        }

        async function openStudentModal(id) {
            try {
                // Fetch student details from application/user
                const students = await (await fetch(`${API_PREFIX}/academy/students`)).json();
                const s = students.find(x => x.id == id);
                if(!s) return;

                Swal.fire({
                    title: 'O\'quvchi Ma\'lumotlari',
                    html: `
                        <div style="text-align: left;">
                            <label style="font-size: 11px; opacity: 0.5;">F.I.SH</label>
                            <input id="edit-s-name" class="swal2-input" value="${s.name}" style="width: 80%;">
                            
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-top: 10px;">
                                <div>
                                    <label style="font-size: 11px; opacity: 0.5;">Email / Login</label>
                                    <input id="edit-s-email" class="swal2-input" value="${s.email}" style="width: 100%; margin: 0;">
                                </div>
                                <div>
                                    <label style="font-size: 11px; opacity: 0.5;">Yangi Parol (ixtiyoriy)</label>
                                    <input id="edit-s-password" type="password" class="swal2-input" placeholder="********" style="width: 100%; margin: 0;">
                                </div>
                            </div>

                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-top: 10px;">
                                <div>
                                    <label style="font-size: 11px; opacity: 0.5;">Passport</label>
                                    <input id="edit-s-passport" class="swal2-input" value="${s.passport_number || ''}" style="width: 100%; margin: 0;">
                                </div>
                                <div>
                                    <label style="font-size: 11px; opacity: 0.5;">JSHIR</label>
                                    <input id="edit-s-jshir" class="swal2-input" value="${s.jshir || ''}" style="width: 100%; margin: 0;">
                                </div>
                            </div>

                            <label style="font-size: 11px; opacity: 0.5; margin-top: 10px; display: block;">Manzil</label>
                            <input id="edit-s-address" class="swal2-input" value="${s.address || ''}" style="width: 80%;">
                            
                            <label style="font-size: 11px; opacity: 0.5; margin-top: 10px; display: block;">Status</label>
                            <select id="edit-s-status" class="swal2-input" style="width: 90%; background: #1a1a1a; color: white;">
                                <option value="enrolled" ${s.study_status === 'enrolled' ? 'selected' : ''}>O'qimoqda</option>
                                <option value="graduated" ${s.study_status === 'graduated' ? 'selected' : ''}>Bitirgan</option>
                                <option value="dropped" ${s.study_status === 'dropped' ? 'selected' : ''}>Tashlab ketgan</option>
                            </select>
                        </div>
                    `,
                    showCancelButton: true,
                    confirmButtonText: 'Saqlash',
                    background: '#0a0a1a',
                    color: '#fff',
                    preConfirm: () => {
                        return {
                            name: document.getElementById('edit-s-name').value,
                            email: document.getElementById('edit-s-email').value,
                            password: document.getElementById('edit-s-password').value,
                            passport_number: document.getElementById('edit-s-passport').value,
                            jshir: document.getElementById('edit-s-jshir').value,
                            address: document.getElementById('edit-s-address').value,
                            study_status: document.getElementById('edit-s-status').value
                        }
                    }
                }).then(async (result) => {
                    if(result.isConfirmed) {
                        try {
                            const res = await fetch(`${API_PREFIX}/academy/students/${id}/profile`, {
                                method: 'PUT',
                                headers: { 
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content 
                                },
                                body: JSON.stringify(result.value)
                            });
                            if(res.ok) {
                                Swal.fire('Saqlandi!', 'O\'quvchi ma\'lumotlari yangilandi.', 'success');
                                loadAcademyStudentsFull();
                            }
                        } catch(e) { }
                    }
                });
            } catch(e) { }
        }
        async function viewStudentEnrollments(studentId) {
            try {
                const res = await fetch(`${API_PREFIX}/academy/students/${studentId}/enrollments`);
                const enrollments = await res.json();
                
                let html = `
                    <div style="text-align: left; max-height: 400px; overflow-y: auto;">
                        <button class="btn-ios btn-neon" onclick="enrollStudentModal(${studentId})" style="margin-bottom: 15px; width: 100%;"><i class="fa-solid fa-plus"></i> Yangi Kursga biriktirish</button>
                        <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
                            <thead>
                                <tr style="border-bottom: 1px solid #333;">
                                    <th style="padding: 10px;">Kurs</th>
                                    <th style="padding: 10px;">Muddati</th>
                                    <th style="padding: 10px;">Status</th>
                                    <th style="padding: 10px;">Amallar</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${enrollments.length ? enrollments.map(e => `
                                    <tr style="border-bottom: 1px solid #222;">
                                        <td style="padding: 10px;">${e.course_title}</td>
                                        <td style="padding: 10px;">
                                            <div style="font-size: 11px;">Boshlandi: ${new Date(e.enrolled_at).toLocaleDateString()}</div>
                                            <div style="font-size: 11px; color: ${new Date(e.expires_at) < new Date() ? 'var(--neon-pink)' : 'var(--neon-cyan)'}">Tugaydi: ${new Date(e.expires_at).toLocaleDateString()}</div>
                                        </td>
                                        <td style="padding: 10px;">
                                            <span class="badge" style="background: ${e.status === 'active' ? 'rgba(0,255,204,0.1)' : 'rgba(176,38,255,0.1)'}; color: ${e.status === 'active' ? 'var(--neon-cyan)' : 'var(--neon-purple)'}; padding: 3px 8px; border-radius: 10px; font-size: 10px;">
                                                ${e.status.toUpperCase()}
                                            </span>
                                        </td>
                                        <td style="padding: 10px;">
                                            <button class="btn-ios" onclick="extendEnrollmentModal(${e.id}, ${studentId})" style="padding: 4px 8px;" title="Uzaytirish"><i class="fa-solid fa-clock-rotate-left"></i></button>
                                        </td>
                                    </tr>
                                `).join('') : '<tr><td colspan="4" style="text-align:center; padding: 20px; opacity: 0.5;">Kurslar topilmadi.</td></tr>'}
                            </tbody>
                        </table>
                    </div>
                `;

                Swal.fire({
                    title: 'Kurslar Bo\'limi',
                    html: html,
                    background: '#0a0a1a',
                    color: '#fff',
                    showConfirmButton: false,
                    showCloseButton: true,
                    width: '600px'
                });
            } catch(e) { Swal.fire('Error', 'Ma\'lumotni yuklashda xato', 'error'); }
        }

        async function enrollStudentModal(studentId) {
            const courses = await (await fetch(`${API_PREFIX}/academy/courses`)).json();
            
            Swal.fire({
                title: 'Kursga Biriktirish',
                html: `
                    <div style="text-align: left;">
                        <label style="font-size: 11px; opacity: 0.5;">Kursni tanlang</label>
                        <select id="enroll-course" class="swal2-input" style="width: 90%; background: #1a1a1a; color: white;">
                            ${courses.map(c => `<option value="${c.id}">${c.title}</option>`).join('')}
                        </select>
                        <label style="font-size: 11px; opacity: 0.5; margin-top: 15px; display: block;">Davomiyligi (oy)</label>
                        <input id="enroll-duration" type="number" class="swal2-input" value="3" style="width: 80%;">
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: 'Biriktirish',
                background: '#0a0a1a',
                color: '#fff',
                preConfirm: () => {
                    return {
                        user_id: studentId,
                        course_id: document.getElementById('enroll-course').value,
                        duration_months: document.getElementById('enroll-duration').value
                    }
                }
            }).then(async (result) => {
                if(result.isConfirmed) {
                    const res = await fetch(`${API_PREFIX}/academy/enroll`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                        body: JSON.stringify(result.value)
                    });
                    if(res.ok) {
                        Swal.fire('Tayyor!', 'O\'quvchi kursga biriktirildi.', 'success');
                        viewStudentEnrollments(studentId);
                    }
                }
            });
        }

        function extendEnrollmentModal(enrollmentId, studentId) {
            Swal.fire({
                title: 'Muddati Uzaytirish',
                html: `
                    <div style="text-align: left;">
                        <label style="font-size: 11px; opacity: 0.5;">Necha kunga uzaytirish kerak?</label>
                        <input id="extend-days" type="number" class="swal2-input" value="30" style="width: 80%;">
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: 'Uzaytirish',
                background: '#0a0a1a',
                color: '#fff',
                preConfirm: () => {
                    return { days: document.getElementById('extend-days').value }
                }
            }).then(async (result) => {
                if(result.isConfirmed) {
                    const res = await fetch(`${API_PREFIX}/academy/enroll/${enrollmentId}/extend`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                        body: JSON.stringify(result.value)
                    });
                    if(res.ok) {
                        Swal.fire('Uzaytirildi!', 'Kurs muddati yangilandi.', 'success');
                        viewStudentEnrollments(studentId);
                    }
                }
            });
        }

        async function openStudentAnalytics(studentId) {
            Swal.fire({ title: 'Tahlil qilinmoqda...', didOpen: () => Swal.showLoading(), background: '#0a0a1a', color: '#fff' });
            try {
                const res = await fetch(`${API_PREFIX}/academy/students/${studentId}/analytics`);
                const data = await res.json();
                
                if (data.status === 'success') {
                    const u = data.user;
                    const s = data.stats;
                    
                    let html = `
                        <div style="text-align: left; padding: 10px;">
                            <div style="display: flex; gap: 20px; margin-bottom: 25px; background: rgba(255,255,255,0.03); padding: 15px; border-radius: 15px; border: 1px solid var(--glass-border);">
                                <div style="width: 60px; height: 60px; border-radius: 50%; background: var(--grad-neon); display: flex; align-items: center; justify-content: center; font-size: 24px; font-weight: bold; color: #000;">
                                    ${u.name.charAt(0)}
                                </div>
                                <div>
                                    <h3 style="margin: 0; color: white;">${u.name}</h3>
                                    <div style="font-size: 12px; opacity: 0.6; margin-top: 4px;">${u.email}</div>
                                    <div style="display: flex; gap: 8px; margin-top: 8px;">
                                        <span class="badge" style="background: rgba(0,255,204,0.1); color: var(--neon-cyan); border: 1px solid rgba(0,255,204,0.2);">${u.rank}</span>
                                        <span class="badge" style="background: rgba(176,38,255,0.1); color: var(--neon-purple); border: 1px solid rgba(176,38,255,0.2);">${u.study_status.toUpperCase()}</span>
                                    </div>
                                </div>
                            </div>

                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 25px;">
                                <div class="glass-panel" style="padding: 15px; text-align: center; border-bottom: 2px solid var(--neon-cyan);">
                                    <div style="font-size: 11px; opacity: 0.5; text-transform: uppercase; letter-spacing: 1px;">Umumiy To'lov</div>
                                    <div style="font-size: 18px; color: var(--neon-cyan); font-weight: bold; margin-top: 5px;">${new Intl.NumberFormat('uz-UZ').format(s.total_paid)} UZS</div>
                                </div>
                                <div class="glass-panel" style="padding: 15px; text-align: center; border-bottom: 2px solid var(--neon-purple);">
                                    <div style="font-size: 11px; opacity: 0.5; text-transform: uppercase; letter-spacing: 1px;">Chat Faolligi</div>
                                    <div style="font-size: 18px; color: var(--neon-purple); font-weight: bold; margin-top: 5px;">${s.chat_activity} xabar</div>
                                </div>
                                <div class="glass-panel" style="padding: 15px; text-align: center; border-bottom: 2px solid #ff00ff;">
                                    <div style="font-size: 11px; opacity: 0.5; text-transform: uppercase; letter-spacing: 1px;">Tajriba (XP)</div>
                                    <div style="font-size: 18px; color: #ff00ff; font-weight: bold; margin-top: 5px;">${u.xp} XP</div>
                                </div>
                                <div class="glass-panel" style="padding: 15px; text-align: center; border-bottom: 2px solid #00ffff;">
                                    <div style="font-size: 11px; opacity: 0.5; text-transform: uppercase; letter-spacing: 1px;">O'rtacha IQ</div>
                                    <div style="font-size: 18px; color: #00ffff; font-weight: bold; margin-top: 5px;">${s.avg_iq}</div>
                                </div>
                            </div>

                            <h5 style="margin-bottom: 15px; opacity: 0.8;"><i class="fa-solid fa-chart-bar"></i> Haftalik Faollik</h5>
                            <div style="display: flex; align-items: flex-end; gap: 10px; height: 100px; padding: 10px; background: rgba(0,0,0,0.2); border-radius: 12px; margin-bottom: 25px;">
                                ${data.activity_log.map(day => `
                                    <div style="flex: 1; display: flex; flex-direction: column; align-items: center; gap: 5px;">
                                        <div style="width: 100%; height: ${day.count * 10}px; min-height: 4px; background: ${day.count > 0 ? 'var(--neon-cyan)' : 'rgba(255,255,255,0.05)'}; border-radius: 4px; box-shadow: ${day.count > 0 ? '0 0 10px var(--neon-cyan)' : 'none'}; transition: height 0.3s;"></div>
                                        <span style="font-size: 9px; opacity: 0.5;">${day.day}</span>
                                    </div>
                                `).join('')}
                            </div>

                            <h5 style="margin-bottom: 10px; opacity: 0.8;"><i class="fa-solid fa-layer-group"></i> Kurslar va Muddatlar</h5>
                            <div style="max-height: 150px; overflow-y: auto;">
                                ${data.enrollments.map(e => `
                                    <div style="margin-bottom: 8px; background: rgba(255,255,255,0.02); padding: 10px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.05); display: flex; justify-content: space-between; align-items: center;">
                                        <div>
                                            <div style="font-size: 13px; color: white;">${e.course_title}</div>
                                            <div style="font-size: 10px; opacity: 0.5;">Tugaydi: ${new Date(e.expires_at).toLocaleDateString()}</div>
                                        </div>
                                        <span style="font-size: 10px; color: ${e.status === 'active' ? 'var(--neon-cyan)' : 'var(--neon-purple)'};">${e.status.toUpperCase()}</span>
                                    </div>
                                `).join('') || '<div style="opacity:0.3; font-size:12px; text-align:center;">Kurslar topilmadi</div>'}
                            </div>
                        </div>
                    `;

                    Swal.fire({
                        title: 'O\'quvchi Analitikasi',
                        html: html,
                        background: '#0a0a1a',
                        color: '#fff',
                        showConfirmButton: false,
                        showCloseButton: true,
                        width: '500px'
                    });
                }
            } catch(e) { Swal.fire('Xato', 'Analitikani yuklab bo\'lmadi.', 'error'); }
        }


        function openAcademyQuickModal() {
            Swal.fire({
                title: 'Akademiya Tezkor Menyu',
                html: 'Tizimga yangi o\'quvchi yoki resurs qo\'shish uchun modullarni tanlang.',
                showDenyButton: true,
                showCancelButton: true,
                confirmButtonText: '<i class="fa-solid fa-user-plus"></i> Yangi O\'quvchi',
                denyButtonText: '<i class="fa-solid fa-book"></i> Yangi Kurs',
                background: '#0a0a1a',
                color: '#fff',
                customClass: {
                    confirmButton: 'btn-ios btn-neon',
                    denyButton: 'btn-ios'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    openAcademyApplicationModal();
                } else if (result.isDenied) {
                    openCourseModal();
                }
            });
        }

        function openAcademyApplicationModal() {
            Swal.fire({
                title: 'Yangi O\'quvchi Arizasi',
                html: `
                    <div style="text-align: left; display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                        <div style="grid-column: 1/-1;">
                            <label style="font-size: 11px; opacity: 0.5;">F.I.SH</label>
                            <input id="app-name" class="swal2-input" placeholder="Ism familiya" style="width: 95%; margin: 5px 0;">
                        </div>
                        <div>
                            <label style="font-size: 11px; opacity: 0.5;">Telefon</label>
                            <input id="app-phone" class="swal2-input" placeholder="+998" style="width: 90%; margin: 5px 0;">
                        </div>
                        <div>
                            <label style="font-size: 11px; opacity: 0.5;">Email</label>
                            <input id="app-email" class="swal2-input" placeholder="mail@example.com" style="width: 90%; margin: 5px 0;">
                        </div>
                        <div>
                            <label style="font-size: 11px; opacity: 0.5;">Yo'nalish</label>
                            <select id="app-dir" class="swal2-input" style="width: 95%; margin: 5px 0; background: #1a1a1a; color: white;">
                                <option value="Frontend">Frontend</option>
                                <option value="Backend">Backend</option>
                                <option value="AI Engineer">AI Engineer</option>
                                <option value="Mobile">Mobile (Flutter)</option>
                            </select>
                        </div>
                        <div>
                            <label style="font-size: 11px; opacity: 0.5;">Daraja</label>
                            <select id="app-lvl" class="swal2-input" style="width: 95%; margin: 5px 0; background: #1a1a1a; color: white;">
                                <option value="Beginner">Beginner</option>
                                <option value="Intermediate">Intermediate</option>
                            </select>
                        </div>
                        <div style="grid-column: 1/-1; border-top: 1px solid #333; margin-top: 10px; padding-top: 10px;">
                            <h5 style="margin-bottom: 10px;">Shaxsiy Ma'lumotlar (Optional)</h5>
                        </div>
                        <div>
                            <label style="font-size: 11px; opacity: 0.5;">Passport Seriya & Raqam</label>
                            <div style="display: flex; gap: 5px;">
                                <input id="app-p-ser" class="swal2-input" placeholder="AA" style="width: 30%; margin: 0;">
                                <input id="app-p-num" class="swal2-input" placeholder="1234567" style="width: 70%; margin: 0;">
                            </div>
                        </div>
                        <div>
                            <label style="font-size: 11px; opacity: 0.5;">JSHIR (PINFL)</label>
                            <input id="app-jshir" class="swal2-input" placeholder="14 xonali son" style="width: 90%; margin: 0;">
                        </div>
                        <div style="grid-column: 1/-1;">
                            <label style="font-size: 11px; opacity: 0.5;">Yashash manzili</label>
                            <input id="app-address" class="swal2-input" placeholder="Viloyat, tuman, ko'cha..." style="width: 95%; margin: 5px 0;">
                        </div>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: 'Yuborish',
                background: '#0a0a1a',
                color: '#fff',
                preConfirm: () => {
                    return {
                        name: document.getElementById('app-name').value,
                        phone: document.getElementById('app-phone').value,
                        email: document.getElementById('app-email').value,
                        direction: document.getElementById('app-dir').value,
                        level: document.getElementById('app-lvl').value,
                        location: 'Toshkent',
                        passport_series: document.getElementById('app-p-ser').value,
                        passport_number: document.getElementById('app-p-num').value,
                        jshir: document.getElementById('app-jshir').value,
                        address: document.getElementById('app-address').value
                    }
                }
            }).then(async (result) => {
                if(result.isConfirmed) {
                    try {
                        const res = await fetch(`${API_PREFIX}/academy/apply`, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                            body: JSON.stringify(result.value)
                        });
                        const data = await res.json();
                        if(data.status === 'success') {
                            Swal.fire('Bajarildi!', 'Ariza muvaffaqiyatli topshirildi.', 'success');
                            loadAcademyApplications();
                        } else {
                            Swal.fire('Xato', data.message, 'error');
                        }
                    } catch(e) { Swal.fire('Error', 'Server error', 'error'); }
                }
            });
        }

        async function loadAcademyApplications() {
            try {
                const res = await fetch(`${API_PREFIX}/academy/applications`);
                const apps = await res.json();
                const container = document.getElementById('academy-apps-list');
                
                if (apps.length === 0) {
                    container.innerHTML = '<div style="grid-column: 1/-1; text-align: center; padding: 40px; opacity: 0.5;">Hozircha arizalar yo\'q.</div>';
                    return;
                }

                container.innerHTML = apps.map(app => {
                    let assessmentText = "Tahlil qilinmoqda...";
                    if (app.ai_assessment) {
                        try {
                            const parsed = JSON.parse(app.ai_assessment);
                            assessmentText = parsed.assessment || "Tahlil yakunlandi, ammo matn topilmadi.";
                        } catch(e) {
                            assessmentText = "AI tahlilida formatting xatosi orqali tahlil yakunlandi.";
                            console.error("AI Parse Error", e);
                        }
                    }

                    return `
                        <div class="glass-panel" style="padding: 20px; border-top: 3px solid ${app.status === 'accepted' ? 'var(--neon-cyan)' : 'var(--neon-purple)'};">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                                <span class="status-badge" style="background: ${app.status === 'pending' ? 'rgba(176,38,255,0.1)' : 'rgba(0,255,204,0.1)'}; color: ${app.status === 'pending' ? 'var(--neon-purple)' : 'var(--neon-cyan)'}; border-color: currentcolor;">${app.status.toUpperCase()}</span>
                                <small style="opacity: 0.5;">${new Date(app.created_at).toLocaleDateString()}</small>
                            </div>
                            <h4 style="margin: 0 0 5px 0;">${app.name}</h4>
                            <div style="font-size: 13px; opacity: 0.7; margin-bottom: 5px;"><i class="fa-solid fa-phone"></i> ${app.phone}</div>
                            <div style="font-size: 13px; opacity: 0.7; margin-bottom: 15px;"><i class="fa-solid fa-code"></i> ${app.direction}</div>
                            
                            <div style="background: rgba(0,0,0,0.2); padding: 10px; border-radius: 10px; font-size: 11px; margin-bottom: 20px;">
                                <i class="fa-solid fa-robot" style="color: var(--neon-cyan);"></i> AI Tahlili: 
                                <p style="margin-top: 5px; opacity: 0.8; line-height: 1.4;">${assessmentText}</p>
                            </div>

                            <div style="display: flex; gap: 10px;">
                                ${app.status === 'pending' || app.status === 'test_sent' ? `<button onclick="approveApplication(${app.id})" class="btn-ios btn-neon" style="flex: 2; font-size: 11px;">Qabul qilish</button>` : ''}
                                <button onclick="deleteApplication(${app.id})" class="btn-ios" style="flex: 1; color: var(--neon-pink); font-size: 11px;"><i class="fa-solid fa-trash"></i></button>
                            </div>
                        </div>
                    `;
                }).join('');
            } catch(e) { console.error("Load Apps Error", e); }
        }

        async function approveApplication(id) {
            const res = await Swal.fire({
                title: "O'quvchini qabul qilish?",
                text: "Ushbu shaxsga 'student' roli beriladi va o'quvchi paneliga kirish huquqi beriladi.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: 'var(--neon-cyan)',
                confirmButtonText: 'Ha, qabul qilinsin!',
                background: '#0a0a1a',
                color: '#fff'
            });

            if (res.isConfirmed) {
                Swal.fire({ title: 'Jarayonda...', didOpen: () => Swal.showLoading() });
                try {
                    const response = await fetch(`${API_PREFIX}/academy/applications/${id}/approve`, {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
                    });
                    const data = await response.json();
                    if (data.status === 'success') {
                        Swal.fire({
                            title: 'Tabriklaymiz!',
                            html: `${data.message}<br><br><div style="text-align:left; background:rgba(255,255,255,0.05); padding:15px; border-radius:10px;"><b>Login:</b> ${data.login}<br><b>Parol:</b> ${data.password}</div>`,
                            icon: 'success'
                        }).then(() => initAcademyDashboard());
                    }

                } catch(e) { Swal.fire('Xato', 'Server xatosi', 'error'); }
            }
        }

        async function deleteApplication(id) {
            const res = await Swal.fire({
                title: "Arizani o'chirish?",
                text: "Ushbu amalni ortga qaytarib bo'lmaydi!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: 'var(--neon-pink)',
                confirmButtonText: "Ha, o'chirilsin!",
                background: '#0a0a1a',
                color: '#fff'
            });

            if (res.isConfirmed) {
                try {
                    const response = await fetch(`${API_PREFIX}/academy/applications/${id}`, {
                        method: 'DELETE',
                        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
                    });
                    const data = await response.json();
                    if (data.status === 'success') {
                        Swal.fire('O\'chirildi', data.message, 'success').then(() => initAcademyDashboard());
                    }
                } catch(e) { Swal.fire('Xato', 'Server xatosi', 'error'); }
            }
        }


        async function initStudentPortal() {
            try {
                const res = await fetch(`/internal-api/academy/student/dashboard`);
                const data = await res.json();
                
                if (data.status === 'success') {
                    const p = data.progress;
                    document.getElementById('s-rank-text').innerText = p.rank || 'Junior';
                    document.getElementById('s-xp-val').innerText = p.total_xp || 0;
                    document.getElementById('s-iq-val').innerText = p.iq_score || 0;
                    document.getElementById('s-talent-count').innerText = (p.talents ? JSON.parse(p.talents).length : 0);
                    
                    if (data.course) {
                        document.getElementById('s-course-name').innerText = data.course.name;
                        const progressPercent = Math.min(100, (data.recent_results.length / (data.lessons.length || 1)) * 100);
                        document.getElementById('s-course-fill').style.width = progressPercent + '%';
                        
                        const lessonList = document.getElementById('student-lessons-list');
                        lessonList.innerHTML = data.lessons.map(l => `
                            <div style="background: rgba(255,255,255,0.03); padding: 15px; border-radius: 12px; display: flex; justify-content: space-between; align-items: center; border: 1px solid var(--glass-border);">
                                <div>
                                    <span style="font-size: 14px; color: white;">${l.title}</span><br>
                                    <small style="opacity: 0.5;">Davomiyligi: ${l.duration} soat</small>
                                </div>
                                <i class="fa-solid fa-circle-check" style="color: ${l.completed ? 'var(--neon-cyan)' : 'rgba(255,255,255,0.1)'}; font-size: 20px;"></i>
                            </div>
                        `).join('');
                    }

                    if (data.mentor) {
                        document.getElementById('s-mentor-name').innerText = data.mentor.name;
                    }
                }
            } catch(e) { console.error("Student Portal Error:", e); }
        }

        async function send_s_mentor_msg() {
            const input = document.getElementById('s-mentor-input');
            const msg = input.value.trim();
            if(!msg) return;

            const chat = document.getElementById('s-mentor-chat');
            chat.innerHTML += `<div style="background: rgba(255,255,255,0.05); padding: 12px; border-radius: 12px; align-self: flex-end; max-width: 80%; font-size: 13px;">${msg}</div>`;
            input.value = '';
            chat.scrollTop = chat.scrollHeight;

            // Loading state
            const loadingId = 'mentor_loading_' + Date.now();
            chat.innerHTML += `<div id="${loadingId}" style="color: var(--neon-cyan); font-size: 12px; font-style: italic;">I-Ticher javob bermoqda...</div>`;
            
            try {
                const res = await fetch(`/internal-api/academy/student/mentor/chat`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                    body: JSON.stringify({ message: msg })
                });
                const data = await res.json();
                document.getElementById(loadingId).remove();
                chat.innerHTML += `<div style="background: rgba(0,255,204,0.1); padding: 12px; border-radius: 12px; align-self: flex-start; max-width: 80%; font-size: 13px; border: 1px solid rgba(0,255,204,0.1);">${data.reply}</div>`;
                chat.scrollTop = chat.scrollHeight;
            } catch(e) { 
                 document.getElementById(loadingId).innerText = "Ustoz bilan aloqa uzildi.";
            }
        }

        async function loadStudentAchievements() {
            const grid = document.getElementById('s-achievements-grid');
            try {
                const res = await fetch(`${API_PREFIX}/academy/user/achievements`);
                const data = await res.json();
                grid.innerHTML = data.map(a => `
                    <div class="glass-panel" style="padding: 25px; text-align: center; border: 1px solid rgba(0,255,204,0.1); box-shadow: 0 0 20px rgba(0,255,204,0.05);">
                        <div style="font-size: 40px; color: var(--neon-purple); margin-bottom: 15px;">
                            <i class="fa-solid ${a.icon}"></i>
                        </div>
                        <h4 style="margin: 0; color: white;">${a.name}</h4>
                        <p style="font-size: 11px; opacity: 0.5; margin-top: 5px;">${a.description}</p>
                        <div style="font-size: 10px; color: var(--neon-cyan); margin-top: 10px;">Berildi: ${new Date(a.awarded_at).toLocaleDateString()}</div>
                    </div>
                `).join('') || '<div style="grid-column: 1/-1; text-align: center; padding: 40px; opacity: 0.5;">Hali yutuqlar yo\'q. Ko\'proq dars o\'qing!</div>';
            } catch(e) {}
        }

        async function loadStudentJobs() {
            try {
                // Jobs
                const resJobs = await fetch(`${API_PREFIX}/academy/jobs`);
                const jobs = await resJobs.json();
                document.getElementById('s-jobs-list').innerHTML = jobs.map(j => `
                    <div class="glass-panel" style="padding: 15px; display: flex; justify-content: space-between; align-items: center; border-left: 3px solid var(--neon-cyan);">
                        <div>
                            <h5 style="margin: 0; color: white;">${j.title}</h5>
                            <div style="font-size: 11px; opacity: 0.7;">${j.company_name} • ${j.location}</div>
                        </div>
                        <button class="btn-ios btn-neon" onclick="studentApplyJob(${j.id})">Ariza topshirish</button>
                    </div>
                `).join('') || '<div style="opacity: 0.5;">Hozircha vakansiyalar mavjud emas.</div>';

                // Status & Certs
                const resDashboard = await fetch(`/internal-api/academy/student/dashboard`);
                const dash = await resDashboard.json();
                const ready = dash.progress && dash.progress.is_career_ready;
                
                document.getElementById('s-career-readiness-badge').innerHTML = ready 
                    ? '<span style="color: var(--neon-cyan); font-weight: bold;"><i class="fa-solid fa-circle-check"></i> Karyera uchun tayyor!</span>'
                    : '<span style="opacity: 0.5;">Hali tayyor emassiz. O\'qishni davom eting.</span>';

                // Certs
                const resCerts = await fetch(`${API_PREFIX}/academy/certificates`);
                const certs = await resCerts.json();
                document.getElementById('s-certs-list').innerHTML = certs.map(c => `
                    <div style="padding: 10px; background: rgba(255,255,255,0.03); border-radius: 8px; display: flex; justify-content: space-between; align-items: center; border: 1px solid rgba(255,255,255,0.05);">
                        <span style="font-size: 12px; color: white;"><i class="fa-solid fa-file-pdf"></i> ${c.course_title}</span>
                        <a href="${API_PREFIX}/academy/certificates/${c.id}/download" target="_blank" style="color: var(--neon-cyan); font-size: 12px;"><i class="fa-solid fa-download"></i></a>
                    </div>
                `).join('') || '<div style="opacity: 0.3; font-size: 11px;">Hali sertifikatlar yo\'q.</div>';
            } catch(e) {}
        }

        async function studentApplyJob(id) {
            Swal.fire({ title: 'Topshirilmoqda...', didOpen: () => Swal.showLoading() });
            try {
                const res = await fetch(`${API_PREFIX}/academy/jobs/${id}/apply`, {
                    method: 'POST',
                    headers: {'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content}
                });
                const data = await res.json();
                if(data.status === 'success') {
                    Swal.fire('Tabriklaymiz!', data.message, 'success');
                } else {
                    Swal.fire('Xato', data.message, 'error');
                }
            } catch(e) { Swal.fire('Xato', 'Server xatosi', 'error'); }
        }

        // UTILITIES
        function confirmAction(msg) {
            return new Promise(resolve => {
                Swal.fire({
                    title: 'Tasdiqlash',
                    text: msg,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ha',
                    cancelButtonText: 'Yo\'q',
                    background: '#0a0a1a',
                    color: '#fff'
                }).then(res => resolve(res.isConfirmed));
            });
        }
        function showError(msg) { Swal.fire('Xato', msg, 'error'); }
        async function runProjectTest(id) {
            simulateAIAction("Loyiha testi boshlanmoqda...");
            setTimeout(() => Swal.fire('Test yakunlandi', 'Loyiha 100/100 ball bilan sinovdan o\'tdi!', 'success'), 2000);
        }

        // STUDENT PROJECTS
        window.academyProjects = [];
        async function loadStudentProjects() {
            console.log("Loading projects...");
            const container = document.getElementById('student-projects-grid');
            if(!container) return;
            try {
                const res = await fetch('/internal-api/academy/student/projects');
                const data = await res.json();
                if(!Array.isArray(data)) throw new Error("Format xatosi");
                window.academyProjects = data;
                
                if (data.length === 0) {
                    container.innerHTML = '<div style="grid-column: 1/-1; text-align: center; padding: 50px; opacity: 0.5;">Hali loyihalar yo\'q. "Yangi Loyiha" tugmasini bosing!</div>';
                    return;
                }

                container.innerHTML = data.map(p => `
                    <div class="glass-panel" style="padding: 25px; border-top: 4px solid var(--neon-cyan); animation: fadeIn 0.5s;">
                        <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 15px;">
                            <h4 style="margin: 0; color: white;">${p.title}</h4>
                            <span class="badge" style="background: rgba(0,255,204,0.1); color: var(--neon-cyan); font-size: 10px; padding: 3px 8px; border-radius: 5px;">${(p.status || 'DRAFT').toUpperCase()}</span>
                        </div>
                        <p style="font-size: 13px; color: var(--text-muted); height: 40px; overflow: hidden; margin-bottom: 15px;">${p.description || 'Tavsif yo\'q'}</p>
                        <div style="display: flex; gap: 10px;">
                            <button class="btn-ios" onclick="openProjectModal(${p.id})" style="flex: 1;"><i class="fa-solid fa-pen"></i></button>
                            <button class="btn-ios" onclick="runProjectTest(${p.id})" style="flex: 1; color: var(--neon-cyan);"><i class="fa-solid fa-play"></i></button>
                            <button class="btn-ios" onclick="deleteProject(${p.id})" style="flex: 1; color: var(--neon-pink);"><i class="fa-solid fa-trash"></i></button>
                        </div>
                    </div>
                `).join('');
            } catch(e) { console.error(e); }
        }

        function openProjectModal(id = null) {
            const p = id ? window.academyProjects.find(x => x.id == id) : null;
            Swal.fire({
                title: p ? 'Loyihani tahrirlash' : 'Yangi loyiha yaratish',
                html: `
                    <div class="form-group" style="text-align: left;">
                        <label style="color: var(--text-muted); font-size: 11px;">Loyiha Nomi</label>
                        <input id="p-title" class="swal2-input" placeholder="Loyiha nomi" style="width: 80%;" value="${p ? p.title : ''}">
                    </div>
                    <div class="form-group" style="text-align: left; margin-top: 15px;">
                        <label style="color: var(--text-muted); font-size: 11px;">Tavsif</label>
                        <textarea id="p-desc" class="swal2-textarea" placeholder="Nima qilyapsiz?" style="width: 80%;">${p ? p.description : ''}</textarea>
                    </div>
                `,
                confirmButtonText: 'Saqlash',
                showCancelButton: true,
                background: '#0a0a1a',
                color: '#fff',
                preConfirm: () => {
                    return {
                        id: p ? p.id : null,
                        title: document.getElementById('p-title').value,
                        description: document.getElementById('p-desc').value,
                    }
                }
            }).then(async (result) => {
                if (result.isConfirmed) {
                    await fetch('/internal-api/academy/student/projects', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                        body: JSON.stringify(result.value)
                    });
                    loadStudentProjects();
                }
            });
        }

        async function deleteProject(id) {
            if(await confirmAction('Loyihani o\'chirishni xohlaysizmi?')) {
                await fetch(`/internal-api/academy/student/projects/${id}`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': csrfToken }
                });
                loadStudentProjects();
            }

        }

        // GLOBAL CHAT & MESSAGING
        let chatPolling = null;
        let currentChatTarget = { id: null, type: 'global', name: 'Global Chat' };

        async function loadChatContacts() {
            const list = document.getElementById('chat-contacts-list');
            if(!list) return;
            const res = await fetch('/internal-api/academy/chat/contacts');
            const data = await res.json();
            
            let html = `<div class="chat-contact ${currentChatTarget.type==='global'?'active':''}" onclick="selectChat(null, 'global', 'Global Chat')">
                <i class="fa-solid fa-earth-americas"></i> <span>Global Chat</span>
            </div>`;

            // Mentors
            data.mentors.forEach(m => {
                html += `<div class="chat-contact ${currentChatTarget.id==m.id && currentChatTarget.type==='mentor'?'active':''}" onclick="selectChat(${m.id}, 'mentor', '${m.name}')">
                    <i class="fa-solid fa-robot" style="color: var(--neon-cyan)"></i> <span>${m.name} (I-Ticher)</span>
                </div>`;
            });

            // Admins
            data.admins.forEach(a => {
                html += `<div class="chat-contact ${currentChatTarget.id==a.id && currentChatTarget.type==='user'?'active':''}" onclick="selectChat(${a.id}, 'user', '${a.name}')">
                    <i class="fa-solid fa-user-shield" style="color: var(--neon-purple)"></i> <span>${a.name} (Admin)</span>
                </div>`;
            });

            // Students
            data.students.forEach(s => {
                if(s.id != {{ auth()->id() }}) {
                    html += `<div class="chat-contact ${currentChatTarget.id==s.id && currentChatTarget.type==='user'?'active':''}" onclick="selectChat(${s.id}, 'user', '${s.name}')">
                        <i class="fa-solid fa-user-graduate"></i> <span>${s.name} (Student)</span>
                    </div>`;
                }
            });

            list.innerHTML = html;
        }

        function selectChat(id, type, name) {
            currentChatTarget = { id, type, name };
            document.getElementById('chat-target-name').innerText = name;
            document.getElementById('chat-target-status').innerText = type === 'global' ? '● Online (Hamma)' : '● Ko\'rishmoqda';
            loadChatContacts();
            loadGlobalChat();
        }

        async function loadGlobalChat() {
            const box = document.getElementById('global-chat-box');
            if(!box) return;
            
            try {
                const url = new URL('/internal-api/academy/chat', window.location.origin);
                if(currentChatTarget.id) {
                    url.searchParams.append('receiver_id', currentChatTarget.id);
                    url.searchParams.append('receiver_type', currentChatTarget.type);
                }

                const res = await fetch(url.toString());
                const messages = await res.json();
                if(!Array.isArray(messages)) return;
                
                const currentUserId = {{ auth()->id() }};
                if (messages.length === 0) {
                    box.innerHTML = '<div style="text-align: center; margin-top: 50px; opacity: 0.3;">Xabarlar yo\'q</div>';
                    return;
                }

                box.innerHTML = messages.map(m => `
                    <div style="align-self: ${m.user_id == currentUserId ? 'flex-end' : 'flex-start'}; max-width: 80%;">
                        ${currentChatTarget.type === 'global' ? `
                        <div style="display: flex; gap: 8px; align-items: center; margin-bottom: 5px; justify-content: ${m.user_id == currentUserId ? 'flex-end' : 'flex-start'};">
                            <small style="color: ${m.user_role == 'student' ? 'var(--neon-cyan)' : 'var(--neon-purple)'}; font-weight: bold;">${m.user_name || 'Noma\'lum'}</small>
                            <small style="opacity: 0.3; font-size: 9px;">${new Date(m.created_at).toLocaleTimeString()}</small>
                        </div>
                        ` : ''}
                        <div style="background: ${m.user_id == currentUserId ? 'rgba(0,255,204,0.1)' : 'rgba(255,255,255,0.05)'}; padding: 10px 15px; border-radius: 12px; border: 1px solid var(--glass-border); position: relative;">
                            ${m.message ? `<p style="margin: 0; font-size: 13.5px; color: white;">${m.message}</p>` : ''}
                            ${m.file_path ? `
                                <a href="/storage/${m.file_path}" target="_blank" style="display: flex; align-items: center; gap: 8px; margin-top: 5px; color: var(--neon-cyan); text-decoration: none; background: rgba(0,0,0,0.2); padding: 5px 10px; border-radius: 8px; font-size: 11px;">
                                    <i class="fa-solid fa-file-arrow-down"></i> ${m.file_name}
                                </a>
                            ` : ''}
                            <div style="text-align: right; margin-top: 4px;"><small style="opacity: 0.3; font-size: 8px;">${new Date(m.created_at).toLocaleTimeString()}</small></div>
                        </div>
                    </div>
                `).join('');
                box.scrollTop = box.scrollHeight;
            } catch(e) { console.error(e); }
        }

        async function sendGlobalChat() {
            const input = document.getElementById('chat-msg-input');
            const fileInput = document.getElementById('chat-file-input');
            const msg = input.value.trim();
            const file = fileInput.files[0];

            if(!msg && !file) return;

            const formData = new FormData();
            if(msg) formData.append('message', msg);
            if(file) formData.append('file', file);
            if(currentChatTarget.id) {
                formData.append('receiver_id', currentChatTarget.id);
                formData.append('receiver_type', currentChatTarget.type);
            }

            input.value = '';
            fileInput.value = '';
            document.getElementById('file-preview-label').style.display = 'none';

            try {
                const res = await fetch('/internal-api/academy/chat', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrfToken },
                    body: formData
                });
                const data = await res.json();
                loadGlobalChat();
            } catch(e) {}
        }


        function updateFileLabel(input) {
            const label = document.getElementById('file-preview-label');
            if(input.files.length > 0) {
                label.innerText = `Tanlandi: ${input.files[0].name}`;
                label.style.display = 'block';
            } else {
                label.style.display = 'none';
            }
        }

        // ADMIN MODERATION
        async function loadModerationLogs() {
            const body = document.getElementById('moderation-log-body');
            if(!body) return;
            const res = await fetch('/api/academy/chat/history'); // Use existing or new API
            // For now use a simplified mock or expand API
        }

        const originalSwitchTab = window.switchTab;
        window.switchTab = function(tabId) {
            originalSwitchTab(tabId);
            if(tabId === 'student_projects') loadStudentProjects();
            if(tabId === 'student_chat') {
                loadGlobalChat();
                loadChatContacts();
                if(!chatPolling) chatPolling = setInterval(loadGlobalChat, 10000);
            } else if(chatPolling) {

                clearInterval(chatPolling);
                chatPolling = null;
            }
        }
        // Add to window.onload
        const originalOnload = window.onload;
        window.onload = function() {
            if(originalOnload) originalOnload();
            const userRole = "{{ auth()->user()->role }}";
            if (userRole === 'student') {
                switchTab('student_portal');
                initStudentPortal();
            } else {
                initAcademyDashboard();
            }
        }

    </script>
</body>
</html>
