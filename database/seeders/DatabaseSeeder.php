<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Tenant;
use App\Models\Subscription;
use App\Models\AiLog;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'ITcloud Master',
            'email' => 'admin@itcloud.uz',
        ]);

        $delta = Tenant::create([
            'company_name' => 'Delta Edu & Visa',
            'domain' => 'crm.itcloud-obsidian.uz',
            'status' => 'active',
            'expires_at' => Carbon::now()->addDays(24),
        ]);

        Subscription::create([
            'tenant_id' => $delta->id,
            'plan_name' => 'Pro AI',
            'duration_days' => 30,
            'amount_paid' => 150000,
            'paid_at' => Carbon::now()->subHours(2),
        ]);

        $med = Tenant::create([
            'company_name' => 'Hayot Nafasi (Med AI)',
            'domain' => 'med.itcloud-obsidian.uz',
            'status' => 'active',
            'expires_at' => Carbon::now()->addDays(112),
        ]);

        $auto = Tenant::create([
            'company_name' => 'Avto-Maktab CRM',
            'domain' => 'auto.itcloud-obsidian.uz',
            'status' => 'blocked',
            'expires_at' => Carbon::now()->subDays(1),
        ]);

        AiLog::create([
            'tenant_id' => $auto->id,
            'agent_type' => 'system',
            'action' => 'Avtomatik bloklandi',
            'details' => '"Avto-maktab CRM" loyihasi to\'lov qilinmagani sababli avtomatik bloklandi. Middleware yoqildi.',
            'created_at' => Carbon::today()->startOfDay(),
        ]);

        AiLog::create([
            'tenant_id' => null,
            'agent_type' => 'sales',
            'action' => 'Yangi mijoz bilan suhbat yakunlandi',
            'details' => 'Yangi mijoz (Azizbek) bilan suhbat yakunlandi. CRM shabloni avto-deploy qilindi. URL yuborildi.',
            'created_at' => Carbon::today()->setHour(12)->setMinute(15),
        ]);

        AiLog::create([
            'tenant_id' => $delta->id,
            'agent_type' => 'finance',
            'action' => 'To\'lov qabul qildi',
            'details' => '"Delta Edu" loyihasi hisobidan 150.000 so\'m to\'lov qabul qildi. Tizim 30 kunga uzaytirildi.',
            'created_at' => Carbon::today()->setHour(14)->setMinute(32),
        ]);
    }
}
