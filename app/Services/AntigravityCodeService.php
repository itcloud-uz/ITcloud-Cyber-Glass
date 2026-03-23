<?php

namespace App\Services;

use App\Models\AiProject;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class AntigravityCodeService
{
    /**
     * Converts JSON config to a Master Prompt for AI
     */
    public static function compilePrompt(AiProject $project)
    {
        $config = $project->config;
        $tables = $config['tables'] ?? 'vazifa_jurnali (nomi, srok, status)';
        $feats = implode(', ', $config['features'] ?? []);
        
        $prompt = "Role: Sen ITcloud Antigravity AI jamoasining Senior Full-Stack dasturchisisan.\n";
        $prompt .= "Task: Mijoz uchun '{$project->name}' nomli to'liq ishlaydigan CRM tizimini generatsiya qilish.\n";
        $prompt .= "Architecture: Laravel 11 + Blade (Cyber-Glass UI style).\n";
        $prompt .= "Database Schema: {$tables}.\n";
        $prompt .= "Included Modules: {$feats}.\n";
        $prompt .= "Constraint: Faqat real va xatosiz PHP/HTML kodlarini fayl nomlari bilan (Header: File: path/name.php) bloklarda generatsiya qil. Hech qanday tushuntirish kerakmas, faqat kod!";
        
        return $prompt;
    }

    /**
     * Call Gemini to generate the codebase
     */
    public static function generateCodebase(AiProject $project, string $prompt)
    {
        $apiKey = env('GEMINI_API_KEY');
        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-pro:generateContent?key={$apiKey}";

        $payload = [
            'contents' => [['parts' => [['text' => $prompt]]]]
        ];

        try {
            $response = Http::post($url, $payload);
            $data = $response->json();
            $code = $data['candidates'][0]['content']['parts'][0]['text'] ?? "// Error generating code";
            
            // In a real scenario, we would parse this multi-file response and save to code_path
            return $code;
        } catch (\Exception $e) {
            Log::error("Antigravity Code Gen Error: " . $e->getMessage());
            return null;
        }
    }
}
