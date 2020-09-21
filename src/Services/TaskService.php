<?php
namespace App\Services;

use App\Entities\TaskEntity as Task;
use App\Entities\UserEntity as User;
use App\Repositories\TasklistRepository;
use App\Repositories\TaskRepository;
use Slim\Http\Request;

class TaskService
{
    private $taskRepository;
    private $tasklistRepository;

    public function __construct(TaskRepository $taskRepository, TasklistRepository $tasklistRepository)
    {
        $this->taskRepository = $taskRepository;
        $this->tasklistRepository = $tasklistRepository;
    }

    public function getTasksByUserAndRequest(User $user, Request $request)
    {
        if ($request->getQueryParam('limit') && $request->getQueryParam('limit') > 0) {
            $restrictions['limit'] = $request->getQueryParam('limit');
        } else {
            $restrictions['limit'] = null;
        }

        $restrictions['sorting'] = $this->getSortingProperties($request);
        if (($restrictions['filtering'] = $this->getFilterProperties($user->getId(), $request)) == 'invalid') {
            return [];
        };
        $restrictions['ranges'] = $this->getRangeProperties($request);
        $tasksByTasklists = $this->taskRepository->getTasksByUserId($user->getId(), $restrictions);
        return $tasksByTasklists;
    }

    public function getSortingProperties(Request $request)
    {
        $sortBy = $request->getQueryParam('sortBy');
        $orderBy = $request->getQueryParam('orderBy');

        $sorting['sortBy'] = [];
        $sorting['orderBy'] = [];
        if ($sortBy) {
            foreach ($sortBy as $sortIndex => $value) {
                if ($this->taskRepository->confirmColumnName($value)) {
                    array_push($sorting['sortBy'], $value);
                    if ($orderBy[$sortIndex] && ($orderBy[$sortIndex] == 'asc' || $orderBy[$sortIndex] == 'desc')) {
                        array_push($sorting['orderBy'], $orderBy[$sortIndex]);
                    } else {
                        array_push($sorting['orderBy'], 'asc');
                    }
                } elseif($value == 'urgency'){
                    array_push($sorting['sortBy'], 'deadline');
                    array_push($sorting['sortBy'], 'priority');

                    if ($orderBy[$sortIndex] && ($orderBy[$sortIndex] == 'asc')) {
                        array_push($sorting['orderBy'], 'desc');
                        array_push($sorting['orderBy'], 'asc');

                    } elseif($orderBy[$sortIndex] == 'desc'){
                        array_push($sorting['orderBy'], 'asc');
                        array_push($sorting['orderBy'], 'desc');
                         } else {
                             array_push($sorting['orderBy'], 'asc');
                    }
                }
            }
        }

        return $sorting;
    }
    public function getFilterProperties($userId, Request $request)
    {
        $filters = [];
        $elements = [];

        if ($lists = $request->getQueryParam('tasklist_id')) {
            foreach ($lists as $list) {
                if (!empty($this->tasklistRepository->getTasklistByIdAndUserId($list, $userId))) {
                    array_push($elements, $list);
                }
            }

            $filters['tasklist_id'] = $elements;
            if (empty($filters['tasklist_id'])) {
                return 'invalid';
            }

            $elements = [];
        }

        if ($urgencys = $request->getQueryParam('urgency')) {
            foreach ($urgencys as $urgency) {
                switch ($urgency) {
                    case 2: 
                        $urgency = '<=';
                        break;
                    case 1: 
                        $urgency = '>';
                        break;
                    case 0:
                        $urgency = '=';
                        break;
                }
           
               array_push($elements, $urgency);
            }
            $filters['urgency'] = $elements;
            if (empty($filters['urgency'])) {
                return 'invalid';
            }

            $elements = [];
        }   


        if ($status = $request->getQueryParam('status')) {
            foreach ($status as $stat) {
                if ($stat >= 0 && $stat <= 1) {
                    array_push($elements, $stat);
                }
            }
            $filters['status'] = $elements;
            if (empty($filters['status'])) {
                return 'invalid';
            }

            $elements = [];
        }

        if ($priorities = $request->getQueryParam('priority')) {
            foreach ($priorities as $priority) {
                if ($priority >= 1 && $priority <= 5) {
                    array_push($elements, $priority);
                }
            }
            $filters['priority'] = $elements;
            if (empty($filters['priority'])) {
                return 'invalid';
            }

            $elements = [];

        }
        return $filters;

    }

    public function getRangeProperties(Request $request)
    {
        $ranges = [];
        $range = [];
        if ($deadlineFrom = $request->getQueryParam('deadlineFrom')) {
            $range['from'] = $deadlineFrom;
        }
        if ($deadlineTo = $request->getQueryParam('deadlineTo')) {
            $range['to'] = $deadlineTo;
        }
        if (!empty($range)) {
            $ranges['deadline'] = $range;
            $range = [];
        }

        if ($createdFrom = $request->getQueryParam('createdFrom')) {
            $range['from'] = $createdFrom;
        }
        if ($createdTo = $request->getQueryParam('createdTo')) {
            $range['to'] = $createdTo;
        }
        if (!empty($range)) {
            $ranges['created_at'] = $range;
            $range = [];
        }

        if ($updatedFrom = $request->getQueryParam('updatedFrom')) {
            $range['from'] = $updatedFrom;
        }
        if ($updatedTo = $request->getQueryParam('updatedTo')) {
            $range['to'] = $updatedTo;
        }
        if (!empty($range)) {
            $ranges['updated_at'] = $range;
            $range = [];
        }
        return $ranges;
    }

    public function storeTask($title, $description, $priority, $deadline, $status, $tasklistId)
    {
        $task = Task::create($title, $description, $priority, $deadline, $status, $tasklistId);
        $this->taskRepository->createTask($task);
    }

    public function getTaskByIdAndUser($taskId, User $user)
    {
        $task = $this->taskRepository->getTaskByIdAndUserId($taskId, $user->getId());
        if (empty($task)) {
            return null;
        }
        return $task;
    }

    public function getTasklistIdByTask(Task $task)
    {
        return $task->getTasklistId();
    }

    public function updateTask($id, $title, $description, $priority, $deadline, $status, $tasklistId)
    {
        return $this->taskRepository->updateTask($id, $title, $description, $priority, $deadline, $status, $tasklistId);
    }

    public function deleteTask($taskId, User $user)
    {
        $task = $this->getTaskByIdAndUser($taskId, $user);
        if (empty($task)) {
            return false;
        }
        return $this->taskRepository->deleteTask($taskId);
    }
}
