<?php
$baseDir = __DIR__ . '/../../';
$envFile = $baseDir . '.env';
if (file_exists($envFile)) {
    $dotenv = Dotenv\Dotenv::create($baseDir);
    $dotenv->load();
    $dotenv->required(['APP_URL'])->notEmpty();
    $dotenv->required(['APP_ENV'])->allowedValues(['development', 'test', 'production']);
    $dotenv->required(['APP_DEBUG'])->isBoolean();
}
