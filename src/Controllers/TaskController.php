<?php

namespace App\Controllers;

use Slim\Container;
use Slim\Http\Request;
use Slim\Http\Response;

class TaskController extends BaseController
{
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function index(Request $request, Response $response, array $args)
    {
        $user = $this->container['userService']->getUserByRequest($request);
        $tasks = $this->container['taskService']->getTasksByUserAndRequest($user, $request);
        return $response->withJson($tasks, 200, JSON_NUMERIC_CHECK);
    }

    public function show(Request $request, Response $response, array $args)
    {
        $route = $request->getAttribute('route');
        $taskId = $route->getArgument('id');
        $user = $this->container['userService']->getUserByRequest($request);
        $task = $this->container['taskService']->getTaskByIdAndUser($taskId, $user);
        if (empty($task)) {
            return $this->responseWithError($response, 'Task not found.', 400);
        }
        return $response->withJson($task, 200, JSON_NUMERIC_CHECK);
    }

    public function store(Request $request, Response $response, array $args)
    {
        $user = $this->container['userService']->getUserByRequest($request);
        $parsedBody = $request->getParsedBody();

        $requestTasklist = $this->container['tasklistService']->getTasklistByIdAndUser($parsedBody['tasklistId'], $user);

        if (!$requestTasklist) {
            return $this->responseWithError($response, 'Task list does not exist.', 400);
        }

        if ($parsedBody['priority'] < 0 || $parsedBody['priority'] > 5) {
            return $this->responseWithError($response, 'Priority has to be between 0 and 5.', 400);
        }
        if ($parsedBody['status'] < 0 || $parsedBody['status'] > 1) {
            return $this->responseWithError($response, 'Status incorrect.', 400);
        }

        $this->container['taskService']->storeTask($parsedBody['title'], $parsedBody['description'], $parsedBody['priority'], $parsedBody['deadline'], $parsedBody['status'], $parsedBody['tasklistId']);
        $returnCode = 201;
        $data = [
            'code' => $returnCode,
            'message' => 'Task created successfully.',
        ];
        return $response->withJson($data);
    }

    public function update(Request $request, Response $response, array $args)
    {
        $user = $this->container['userService']->getUserByRequest($request);
        $route = $request->getAttribute('route');
        $taskId = $route->getArgument('id');

        $parsedBody = $request->getParsedBody();

        if ($parsedBody['id'] != $taskId) {
            return $this->responseWithError($response, 'Task ID does not match with given task.', 400);
        }

        if (!$this->container['tasklistService']->getTasklistByIdAndUser($parsedBody['tasklistId'], $user)) {
            return $this->responseWithError($response, 'Tasklist not found.', 400);
        }

        if ($parsedBody['priority'] < 0 || $parsedBody['priority'] > 5) {
            return $this->responseWithError($response, 'Priority has to be between 0 and 5.', 400);

        }
        if ($parsedBody['status'] < 0 || $parsedBody['status'] > 1) {
            return $this->responseWithError($response, 'Status incorrect.', 400);
        }

        if ($this->container['taskService']->updateTask($parsedBody['id'], $parsedBody['title'], $parsedBody['description'], $parsedBody['priority'], $parsedBody['deadline'], $parsedBody['status'], $parsedBody['tasklistId'])) {
            $returnCode = 200;
            $data = [
                'code' => $returnCode,
                'message' => 'Task updated successfully.',
            ];
            return $response->withJson($data);
        }
        return $this->responseWithError($response, 'Task update failed.', 404);
    }

    public function destroy(Request $request, Response $response, array $args)
    {
        $user = $this->container['userService']->getUserByRequest($request);
        $route = $request->getAttribute('route');
        $taskId = $route->getArgument('id');
        if ($this->container['taskService']->deleteTask($taskId, $user)) {
            $returnCode = 200;
            $data = [
                'code' => $returnCode,
                'message' => 'Task deleted successfully.',
            ];
            return $response->withJson($data);
        }
        return $this->responseWithError($response, 'Task not found.', 404);
    }

}
