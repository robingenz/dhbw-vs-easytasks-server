<?php
namespace App\Repositories;

use App\Entities\SessionEntity as Session;
use App\Entities\UserEntity as User;
use RedBeanPHP\R;

class SessionRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct('session');
    }

    public function startSessionForUser(Session $session, User $user)
    {
        $sessionBean = R::dispense($this->getTable());
        $sessionBean->refreshToken = $session->getRefreshToken();
        $sessionBean->createdAt = time();
        $sessionBean->updatedAt = time();
        $userBean = R::load('user', $user->getId());
        $userBean->ownSessionList[] = $sessionBean;
        $id = R::store($userBean);
        return $this->getSessionByRefreshToken($session->getRefreshToken());
    }

    public function clearSessionForUser(Session $session, User $user)
    {
        R::hunt($this->getTable(), ' id = ? AND user_id = ? ', [$session->getId(), $user->getId()]);
    }

    public function verifyRefreshToken($refreshToken)
    {
        $sessionElement = R::findOne($this->getTable(), ' refresh_token = ? ', [$refreshToken]);
        if (!empty($sessionElement)) {
            return true;
        }
        return false;
    }

    public function getSessionByRefreshToken($refreshToken)
    {
        $sessionElement = R::findOne($this->getTable(), ' refresh_token = ? ', [$refreshToken]);
        if (empty($sessionElement)) {
            return null;
        }
        return new Session($sessionElement['id'], $sessionElement['refresh_token'], $sessionElement['user_id'], $sessionElement['updated_at'], $sessionElement['created_at']);
    }

}
