<?php
namespace App\Repositories;

use App\Entities\TaskEntity as Task;
use RedBeanPHP\R;

class TaskRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct('task');
    }

    public function createTask(Task $task)
    {
        $taskBean = R::dispense($this->getTable());
        $taskBean->title = $task->getTitle();
        $taskBean->description = $task->getDescription();
        $taskBean->priority = $task->getPriority();
        $taskBean->deadline = $task->getDeadline();
        $taskBean->status = $task->getStatus();
        $taskBean->createdAt = $task->getCreatedAt();
        $taskBean->updatedAt = $task->getUpdatedAt();

        $tasklistElement = R::load('tasklist', $task->getTasklistId());
        if ($tasklistElement) {
            $tasklistElement->xownTaskList[] = $taskBean;
            R::store($tasklistElement);
        }
    }
//tasklist ID mitgeben
    public function getTasksByTasklistId($tasklistId)
    {
        $tasks = [];
        $taskElements = R::findAll($this->getTable(), ' tasklist_id = ? ORDER BY id desc ', [$tasklistId]);

        foreach ($taskElements as $taskElement) {
            array_push($tasks, new Task($taskElement['id'], $taskElement['title'], $taskElement['description'], $taskElement['priority'], $taskElement['deadline'], $taskElement['status'], $taskElement['created_at'], $taskElement['updated_at'], $taskElement['tasklist_id']));

        }

        return $tasks;
    }

    public function getTasksByUserId($userId, $restrictions)
    {
        $queryBindings = [];
        $sortQuery = 'SELECT ' . $this->getTable() . '.* FROM ' . $this->getTable() . ' JOIN tasklist ON ' . $this->getTable() . '.tasklist_id = tasklist.id JOIN user ON tasklist.user_id = user.id WHERE user.id = ? ';
        array_push($queryBindings, $userId);
        foreach ($restrictions['filtering'] as $varName => $value) {
            if ($varName == 'urgency') {
                $used = false;
                for ($i = 0; $i < sizeOf($value); $i++) {
                    if ($used == false) {
                        if ($value[$i] != '=') {
                            $timestamp = time();
                            $sortQuery .= ' AND (( task.deadline - ' . $timestamp . ') ' . $value[$i] . ' (7 * 24 * 60 * 60) ';
                        } else {
                            $sortQuery .= ' AND ( task.deadline IS NULL ';
                        }

                    } else {
                        if ($value[$i] != '=') {
                            $timestamp = time();
                            $sortQuery .= ' OR (task.deadline - ' . $timestamp . ') ' . $value[$i] . ' (7 * 24 * 60 * 60)  ';
                        } else {
                            $sortQuery .= ' OR task.deadline IS NULL ';
                        }

                    }

                    if ($value[$i]) {
                        $used = true;
                    }

                }
                $sortQuery .= ') ';

            } else {
                if (!empty($value)) {
                    $sortQuery .= 'AND ' . $this->getTable() . '.' . $varName . ' IN ( ' . R::genSlots($value) . ' ) ';
                    $queryBindings = array_merge($queryBindings, $value);
                }
            }
        }

        foreach ($restrictions['ranges'] as $varName => $value) {
            if (!empty($value)) {
                if (!empty($value['from'])) {
                    $sortQuery .= 'AND ' . $this->getTable() . '.' . $varName . ' >= ? ';
                    array_push($queryBindings, $value['from']);
                }
                if (!empty($value['to'])) {
                    $sortQuery .= 'AND ' . $varName . ' <= ? ';
                    array_push($queryBindings, $value['to']);
                }
            }
        }

        $sortQuery .= 'ORDER BY ';
        if (!empty($restrictions['sorting']['sortBy'])) {
            foreach ($restrictions['sorting']['sortBy'] as $sortIndex => $value) {
                if ($restrictions['sorting']['orderBy'][$sortIndex] == 'desc') {
                    $sortQuery .= 'NOT ';
                }
                $sortQuery .= 'ISNULL(' . $this->getTable() . '.' . $value . '), ';
                $sortQuery .= $this->getTable() . '.' . $value . ' ' . $restrictions['sorting']['orderBy'][$sortIndex] . ' ';
                if ($sortIndex < sizeOf($restrictions['sorting']['sortBy']) - 1) {
                    $sortQuery .= ', ';
                }
            }
        } else {
            $sortQuery .= 'id desc ';
        }

        if ($restrictions['limit']) {
            $sortQuery .= 'limit ?';
            array_push($queryBindings, intval($restrictions['limit']));
        }
        $taskElements = R::getAll($sortQuery, $queryBindings);

        $tasks = [];

        foreach ($taskElements as $taskElement) {
            array_push($tasks, new Task($taskElement['id'], $taskElement['title'], $taskElement['description'], $taskElement['priority'], $taskElement['deadline'], $taskElement['status'], $taskElement['created_at'], $taskElement['updated_at'], $taskElement['tasklist_id']));
        }

        return $tasks;
    }

    public function getTaskByIdAndUserId($taskId, $userId)
    {
        $queryString = 'SELECT ' . $this->getTable() . '.* FROM ' . $this->getTable() . ' JOIN tasklist ON task.tasklist_id = tasklist.id JOIN user ON tasklist.user_id = user.id WHERE ' . $this->getTable() . '.id = ? AND user_id = ? ';
        $taskElement = R::getRow($queryString, [$taskId, $userId]);
        if (empty($taskElement)) {
            return null;
        }
        return new Task($taskElement['id'], $taskElement['title'], $taskElement['description'], $taskElement['priority'], $taskElement['deadline'], $taskElement['status'], $taskElement['created_at'], $taskElement['updated_at'], $taskElement['tasklist_id']);

    }

    public function updateTask($id, $title, $description, $priority, $deadline, $status, $tasklistId)
    {
        $taskElement = R::findOne($this->getTable(), ' id = ? ', [$id]);
        if (!empty($taskElement) && $taskElement['status'] != 1 && $tasklistId == $taskElement['tasklist_id']) {
            $taskElement['title'] = $title;
            $taskElement['description'] = $description;
            $taskElement['priority'] = $priority;
            $taskElement['deadline'] = $deadline;
            $taskElement['status'] = $status;
            $taskElement['updated_at'] = time();
            $taskId = R::store($taskElement);
            return $this->getTaskById($id);
        }

        return false;

    }

    public function getTaskById($taskId)
    {
        $taskElement = R::findOne($this->getTable(), ' id = ? ', [$taskId]);
        if (empty($taskElement)) {
            return null;
        }
        return new Task($taskElement['id'], $taskElement['title'], $taskElement['description'], $taskElement['priority'], $taskElement['deadline'], $taskElement['status'], $taskElement['created_at'], $taskElement['updated_at'], $taskElement['tasklist_id']);
    }

    public function deleteTask($taskId)
    {
        if ($taskElement = R::findOne($this->getTable(), ' id = ? ', [$taskId])) {
            R::trash($taskElement);
            return true;
        }
        return false;
    }

    public function confirmColumnName($sortBy)
    {
        $columns = R::inspect($this->getTable());
        foreach ($columns as $column => $value) {
            if ($sortBy == $column) {
                return true;
            }
        }
        return false;
    }
}
