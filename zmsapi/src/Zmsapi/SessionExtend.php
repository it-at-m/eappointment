<?php

namespace BO\Zmsapi;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class SessionExtend
{
    public function __invoke(RequestInterface $request, ResponseInterface $response, array $args)
    {
        // Get the X-AuthKey from header or cookie
        $authKey = $request->getHeaderLine('X-AuthKey');
        if (!$authKey) {
            $cookies = $request->getCookieParams();
            $authKey = isset($cookies['X-AuthKey']) ? $cookies['X-AuthKey'] : null;
        }
        if (!$authKey) {
            return $response->withStatus(401);
        }

        // Find user by auth key
        $useraccountRepo = new \BO\Zmsdb\Useraccount();
        $user = $useraccountRepo->readEntityByAuthKey($authKey);
        if (!$user || !$user->hasId()) {
            return $response->withStatus(401);
        }

        // Update SessionExpiry
        $newExpiry = new \DateTime();
        $newExpiry->setTimestamp(time() + \App::SESSION_DURATION);
        $useraccountRepo->updateSessionExpiry($authKey, $newExpiry);

        $response->getBody()->write(json_encode(['status' => 'ok']));
        return $response->withHeader('Content-Type', 'application/json');
    }
}
