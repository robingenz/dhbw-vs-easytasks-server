<?php
namespace App\Repositories;

use App\Entities\UserEntity as User;
use RedBeanPHP\R;

class UserRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct('user');
    }

    public function createUser(User $user)
    {
        $userBean = R::dispense($this->getTable());
        $userBean->username = $user->getUsername();
        $userBean->password = $user->getPassword();
        $userBean->createdAt = time();
        $userBean->updatedAt = time();
        $userId = R::store($userBean);
        return $this->getUserById($userId);
    }

    public function getUserById($id)
    {
        $userElement = R::findOne($this->getTable(), ' id = ? ', [$id]);
        if (empty($userElement)) {
            return null;
        }
        return new User($userElement['id'], $userElement['username'], $userElement['password'], $userElement['updated_at'], $userElement['created_at']);
    }

    public function getUserByUsername($username)
    {
        $userElement = R::findOne($this->getTable(), ' username = ? ', [$username]);
        if (empty($userElement)) {
            return null;
        }
        return new User($userElement['id'], $userElement['username'], $userElement['password'], $userElement['updated_at'], $userElement['created_at']);
    }

}
