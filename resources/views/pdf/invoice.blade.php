<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoys #{{ $subscription->id }}</title>
    <style>
        body { font-family: sans-serif; color: #333; margin: 0; padding: 20px; }
        .header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #00ffcc; padding-bottom: 20px; margin-bottom: 30px; }
        .logo { font-size: 24px; font-weight: bold; color: #00ffcc; }
        .title { font-size: 20px; color: #555; }
        .info-row { margin-bottom: 20px; }
        .info-col { width: 48%; display: inline-block; vertical-align: top; }
        table { width: 100%; border-collapse: collapse; margin-top: 30px; }
        th { background: #f5f5f5; text-align: left; padding: 12px; border: 1px solid #ddd; }
        td { padding: 12px; border: 1px solid #ddd; }
        .total { text-align: right; margin-top: 30px; font-size: 18px; font-weight: bold; color: #b026ff; }
        .footer { margin-top: 50px; font-size: 12px; color: #888; text-align: center; border-top: 1px solid #eee; padding-top: 20px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">ITcloud Cyber-Glass</div>
        <div class="title">RASMIY INVOYS</div>
    </div>

    <div class="info-row">
        <div class="info-col">
            <strong>Yuboruvchi:</strong><br>
            ITcloud Uzbekistan LLC<br>
            Toshkent, Shayxontohur tumani<br>
            +998 71 200 00 00
        </div>
        <div class="info-col" style="text-align: right;">
            <strong>Mijoz:</strong><br>
            {{ $tenant->company_name }}<br>
            Domen: {{ $tenant->domain }}<br>
            Sana: {{ $subscription->paid_at->format('d.m.Y H:i') }}
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Xizmat tavsifi</th>
                <th>Muddat (kun)</th>
                <th>Narxi</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $subscription->plan_name }} litsenziyasi</td>
                <td>{{ $subscription->duration_days }}</td>
                <td>{{ number_format($subscription->amount_paid, 0, '.', ' ') }} UZS</td>
            </tr>
        </tbody>
    </table>

    <div class="total"> Jami: {{ number_format($subscription->amount_paid, 0, '.', ' ') }} UZS</div>

    <div class="footer">
        Ushbu inyovs ITcloud Obsidian OS orqali avtomatik yaratildi.<br>
        To'lov uchun rahmat!
    </div>
</body>
</html>
