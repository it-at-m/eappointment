<?php

namespace BO\Zmsclient\Psr7;

/**
 * Layer to change PSR7 implementation if necessary
 */
class Uri extends \Slim\Http\Uri implements \Psr\Http\Message\UriInterface
{
    public function __construct(
        $schemeOrUri = '',
        $host = null,
        $port = null,
        $path = '/',
        $query = '',
        $fragment = '',
        $user = '',
        $password = ''
    ) {
        if ($host !== null) {
            parent::__construct($schemeOrUri, $host, $port, $path, $query, $fragment, $user, $password);
        } else {
            $temp = $this->createFromString($schemeOrUri);
            $this->scheme = $temp->scheme;
            $this->host = $temp->host;
            $this->port = $temp->port;
            $this->path = $temp->path;
            $this->query = $temp->query;
            $this->fragment = $temp->fragment;
            $this->user = $temp->user;
            $this->password = $temp->password;
        }
    }
}
