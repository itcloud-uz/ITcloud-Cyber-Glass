<?php
$key = parse_ini_file('.env')['GEMINI_API_KEY'];
$json = file_get_contents("https://generativelanguage.googleapis.com/v1beta/models?key=" . $key);
$data = json_decode($json, true);
foreach($data['models'] as $m) {
    if(in_array('generateContent', $m['supportedGenerationMethods'])) {
        echo $m['name'] . "\n";
    }
}
