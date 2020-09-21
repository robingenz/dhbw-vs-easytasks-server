<?php
namespace App\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Container;

class AuthMiddleware
{
    private $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function __invoke(Request $request, Response $response, $next)
    {
        $authHeader = $request->getHeaderLine('Authorization');
        $validToken = false;
        if (!empty($authHeader)) {
            $token = explode('Bearer ', $authHeader)[1];
            $validToken = $this->container['authService']->verifyAccessToken($token);
        }
        if (!$validToken) {
            $statusCode = 401;
            $data = [
                'code' => $statusCode,
                'message' => 'Authorization has been denied for this request.',
            ];
            return $response->withJson($data, $statusCode, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        }
        $request = $request->withAttribute('jwt', $this->container['authService']->decodeJwt($token));
        return $next($request, $response);
    }
}
