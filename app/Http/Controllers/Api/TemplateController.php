<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Template;

class TemplateController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required',
            'description' => 'required',
            'price' => 'required|numeric',
            'preview_url' => 'nullable|url'
        ]);

        $template = Template::create($data);

        return response()->json(['status' => 'success', 'template' => $template]);
    }

    public function destroy($id)
    {
        Template::destroy($id);
        return response()->json(['status' => 'success']);
    }
}
