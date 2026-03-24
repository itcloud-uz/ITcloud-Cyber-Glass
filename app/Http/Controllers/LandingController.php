<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Template;
use App\Models\Lead;

class LandingController extends Controller
{
    public function index()
    {
        $templates = Template::all();
        $newestTemplate = Template::orderBy('created_at', 'desc')->first();
        $priceServices = \App\Models\PriceService::all();
        return view('landing', compact('templates', 'newestTemplate', 'priceServices'));
    }

    public function constructor()
    {
        $templates = Template::all();
        $priceServices = \App\Models\PriceService::all();
        return view('constructor', compact('templates', 'priceServices'));
    }

    public function submitInquiry(Request $request)
    {
        // Save potential client as a Lead with Architect Details
        $lead = Lead::create([
            'client_name' => $request->name,
            'phone' => $request->phone,
            'status' => 'new',
            'agent_type' => 'client_architect',
            'action' => 'Full Project Design',
            'details' => json_encode([
                'project_name' => $request->project_name,
                'category' => $request->category,
                'modules' => $request->selected_modules ?? [],
                'design_notes' => $request->design_notes,
                'estimated_budget' => $request->budget
            ])
        ]);

        return response()->json([
            'status' => 'success', 
            'message' => 'Tabriklaymiz! Sizning loyihangiz dizayni muvaffaqiyatli saqlandi. ITcloud muhandislari uni tahlil qilib, siz bilan bog\'lanishadi.'
        ]);
    }
}
