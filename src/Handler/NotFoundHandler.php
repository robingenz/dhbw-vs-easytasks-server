<?php
namespace App\Handler;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class NotFoundHandler
{
    public function __invoke(Request $request, Response $response)
    {
        $statusCode = 404;
        $data = [
            'code' => $statusCode,
            'message' => 'Page not found.',
        ];
        return $response->withJson($data, $statusCode, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }
}
