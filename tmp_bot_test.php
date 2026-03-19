<?php
require 'vendor/autoload.php';
$token = "7957350025:AAE_RUNV4L-cnFv_bnL7gyYiAKnZKKWBy54";
$url = "https://api.telegram.org/bot{$token}/getMe";
$res = file_get_contents($url);
echo "Bot Info: " . $res . "\n";

$webhookUrl = "https://itcloud.uz/api/webhook/telegram/{$token}"; // Assuming this host
echo "Setting Webhook to: " . $webhookUrl . "\n";
$setWebhookUrl = "https://api.telegram.org/bot{$token}/setWebhook?url=" . urlencode($webhookUrl);
$res2 = file_get_contents($setWebhookUrl);
echo "Set Webhook Result: " . $res2 . "\n";
