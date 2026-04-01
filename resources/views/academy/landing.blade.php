<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ITcloud Academy | Kelajak Dasturchilari Markazi</title>
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
            position: fixed; top: 0; left: 0; width: 100%; padding: 20px 8%; z-index: 1000;
            display: flex; justify-content: space-between; align-items: center;
            background: rgba(5, 5, 10, 0.8); backdrop-filter: blur(15px);
            border-bottom: 1px solid var(--glass-border);
        }
        .logo-animated { font-size: 24px; font-weight: 800; cursor: pointer; text-decoration: none; color: white; }
        .logo-animated span { color: var(--neon-cyan); }

        .nav-right { display: flex; align-items: center; gap: 20px; }
        
        .lang-switch { display: flex; gap: 10px; }
        .lang-switch a { text-decoration: none; font-size: 14px; color: var(--text-muted); padding: 5px 10px; border-radius: 8px; border: 1px solid var(--glass-border); transition: 0.3s; }
        .lang-switch a.active { background: rgba(0, 255, 204, 0.1); color: var(--neon-cyan); border-color: var(--neon-cyan); }

        .user-cabinet-btn {
            width: 45px; height: 45px; border-radius: 50%; border: 2px solid var(--glass-border);
            display: flex; align-items: center; justify-content: center;
            background: var(--glass-bg); cursor: pointer; transition: 0.4s; color: white; overflow: hidden;
        }
        .user-cabinet-btn:hover { border-color: var(--neon-cyan); box-shadow: 0 0 20px rgba(0, 255, 204, 0.3); transform: scale(1.05); }
        .user-cabinet-btn i { font-size: 20px; }

        /* Layout */
        section { padding: 120px 8%; position: relative; }
        .glass-panel { background: var(--glass-bg); border: 1px solid var(--glass-border); border-radius: 30px; backdrop-filter: var(--glass-blur); transition: 0.4s; }
        .glass-panel:hover { border-color: rgba(255,255,255,0.15); }

        /* Hero */
        .hero { min-height: 80vh; display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center; }
        .hero h1 { font-size: clamp(36px, 6vw, 68px); font-weight: 800; line-height: 1.1; margin-bottom: 25px; }
        .hero h1 span { background: linear-gradient(90deg, var(--neon-cyan), var(--neon-purple)); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .hero p { font-size: clamp(16px, 1.5vw, 20px); color: var(--text-muted); max-width: 800px; margin-bottom: 50px; line-height: 1.6; }

        /* Form */
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .input-group { margin-bottom: 25px; }
        .input-group label { display: block; font-size: 12px; color: var(--text-muted); margin-bottom: 8px; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; }
        .input-field {
            width: 100%; padding: 15px; background: rgba(0,0,0,0.3); border: 1px solid var(--glass-border);
            border-radius: 12px; color: white; outline: none; transition: 0.3s;
        }
        .input-field:focus { border-color: var(--neon-cyan); background: rgba(0,0,0,0.5); }

        .btn-neon {
            padding: 18px 40px; border-radius: 15px; border: none; background: var(--neon-cyan); color: #000;
            font-weight: 800; font-size: 16px; cursor: pointer; transition: 0.3s; display: inline-flex; align-items: center; gap: 10px;
            width: 100%; justify-content: center;
        }
        .btn-neon:hover { transform: translateY(-3px); box-shadow: 0 10px 30px rgba(0, 255, 204, 0.4); }

        /* Features */
        .feature-card { padding: 30px; }
        .feature-card i { font-size: 32px; margin-bottom: 20px; display: block; }

        /* Game Section */
        .game-area { padding: 40px; text-align: center; }
        .game-card { display: none; }
        .game-card.active { display: block; animation: slideIn 0.5s ease; }
        @keyframes slideIn { from { opacity: 0; transform: translateX(20px); } to { opacity: 1; transform: translateX(0); } }

        .choice-btn {
            background: rgba(255,255,255,0.05); border: 1px solid var(--glass-border); padding: 15px 25px;
            border-radius: 12px; cursor: pointer; transition: 0.3s; margin: 10px; display: inline-block;
            min-width: 200px;
        }
        .choice-btn:hover { background: rgba(255,255,255,0.1); border-color: var(--neon-cyan); color: var(--neon-cyan); }

        #game-result { font-weight: 800; color: var(--neon-cyan); font-size: 24px; margin-top: 20px; }

        /* Responsive */
        @media (max-width: 1024px) {
            nav { padding: 15px 5%; }
            .hero h1 { font-size: 42px; }
            .form-grid { grid-template-columns: 1fr !important; }
            section { padding: 60px 5%; }
            .nav-right { gap: 10px; }
            .logo-animated { font-size: 20px; }
            .btn-neon { padding: 15px 30px; }
            .grid-responsive { grid-template-columns: 1fr !important; }
        }

        .stars-container { position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: -2; pointer-events: none; }
        .star { position: absolute; background: white; border-radius: 50%; opacity: 0.5; }
    </style>
</head>
<body>

    <div class="stars-container" id="stars"></div>
    <div class="ambient-blob blob-1"></div>
    <div class="ambient-blob blob-2"></div>

    <nav>
        <a href="/" class="logo-animated">IT<span>cloud</span> Academy</a>
        
        <div class="nav-right">
            <div class="lang-switch">
                <a href="{{ route('lang.switch', 'uz') }}" class="{{ App::getLocale() == 'uz' ? 'active' : '' }}">UZ</a>
                <a href="{{ route('lang.switch', 'ru') }}" class="{{ App::getLocale() == 'ru' ? 'active' : '' }}">RU</a>
                <a href="{{ route('lang.switch', 'en') }}" class="{{ App::getLocale() == 'en' ? 'active' : '' }}">EN</a>
            </div>

            <div class="user-cabinet-btn" onclick="location.href='{{ route('login') }}'" title="Shaxsiy Kabinet">
                <i class="fa-solid fa-user-astronaut"></i>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div style="background: rgba(176, 38, 255, 0.1); padding: 8px 16px; border-radius: 20px; border: 1px solid var(--neon-purple); color: var(--neon-purple); font-weight: 700; font-size: 12px; letter-spacing: 1.5px; margin-bottom: 30px;">
            <i class="fa-solid fa-graduation-cap"></i> KELAJAK DASTURCHILARI MARKAZI
        </div>
        <h1>ITcloud Academy: <span>Siz kutgan innovatsion ta'lim</span></h1>
        <p>Amaliyot va ish bilan ta'minlash kafolati. Biz shunchaki kurs emasmiz, biz sizning IT sohasidagi muvaffaqiyatli kelajagingiz poydevorimiz.</p>
        <a href="#enroll" class="btn-neon" style="width: auto; padding: 20px 60px;">
            Hozir Ro'yxatdan o'ting <i class="fa-solid fa-arrow-right"></i>
        </a>
    </section>

    <!-- Programming Languages Section -->
    <section id="languages" style="padding-top: 50px;">
        <div style="text-align: center; margin-bottom: 60px;">
            <h2 style="font-size: 32px; font-weight: 800;">Biz o'rgatadigan <span style="color: var(--neon-cyan);">Texnologiyalar</span></h2>
            <p style="color: var(--text-muted); margin-top: 15px;">Dunyoning eng kuchli kompaniyalari ishlatadigan zamonaviy vositalarni biz bilan o'rganing.</p>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 20px;">
            <div class="glass-panel" style="padding: 30px; text-align: center; border-bottom: 3px solid #f7df1e;">
                <i class="fa-brands fa-js" style="font-size: 48px; color: #f7df1e; margin-bottom: 15px;"></i>
                <h4 style="font-size: 18px;">JavaScript</h4>
                <p style="font-size: 12px; color: var(--text-muted); margin-top: 10px;">Web interaktivligi</p>
            </div>
            <div class="glass-panel" style="padding: 30px; text-align: center; border-bottom: 3px solid #777bb4;">
                <i class="fa-brands fa-php" style="font-size: 48px; color: #777bb4; margin-bottom: 15px;"></i>
                <h4 style="font-size: 18px;">PHP</h4>
                <p style="font-size: 12px; color: var(--text-muted); margin-top: 10px;">Backend quvvati</p>
            </div>
            <div class="glass-panel" style="padding: 30px; text-align: center; border-bottom: 3px solid #3776ab;">
                <i class="fa-brands fa-python" style="font-size: 48px; color: #3776ab; margin-bottom: 15px;"></i>
                <h4 style="font-size: 18px;">Python</h4>
                <p style="font-size: 12px; color: var(--text-muted); margin-top: 10px;">AI & Data Science</p>
            </div>
            <div class="glass-panel" style="padding: 30px; text-align: center; border-bottom: 3px solid #61dafb;">
                <i class="fa-brands fa-react" style="font-size: 48px; color: #61dafb; margin-bottom: 15px;"></i>
                <h4 style="font-size: 18px;">React / Vue</h4>
                <p style="font-size: 12px; color: var(--text-muted); margin-top: 10px;">Modern Frontend</p>
            </div>
            <div class="glass-panel" style="padding: 30px; text-align: center; border-bottom: 3px solid #ff2d20;">
                <i class="fa-brands fa-laravel" style="font-size: 48px; color: #ff2d20; margin-bottom: 15px;"></i>
                <h4 style="font-size: 18px;">Laravel</h4>
                <p style="font-size: 12px; color: var(--text-muted); margin-top: 10px;">Mukammal Backend</p>
            </div>
            <div class="glass-panel" style="padding: 30px; text-align: center; border-bottom: 3px solid #3178c6;">
                <i class="fa-solid fa-code" style="font-size: 48px; color: #3178c6; margin-bottom: 15px;"></i>
                <h4 style="font-size: 18px;">C / C++</h4>
                <p style="font-size: 12px; color: var(--text-muted); margin-top: 10px;">System Programs</p>
            </div>
            <div class="glass-panel" style="padding: 30px; text-align: center; border-bottom: 3px solid #68217a;">
                <i class="fa-brands fa-microsoft" style="font-size: 48px; color: #68217a; margin-bottom: 15px;"></i>
                <h4 style="font-size: 18px;">C# / .NET</h4>
                <p style="font-size: 12px; color: var(--text-muted); margin-top: 10px;">Enterprise Apps</p>
            </div>
            <div class="glass-panel" style="padding: 30px; text-align: center; border-bottom: 3px solid #00add8;">
                <i class="fa-brands fa-golang" style="font-size: 48px; color: #00add8; margin-bottom: 15px;"></i>
                <h4 style="font-size: 18px;">Go</h4>
                <p style="font-size: 12px; color: var(--text-muted); margin-top: 10px;">Microservices</p>
            </div>
            <div class="glass-panel" style="padding: 30px; text-align: center; border-bottom: 3px solid #f06529;">
                <i class="fa-brands fa-html5" style="font-size: 48px; color: #f06529; margin-bottom: 15px;"></i>
                <h4 style="font-size: 18px;">HTML / CSS</h4>
                <p style="font-size: 12px; color: var(--text-muted); margin-top: 10px;">Layout Design</p>
            </div>
            <div class="glass-panel" style="padding: 30px; text-align: center; border-bottom: 3px solid var(--neon-cyan); box-shadow: 0 0 20px rgba(0, 255, 204, 0.2);">
                <i class="fa-solid fa-brain" style="font-size: 48px; color: var(--neon-cyan); margin-bottom: 15px;"></i>
                <h4 style="font-size: 18px;">AI Prompts</h4>
                <p style="font-size: 12px; color: var(--text-muted); margin-top: 10px;">Prompt Engineering</p>
            </div>
        </div>
    </section>

    <!-- Detailed Courses Section -->
    <section id="courses">
        <div style="text-align: center; margin-bottom: 60px;">
            <h2 style="font-size: 32px; font-weight: 800;">Akademiyamiz <span style="color: var(--neon-purple);">Kurslari</span></h2>
            <p style="color: var(--text-muted); margin-top: 15px;">Tanlagan yo'nalishingiz bo'yicha eng kuchli kurslar ro'yxati.</p>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px;">
            <div class="glass-panel feature-card">
                <div style="width: 50px; height: 50px; background: rgba(0,255,204,0.1); border-radius: 12px; display: flex; align-items: center; justify-content: center; margin-bottom: 20px;">
                    <i class="fa-solid fa-laptop-code" style="color: var(--neon-cyan); margin-bottom: 0;"></i>
                </div>
                <h3>Frontend Master</h3>
                <p style="color: var(--text-muted); margin-top: 15px; font-size: 14px;">React, Vue, Next.js va zamonaviy kutubxonalar orqali yuqori sifatli veb-ilovalarni yaratishni o'rganing.</p>
            </div>
            <div class="glass-panel feature-card">
                <div style="width: 50px; height: 50px; background: rgba(176,38,255,0.1); border-radius: 12px; display: flex; align-items: center; justify-content: center; margin-bottom: 20px;">
                    <i class="fa-solid fa-server" style="color: var(--neon-purple); margin-bottom: 0;"></i>
                </div>
                <h3>Backend Pro</h3>
                <p style="color: var(--text-muted); margin-top: 15px; font-size: 14px;">Laravel, Python (Django/FastAPI) va Node.js orqali murakkab ma'lumotlar bazasi va xavfsiz tizimlar arxitekturasini quring.</p>
            </div>
            <div class="glass-panel feature-card">
                <div style="width: 50px; height: 50px; background: rgba(0,255,204,0.1); border-radius: 12px; display: flex; align-items: center; justify-content: center; margin-bottom: 20px;">
                    <i class="fa-solid fa-robot" style="color: var(--neon-cyan); margin-bottom: 0;"></i>
                </div>
                <h3>AI & Prompt Logic</h3>
                <p style="color: var(--text-muted); margin-top: 15px; font-size: 14px;">LLM (GPT-4, Gemini) bilan ishlash, Prompt Engineering texnikalari va AI jarayonlarini avtomatlashtirish.</p>
            </div>
            <div class="glass-panel feature-card">
                <div style="width: 50px; height: 50px; background: rgba(255,0,127,0.1); border-radius: 12px; display: flex; align-items: center; justify-content: center; margin-bottom: 20px;">
                    <i class="fa-solid fa-mobile-screen" style="color: var(--neon-pink); margin-bottom: 0;"></i>
                </div>
                <h3>Mobile Apps</h3>
                <p style="color: var(--text-muted); margin-top: 15px; font-size: 14px;">Flutter va Kotlin yordamida iOS va Android platformalari uchun unversal va tezkor mobil ilovalar yaratish.</p>
            </div>
        </div>
    </section>

    <!-- Features -->
    <section id="features" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px;">
        <div class="glass-panel feature-card">
            <i class="fa-solid fa-code-merge" style="color: var(--neon-cyan)"></i>
            <h3>ITcloud Sandbox</h3>
            <p style="color: var(--text-muted); margin-top: 15px;">O'quvchilar real loyihalarda xavfsiz Sandbox hududida ishlashadi. Barcha kodlar AI Mentor tomonidan tahlil qilinadi.</p>
        </div>
        <div class="glass-panel feature-card">
            <i class="fa-solid fa-coins" style="color: var(--neon-purple)"></i>
            <h3>Bounty Tizimi</h3>
            <p style="color: var(--text-muted); margin-top: 15px;">Vazifalarni muvaffaqiyatli bajargan o'quvchilar nafaqat XP, balki real pullik mukofotlar va oylik maosh olishadi.</p>
        </div>
        <div class="glass-panel feature-card">
            <i class="fa-solid fa-handshake-simple" style="color: var(--neon-pink)"></i>
            <h3>ISA Kafolati</h3>
            <p style="color: var(--text-muted); margin-top: 15px;">Bitiruvchilar ITcloud jamoasida 1.5 yil davomida kafolatlangan ish bilan ta'minlanadi. Bu sizning karyerangiz kafolati!</p>
        </div>
    </section>

    <!-- Game Section -->
    <section id="game">
        <div class="glass-panel game-area">
            <h2 style="margin-bottom: 20px;">Siz uchun qaysi yo'nalish mos?</h2>
            <p style="color: var(--text-muted); margin-bottom: 40px;">Keling, kichik o'yin orqali aniqlaymiz.</p>
            
            <div id="q1" class="game-card active">
                <h3>1. Sizga ko'proq nima yoqadi?</h3>
                <div class="choice-btn" onclick="nextQuestion(1, 'design')">Chiroyli interfeyslar yaratish</div>
                <div class="choice-btn" onclick="nextQuestion(1, 'logic')">Murakkab mantiqiy masalalar</div>
            </div>

            <div id="q2" class="game-card">
                <h3>2. Sizning kuchli tarafingiz?</h3>
                <div class="choice-btn" onclick="nextQuestion(2, 'creative')">Kreativ fikrlash</div>
                <div class="choice-btn" onclick="nextQuestion(2, 'system')">Tizimli tahlil qilish</div>
            </div>

            <div id="q3" class="game-card">
                <h3>3. Kelajakda o'zingizni ko'rasiz...</h3>
                <div class="choice-btn" onclick="showResult('frontend')">Eng zamonaviy saytlar ustasi</div>
                <div class="choice-btn" onclick="showResult('backend')">Katta tizimlar muhandisi</div>
                <div class="choice-btn" onclick="showResult('ai')">Sun'iy intellekt arxitektori</div>
            </div>

            <div id="game-res-box" style="display: none;">
                <div id="game-result"></div>
                <p style="margin-top: 15px; opacity: 0.7;">Qoyil! Bu yo'nalish hozirda eng talabgir. Pastdagi formani to'ldiring va AI tahlilini oling!</p>
            </div>
        </div>
    </section>

    <!-- Enrollment Form -->
    <section id="enroll">
        <div class="glass-panel" style="padding: 60px;">
            <div style="text-align: center; margin-bottom: 50px;">
                <h2 style="font-size: 36px; margin-bottom: 15px;">O'quvchi Bo'lish uchun Ariza</h2>
                <p style="color: var(--text-muted)">Ma'lumotlaringizni qoldiring, AI Agentimiz ularni 60 soniya ichida tahlil qiladi.</p>
            </div>

            <form id="academyEnrollForm">
                <div class="form-grid">
                    <div class="input-group">
                        <label>To'liq Ismingiz</label>
                        <input type="text" id="name" class="input-field" placeholder="Masalan: Abdullo Alisherov" required>
                    </div>
                    <div class="input-group">
                        <label>Email Manzilingiz</label>
                        <input type="email" id="email" class="input-field" placeholder="example@itcloud.uz" required>
                    </div>
                </div>
                <div class="form-grid">
                    <div class="input-group">
                        <label>Telefon Raqamingiz</label>
                        <input type="text" id="phone" class="input-field" value="+998" required>
                    </div>
                    <div class="input-group">
                        <label>Yashash Joyingiz</label>
                        <input type="text" id="location" class="input-field" placeholder="Shahar, Tuman" required>
                    </div>
                </div>
                <div class="form-grid">
                    <div class="input-group">
                        <label>Yo'nalish</label>
                        <select id="direction" class="input-field" style="background: #000;">
                            <option value="frontend">Frontend (Vue/React)</option>
                            <option value="backend">Backend (Laravel/Python)</option>
                            <option value="ai">AI & Prompt Engineering</option>
                        </select>
                    </div>
                    <div class="input-group">
                        <label>Hozirgi Darajangiz</label>
                        <select id="level" class="input-field" style="background: #000;">
                            <option value="beginner">Noldan (Beginner)</option>
                            <option value="intermediate">Kichik tajriba (Junior)</option>
                        </select>
                    </div>
                </div>
                
                <div style="margin-top: 40px;">
                    <button type="submit" class="btn-neon">
                        Arizani Yuborish & AI Review <i class="fa-solid fa-robot"></i>
                    </button>
                    <p style="text-align: center; margin-top: 20px; font-size: 13px; color: var(--text-muted);">
                        <i class="fa-solid fa-lock"></i> Ma'lumotlaringiz AES-256 algoritmi bilan himoyalangan.
                    </p>
                </div>
            </form>
        </div>
    </section>

    <footer style="padding: 60px 8%; text-align: center; border-top: 1px solid var(--glass-border); opacity: 0.6;">
        &copy; 2026 ITcloud Academy | Kelajak Texnologiyalari Markazi.
    </footer>

    <script>
        // Stars
        const starsContainer = document.getElementById('stars');
        for (let i = 0; i < 150; i++) {
            const star = document.createElement('div');
            star.className = 'star';
            star.style.width = Math.random() * 3 + 'px';
            star.style.height = star.style.width;
            star.style.left = Math.random() * 100 + '%';
            star.style.top = Math.random() * 100 + '%';
            star.style.opacity = Math.random();
            starsContainer.appendChild(star);
        }

        // Game Logic
        function nextQuestion(current, choice) {
            document.getElementById(`q${current}`).classList.remove('active');
            document.getElementById(`q${current + 1}`).classList.add('active');
        }

        function showResult(direction) {
            document.getElementById('q3').classList.remove('active');
            const resBox = document.getElementById('game-res-box');
            const resText = document.getElementById('game-result');
            resBox.style.display = 'block';
            
            let label = "";
            if(direction === 'frontend') label = "Tabriklaymiz! Siz — Tug'ma Frontend Arxitektori ekansiz!";
            if(direction === 'backend') label = "Ajoyib! Sizning mantiqiy fikrlashingiz sizni Backend Guru qiladi!";
            if(direction === 'ai') label = "G'aroyib! Kelajak sizniki. Siz AI muhandisligiga mos ekansiz!";
            
            resText.innerText = label;
            document.getElementById('direction').value = direction;
            
            window.scrollTo({ top: document.getElementById('enroll').offsetTop - 100, behavior: 'smooth' });
        }

        // Form Submit
        document.getElementById('academyEnrollForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            Swal.fire({
                title: 'AI Agent Tahlil Qilmoqda...',
                html: '<i class="fa-solid fa-microchip fa-spin" style="font-size: 40px; color: var(--neon-cyan);"></i><br><br>Sizning profilingiz tahlil qilinmoqda...',
                background: '#050510',
                color: '#fff',
                showConfirmButton: false,
                allowOutsideClick: false
            });

            const data = {
                name: document.getElementById('name').value,
                email: document.getElementById('email').value,
                phone: document.getElementById('phone').value,
                location: document.getElementById('location').value,
                direction: document.getElementById('direction').value,
                level: document.getElementById('level').value
            };

            try {
                const res = await fetch('/api/inquiry/submit', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                    body: JSON.stringify(data)
                });
                const result = await res.json();
                if(result.status === 'success') {
                    Swal.fire({
                        title: 'Muvaffaqiyat!',
                        text: 'Arizangiz qabul qilindi. AI mentorimiz 5 daqiqa ichida siz bilan bog\'lanadi!',
                        icon: 'success',
                        background: '#050510',
                        color: '#fff'
                    });
                    e.target.reset();
                }
            } catch(e) { 
                Swal.fire('Xato', 'Server bilan bog\'lanishda xatolik', 'error');
            }
        });
    </script>
</body>
</html>
