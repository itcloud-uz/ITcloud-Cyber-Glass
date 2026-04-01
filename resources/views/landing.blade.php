<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ITcloud | Kelajak Texnologiyalari Markazi</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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

        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Outfit', sans-serif; scroll-behavior: smooth; }
        body { background-color: var(--bg-dark); color: var(--text-main); overflow-x: hidden; }

        /* Background Blobs */
        .ambient-blob { position: absolute; border-radius: 50%; filter: blur(140px); z-index: -1; opacity: 0.15; pointer-events: none; }
        .blob-1 { width: 600px; height: 600px; background: var(--neon-purple); top: -10%; left: -10%; }
        .blob-2 { width: 500px; height: 500px; background: var(--neon-cyan); bottom: 10%; right: -5%; }

        /* Navigation */
        nav {
            position: fixed; top: 0; left: 0; width: 100%; padding: 25px 8%; z-index: 1000;
            display: flex; justify-content: space-between; align-items: center;
            background: rgba(5, 5, 10, 0.8); backdrop-filter: blur(15px);
            border-bottom: 1px solid var(--glass-border);
        }
        .logo-animated { font-size: 26px; font-weight: 800; animation: logoFloat 4s ease-in-out infinite; cursor: pointer; }
        @keyframes logoFloat { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-5px); } }

        .nav-links { display: flex; gap: 30px; margin-right: auto; margin-left: 50px; }
        .nav-links a { text-decoration: none; color: var(--text-muted); font-weight: 500; font-size: 14px; transition: 0.3s; text-transform: uppercase; letter-spacing: 1px; }
        .nav-links a:hover { color: var(--neon-cyan); text-shadow: 0 0 10px var(--neon-cyan); }

        .lang-switch { display: flex; gap: 15px; margin-left: 30px; }
        .lang-switch a { text-decoration: none; font-size: 18px; opacity: 0.5; transition: 0.3s; }
        .lang-switch a:hover, .lang-switch a.active { opacity: 1; transform: scale(1.1); }

        /* Section Global Styles */
        section { padding: 120px 10%; position: relative; }
        .section-header { margin-bottom: 80px; text-align: center; }
        .section-header h2 { font-size: clamp(32px, 5vw, 48px); font-weight: 800; margin-bottom: 25px; line-height: 1.2; }
        .section-header h2 span { color: var(--neon-cyan); }
        .section-header p { color: var(--text-muted); max-width: 650px; margin: 0 auto; font-size: 17px; line-height: 1.7; }

        /* Hero */
        .hero { min-height: 100vh; display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center; padding-top: 100px; }
        .hero h1 { font-size: clamp(40px, 8vw, 76px); font-weight: 800; line-height: 1.1; margin-bottom: 30px; max-width: 1000px; }
        .hero h1 span { background: linear-gradient(90deg, var(--neon-cyan), var(--neon-purple)); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .hero p { font-size: clamp(16px, 2vw, 22px); color: var(--text-muted); max-width: 850px; margin-bottom: 60px; line-height: 1.6; }

        .btn-neon { padding: 22px 50px; border-radius: 20px; border: none; background: var(--neon-cyan); color: #000; font-weight: 800; font-size: 18px; cursor: pointer; transition: 0.3s; text-decoration: none; display: inline-flex; align-items: center; gap: 15px; }
        .btn-neon:hover { transform: scale(1.05); box-shadow: 0 0 50px rgba(0,255,204,0.6); }

        /* Social Proof Carousel */
        .carousel-wrapper { overflow: hidden; white-space: nowrap; padding: 60px 0; border-top: 1px solid var(--glass-border); border-bottom: 1px solid var(--glass-border); position: relative; width: 100%; margin-top: 80px; }
        .carousel-track { display: inline-block; animation: scroll 40s linear infinite; }
        .carousel-track img { height: 35px; margin: 0 70px; filter: grayscale(1) brightness(3); opacity: 0.4; transition: 0.4s; }
        .carousel-track img:hover { filter: grayscale(0) brightness(1); opacity: 1; }
        @keyframes scroll { from { transform: translateX(0); } to { transform: translateX(-50%); } }

        /* Products Grid */
        .product-card { 
            background: var(--glass-bg); border: 1px solid var(--glass-border); border-radius: 40px; padding: clamp(30px, 5vw, 60px);
            transition: 0.4s; overflow: hidden; position: relative; margin-bottom: 50px;
        }
        .product-card:hover { border-color: var(--neon-cyan); box-shadow: 0 0 60px rgba(0, 255, 204, 0.15); }

        /* Dashboard Demo Mockup */
        .dash-mockup {
            background: #080812; border-radius: 24px; border: 1px solid var(--glass-border); overflow: hidden;
            width: 100%; height: 380px; margin-top: 40px; position: relative; box-shadow: 0 30px 60px rgba(0,0,0,0.6);
        }
        .dash-sidebar { width: 70px; height: 100%; background: #0c0c16; border-right: 1px solid var(--glass-border); position: absolute; }
        .dash-content { padding: 30px 30px 30px 100px; height: 100%; display: flex; flex-direction: column; }
        .dash-card { background: rgba(255,255,255,0.03); border: 1px solid var(--glass-border); border-radius: 16px; padding: 20px; animation: cardGlow 4s infinite alternate; }
        @keyframes cardGlow { from { border-color: var(--glass-border); } to { border-color: var(--neon-cyan); } }

        /* FaceID Mesh Animation */
        .face-scan-box { width: clamp(280px, 40vw, 420px); height: clamp(280px, 40vw, 420px); margin: 0 auto; position: relative; border-radius: 50%; border: 2px dashed var(--neon-cyan); padding: 25px; animation: rotateDashed 15s linear infinite; }
        @keyframes rotateDashed { from { transform: rotate(0); } to { transform: rotate(360deg); } }
        .face-inner { width: 100%; height: 100%; border-radius: 50%; overflow: hidden; background: url('https://cdn.pixabay.com/photo/2016/01/10/22/07/man-1132617_1280.png') center/cover; position: relative; filter: grayscale(1) brightness(0.6); }
        .scan-line { position: absolute; width: 100%; height: 3px; background: var(--neon-cyan); box-shadow: 0 0 20px var(--neon-cyan); animation: scanLine 3s ease-in-out infinite; }
        @keyframes scanLine { 0% { top: 0; } 50% { top: 100%; } 100% { top: 0; } }

        /* Calculator */
        .calculator-card { background: var(--glass-bg); padding: clamp(30px, 5vw, 60px); border-radius: 40px; border: 1px solid var(--neon-purple); box-shadow: 0 20px 50px rgba(176, 38, 255, 0.1); }
        .calc-option { display: flex; align-items: center; justify-content: space-between; padding: 18px 25px; border-radius: 18px; background: rgba(0,0,0,0.3); margin-bottom: 12px; cursor: pointer; transition: 0.3s; border: 1px solid var(--glass-border); }
        .calc-option:hover { border-color: rgba(0, 255, 204, 0.4); }
        .calc-option.active { border: 1px solid var(--neon-cyan); background: rgba(0,255,204,0.08); box-shadow: 0 0 20px rgba(0, 255, 204, 0.1); }

        .price-display { font-size: clamp(40px, 6vw, 64px); font-weight: 800; color: var(--neon-cyan); margin-top: 35px; text-align: center; text-shadow: 0 0 30px rgba(0, 255, 204, 0.3); }

        /* Contact Section */
        .contact-container { display: grid; grid-template-columns: 1.2fr 0.8fr; gap: clamp(40px, 8vw, 100px); align-items: start; }
        .form-glass { background: var(--glass-bg); padding: clamp(30px, 5vw, 60px); border-radius: 40px; border: 1px solid var(--glass-border); backdrop-filter: var(--glass-blur); }
        
        .input-group { margin-bottom: 30px; display: flex; flex-direction: column; width: 100%; }
        .input-group label { display: block; font-size: 13px; color: var(--text-muted); margin-bottom: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 1.5px; text-align: left; }
        .input-field { 
            width: 100%; padding: 20px; background: rgba(0,0,0,0.4); border: 1px solid var(--glass-border); 
            border-radius: 20px; color: #fff; outline: none; transition: 0.3s; font-size: 16px; 
            box-shadow: inset 0 2px 10px rgba(0,0,0,0.2);
        }
        .input-field::placeholder { color: rgba(255,255,255,0.2); }
        .input-field:focus { border-color: var(--neon-cyan); background: rgba(0,0,0,0.6); box-shadow: 0 0 20px rgba(0,255,204,0.1); }
        
        textarea.input-field { resize: none; min-height: 140px; } footer { padding: 60px 10%; border-top: 1px solid var(--glass-border); text-align: center; color: var(--text-muted); }

        /* Dynamic Starfield Background */
        .stars-container { position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: -2; pointer-events: none; overflow: hidden; background: radial-gradient(circle at 50% 50%, #0a0a20 0%, #05050a 100%); }
        .star { position: absolute; background: white; border-radius: 50%; opacity: 0.8; box-shadow: 0 0 10px #fff; animation: blink-star var(--duration) infinite alternate; }
        @keyframes blink-star { from { opacity: 0.2; transform: scale(0.6); } to { opacity: 1; transform: scale(1.3); } }

        /* Shooting Star Effect */
        .shooting-star { position: absolute; top: 0; left: 0; width: 3px; height: 3px; background: linear-gradient(90deg, #fff, transparent); border-radius: 50%; opacity: 0; pointer-events: none; z-index: -1; animation: shoot-random 6s linear infinite; }
        @keyframes shoot-random { 
            0% { transform: translate(0, 0) rotate(215deg) scale(0); opacity: 0; } 
            1% { opacity: 1; transform: translate(0, 0) rotate(215deg) scale(1); }
            20% { transform: translate(600px, 600px) rotate(215deg) scale(1); opacity: 0; }
            100% { opacity: 0; }
        }

        /* Mouse Magic Wand Canvas */
        #magicCursor { position: fixed; top: 0; left: 0; pointer-events: none; z-index: 9999; }

        @media (max-width: 1024px) { 
            .nav-links { display: none; } 
            .hero h1 { font-size: 52px; } 
            .grid, .contact-container, .grid-responsive { grid-template-columns: 1fr !important; } 
            nav { padding: 15px 5%; }
            .btn-neon { padding: 15px 25px; font-size: 15px; }
            section { padding: 80px 5%; }
        }
    </style>
</head>
<body>

    <div class="stars-container" id="stars"></div>
    <canvas id="magicCursor"></canvas>

    <div class="ambient-blob blob-1"></div>
    <div class="ambient-blob blob-2"></div>

    <nav>
        <div class="logo-animated" ondblclick="location.href='{{ route('login') }}'" style="cursor: pointer;">
            IT<span style="color: var(--neon-cyan)">cloud</span>
        </div>
        <div class="nav-links">
            <a href="#products">{{ __('Services') }}</a>
            <a href="#security">Cyber-Gate</a>
            <a href="#calc">{{ __('Calculator') }}</a>
            <a href="#contact">Contact</a>
        </div>
        <div style="display: flex; align-items: center; gap: 20px;">
            <div class="lang-switch">
                <a href="{{ route('lang.switch', 'uz') }}" class="{{ App::getLocale() == 'uz' ? 'active' : '' }}" title="O'zbek">UZ</a>
                <a href="{{ route('lang.switch', 'ru') }}" class="{{ App::getLocale() == 'ru' ? 'active' : '' }}" title="Русский">RU</a>
                <a href="{{ route('lang.switch', 'en') }}" class="{{ App::getLocale() == 'en' ? 'active' : '' }}" title="English">EN</a>
            </div>

            <div style="width: 45px; height: 45px; border-radius: 50%; border: 2px solid var(--glass-border); display: flex; align-items: center; justify-content: center; background: var(--glass-bg); cursor: pointer; transition: 0.4s; color: white;" onclick="location.href='{{ route('login') }}'" title="{{ __('Personal Cabinet') }}">
                <i class="fa-solid fa-user-astronaut" style="font-size: 20px;"></i>
            </div>
            
            <a href="{{ route('client.login') }}" class="btn-neon" style="padding: 12px 25px; font-size: 14px; border-radius: 12px;">
                <i class="fa-solid fa-user-gear"></i> {{ __('Client Portal') }}
            </a>
        </div>
    </nav>

    <!-- Section 1: Hero -->
    <section class="hero" id="home">
        <div style="position: absolute; top: 100px; left: 8%; background: rgba(176, 38, 255, 0.1); padding: 6px 15px; border-radius: 10px; border: 1px solid var(--neon-purple); color: var(--neon-purple); font-weight: 700; font-size: 11px; letter-spacing: 1px; width: fit-content; z-index: 5;">
            <i class="fa-solid fa-bolt"></i> {{ __('URGENCY: 2026 TRENDS ENABLED') }}
        </div>
        <h1>{{ __('We create future') }} <span>{{ __('Software Solutions') }}</span></h1>
        <p>{{ __('ITcloud — born as an innovative startup from a team of engineers with many years of international experience. A new era of automating your business.') }}</p>
        
        <a href="/constructor" class="btn-neon">
            <i class="fa-solid fa-compass-drafting"></i> {{ __('Start Projects') }}
        </a>

        <!-- Social Proof Carousel -->
        <div style="margin-top: 80px; width: 100%;">
            <p style="color: var(--text-muted); font-size: 12px; text-transform: uppercase; letter-spacing: 3px; margin-bottom: 30px;">{{ __('Trusted by those who lead') }}</p>
            <div class="carousel-wrapper">
                <div class="carousel-track">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/a/a9/Amazon_logo.svg" alt="Amazon">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/2/2f/Google_2015_logo.svg" alt="Google">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/0/08/Netflix_2015_logo.svg" alt="Netflix">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/e/e8/Tesla_logo.png" alt="Tesla">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/4/44/Microsoft_logo.svg" alt="Microsoft">
                    <!-- Duplicate for infinite -->
                    <img src="https://upload.wikimedia.org/wikipedia/commons/a/a9/Amazon_logo.svg" alt="Amazon">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/2/2f/Google_2015_logo.svg" alt="Google">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/0/08/Netflix_2015_logo.svg" alt="Netflix">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/e/e8/Tesla_logo.png" alt="Tesla">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/4/44/Microsoft_logo.svg" alt="Microsoft">
                </div>
            </div>
        </div>
    </section>

    <!-- Section 2: Interactive Product Showcase -->
    <section id="products">
        <div class="section-header">
            <h2>{{ __('Obsidian OS') }} <span>{{ __('Automation') }}</span></h2>
            <p>{{ __('Not just text, feel the genius architecture. Hover over the interface.') }}</p>
        </div>

        <div class="product-card">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 50px; align-items: center;">
                <div>
                    <h3 style="color: var(--neon-cyan);">{{ __('Interactive Dashboard Demo') }}</h3>
                    <p>{{ __('Obsidian OS v1 – system manages all financial flows, employee KPIs and AI logs from a single genius panel in real time.') }}</p>
                    <ul style="list-style: none; color: var(--text-muted); margin-bottom: 30px;">
                        <li style="margin-bottom: 15px;"><i class="fa-solid fa-check" style="color: var(--neon-cyan); margin-right: 15px;"></i> {{ __('Deep ML Analysis') }}</li>
                        <li style="margin-bottom: 15px;"><i class="fa-solid fa-check" style="color: var(--neon-cyan); margin-right: 15px;"></i> {{ __('Zero-Latency Sync') }}</li>
                    </ul>
                    <a href="/constructor" class="btn-neon" style="padding: 15px 35px; font-size: 15px;">{{ __('View Full Version') }}</a>
                </div>
                <!-- Mini Mockup Animation -->
                <div class="dash-mockup">
                    <div class="dash-sidebar"></div>
                    <div class="dash-content">
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                            <div class="dash-card">
                                <span style="font-size: 10px; opacity: 0.5;">Income / Today</span>
                                <div style="font-size: 20px; font-weight: 800; color: var(--neon-cyan); margin-top: 5px;">$12,450</div>
                                <div style="width: 100%; height: 5px; background: rgba(0,0,0,0.5); margin-top: 15px; border-radius: 5px;">
                                    <div style="width: 80%; height: 100%; background: var(--neon-cyan); border-radius: 5px;"></div>
                                </div>
                            </div>
                            <div class="dash-card" style="animation-delay: 0.5s;">
                                <span style="font-size: 10px; opacity: 0.5;">Active Users</span>
                                <div style="font-size: 20px; font-weight: 800; color: var(--neon-purple); margin-top: 5px;">1,245</div>
                                <div style="width: 100%; height: 5px; background: rgba(0,0,0,0.5); margin-top: 15px; border-radius: 5px;">
                                    <div style="width: 45%; height: 100%; background: var(--neon-purple); border-radius: 5px;"></div>
                                </div>
                            </div>
                        </div>
                        <div style="margin-top: 20px; background: rgba(255,255,255,0.02); height: 140px; border-radius: 15px; border: 1px dashed var(--glass-border); display: flex; align-items: center; justify-content: center; color: var(--text-muted); font-size: 12px;">
                            <i class="fa-solid fa-brain" style="margin-right: 15px; font-size: 24px; color: var(--neon-cyan);"></i> Processing Real-Time AI Metrics...
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Section 3: Cyber-Gate Security -->
    <section id="security" style="background: rgba(176, 38, 255, 0.02)">
        <div class="section-header">
            <h2>{{ __('Cyber-Defense') }} <span>{{ __('FaceID 2026') }}</span></h2>
            <p>{{ __('Passwords are in the past. Protect your business through human face recognition system.') }}</p>
        </div>

        <div class="grid-responsive" style="display: grid; grid-template-columns: 1fr 1fr; gap: 80px; align-items: center;">
            <div class="face-scan-box">
                <div class="face-inner">
                    <div class="scan-line"></div>
                </div>
            </div>
            <div>
                <h3 style="font-size: 32px; margin-bottom: 25px;">{{ __('Biometric') }} <span>{{ __('Master-Key') }}</span></h3>
                <p style="color: var(--text-muted); line-height: 1.8; margin-bottom: 30px;">
                    {{ __('FaceID integration links the entire system to a single human face. Data theft or alien employee entry is nulled. Liveness check through genius combination of OpenCV and Gemini Vision model.') }}
                </p>
                <div class="service-card" style="padding: 25px; margin-bottom: 15px;">
                    <i class="fa-solid fa-shield-virus" style="margin-bottom: 15px; font-size: 24px;"></i>
                    <h4 style="margin-bottom: 5px;">{{ __('Anti-Spoofing Algorithm') }}</h4>
                    <p style="font-size: 13px;">{{ __('System cannot be fooled by photo or video.') }}</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Section 4: Quotation Calculator -->
    <section id="calc">
        <div class="section-header">
            <h2>{{ __('Project Price') }} <span>{{ __('Calculation') }}</span></h2>
            <p>{{ __('Select necessary modules and set the project scope. AI outputs real-time pricing.') }}</p>
        </div>

        <div class="calculator-card">
            <div class="grid-responsive" style="display: grid; grid-template-columns: 1fr 1fr; gap: 40px;">
                <div>
                    <h4 style="margin-bottom: 20px; color: var(--text-muted); text-transform: uppercase; font-size: 12px;">1. {{ __('Select Modules') }}</h4>
                    @foreach($priceServices as $ps)
                    <div class="calc-option" onclick="toggleService(this, {{ $ps->base_price }}, {{ $ps->max_price }}, {{ $ps->min_days }})">
                        <span><i class="fa-solid {{ $ps->icon }}"></i> {{ $ps->name }}</span>
                        <b>+${{ number_format($ps->base_price) }}</b>
                    </div>
                    @endforeach

                    <h4 style="margin-top: 30px; margin-bottom: 20px; color: var(--text-muted); text-transform: uppercase; font-size: 12px;">2. {{ __('Project Scope') }}</h4>
                    <div style="display: flex; gap: 10px;">
                        <button class="btn-ios scope-btn active" onclick="setScope(this, 1.0)" style="flex:1;">Standard</button>
                        <button class="btn-ios scope-btn" onclick="setScope(this, 1.5)" style="flex:1;">Enterprise</button>
                        <button class="btn-ios scope-btn" onclick="setScope(this, 2.0)" style="flex:1;">Full Mastery</button>
                    </div>
                </div>
                <div style="text-align: center; display: flex; flex-direction: column; justify-content: center; background: rgba(0,0,0,0.2); border-radius: 30px; padding: 40px;">
                    <p style="text-transform: uppercase; color: var(--text-muted); font-size: 13px; letter-spacing: 2px;">{{ __('Estimated Value') }}</p>
                    <div class="price-display" id="totalPrice">$0</div>
                    <p style="margin-top: 15px; color: var(--neon-purple); font-weight: 700;">{{ __('Deadline') }}: <span id="timeDays">0</span> {{ __('days') }}</p>
                    
                    <div style="margin-top: 30px; font-size: 12px; color: var(--text-muted);">
                        * {{ __('Prices are displayed according to real-time adjustments in the Master Panel.') }}
                    </div>

                    <a href="/constructor" class="btn-neon" style="margin-top: 40px; justify-content: center;">
                        {{ __('Draw Project Architecture') }}
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Section 5: Contact -->
    <section id="contact">
        <div class="section-header">
            <h2>{{ __('Contact') }} <span>{{ __('Us') }}</span></h2>
            <p>{{ __('Have questions? Visit our office or write to us.') }}</p>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 80px;">
            <div class="form-glass" style="background: var(--glass-bg); padding: 50px; border-radius: 40px;">
                <form id="contactFormFull">
                    <div class="input-group">
                        <label>{{ __('Your Name') }}</label>
                        <input type="text" name="name" class="input-field" placeholder="Abdullo" required>
                    </div>
                    <div class="input-group">
                        <label>{{ __('Telegram / Phone') }}</label>
                        <input type="text" name="phone" class="input-field" placeholder="@username / +998" required>
                    </div>
                    <div class="input-group">
                        <label for="contact_msg">{{ __('Your Message') }}</label>
                        <textarea id="contact_msg" class="input-field" placeholder="{{ __('Tell us about your idea...') }}"></textarea>
                    </div>
                    <button type="submit" class="btn-neon" style="width: 100%;">
                        {{ __('Submit Request') }} <i class="fa-solid fa-paper-plane" style="margin-left: 10px;"></i>
                    </button>
                </form>
            </div>
            <div>
                <h3 style="margin-bottom: 40px; font-size: 32px;">{{ __('Communication') }} <span>{{ __('Points') }}</span></h3>
                <div style="display: flex; flex-direction: column; gap: 30px; color: var(--text-muted);">
                    <p style="font-size: 18px;"><i class="fa-solid fa-envelope" style="color: var(--neon-cyan); width: 40px;"></i> {{ $settings['contact_email'] ?? 'support@itcloud.uz' }}</p>
                    <p style="font-size: 18px;"><i class="fa-solid fa-phone" style="color: var(--neon-cyan); width: 40px;"></i> {{ $settings['contact_phone'] ?? '+998 71 234 56 78' }}</p>
                    <p style="font-size: 18px;"><i class="fa-solid fa-map-pin" style="color: var(--neon-cyan); width: 40px;"></i> {{ $settings['contact_address'] ?? 'Toshkent sh., IT-Park' }}</p>
                </div>
                <div style="display: flex; gap: 20px; margin-top: 60px;">
                    <a href="{{ $settings['social_telegram'] ?? '#' }}" class="social-icon"><i class="fa-brands fa-telegram"></i></a>
                    <a href="{{ $settings['social_instagram'] ?? '#' }}" class="social-icon"><i class="fa-brands fa-instagram"></i></a>
                    <a href="{{ $settings['social_linkedin'] ?? '#' }}" class="social-icon"><i class="fa-brands fa-linkedin"></i></a>
                </div>
            </div>
        </div>
    </section>

    <!-- ITcloud Academy Teaser -->
    <section id="academy" style="padding: 120px 10%; background: linear-gradient(180deg, rgba(176,38,255,0.05) 0%, rgba(5,5,10,0) 100%);">
        <div class="product-card" style="text-align: center; border: 1px solid var(--neon-purple); background: rgba(176,38,255,0.03);">
            <div style="background: rgba(176, 38, 255, 0.1); padding: 8px 16px; border-radius: 20px; border: 1px solid var(--neon-purple); color: var(--neon-purple); font-weight: 700; font-size: 11px; letter-spacing: 2px; width: fit-content; margin: 0 auto 30px;">
                ITCLOUD ACADEMY
            </div>
            <h2 style="font-size: clamp(32px, 5vw, 56px); font-weight: 800; margin-bottom: 25px;">
                Kelajak Dasturchilarini <span style="color: var(--neon-purple);">Tayyorlaymiz</span>
            </h2>
            <p style="font-size: 20px; color: var(--text-muted); max-width: 800px; margin: 0 auto 50px;">
                Real loyihalar, Bounty tizimi va ISA kafolati bilan professional IT karyerangizni boshlang.
            </p>
            <div style="display: flex; gap: 20px; justify-content: center; flex-wrap: wrap;">
                <a href="{{ route('academy.landing') }}" class="btn-neon" style="background: var(--neon-purple); box-shadow: 0 0 30px rgba(176,38,255,0.3); width: auto; padding: 20px 50px;">
                    Akademiya Portali <i class="fa-solid fa-arrow-up-right-from-square"></i>
                </a>
            </div>
        </div>
    </section>

    <footer style="background: rgba(0,0,0,0.5); padding: 80px 10% 40px; border-top: 1px solid var(--neon-cyan);">
        <p style="opacity: 0.6; letter-spacing: 1px;" ondblclick="secretEntryPrompt()">
            &copy; 2026 ITcloud Cyber-Glass Engine. {{ __('Built on years of international experience.') }}
        </p>
        <div style="margin-top: 20px; font-size: 12px; color: var(--neon-cyan); opacity: 0.4;">
            AES-256 Encrypted Connection | AI Guard Active
        </div>
    </footer>

    <script>
        let selectedBase = 0;
        let selectedMax = 0;
        let selectedDays = 0;
        let currentScope = 1.0;

        function toggleService(el, base, max, days) {
            el.classList.toggle('active');
            if (el.classList.contains('active')) {
                selectedBase += base;
                selectedMax += max;
                selectedDays += days;
            } else {
                selectedBase -= base;
                selectedMax -= max;
                selectedDays -= days;
            }
            recalculate();
        }

        function setScope(btn, multiplier) {
            document.querySelectorAll('.scope-btn').forEach(el => el.classList.remove('active'));
            btn.classList.add('active');
            currentScope = multiplier;
            recalculate();
        }

        function recalculate() {
            // Narx: Baza + (Maks - Baza) * (Scope - 1.0)
            let scopeRatio = currentScope - 1.0;
            let finalPrice = selectedBase + (selectedMax - selectedBase) * scopeRatio;
            
            // Muddat ham scopega qarab oshadi
            let finalDays = Math.ceil(selectedDays * (1 + (currentScope - 1) / 2));

            document.getElementById('totalPrice').innerText = '$' + Math.round(finalPrice).toLocaleString();
            document.getElementById('timeDays').innerText = finalDays;
        }

        document.getElementById('contactFormFull').addEventListener('submit', async (e) => {
            e.preventDefault();
            Swal.fire({
                title: 'Yuborilmoqda...',
                background: '#141423',
                color: '#fff',
                didOpen: () => { Swal.showLoading(); }
            });
            setTimeout(() => {
                Swal.fire('Muvaffaqiyat!', 'Siz bilan tezda bog\'lanamiz!', 'success');
                e.target.reset();
            }, 1000);
        });
        document.getElementById('academyForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            Swal.fire({ title: 'AI Review Jarayonda...', background: '#0a0a1a', color: '#fff', didOpen: () => Swal.showLoading() });
            
            const data = {
                name: document.getElementById('acad_name').value,
                email: document.getElementById('acad_email').value,
                phone: document.getElementById('acad_phone').value,
                location: document.getElementById('acad_location').value,
                direction: document.getElementById('acad_direction').value,
                level: document.getElementById('acad_level').value
            };

            try {
                const res = await fetch('/api/academy/apply', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                    body: JSON.stringify(data)
                });
                const result = await res.json();
                if(result.status === 'success') {
                    Swal.fire('Muvaffaqiyat!', 'Ariza qabul qilindi. AI agent tahlil natijasini Telegram va Pochtangizga yuboradi!', 'success');
                    e.target.reset();
                }
            } catch(e) { Swal.fire('Xato', 'Ulanish xatosi', 'error'); }
        });

        function secretEntryPrompt() {
            Swal.fire({
                title: 'Secret Portal Access',
                input: 'password',
                inputPlaceholder: 'Student/Dev Token',
                background: '#0a0a1a',
                color: '#fff',
                confirmButtonColor: 'var(--neon-purple)',
                showCancelButton: true
            }).then((result) => {
                if (result.value) {
                    // Simulation: Verification and redirect
                    Swal.fire('Identity Verified', 'Redirecting to Nexus Dev Panel...', 'success');
                }
            });
        }

        // Initialize Background Stars
        function initStars() {
            const container = document.getElementById('stars');
            // Static stars
            for(let i=0; i<400; i++) {
                const star = document.createElement('div');
                star.className = 'star';
                const size = Math.random() * 2 + 0.5;
                star.style.width = size + 'px';
                star.style.height = size + 'px';
                star.style.left = Math.random() * 100 + '%';
                star.style.top = Math.random() * 100 + '%';
                star.style.setProperty('--duration', (Math.random() * 4 + 3) + 's');
                star.style.animationDelay = Math.random() * 5 + 's';
                if(Math.random() > 0.96) star.style.boxShadow = '0 0 15px #00ffcc';
                container.appendChild(star);
            }
            // Shooting stars generator - Slower interval
            setInterval(() => {
                const s = document.createElement('div');
                s.className = 'shooting-star';
                s.style.left = Math.random() * 100 + '%';
                s.style.top = Math.random() * 40 + '%';
                s.style.width = (Math.random() * 100 + 100) + 'px'; // Trail length
                s.style.animationDuration = (Math.random() * 3 + 3) + 's'; // Slower movement
                container.appendChild(s);
                setTimeout(() => s.remove(), 6000);
            }, 8000); // Less frequent
        }
        initStars();

        // Magic Wand Cursor Effect
        const canvas = document.getElementById('magicCursor');
        const ctx = canvas.getContext('2d');
        let particles = [];

        function resizeCanvas() {
            canvas.width = window.innerWidth;
            canvas.height = window.innerHeight;
        }
        window.addEventListener('resize', resizeCanvas);
        resizeCanvas();

        class Particle {
            constructor(x, y) {
                this.x = x;
                this.y = y;
                this.size = Math.random() * 4 + 1;
                this.speedX = Math.random() * 2 - 1;
                this.speedY = Math.random() * 2 - 1;
                this.color = Math.random() > 0.5 ? '#00ffcc' : '#b026ff';
                this.alpha = 1;
            }
            update() {
                this.x += this.speedX;
                this.y += this.speedY;
                this.alpha -= 0.02;
                if(this.size > 0.2) this.size -= 0.1;
            }
            draw() {
                ctx.globalAlpha = this.alpha;
                ctx.fillStyle = this.color;
                ctx.beginPath();
                ctx.arc(this.x, this.y, this.size, 0, Math.PI * 2);
                ctx.fill();
            }
        }

        window.addEventListener('mousemove', (e) => {
            for(let i=0; i<3; i++) {
                particles.push(new Particle(e.clientX, e.clientY));
            }
        });

        function animateParticles() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            for(let i=0; i<particles.length; i++) {
                particles[i].update();
                particles[i].draw();
                if(particles[i].alpha <= 0) {
                    particles.splice(i, 1);
                    i--;
                }
            }
            requestAnimationFrame(animateParticles);
        }
        animateParticles();
    </script>
</body>
</html>
