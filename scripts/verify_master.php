<?php
$token = '8738481163:AAHObhjMQhF6suFnFvF0gF0SsQ--mdKcCMU';
$res = @file_get_contents("https://api.telegram.org/bot$token/getMe");
echo "Result: " . ($res ?: "FAIL\n");
