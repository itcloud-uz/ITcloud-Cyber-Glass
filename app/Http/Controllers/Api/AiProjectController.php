<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AiProject;
use App\Models\Tenant;
use App\Jobs\ProcessAiProjectCode;

class AiProjectController extends Controller
{
    /**
     * Submit a new Antigravity Project
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'category' => 'required',
            'domain' => 'required',
        ]);

        // 1. Create a placeholder tenant for this project!
        $tenant = Tenant::create([
           'company_name' => $request->name,
           'domain' => $request->domain,
           'status' => 'pending',
           'subscription_ends_at' => now()->addDays(7)
        ]);

        // 2. Create AI Project entry
        $project = AiProject::create([
            'tenant_id' => $tenant->id,
            'name' => $request->name,
            'config' => $request->all(),
            'status' => 'queued',
            'progress' => 5
        ]);

        // 3. Dispatch the big Pipeline Job!
        ProcessAiProjectCode::dispatch($project->id);

        return response()->json([
            'status' => 'success',
            'project' => $project,
            'message' => 'Antigravity Pipeline started successfully!'
        ]);
    }

    /**
     * Get all pipeline projects
     */
    public function index()
    {
        return response()->json(AiProject::with('tenant')->latest()->get());
    }
}
