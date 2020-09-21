<?php

namespace App\Controllers;

use Slim\Container;
use Slim\Http\Request;
use Slim\Http\Response;

class ReportController extends BaseController
{
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function index(Request $request, Response $response, array $args)
    {
        $user = $this->container['userService']->getUserByRequest($request);
        $evaluationObject = $this->container['reportService']->getEvaluationObject($user);
        return $response->withJson($evaluationObject, 200, JSON_NUMERIC_CHECK);
    }
}
