<?php
use App\Controllers\AuthController;
use App\Controllers\DefaultController;
use App\Controllers\ReportController;
use App\Controllers\TaskController;
use App\Controllers\TasklistController;
use App\Middleware\AuthMiddleware;
use Slim\App;

$app->get('/', DefaultController::class . ':index');
$app->get('/ping', DefaultController::class . ':ping');

$app->group('/auth', function (App $app) {
    $app->post('/register', AuthController::class . ':register');
    $app->post('/token', AuthController::class . ':token');
    $app->post('/revoke', AuthController::class . ':revoke')->add(new AuthMiddleware($app->getContainer()));
});

$app->group('', function (App $app) {
    $app->group('/tasks', function (App $app) {
        $app->get('', TaskController::class . ':index');
        $app->get('/{id}', TaskController::class . ':show');
        $app->post('', TaskController::class . ':store');
        $app->put('/{id}', TaskController::class . ':update');
        $app->delete('/{id}', TaskController::class . ':destroy');
    });

    $app->group('/report', function (App $app) {
        $app->get('', ReportController::class . ':index');
    });

    $app->group('/tasklists', function (App $app) {
        $app->get('', TasklistController::class . ':index');
        $app->get('/{id}', TasklistController::class . ':show');
        $app->post('', TasklistController::class . ':store');
        $app->put('/{id}', TasklistController::class . ':update');
        $app->delete('/{id}', TasklistController::class . ':destroy');
    });
})->add(new AuthMiddleware($app->getContainer()));

$app->options('/{routes:.+}', function ($request, $response, array $args) {
    return $response;
});

$app->map(['GET', 'POST', 'PUT', 'DELETE', 'PATCH'], '/{routes:.+}', function ($request, $response) {
    $handler = $this->notFoundHandler;
    return $handler($request, $response);
});
