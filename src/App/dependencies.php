<?php
use RedBeanPHP\R;
use Slim\Container;

$container = $app->getContainer();

function setupDb(Container $container)
{
    $dbSettings = $container->get('settings')['db'];
    $dsn = sprintf('%s:host=%s;port=%s;dbname=%s', $dbSettings['driver'], $dbSettings['hostname'], $dbSettings['port'], $dbSettings['database']);
    R::setup($dsn, $dbSettings['username'], $dbSettings['password']);
}

setupDb($container);
