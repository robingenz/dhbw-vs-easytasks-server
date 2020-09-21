<?php
use App\Middleware\CorsMiddleware;

$container = $app->getContainer();

$app->add(new CorsMiddleware($container));
