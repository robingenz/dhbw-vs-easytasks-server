<?php
namespace App\Handler;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ErrorHandler
{
    public function __invoke(Request $request, Response $response, \Exception $exception)
    {
        $statusCode = $exception->getCode() <= 599 && $exception->getCode() >= 100 ? $exception->getCode() : 500;
        $data = [
            'code' => $statusCode,
            'message' => $exception->getMessage(),
        ];
        return $response->withJson($data, $statusCode, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }
}
