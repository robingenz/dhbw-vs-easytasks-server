<?php
namespace App\Services;

use App\Entities\UserEntity as User;
use App\Repositories\ReportRepository;

class ReportService
{

    private $reportRepository;

    public function __construct(ReportRepository $reportRepository)
    {
        $this->reportRepository = $reportRepository;

    }

    public function getEvaluationObject(User $user)
    {
        //Weil jede Methode die User-Id benötigt: Evtl. in Entity übertragbar
        $this->reportRepository->setUserId($user->getid());
        $evaluationObject['openTasks'] = $this->getOpenTaskCount();
        $evaluationObject['dueTasksTwoDays'] = $this->dueTaskCountTwoDays();
        $evaluationObject['percentTasksExceeded'] = $this->getPercentTaskExceeded();
        $evaluationObject['averageTaskProcessingTime'] = $this->getAverageProcessingTime();
        $evaluationObject['tasksPerPriority'] = $this->getTaskCountPerPriority();
        $evaluationObject['taskCountPerTasklist'] = $this->getOpenTaskCountPerTasklist();
        $evaluationObject['taskCountActualWeek'] = $this->getTaskCountActualWeek();
        $evaluationObject['bestDay'] = $this->getBestDayThisWeek();
        return $evaluationObject;

    }

    public function getOpenTaskCount()
    {
        return $this->reportRepository->getOpenTaskCount();
    }

    public function dueTaskCountTwoDays()
    {
        return $this->reportRepository->dueTaskCountTwoDays();
    }

    public function getPercentTaskExceeded()
    {
        return round(($this->reportRepository->getPercentTaskExceeded() * 100), 1);
    }

    public function getAverageProcessingTime()
    {
        return round($this->reportRepository->getAverageProcessingTime()); // / 60 / 60 / 24); // , 2);
    }

    public function getTaskCountPerPriority()
    {
        $taskCountPerPriority = [];

        $priorities = [1, 2, 3, 4, 5];
        foreach ($priorities as $priority) {
            $taskCount = $this->reportRepository->getTaskCountPerPriority($priority);
            $taskCountPerPriority[$priority] = $taskCount;
        }
        return $taskCountPerPriority;

    }

    public function getOpenTaskCountPerTasklist()
    {
        return $this->reportRepository->getOpenTaskCountPerTasklist();
    }

    public function getTaskCountActualWeek()
    {
        return $this->reportRepository->getTaskCountActualWeek();
    }

    public function getBestDayThisWeek()
    {
        return $this->reportRepository->getBestDayThisWeek();
    }
}
