<?php

namespace BO\Zmsclient\Psr7;

use Slim\Psr7\Factory\UriFactory;

/**
 * Layer to change PSR7 implementation if necessary
 */
class Uri extends \Slim\Psr7\Uri implements \Psr\Http\Message\UriInterface
{
    public function __construct(
        string $schemeOrUri = '',
        ?string $host = null,
        ?int $port = null,
        string $path = '/',
        string $query = '',
        string $fragment = '',
        string $user = '',
        string $password = ''
    ) {
        if ($host !== null) {
            parent::__construct($schemeOrUri, $host, $port, $path, $query, $fragment, $user, $password);
            return;
        }

        $temp = (new UriFactory())->createUri($schemeOrUri);
        $userInfo = $temp->getUserInfo();
        $parsedUser = '';
        $parsedPassword = '';
        if ($userInfo !== '') {
            $parts = explode(':', $userInfo, 2);
            $parsedUser = $parts[0];
            $parsedPassword = $parts[1] ?? '';
        }
        parent::__construct(
            $temp->getScheme(),
            $temp->getHost(),
            $temp->getPort(),
            $temp->getPath(),
            $temp->getQuery(),
            $temp->getFragment(),
            $parsedUser,
            $parsedPassword
        );
    }
}
