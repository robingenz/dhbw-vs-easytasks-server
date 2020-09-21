<?php
namespace App\Services;

use App\Entities\TasklistEntity as Tasklist;
use App\Entities\UserEntity as User;
use App\Repositories\TasklistRepository;
use App\Repositories\TaskRepository;

class TasklistService
{
    private $tasklistRepository;

    public function __construct(TasklistRepository $tasklistRepository, TaskRepository $taskRepository)
    {
        $this->tasklistRepository = $tasklistRepository;
        $this->taskRepository = $taskRepository;
    }

    public function getTasklistsByUser(User $user)
    {
        return $this->tasklistRepository->getTasklistsByUserId($user->getId());
    }

    public function getTasklistsWithTasksByUser(User $user)
    {
        $tasklistsWithTasks = [];

        $tasklists = $this->getTasklistsByUser($user);

        if (empty($tasklists)) {
            return $tasklistsWithTasks;
        }

        foreach ($tasklists as $tasklist) {
            $tasklistId = $tasklist->getId();
            $tasklistTitle = $tasklist->getTitle();
            $tasks = $this->taskRepository->getTasksByTasklistId($tasklistId);
            $tasklistWithTasks = [
                'id' => $tasklistId,
                'title' => $tasklistTitle,
                'tasks' => $tasks,
            ];
            array_push($tasklistsWithTasks, $tasklistWithTasks);
        }
        return $tasklistsWithTasks;
    }

    public function getTasklistByIdAndUser($tasklistId, User $user)
    {
        return $this->tasklistRepository->getTasklistByIdAndUserId($tasklistId, $user->getId());
    }

    public function getTasklistWithTasksByIdAndUser($tasklistId, User $user)
    {
        $tasklist = $this->getTasklistByIdAndUser($tasklistId, $user);
        if (empty($tasklist)) {
            return null;
        }

        $tasklistId = $tasklist->getId();
        $tasklistTitle = $tasklist->getTitle();

        $tasks = $this->taskRepository->getTasksByTasklistId($tasklistId);
        $tasklistWithTasks = [
            'id' => $tasklistId,
            'title' => $tasklistTitle,
            'tasks' => $tasks,
        ];

        return $tasklistWithTasks;

    }

    public function storeTasklist($title, User $user)
    {
        $tasklist = Tasklist::create($title, $user->getId());
        $this->tasklistRepository->createTasklist($tasklist);
    }

    public function updateTasklist($id, $title, User $user)
    {
        return $this->tasklistRepository->updateTasklist($id, $title, $user->getId());
    }

    public function deleteTasklist($tasklistId, User $user)
    {
        $tasklist = $this->getTasklistByIdAndUser($tasklistId, $user);
        if (empty($tasklist)) {
            return false;
        }
        return $this->tasklistRepository->deleteTasklist($tasklistId);
    }
}
