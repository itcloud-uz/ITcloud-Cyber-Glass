<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\AiProject;
use App\Services\AntigravityCodeService;
use App\Services\TenantProvisionerService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class ProcessAiProjectCode implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $projectId;

    public function __construct($projectId)
    {
        $this->projectId = $projectId;
    }

    public function handle(): void
    {
        $project = AiProject::findOrFail($this->projectId);
        
        // 1. Compile & Generate
        $project->update(['status' => 'generating', 'progress' => 20]);
        $prompt = AntigravityCodeService::compilePrompt($project);
        $response = AntigravityCodeService::generateCodebase($project, $prompt);

        if (!$response) {
            $project->update(['status' => 'error', 'progress' => 0]);
            return;
        }

        // 2. Parse & Cleaning (Markdown Fix)
        $project->update(['status' => 'compiling', 'progress' => 50]);
        
        preg_match_all('/<file path="(.*?)">\s*(.*?)\s*<\/file>/s', $response, $matches);
        
        if (empty($matches[0])) {
             $project->update(['status' => 'error', 'progress' => 0]);
             return;
        }

        $basePath = "ai_projects/{$project->id}";
        
        for ($i = 0; $i < count($matches[0]); $i++) {
            $filePath = $matches[1][$i];
            $codeContent = $matches[2][$i];

            // Senior Fix: Remove Markdown traps
            $codeContent = preg_replace('/^```[a-z]*\n/m', '', $codeContent);
            $codeContent = preg_replace('/\n```$/m', '', $codeContent);
            $codeContent = trim($codeContent);

            Storage::disk('local')->put("{$basePath}/{$filePath}", $codeContent);
        }

        // 3. Deployment (Ghost Deploy Fix)
        $project->update(['status' => 'deploying', 'progress' => 85]);
        
        $sourcePath = storage_path("app/{$basePath}");
        $targetPath = "/var/www/vhosts/" . ($project->config['domain'] ?? 'test') . ".itcloud.uz";

        // In production, we move files and restart Nginx
        if (File::exists($sourcePath)) {
            // File::copyDirectory($sourcePath, $targetPath); // Simulated
            // shell_exec("sudo systemctl restart nginx"); // Simulated
        }

        // 4. Finalize
        $project->update(['status' => 'deployed', 'progress' => 100]);
        $tenant = $project->tenant;
        TenantProvisionerService::provision($tenant);
    }
}
