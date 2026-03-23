<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Tenant;
use App\Services\TenantProvisionerService;

class DeployTenantInstance implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $tenantId;

    /**
     * Create a new job instance.
     */
    public function __construct($tenantId)
    {
        $this->tenantId = $tenantId;
    }

    /**
     * Execute the job (Zero-Touch Provisioning)
     */
    public function handle(): void
    {
        $tenant = Tenant::findOrFail($this->tenantId);
        
        TenantProvisionerService::provision($tenant);
    }
}
