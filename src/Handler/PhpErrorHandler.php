<?php
namespace App\Handler;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class PhpErrorHandler
{
    public function __invoke(Request $request, Response $response, \Error $exception)
    {
        $statusCode = 500;
        $data = [
            'code' => $statusCode,
            'message' => 'A website error has occurred. Sorry for the temporary inconvenience.',
        ];
        return $response->withJson($data, $statusCode, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }
}
