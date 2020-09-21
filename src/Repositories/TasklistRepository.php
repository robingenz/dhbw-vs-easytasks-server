<?php
namespace App\Repositories;

use App\Entities\TasklistEntity as Tasklist;
use RedBeanPHP\R;

class TasklistRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct('tasklist');
    }

    public function createTasklist(Tasklist $tasklist)
    {
        $tasklistBean = R::dispense($this->getTable());
        $tasklistBean->title = $tasklist->getTitle();
        $tasklistBean->createdAt = $tasklist->getCreatedAt();
        $tasklistBean->updatedAt = $tasklist->getUpdatedAt();
        $taskId = R::store($tasklistBean);

        $userElement = R::load('user', $tasklist->getUserId());
        if ($userElement) {
            $userElement->xownTasklistList[] = $tasklistBean;
            R::store($userElement);
        }
    }
//tasklist ID mitgeben
    public function getTasklistsByUserId($userId)
    {
        $tasklistElements = R::findAll($this->getTable(), ' user_id = ? ', [$userId]);

        $tasklists = [];
        foreach ($tasklistElements as $tasklistElement) {
            array_push($tasklists, new Tasklist($tasklistElement['id'], $tasklistElement['title'], $tasklistElement['createdAt'], $tasklistElement['updatedAt'], $tasklistElement['userId']));
        }

        return $tasklists;
    }

    public function getTasklistByIdAndUserId($tasklistId, $userId)
    {

        $tasklistElement = R::findOne($this->getTable(), ' id = ? AND user_id = ? ', [$tasklistId, $userId]);
        if (empty($tasklistElement)) {
            return null;
        }
        return new TaskList($tasklistElement['id'], $tasklistElement['title'], $tasklistElement['createdAt'], $tasklistElement['updatedAt'], $tasklistElement['userId']);
    }

    public function getTasklistById($tasklistId)
    {
        $tasklistElement = R::findOne($this->getTable(), ' id = ? ', [$tasklistId]);
        if (empty($tasklistElement)) {
            return null;
        }
        return new Tasklist($tasklistElement['id'], $tasklistElement['title'], $tasklistElement['createdAt'], $tasklistElement['updatedAt'], $tasklistElement['userId']);
    }

    public function updateTasklist($id, $title, $userId)
    {
        $tasklistElement = R::findOne($this->getTable(), ' id = ? AND user_id = ? ', [$id, $userId]);
        if (!empty($tasklistElement)) {
            $tasklistElement['title'] = $title;
            $tasklistElement['updated_at'] = time();
            $taskId = R::store($tasklistElement);
            return $this->getTasklistById($id);
        }
    }

    public function deleteTasklist($tasklistId)
    {
        $tasklistElement = R::findOne($this->getTable(), ' id = ? ', [$tasklistId]);

        if ($tasklistElement) {
            R::trash($tasklistElement);
            return true;
        }

        return false;

    }
}
