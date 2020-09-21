<?php
namespace App\Services;

use App\Repositories\SessionRepository;

class SessionService
{
    private $sessionRepository;

    public function __construct(SessionRepository $sessionRepository)
    {
        $this->sessionRepository = $sessionRepository;
    }

    public function getSessionByRefreshToken($refreshToken)
    {
        return $this->sessionRepository->getSessionByRefreshToken($refreshToken);
    }
}
