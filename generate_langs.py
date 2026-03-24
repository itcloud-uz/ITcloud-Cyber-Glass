import re
import json
import os

files = [
    'resources/views/welcome.blade.php',
    'resources/views/landing.blade.php'
]

translations = {
    'Dashboard': {'uz': 'Boshqaruv Paneli', 'ru': 'Панель управления', 'tr': 'Kontrol Paneli'},
    'Clients': {'uz': 'Mijozlar', 'ru': 'Клиенты', 'tr': 'Müşteriler'},
    'Employees': {'uz': 'Xodimlar', 'ru': 'Сотрудники', 'tr': 'Çalışanlar'},
    'Settings': {'uz': 'Sozlamalar', 'ru': 'Настройки', 'tr': 'Ayarlar'},
    'Pricing': {'uz': 'Narxlar', 'ru': 'Цены', 'tr': 'Fiyatlandırma'},
    'AI Agents': {'uz': 'AI Agentlar', 'ru': 'ИИ Агенты', 'tr': 'Yapay Zeka Ajanları'},
    'Server Health': {'uz': 'Server Holati', 'ru': 'Состояние сервера', 'tr': 'Sunucu Durumu'},
    'Security Logs': {'uz': 'Xavfsizlik', 'ru': 'Журналы безопасности', 'tr': 'Güvenlik Günlükleri'},
    'Templates': {'uz': 'Shablonlar', 'ru': 'Шаблоны', 'tr': 'Şablonlar'},
    'Developer Portal': {'uz': 'Dasturchilar', 'ru': 'Портал разработчиков', 'tr': 'Geliştirici Portalı'},
    'Bot Manager': {'uz': 'Bot Boshqaruvi', 'ru': 'Управление ботами', 'tr': 'Bot Yöneticisi'},
    'Human Handoff': {'uz': 'Jonli Chat', 'ru': 'Живой чат', 'tr': 'Canlı Sohbet'},
    'Logout': {'uz': 'Chiqish', 'ru': 'Выход', 'tr': 'Çıkış'},
    'Total Revenue': {'uz': 'Umumiy Daromad', 'ru': 'Общий доход', 'tr': 'Toplam Gelir'},
    'Active Clients': {'uz': 'Faol Mijozlar', 'ru': 'Активные клиенты', 'tr': 'Aktif Müşteriler'},
    'Today\'s Leads': {'uz': 'Bugungi Lidlar', 'ru': 'Cегодняшние Лиды', 'tr': 'Bugünkü Fırsatlar'},
    'Total Agents': {'uz': 'Jami Agentlar', 'ru': 'Всего агентов', 'tr': 'Toplam Ajanlar'},
    'Company Growth Dynamics': {'uz': 'Kompaniya O\'sish Dinamikasi', 'ru': 'Динамика роста компании', 'tr': 'Şirket Büyüme Dinamiği'},
    'AI Activity (Live)': {'uz': 'AI Faollik (Jonli)', 'ru': 'Активность ИИ (Вживую)', 'tr': 'YZ Aktivitesi (Canlı)'},
    'Company Finance & Sales Center': {'uz': 'Moliya va Sotuv Markazi', 'ru': 'Финансовый и торговый центр', 'tr': 'Finans ve Satış Merkezi'},
    'System and Company Settings': {'uz': 'Tizim va Kompaniya Sozlamalari', 'ru': 'Системные настройки', 'tr': 'Sistem ve Şirket Ayarları'},
    'Save Data': {'uz': 'Ma\'lumotlarni Saqlash', 'ru': 'Сохранить данные', 'tr': 'Verileri Kaydet'},
    'General': {'uz': 'Asosiy', 'ru': 'Основной', 'tr': 'Genel'},
    'Social Proof': {'uz': 'Mijozlar Fikri', 'ru': 'Отзывы клиентов', 'tr': 'Sosyal Kanıt'},
    'Calculator': {'uz': 'Kalkulyator', 'ru': 'Калькулятор', 'tr': 'Hesap Makinesi'},
    'Project Network': {'uz': 'Loyiha Tarmog\'i', 'ru': 'Сеть проектов', 'tr': 'Proje Ağı'},
    'New Client': {'uz': 'Yangi Mijoz', 'ru': 'Новый клиент', 'tr': 'Yeni Müşteri'},
}

en_dict = {}
uz_dict = {}
ru_dict = {}
tr_dict = {}

for key, langs in translations.items():
    en_dict[key] = key
    uz_dict[key] = langs.get('uz', key)
    ru_dict[key] = langs.get('ru', key)
    tr_dict[key] = langs.get('tr', key)

os.makedirs('lang', exist_ok=True)
with open('lang/en.json', 'w', encoding='utf-8') as f: json.dump(en_dict, f, ensure_ascii=False, indent=4)
with open('lang/uz.json', 'w', encoding='utf-8') as f: json.dump(uz_dict, f, ensure_ascii=False, indent=4)
with open('lang/ru.json', 'w', encoding='utf-8') as f: json.dump(ru_dict, f, ensure_ascii=False, indent=4)
with open('lang/tr.json', 'w', encoding='utf-8') as f: json.dump(tr_dict, f, ensure_ascii=False, indent=4)

print("Language files have been created successfully.")
