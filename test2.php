<?php
require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$key = env('GEMINI_API_KEY');
$m = Illuminate\Support\Facades\Http::get('https://generativelanguage.googleapis.com/v1beta/models?key='.$key)->json();
$res = [];
foreach($m['models']??[] as $model) {
    if(in_array('generateContent', $model['supportedGenerationMethods']??[])) {
        $res[] = $model['name'];
    }
}
echo implode("\n", $res);
