<?php
namespace App\Services;

use App\Entities\SessionEntity as Session;
use App\Repositories\UserRepository;
use Slim\Http\Request;

class UserService
{
    private $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function getUserByUsername($username)
    {
        return $this->userRepository->getUserByUsername($username);
    }

    public function getUserBySession(Session $session)
    {
        return $this->userRepository->getUserById($session->getUserId());
    }

    public function getUserById($id)
    {
        return $this->userRepository->getUserById($id);
    }

    public function getUserByRequest(Request $request)
    {
        $parsedBody = $request->getParsedBody();
        $userId = $request->getAttribute('jwt')->sub;
        return $this->getUserById($userId);
    }

    public function confirmUserHasId($userId, User $user)
    {
        return ($userId == $user->getId());
    }
}
