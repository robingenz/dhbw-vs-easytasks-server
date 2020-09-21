<?php

namespace App\Controllers;

use Slim\Container;
use Slim\Http\Request;
use Slim\Http\Response;

class AuthController extends BaseController
{
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function token(Request $request, Response $response, array $args)
    {
        $parsedBody = $request->getParsedBody();
        switch ($parsedBody['grant_type']) {
            case 'password':
                $validCredentials = $this->container['authService']->verifyUserCredentials($parsedBody['username'], $parsedBody['password']);
                if (!$validCredentials) {
                    return $this->responseWithError($response, 'Incorrect username or password.', 401);
                }
                $user = $this->container['userService']->getUserByUsername($parsedBody['username']);
                $accessToken = $this->container['authService']->issueAccessTokenForUser($user);
                $refreshToken = $this->container['authService']->issueRefreshTokenForUser($user);
                $data = [
                    'token_type' => 'bearer',
                    'access_token' => $accessToken,
                    'expired_in' => getenv('JWT_EXPIRY_TIME'),
                    'refresh_token' => $refreshToken,
                ];
                return $response->withJson($data, 201);
            case 'refresh_token':
                $validRefreshToken = $this->container['authService']->verifyRefreshToken($parsedBody['refresh_token']);
                if (!$validRefreshToken) {
                    return $this->responseWithError($response, 'Incorrect refresh token.', 401);
                }
                $session = $this->container['sessionService']->getSessionByRefreshToken($parsedBody['refresh_token']);
                $user = $this->container['userService']->getUserBySession($session);
                $accessToken = $this->container['authService']->issueAccessTokenForUser($user);
                $data = [
                    'token_type' => 'bearer',
                    'access_token' => $accessToken,
                    'expired_in' => getenv('JWT_EXPIRY_TIME'),
                ];
                return $response->withJson($data, 201);
            default:
                return $this->responseWithError($response, 'Invalid grant.', 400);
        }
    }

    public function register(Request $request, Response $response, array $args)
    {
        $parsedBody = $request->getParsedBody();
        $userByUsername = $this->container['userService']->getUserByUsername($parsedBody['username']);
        if (!empty($userByUsername)) {
            return $this->responseWithError($response, 'This username is already taken.', 409);
        }
        if (strlen($parsedBody['password']) < 6) {
            return $this->responseWithError($response, 'The password is too short.', 400);
        }
        $this->container['authService']->register($parsedBody['username'], $parsedBody['password']);
        $statusCode = 201;
        $data = [
            'code' => $statusCode,
            'message' => 'Your registration was successful.',
        ];
        return $response->withJson($data, $statusCode);
    }

    public function revoke(Request $request, Response $response, array $args)
    {
        $parsedBody = $request->getParsedBody();
        $user = $this->container['userService']->getUserByRequest($request);
        $this->container['authService']->revokeRefreshTokenForUser($user, $parsedBody['refresh_token']);
        $data = [
            'code' => 200,
            'message' => 'The token was successfully revoked.',
        ];
        return $response->withJson($data);
    }
}
