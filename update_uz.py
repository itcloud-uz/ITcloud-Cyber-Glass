import json

new_translations = {
    'Start Projects': 'Loyihalarni Boshlash',
    'Trusted by those who lead': 'Tizimimizga ishonch bildirganlar',
    'Obsidian OS': 'Obsidian OS',
    'Automation': 'Avtomatlashtirish',
    'Not just text, feel the genius architecture. Hover over the interface.': 'Faqat matn emas, daho arxitekturani his qiling. Sichqonchani interfeys ustiga olib boring.',
    'View Full Version': 'To\'liq Versiyani Ko\'rish',
    'Cyber-Defense': 'Kiber-Himoya',
    'FaceID 2026': 'FaceID 2026',
    'Passwords are in the past. Protect your business through human face recognition system.': 'Parollar o\'tmishda qoldi. Biznesingizni inson yuzi orqali tanib olish tizimi bilan himoyalang.',
    'Project Price': 'Loyiha Narxini',
    'Calculation': 'Hisoblash',
    'Select necessary modules and set the project scope. AI outputs real-time pricing.': 'O\'zingizga kerakli modullarni tanlang va loyihaning qamrovini (Scope) belgilang. AI real-time holatda narx chiqaradi.',
    'Draw Project Architecture': 'Loyiha Arxitekturasini Chizish',
    'Contact': 'Biz bilan',
    'Us': 'Bog\'laning',
    'Have questions? Visit our office or write to us.': 'Savollaringiz bormi? Ofisimizga keling yoki bizga yozing.',
    'Communication': 'Aloqa',
    'Points': 'Nuqtalari',
    'Employees and Master Admins': 'Xodimlar va Master Adminlar',
    'New Employee Settings': 'Yangi Xodim Sozlamalari (Tizimga Kiritish)',
    'Full Name': 'To\'liq Ismi',
    'Email / Login': 'Email / Login',
    'Password': 'Parol',
    'Passport Number (PINFL)': 'Pasport Raqami (JSHSHR)',
    'Role': 'Roli',
    'Face Photo (Base64 or Image)': 'Yuz qiyofasi (Face ID Base64 yoki Rasm)',
    'Gemini AI Agents Center': 'Gemini AI Agentlar Markazi',
    'Company Finance': 'Kompaniya Moliyasi',
    'Payme and Click Integration': 'Payme va Click Integratsiyasi',
    'This section displays all payment history via webhook. Clients are automatically activated after payment.': 'Bu bo\'limda webhook orqali tushgan barcha to\'lovlar tarixi ko\'rsatiladi. Mijozlar to\'lov qilgach avtomatik active holatga o\'tadi.',
    'Server Health': 'Server Holati (System Health)',
    'Security Logs': 'Xavfsizlik Jurnali (Face ID & Fail2Ban)',
    'Live Chat': 'Qutqaruv Chati (Human Handoff)',
    'AI Agents Unification': 'AI Agentlarni Unifikatsiya Qilish'
}

with open('lang/uz.json', 'r', encoding='utf-8') as f:
    data = json.load(f)

for key, val in new_translations.items():
    data[key] = val

with open('lang/uz.json', 'w', encoding='utf-8') as f:
    json.dump(data, f, ensure_ascii=False, indent=4)
