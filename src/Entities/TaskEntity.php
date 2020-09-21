<?php

namespace App\Entities;

class TaskEntity extends BaseEntity implements \JsonSerializable
{

    private $id;
    private $title;
    private $description;
    private $priority;
    private $deadline;
    private $status;
    private $tasklistId;

    public function __construct($id, $title, $description, $priority, $deadline, $status, $createdAt, $updatedAt, $tasklistId)
    {
        parent::__construct($updatedAt, $createdAt);
        $this->id = $id;
        $this->title = $title;
        $this->description = $description;
        $this->priority = $priority;
        $this->deadline = $deadline;
        $this->status = $status;
        $this->tasklistId = $tasklistId;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getTitle()
    {
        return $this->title;
    }

    // public function setTitle($title)
    // {
    //     $this->title = $title;
    // }

    public function getDescription()
    {
        return $this->description;
    }

    // public function setDescription($description)
    // {
    //     $this->$description = $description;
    // }

    public function getPriority()
    {
        return $this->priority;
    }
    public function getDeadline()
    {
        return $this->deadline;
    }
    public function getStatus()
    {
        return $this->status;
    }

    // public function setStatus($status)
    // {
    //     $this->$status = $status;
    // }

    public function getTasklistId()
    {
        return $this->tasklistId;
    }

    public function jsonSerialize()
    {
        $vars = get_object_vars($this);

        return $vars;
    }

    public static function create($title, $description, $prioriy, $deadline, $status, $tasklistId)
    {
        return new self(0, $title, $description, $prioriy, $deadline, $status, time(), time(), $tasklistId);
    }

}
