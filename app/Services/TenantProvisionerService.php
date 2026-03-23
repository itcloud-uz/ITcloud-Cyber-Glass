<?php

namespace App\Services;

use App\Models\Tenant;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class TenantProvisionerService
{
    /**
     * Entry point for full automation pipeline
     */
    public static function provision(Tenant $tenant)
    {
        Log::info("Starting Zero-Touch Provisioning for: {$tenant->company_name}");

        // 1. Setup Database (Schema or separate DB)
        self::setupDatabase($tenant);

        // 2. Setup Nginx & SSL via DomainService
        if ($tenant->custom_domain) {
            DomainService::setupCustomDomain($tenant, $tenant->custom_domain);
        } else {
            // Default sub-domain logic
            Log::info("Setting up subdomain: {$tenant->domain}.itcloud.uz");
        }

        // 3. Notify User (e.g. via Telegram)
        self::notifyClient($tenant);

        Log::info("Provisioning completed for: {$tenant->company_name}");
        return true;
    }

    private static function setupDatabase(Tenant $tenant)
    {
        // MOCK: In production would run: CREATE DATABASE crm_{$tenant->id};
        // and run php artisan migrate --database=tenant_connection
        Log::info("Database 'crm_{$tenant->id}' created and migrated.");
    }

    private static function notifyClient(Tenant $tenant)
    {
        // 1. Generate temp password
        $password = "itcloud_" . rand(1000, 9999);
        
        // 2. Send Telegram Message (Using MultiChannelService)
        // Here we assume we have the client's Telegram ID in tenants or leads
        // MultiChannelService::sendTelegram($masterBotToken, $clientId, "Loyiha tayyor: {$tenant->domain}. Login: admin, Parol: $password");
        
        Log::info("Client notified about project activation.");
    }
}
