<?php
namespace App\Handler;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class NotAllowedHandler
{
    public function __invoke(Request $request, Response $response, $methods)
    {
        $statusCode = 405;
        $data = [
            'code' => $statusCode,
            'message' => 'Method must be one of: ' . implode(', ', $methods),
        ];
        return $response
            ->withHeader('Allow', implode(', ', $methods))
            ->withJson($data, $statusCode, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }
}
