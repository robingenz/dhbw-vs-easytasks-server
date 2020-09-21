<?php
use Psr\Container\ContainerInterface;

$container = $app->getContainer();

if (!evalBool(getenv('APP_DEBUG'))) {
    $container['errorHandler'] = function (ContainerInterface $container) {
        return new App\Handler\ErrorHandler();
    };

    $container['phpErrorHandler'] = function (ContainerInterface $container) {
        return new App\Handler\PhpErrorHandler();
    };

    $container['notFoundHandler'] = function (ContainerInterface $container) {
        return new App\Handler\NotFoundHandler();
    };

    $container['notAllowedHandler'] = function (ContainerInterface $container) {
        return new App\Handler\NotAllowedHandler();
    };
}

$container['userRepository'] = function (ContainerInterface $container) {
    return new App\Repositories\UserRepository();
};

$container['sessionRepository'] = function (ContainerInterface $container) {
    return new App\Repositories\SessionRepository();
};

$container['authService'] = function (ContainerInterface $container) {
    return new App\Services\AuthService($container->get('userRepository'), $container->get('sessionRepository'));
};

$container['userService'] = function (ContainerInterface $container) {
    return new App\Services\UserService($container->get('userRepository'));
};

$container['sessionService'] = function (ContainerInterface $container) {
    return new App\Services\SessionService($container->get('sessionRepository'));
};

$container['taskRepository'] = function (ContainerInterface $container) {
    return new App\Repositories\TaskRepository();
};

$container['tasklistRepository'] = function (ContainerInterface $container) {
    return new App\Repositories\TasklistRepository();
};

$container['reportRepository'] = function (ContainerInterface $container) {
    return new App\Repositories\ReportRepository();
};

$container['taskService'] = function (ContainerInterface $container) {
    return new App\Services\TaskService($container->get('taskRepository'), $container->get('tasklistRepository'));
};

$container['tasklistService'] = function (ContainerInterface $container) {
    return new App\Services\TasklistService($container->get('tasklistRepository'), $container->get('taskRepository'));
};

$container['reportService'] = function (ContainerInterface $container) {
    return new App\Services\ReportService($container->get('reportRepository'));
};
