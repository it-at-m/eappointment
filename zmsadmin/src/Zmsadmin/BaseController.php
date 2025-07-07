<?php

/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * @SuppressWarnings(NumberOfChildren)
 *
 */
abstract class BaseController extends \BO\Slim\Controller
{
    public function __invoke(RequestInterface $request, ResponseInterface $response, array $args)
    {
        $request = $this->initRequest($request);
        $noCacheResponse = \BO\Slim\Render::withLastModified($response, time(), '0');

        // Extend session timeout for authenticated users
        $authKey = \BO\Zmsclient\Auth::getKey();
        if ($authKey) {
            \BO\Zmsclient\Auth::setKey($authKey, time() + \App::SESSION_DURATION);
            // Call zmsapi to extend session expiry in the database
            if (isset(\App::$http)) {
                try {
                    \App::$http->readPostResult('/session/extend', []);
                } catch (\Exception $e) {
                    // Optionally log or ignore
                }
            }
            if (class_exists('App') && isset(\App::$log)) {
                $sessionHash = hash('sha256', $authKey);
                \App::$log->info('Session extended (sliding timeout)', [
                    'event' => 'auth_session_extended',
                    'timestamp' => date('c'),
                    'hashed_session_token' => $sessionHash
                ]);
            }
        }

        return $this->readResponse($request, $noCacheResponse, $args);
    }

    /**
     * @codeCoverageIgnore
     *
     */
    public function readResponse(RequestInterface $request, ResponseInterface $response, array $args)
    {
        return parent::__invoke($request, $response, $args);
    }

    public function getSchemaConstraintList($schema)
    {
        $list = [];
        $locale = \App::$language->getLocale();
        foreach ($schema->properties as $key => $property) {
            if (isset($property['x-locale'])) {
                $constraints = $property['x-locale'][$locale];
                if ($constraints) {
                    $list[$key]['description'] = $constraints['messages'];
                }
            }
        }
        return $list;
    }
}
