<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ITcloud | {{ __('Project Constructor') }}</title>
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

        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Outfit', sans-serif; }
        body { background-color: var(--bg-dark); color: var(--text-main); overflow-x: hidden; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 50px 20px; }

        .ambient-blob { position: absolute; border-radius: 50%; filter: blur(140px); z-index: -1; opacity: 0.3; }
        .blob-1 { width: 500px; height: 500px; background: var(--neon-purple); top: -10%; left: -10%; }
        .blob-2 { width: 400px; height: 400px; background: var(--neon-cyan); bottom: 10%; right: -5%; }

        .constructor-main { width: 1000px; min-height: 700px; background: var(--glass-bg); backdrop-filter: var(--glass-blur); border: 1px solid var(--glass-border); border-radius: 40px; border: 1px solid var(--neon-cyan); padding: 50px; box-shadow: 0 0 50px rgba(0, 255, 204, 0.1); position: relative; display: flex; flex-direction: column; }

        .logo-box { position: absolute; top: 40px; right: 40px; font-weight: 800; font-size: 20px; color: var(--text-muted); }

        .header { margin-bottom: 50px; }
        .header h1 { font-size: 36px; margin-bottom: 10px; }
        .header p { color: var(--text-muted); font-size: 16px; }

        /* Stepper Logic */
        .step { display: none; animation: fadeIn 0.4s ease-in-out; }
        .step.active { display: block; }

        @keyframes fadeIn { from { opacity: 0; transform: translateX(20px); } to { opacity: 1; transform: translateX(0); } }

        /* Interactive Grid */
        .module-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-top: 30px; }
        .module-card { background: rgba(0,0,0,0.3); border: 1px solid var(--glass-border); padding: 25px; border-radius: 20px; cursor: pointer; transition: 0.3s; text-align: center; }
        .module-card:hover { border-color: var(--neon-cyan); transform: scale(1.02); }
        .module-card.selected { border-color: var(--neon-cyan); background: rgba(0, 255, 204, 0.1); box-shadow: 0 0 20px rgba(0, 255, 204, 0.2); }
        .module-card i { font-size: 28px; color: var(--neon-cyan); margin-bottom: 15px; display: block; }
        .module-card b { font-size: 16px; display: block; margin-bottom: 5px; }
        .module-card span { font-size: 12px; color: var(--text-muted); }

        /* Form Styling */
        .input-group { margin-bottom: 25px; }
        .input-group label { display: block; color: var(--text-muted); font-size: 13px; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 10px; font-weight: 600; padding-left: 5px; }
        .input-field { width: 100%; padding: 18px 25px; background: rgba(0,0,0,0.5); border: 1px solid var(--glass-border); border-radius: 20px; color: white; outline: none; transition: 0.3s; font-size: 16px; }
        .input-field:focus { border-color: var(--neon-cyan); box-shadow: 0 0 20px rgba(0, 255, 204, 0.1); }

        .footer-nav { margin-top: auto; display: flex; justify-content: space-between; align-items: center; padding-top: 40px; }
        .btn-ios { padding: 16px 35px; border-radius: 18px; border: none; cursor: pointer; font-weight: 700; font-size: 15px; transition: 0.3s; }
        .btn-back { background: rgba(255,255,255,0.05); color: var(--text-muted); }
        .btn-next { background: var(--neon-cyan); color: #000; box-shadow: 0 0 20px rgba(0,255,204,0.3); }
        .btn-next:hover { transform: scale(1.05); box-shadow: 0 0 40px rgba(0,255,204,0.5); }

        /* Progress Bar */
        .progress-bar { position: absolute; top: 0; left: 0; height: 4px; background: linear-gradient(90deg, var(--neon-cyan), var(--neon-purple)); border-radius: 0 4px 4px 0; transition: 0.5s ease; width: 25%; }
    </style>
</head>
<body>

    <div class="ambient-blob blob-1"></div>
    <div class="ambient-blob blob-2"></div>

    <div class="constructor-main">
        <div style="position: absolute; top: 20px; left: 40px; display: flex; gap: 15px; z-index: 100;">
            <a href="{{ route('lang.switch', 'uz') }}" style="text-decoration: none; opacity: {{ App::getLocale() == 'uz' ? '1' : '0.4' }};">🇺🇿</a>
            <a href="{{ route('lang.switch', 'tr') }}" style="text-decoration: none; opacity: {{ App::getLocale() == 'tr' ? '1' : '0.4' }};">🇹🇷</a>
            <a href="{{ route('lang.switch', 'ru') }}" style="text-decoration: none; opacity: {{ App::getLocale() == 'ru' ? '1' : '0.4' }};">🇷🇺</a>
            <a href="{{ route('lang.switch', 'en') }}" style="text-decoration: none; opacity: {{ App::getLocale() == 'en' ? '1' : '0.4' }};">🇺🇸</a>
        </div>
        <div class="progress-bar" id="progress-indicator"></div>
        <div class="logo-box">IT<span>cloud</span> Architect</div>

        <form id="architectForm">
            <!-- Step 1: Basic Info -->
            <div class="step active" id="step-1">
                <div class="header">
                    <h1>{{ __('Building Your Project') }}</h1>
                    <p>{{ __('Enter the basic identifiers of your project.') }}</p>
                </div>
                
                <div class="input-group">
                    <label>{{ __('Project Name (Brand)') }}</label>
                    <input type="text" name="project_name" class="input-field" placeholder="Masalan: Elite Store" required>
                </div>

                <div class="input-group">
                    <label>{{ __('Field or Category') }}</label>
                    <select name="category" class="input-field" style="background: #000;">
                        @foreach($priceServices as $ps)
                            <option value="{{ $ps->slug }}">{{ $ps->name }}</option>
                        @endforeach
                        <option value="custom">{{ __('Custom Software') }}</option>
                    </select>
                </div>
                
                <div class="input-group">
                    <label>{{ __('Your Name and Phone Number') }}</label>
                    <div style="display: flex; gap: 15px;">
                        <input type="text" name="name" class="input-field" placeholder="Ism" required>
                        <input type="text" name="phone" class="input-field" placeholder="+998" required>
                    </div>
                </div>
            </div>

            <!-- Step 2: Module Selection -->
            <div class="step" id="step-2">
                <div class="header">
                    <h1>{{ __('Select Modules') }}</h1>
                    <p>{{ __('What powers do you want your project to have?') }}</p>
                </div>

                <div class="module-grid">
                    <div class="module-card" data-module="crm" onclick="toggleModule(this)">
                        <i class="fa-solid fa-users-gear"></i>
                        <b>CRM Modul</b>
                        <span>Mijozlar bazasi va statistika</span>
                    </div>
                    <div class="module-card" data-module="telegram_bot" onclick="toggleModule(this)">
                        <i class="fa-brands fa-telegram"></i>
                        <b>Telegram AI Bot</b>
                        <span>Mijozlar bilan asinxron suhbat</span>
                    </div>
                    <div class="module-card" data-module="payments" onclick="toggleModule(this)">
                        <i class="fa-solid fa-credit-card"></i>
                        <b>To'lov Tizimi</b>
                        <span>Payme, Click, Stripe integratsiyasi</span>
                    </div>
                    <div class="module-card" data-module="faceid" onclick="toggleModule(this)">
                        <i class="fa-solid fa-fingerprint"></i>
                        <b>FaceID Login</b>
                        <span>Biometrik xavfsizlik va kirish</span>
                    </div>
                    <div class="module-card" data-module="logistics" onclick="toggleModule(this)">
                        <i class="fa-solid fa-truck-fast"></i>
                        <b>Logistika</b>
                        <span>Buyurtmalarni yetkazish nazorati</span>
                    </div>
                    <div class="module-card" data-module="analytics" onclick="toggleModule(this)">
                        <i class="fa-solid fa-chart-line"></i>
                        <b>Deep Analytics</b>
                        <span>AI tomonidan o'rganiluvchi tahlil</span>
                    </div>
                </div>
            </div>

            <!-- Step 3: Design & Budget -->
            <div class="step" id="step-3">
                <div class="header">
                    <h1>{{ __('Design and Budget') }}</h1>
                    <p>{{ __('Define visual requirements and estimated budget.') }}</p>
                </div>

                <div class="input-group">
                    <label>{{ __('Design and Special Requirements (Notes)') }}</label>
                    <textarea name="design_notes" class="input-field" style="height: 120px;" placeholder="Notes..."></textarea>
                </div>

                <div class="input-group">
                    <label>{{ __('Project budget (USD)') }}</label>
                    <input type="range" name="budget" min="500" max="10000" step="100" class="input-field" style="padding: 0; background: none; margin-top: 10px;" oninput="this.nextElementSibling.innerText = this.value + '$'">
                    <span style="display: block; text-align: center; color: var(--neon-cyan); font-weight: 800; font-size: 24px; margin-top: 10px;">5000$</span>
                </div>
            </div>

            <div class="footer-nav">
                <button type="button" class="btn-ios btn-back" id="btn-back" onclick="changeStep(-1)" style="visibility: hidden;">{{ __('Back') }}</button>
                <div style="color: var(--text-muted); font-size: 14px;">{{ __('Step') }}: <span id="current-step-num">1</span> / 3</div>
                <button type="button" class="btn-ios btn-next" id="btn-next" onclick="changeStep(1)">{{ __('Next') }} <i class="fa-solid fa-arrow-right"></i></button>
            </div>
        </form>
    </div>

    <script>
        let currentStep = 1;
        const totalSteps = 3;
        const selectedModules = new Set();
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

        function toggleModule(el) {
            const module = el.dataset.module;
            if (selectedModules.has(module)) {
                selectedModules.delete(module);
                el.classList.remove('selected');
            } else {
                selectedModules.add(module);
                el.classList.add('selected');
            }
        }

        function changeStep(dir) {
            if (dir === 1 && currentStep === totalSteps) {
                submitFinalDesign();
                return;
            }

            document.getElementById(`step-${currentStep}`).classList.remove('active');
            currentStep += dir;
            document.getElementById(`step-${currentStep}`).classList.add('active');

            // Button visibility & text
            document.getElementById('btn-back').style.visibility = currentStep === 1 ? 'hidden' : 'visible';
            document.getElementById('btn-next').innerHTML = currentStep === totalSteps ? 'Loyihani Yaratish <i class="fa-solid fa-check"></i>' : 'Keyingisi <i class="fa-solid fa-arrow-right"></i>';
            document.getElementById('current-step-num').innerText = currentStep;
            
            // Progress Bar
            document.getElementById('progress-indicator').style.width = (currentStep / totalSteps * 100) + '%';
        }

        async function submitFinalDesign() {
            const form = document.getElementById('architectForm');
            const formData = new FormData(form);
            const dataHash = Object.fromEntries(formData);
            
            dataHash.selected_modules = Array.from(selectedModules);

            Swal.fire({
                title: 'Loyiha yuborilmoqda...',
                html: 'Tizim sizning me\'moriy rejangizni tahlil qilmoqda...',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });

            try {
                const res = await fetch('/submit-inquiry', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                    body: JSON.stringify(dataHash)
                });
                const result = await res.json();
                
                if (result.status === 'success') {
                    Swal.fire({
                        title: 'Dizayn qabul qilindi!',
                        text: result.message,
                        icon: 'success',
                        background: '#141423',
                        color: '#fff',
                        confirmButtonColor: '#00ffcc'
                    }).then(() => {
                        window.location.href = '/';
                    });
                }
            } catch (err) {
                Swal.fire('Xatolik!', 'Aloqa uzildi.', 'error');
            }
        }
    </script>
</body>
</html>
