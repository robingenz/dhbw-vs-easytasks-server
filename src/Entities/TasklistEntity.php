<?php
namespace App\Entities;

class TasklistEntity extends BaseEntity implements \JsonSerializable
{

    private $id;
    private $title;
    private $userId;

    public function __construct($id, $title, $createdAt, $updatedAt, $userId)
    {
        parent::__construct($updatedAt, $createdAt);
        $this->id = $id;
        $this->title = $title;
        $this->userId = $userId;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getUserId()
    {
        return $this->userId;
    }
    public function jsonSerialize()
    {
        $vars = get_object_vars($this);

        return $vars;
    }

    public static function create($title, $userId)
    {
        return new self(0, $title, time(), time(), $userId);
    }
}
