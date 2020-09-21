<?php

namespace App\Controllers;

use Slim\Container;
use Slim\Http\Request;
use Slim\Http\Response;

class TasklistController extends BaseController
{
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function index(Request $request, Response $response, array $args)
    {
        $user = $this->container['userService']->getUserByRequest($request);
        if ($request->getQueryParam('withTasks')) {
            $tasklists = $this->container['tasklistService']->getTasklistsWithTasksByUser($user);
        } else {
            $tasklists = $this->container['tasklistService']->getTasklistsByUser($user);
        }
        return $response->withJson($tasklists, 200, JSON_NUMERIC_CHECK);
    }

    public function show(Request $request, Response $response, array $args)
    {
        $route = $request->getAttribute('route');
        $tasklistId = $route->getArgument('id');
        $user = $this->container['userService']->getUserByRequest($request);

        if ($request->getQueryParam('withTasks')) {
            $tasklist = $this->container['tasklistService']->getTasklistWithTasksByIdAndUser($tasklistId, $user);
        } else {
            $tasklist = $this->container['tasklistService']->getTasklistByIdAndUser($tasklistId, $user);
        }
        if (empty($tasklist)) {
            return $this->responseWithError($response, 'Task list not found.', 404);
        }
        return $response->withJson($tasklist, 200, JSON_NUMERIC_CHECK);
    }

    public function store(Request $request, Response $response, array $args)
    {
        $user = $this->container['userService']->getUserByRequest($request);
        $parsedBody = $request->getParsedBody();

        if ((!$parsedBody['title']) || $parsedBody['title'] == '') {
            return $this->responseWithError($response, 'Title has to bet set.', 404);
        }

        $this->container['tasklistService']->storeTasklist($parsedBody['title'], $user);

        $returnCode = 201;
        $data = [
            'code' => $returnCode,
            'message' => 'Task list created successfully.',
        ];
        return $response->withJson($data);
    }

    public function update(Request $request, Response $response, array $args)
    {
        $user = $this->container['userService']->getUserByRequest($request);
        $route = $request->getAttribute('route');
        $tasklistId = $route->getArgument('id');

        $parsedBody = $request->getParsedBody();

        if ($parsedBody['id'] != $tasklistId) {
            return $this->responseWithError($response, 'Task list ID does not match with given task list.', 400);
        }

        if ($parsedBody['title'] == null || $parsedBody['title'] == '') {
            return $this->responseWithError($response, 'Title has to bet set.', 400);
        }

        if ($this->container['tasklistService']->updateTasklist($parsedBody['id'], $parsedBody['title'], $user)) {
            $returnCode = 200;
            $data = [
                'code' => $returnCode,
                'message' => 'Task list updated successfully.',
            ];
            return $response->withJson($data);
        }
        return $this->responseWithError($response, 'Task list not found.', 404);
    }

    public function destroy(Request $request, Response $response, array $args)
    {
        $user = $this->container['userService']->getUserByRequest($request);
        $route = $request->getAttribute('route');
        $tasklistId = $route->getArgument('id');

        if ($this->container['tasklistService']->deleteTasklist($tasklistId, $user)) {
            $returnCode = 200;
            $data = [
                'code' => $returnCode,
                'message' => 'Task list deleted successfully.',
            ];
            return $response->withJson($data);
        }
        return $this->responseWithError($response, 'Task list not found.', 404);
    }

}
