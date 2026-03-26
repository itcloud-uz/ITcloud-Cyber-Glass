<?php
require 'vendor/autoload.php';

// Manually load .env since we are in a subfolder and using raw PHP
$env = parse_ini_file(dirname(__DIR__) . '/.env');

$tokens = [
    'VERIFICATION' => $env['TELEGRAM_BOT_TOKEN_VERIFICATION'] ?? null,
    'ACADEMY' => $env['TELEGRAM_BOT_TOKEN_ACADEMY'] ?? null,
    'MASTER' => $env['TELEGRAM_BOT_TOKEN_MASTER'] ?? null,
];

foreach ($tokens as $name => $token) {
    if (!$token) {
        echo "Checking $name: MISSING\n";
        continue;
    }
    $url = "https://api.telegram.org/bot{$token}/getMe";
    $json = @file_get_contents($url);
    if ($json) {
        $data = json_decode($json, true);
        echo "Checking $name: OK (" . ($data['result']['username'] ?? 'unknown') . ")\n";
    } else {
        echo "Checking $name: FAIL ($token)\n";
    }
}
