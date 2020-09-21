<?php
namespace App\Entities;

class UserEntity extends BaseEntity
{
    private $id;
    private $username;
    private $password;

    public function __construct($id, $username, $password, $updatedAt, $createdAt)
    {
        parent::__construct($updatedAt, $createdAt);
        $this->id = $id;
        $this->username = $username;
        $this->password = $password;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public static function hashPassword($password)
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    public static function verifyPassword($password, $hash)
    {
        return password_verify($password, $hash);
    }

    public static function create($username, $password)
    {
        if (strlen($password) < 6) {
            throw new \Exception('The password is too short.');
        }
        $hash = self::hashPassword($password);
        return new self(0, $username, $hash, time(), time());
    }

}
