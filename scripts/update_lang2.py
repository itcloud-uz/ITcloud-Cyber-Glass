import json
import os

new_translations = {
    'Interactive Dashboard Demo': 'Interaktiv Dashboard Demo',
    'Obsidian OS v1 – system manages all financial flows, employee KPIs and AI logs from a single genius panel in real time.': 'Obsidian OS v1 – tizim real vaqt rejimida barcha moliyaviy oqimlarni, xodimlar KPI va AI logslarini bitta daho paneldan boshqaradi.',
    'Deep ML Analysis': 'Deep ML Tahlili',
    'Zero-Latency Sync': 'Zero-Latency sinxronizatsiyasi',
    'Biometric': 'Biometrik',
    'Master-Key': 'Master-Key',
    'FaceID integration links the entire system to a single human face. Data theft or alien employee entry is nulled. Liveness check through genius combination of OpenCV and Gemini Vision model.': 'FaceID integratsiyasi butun tizimni yagona inson qiyofasiga bog\'laydi. Ma\'lumotlar o\'g\'irlanishi yuz foiz bartaraf etiladi.',
    'Anti-Spoofing Algorithm': 'Anti-Spoofing Algoritmi',
    'System cannot be fooled by photo or video.': 'Tizimni rasm yoki video orqali aldab bo\'lmaydi.'
}

base_dir = 'lang'
for lang in ['uz', 'tr', 'ru', 'en']:
    path = os.path.join(base_dir, f"{lang}.json")
    if os.path.exists(path):
        with open(path, 'r', encoding='utf-8') as f:
            data = json.load(f)
        
        for k, v in new_translations.items():
            if lang == 'uz': data[k] = v
            elif lang == 'en': data[k] = k
            else: data[k] = k # Provide english fallback for now
            
        with open(path, 'w', encoding='utf-8') as f:
            json.dump(data, f, ensure_ascii=False, indent=4)
