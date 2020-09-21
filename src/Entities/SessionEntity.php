<?php
namespace App\Entities;

class SessionEntity extends BaseEntity
{
    private $id;
    private $refresh_token;
    private $user_id;

    public function __construct($id, $refresh_token, $user_id, $updatedAt, $createdAt)
    {
        parent::__construct($updatedAt, $createdAt);
        $this->id = $id;
        $this->refresh_token = $refresh_token;
        $this->user_id = $user_id;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getRefreshToken()
    {
        return $this->refresh_token;
    }

    public function getUserId()
    {
        return $this->user_id;
    }

    public static function create($refreshToken)
    {
        return new self(0, $refreshToken, 0, time(), time());
    }

}
