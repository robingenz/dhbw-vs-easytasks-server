<?php
namespace App\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Container;

class CorsMiddleware
{
    private $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function __invoke(Request $request, Response $response, $next)
    {
        $response = $next($request, $response);
        return $response
            ->withHeader('Access-Control-Allow-Origin', getenv('APP_URL'))
            ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
    }
}
