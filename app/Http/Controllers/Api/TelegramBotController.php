<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TelegramBot;

class TelegramBotController extends Controller
{
    public function index()
    {
        return response()->json(TelegramBot::all());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required',
            'token' => 'required|unique:telegram_bots',
            'agent_type' => 'required|in:sales,finance,support,custom',
            'is_active' => 'boolean'
        ]);

        $bot = TelegramBot::create($data);

        return response()->json(['status' => 'success', 'bot' => $bot]);
    }

    public function update(Request $request, $id)
    {
        $bot = TelegramBot::findOrFail($id);
        $bot->update($request->all());
        return response()->json(['status' => 'success']);
    }

    public function destroy($id)
    {
        TelegramBot::destroy($id);
        return response()->json(['status' => 'success']);
    }
}
