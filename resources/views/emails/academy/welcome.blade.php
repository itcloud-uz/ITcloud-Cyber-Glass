<x-mail::message>
# Salom, {{ $app->name }}!

ITcloud Academy'ga bo'lgan qiziqishingizdan juda mamnunmiz. Sizning arizangiz muvaffaqiyatli qabul qilindi va bizning **I-Ticher** agentimiz uni tahlil qilishni boshladi.

### Ariza ma'lumotlari:
- **Yo'nalish:** {{ ucfirst($app->direction) }}
- **Daraja:** {{ ucfirst($app->level) }}

Navbatdagi qadam: Telegram botimizga a'zo bo'ling va birinchi mantiqiy testni topshiring.

<x-mail::button :url="'https://t.me/itcloud_academy_bot?start=' . $app->access_token">
I-Ticher bilan darsni boshlash
</x-mail::button>

Agar biror savolingiz bo'lsa, ushbu xatga javob berishingiz yoki bizning qo'llab-quvvatlash markazimiz bilan bog'lanishingiz mumkin.

Kelajakni birgalikda quramiz!

Rahmat,<br>
{{ config('app.name') }} Team
</x-mail::message>
