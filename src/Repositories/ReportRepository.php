<?php
namespace App\Repositories;

use RedBeanPHP\R;

class ReportRepository extends BaseRepository
{
    private $userId;
    public function __construct()
    {}

    public function setUserId($userId)
    {
        $this->userId = $userId;
    }

    public function getOpenTaskCount()
    {
        $queryString = 'SELECT COUNT(*) FROM task JOIN tasklist ON task.tasklist_id = tasklist.id JOIN user ON tasklist.user_id = user.id WHERE status <> 1 AND user.id = ' . $this->userId;
        $taskCount = R::getRow($queryString);
        return $taskCount['COUNT(*)'];

    }

    public function dueTaskCountTwoDays()
    {
        $timestamp = time();
        $queryString = 'SELECT COUNT(*) FROM task JOIN tasklist ON task.tasklist_id = tasklist.id JOIN user ON tasklist.user_id = user.id WHERE status <> 1 AND ( task.deadline - ' . $timestamp . ' ) <= ( 2 * 24 * 60 * 60 ) AND task.deadline >= ' . $timestamp . ' AND user.id = ' . $this->userId;
        $taskCount = R::getRow($queryString);
        return $taskCount['COUNT(*)'];

    }

    public function getPercentTaskExceeded()
    {
        $timestamp = time();
        $queryString = 'SELECT COUNT(*) FROM task JOIN tasklist ON task.tasklist_id = tasklist.id JOIN user ON tasklist.user_id = user.id WHERE ( task.updated_at > task.deadline AND task.status = 1 OR task.deadline <' . $timestamp . ' AND task.status <> 1 ) AND user.id = ' . $this->userId;
        $exceededTaskCount = R::getRow($queryString);

        $queryString = 'SELECT COUNT(*) FROM task JOIN tasklist ON task.tasklist_id = tasklist.id JOIN user ON tasklist.user_id = user.id WHERE user.id = ' . $this->userId;
        $totalTaskCount = R::getRow($queryString);

        if ($totalTaskCount['COUNT(*)'] != 0) {
            return ($exceededTaskCount['COUNT(*)'] / $totalTaskCount['COUNT(*)']);

        } else {
            return 0;
        }
    }

    public function getAverageProcessingTime()
    {
        $queryString = 'SELECT AVG(task.updated_at-task.created_at) FROM task JOIN tasklist ON task.tasklist_id = tasklist.id JOIN user ON tasklist.user_id = user.id WHERE user.id = ' . $this->userId . ' AND task.status = 1';
        return R::getRow($queryString)['AVG(task.updated_at-task.created_at)'];

    }

    public function getTaskCountPerPriority($priority)
    {
        // for each priority count tasks
        $queryString = 'SELECT COUNT(*) FROM task JOIN tasklist ON task.tasklist_id = tasklist.id JOIN user ON tasklist.user_id = user.id WHERE user.id = ' . $this->userId . ' AND task.priority = ' . $priority;
        return R::getRow($queryString)['COUNT(*)'];
    }

    public function getOpenTaskCountPerTasklist()
    {

        $queryString = 'SELECT tasklist.id, tasklist.title FROM tasklist JOIN user ON tasklist.user_id = user.id WHERE user.id = ' . $this->userId;
        $tasklistBeans = R::getAll($queryString);

        $queryString = 'SELECT tasklist.id, COUNT(*)  FROM task JOIN tasklist ON task.tasklist_id = tasklist.id JOIN user ON tasklist.user_id = user.id WHERE task.status <> 1 AND user.id = ' . $this->userId . ' GROUP BY tasklist.id';
        $openTaskCountPerTasklistBeans = R::getAll($queryString);

        $queryString = 'SELECT tasklist.id, COUNT(*)  FROM task JOIN tasklist ON task.tasklist_id = tasklist.id JOIN user ON tasklist.user_id = user.id WHERE task.status = 1 AND user.id = ' . $this->userId . ' GROUP BY tasklist.id';
        $closedTaskCountPerTasklistBeans = R::getAll($queryString);

        $timestamp = time();

        $queryString = 'SELECT tasklist.id, COUNT(*) FROM task JOIN tasklist ON task.tasklist_id = tasklist.id JOIN user ON tasklist.user_id = user.id WHERE  task.status <> 1 AND task.deadline <' . $timestamp . ' AND user.id = ' . $this->userId . ' GROUP BY tasklist.id';
        $dueTaskCountPerTasklistBeans = R::getAll($queryString);

        $taskCountPerTasklist = [];

        foreach ($tasklistBeans as $tasklistBean) {
            $taskCountTasklist = [];
            $taskCountTasklist['tasklistId'] = $tasklistBean['id'];
            $taskCountTasklist['tasklistTitle'] = $tasklistBean['title'];
            if (sizeOf($openTaskCountPerTasklistBeans) > 0) {
                foreach ($openTaskCountPerTasklistBeans as $openTaskCountPerTasklistBean) {
                    if ($openTaskCountPerTasklistBean['id'] == $tasklistBean['id']) {
                        $taskCountTasklist['openTasks'] = $openTaskCountPerTasklistBean['COUNT(*)'];
                        break;
                    } else {
                        $taskCountTasklist['openTasks'] = 0;
                    }
                }
            } else {
                $taskCountTasklist['openTasks'] = 0;
            }

            if (sizeOf($dueTaskCountPerTasklistBeans) > 0) {

                foreach ($dueTaskCountPerTasklistBeans as $dueTaskCountPerTasklistBean) {
                    if ($dueTaskCountPerTasklistBean['id'] == $tasklistBean['id']) {
                        $taskCountTasklist['expiredTasks'] = $dueTaskCountPerTasklistBean['COUNT(*)'];
                        break;
                    } else {
                        $taskCountTasklist['expiredTasks'] = 0;
                    }
                }

            } else {
                $taskCountTasklist['expiredTasks'] = 0;

            }

            if (sizeOf($closedTaskCountPerTasklistBeans) > 0) {

                foreach ($closedTaskCountPerTasklistBeans as $closedTaskCountPerTasklistBean) {
                    if ($closedTaskCountPerTasklistBean['id'] == $tasklistBean['id']) {
                        $taskCountTasklist['closedTasks'] = $closedTaskCountPerTasklistBean['COUNT(*)'];
                        break;
                    } else {
                        $taskCountTasklist['closedTasks'] = 0;
                    }

                }
            } else {
                $taskCountTasklist['closedTasks'] = 0;
            }
            array_push($taskCountPerTasklist, $taskCountTasklist);
        }

        return $taskCountPerTasklist;
    }

