<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Subscription;

class InvoiceController extends Controller
{
    public function download($id)
    {
        $subscription = Subscription::with('tenant')->findOrFail($id);
        $tenant = $subscription->tenant;

        $pdf = Pdf::loadView('pdf.invoice', compact('subscription', 'tenant'));
        
        return $pdf->download("Invoys_{$tenant->company_name}_{$subscription->id}.pdf");
    }
}
