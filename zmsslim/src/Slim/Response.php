<?php
/**
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

declare(strict_types=1);

namespace BO\Slim;

use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\UriInterface;

class Response extends \Slim\Psr7\Response
{
    /**
     * This method prepares the response object to return an HTTP Redirect
     * response to the client.
     *
     * @param  string|UriInterface $url    The redirect destination.
     * @param  int|null            $status The redirect HTTP status code.
     *
     * @return static
     */
    public function withRedirect($url, int $status = null): Response
    {
        /** @var Response $redirectResponse */
        $redirectResponse = $this->withHeader('Location', (string) $url);

        if ($status !== null) {
            return $redirectResponse->withStatus($status);
        }

        if ($this->getStatusCode() === StatusCodeInterface::STATUS_OK) {
            return $redirectResponse->withStatus(StatusCodeInterface::STATUS_FOUND);
        }

        // return with a former defined status code
        return $redirectResponse;
    }
}