    public function getTaskCountActualWeek()
    {
        $taskCountActualWeek = [];
        //actual timestamp
        //seconds wmissing to 01.05.1970 00:00:00, first monday
        //seconds of 1 weak
        // in total seconds until last monday 00:00:00
        $mondays = $this->getMondays();
        $mondayThisWeak = $mondays['thisWeek'];
        $mondayNextWeak = $mondays['nextWeek'];

        $queryString = 'SELECT COUNT(*)  FROM task JOIN tasklist ON task.tasklist_id = tasklist.id JOIN user ON tasklist.user_id = user.id WHERE task.status = 1 AND task.updated_at BETWEEN ' . $mondayThisWeak . ' AND ' . $mondayNextWeak . ' AND user.id = ' . $this->userId;
        $closedTasksThisWeek = R::getRow($queryString)['COUNT(*)'];
        $taskCountActualWeek['closedTasks'] = $closedTasksThisWeek;

        $queryString = 'SELECT COUNT(*)  FROM task JOIN tasklist ON task.tasklist_id = tasklist.id JOIN user ON tasklist.user_id = user.id WHERE ( task.status <> 1 AND task.deadline < ' . time() . ' OR task.status = 1 AND task.updated_at > task.deadline ) AND task.deadline BETWEEN ' . $mondayThisWeak . ' AND ' . $mondayNextWeak . ' AND user.id = ' . $this->userId;
        $exceededTasksActualWeek = R::getRow($queryString)['COUNT(*)'];
        $taskCountActualWeek['exceededTasks'] = $exceededTasksActualWeek;

        return $taskCountActualWeek;

    }

    public function getBestDayThisWeek()
    {
        $mondays = $this->getMondays();
        $mondayThisWeak = $mondays['thisWeek'];
        $mondayNextWeak = $mondays['nextWeek'];

        $thisDay = $mondayThisWeak;
        $nextDay = 0;
        $day = 1;
        $taskCount = 0;
        $mostTasks = 1;

        while ($nextDay < $mondayNextWeak) {
            $nextDay = $thisDay + (604800 / 7);

            $queryString = 'SELECT COUNT(*)  FROM task JOIN tasklist ON task.tasklist_id = tasklist.id JOIN user ON tasklist.user_id = user.id WHERE task.status = 1 AND task.updated_at BETWEEN ' . $thisDay . ' AND ' . $nextDay . ' - 1 AND user.id = ' . $this->userId;
            $tasksPerDay = R::getRow($queryString)['COUNT(*)'];

            if ($tasksPerDay > $taskCount) {
                $taskCount = $tasksPerDay;
                $mostTasks = $day;
            }

            $thisDay = $nextDay;
            $day++;
        }
        return $mostTasks;
    }

    //additional functions
    public function getDueTaskCount()
    {

        $queryString = 'SELECT COUNT(*)  FROM task JOIN tasklist ON task.tasklist_id = tasklist.id JOIN user ON tasklist.user_id = user.id WHERE task.status = 1 AND user.id = ' . $this->userId;
        $taskCount = R::getRow($queryString);
        return $taskCount['COUNT(*)'];
    }

    public function getMondays()
    {
        $mondays = [];
        $mondayThisWeak = time() - ((time() - 345600) % 604800);
        $mondayNextWeak = $mondayThisWeak + 604800;
        $mondays['thisWeek'] = $mondayThisWeak;
        $mondays['nextWeek'] = $mondayNextWeak;
        return $mondays;
    }
}
