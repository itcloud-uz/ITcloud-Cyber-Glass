<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tenant;
use App\Models\AiLog;
use Illuminate\Support\Carbon;

class AnalyzeChurn extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'app:analyze-churn';

    /**
     * The console command description.
     */
    protected $description = 'AI analyzes active tenants for churn risk and sends retention offers';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("AI Churn Analysis started...");

        $riskTenants = Tenant::where('status', 'active')
            ->where(function($query) {
                // Criteria A: Inactive for more than 7 days
                $query->where('last_active_at', '<', Carbon::now()->subDays(7))
                      ->orWhereNull('last_active_at');
            })
            ->where('expires_at', '<', Carbon::now()->addDays(5)) // Logic: Expiring soon
            ->get();

        foreach ($riskTenants as $tenant) {
            $this->warn("Churn Risk Detected: {$tenant->company_name}");

            // 1. Log the prediction
            AiLog::create([
                'tenant_id' => $tenant->id,
                'agent_type' => 'finance',
                'action' => 'Churn Risk Prediction',
                'details' => "Tizim aniqladi: Mijoz oxirgi 7 kunda kirmagan va obunasi tugashiga 5 kundan kam qolgan. Avtomat chegirma taklif qilindi."
            ]);

            // 2. Automated Retention Offer (Mocking sending an email/telegram)
            // self::sendRetentionOffer($tenant);
            
            $this->info("Retention offer logged for {$tenant->company_name}");
        }

        $this->info("Analysis completed. " . $riskTenants->count() . " risk(s) found.");
    }
}
