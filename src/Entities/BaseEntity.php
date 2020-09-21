<?php
namespace App\Entities;

abstract class BaseEntity
{
    protected $updatedAt;
    protected $createdAt;

    public function __construct($updatedAt, $createdAt)
    {
        $this->updatedAt = $updatedAt;
        $this->createdAt = $createdAt;
    }

    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;
    }

    public function getCreatedAt()
    {
        return $this->createdAt;
    }

}
