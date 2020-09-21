<?php
namespace App\Services;

use App\Entities\SessionEntity as Session;
use App\Entities\UserEntity as User;
use App\Repositories\SessionRepository;
use App\Repositories\UserRepository;
use Firebase\JWT\JWT;

class AuthService
{
    private $userRepository;
    private $sessionRepository;

    public function __construct(UserRepository $userRepository, SessionRepository $sessionRepository)
    {
        $this->userRepository = $userRepository;
        $this->sessionRepository = $sessionRepository;
    }

    public function register($username, $password)
    {
        $user = User::create($username, $password);
        $this->userRepository->createUser($user);
    }

    public function verifyUserCredentials($username, $password)
    {
        try {
            $user = $this->userRepository->getUserByUsername($username);
            if (empty($user)) {
                return false;
            }
            return User::verifyPassword($password, $user->getPassword());
        } catch (\Exception $e) {
            return false;
        }
    }

    public function issueAccessTokenForUser(User $user)
    {
        $now = time();
        $expires_in = getenv('JWT_EXPIRY_TIME');
        $token = array(
            'iss' => getenv('APP_URL'),
            'sub' => $user->getId(),
            'exp' => $now + $expires_in,
            'nbf' => $now,
            'iat' => $now,
        );
        $jwt = JWT::encode($token, getenv('JWT_SECRET_KEY'), 'HS256');
        return $jwt;
    }

    public function verifyAccessToken($accessToken)
    {
        try {
            $decodedJwt = $this->decodeJwt($accessToken);
            if (!isset($decodedJwt->sub)) {
                return false;
            }
            if (!isset($decodedJwt->iss) || $decodedJwt->iss != getenv('APP_URL')) {
                return false;
            }
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function decodeJwt($jwt)
    {
        return JWT::decode($jwt, getenv('JWT_SECRET_KEY'), ['HS256']);
    }

    public function issueRefreshTokenForUser(User $user)
    {
        $refreshToken = bin2hex(openssl_random_pseudo_bytes(64));
        $session = Session::create($refreshToken);
        $session = $this->sessionRepository->startSessionForUser($session, $user);
        return $session->getRefreshToken();
    }

    public function revokeRefreshTokenForUser(User $user, $refreshToken)
    {
        $session = $this->sessionRepository->getSessionByRefreshToken($refreshToken);
        $this->sessionRepository->clearSessionForUser($session, $user);
    }

    public function verifyRefreshToken($refreshToken)
    {
        return $this->sessionRepository->verifyRefreshToken($refreshToken);
    }
}
