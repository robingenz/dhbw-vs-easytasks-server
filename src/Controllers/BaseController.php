<?php
namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;

abstract class BaseController
{
    protected $container;

    public function responseWithError(Response $response, $message, $statusCode)
    {
        $data = [
            'code' => $statusCode,
            'message' => $message,
        ];
        return $response->withJson($data, $statusCode, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }
}
