<?php
/**
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

declare(strict_types=1);

namespace BO\Slim;

use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use BO\Slim\Exception\UnknownIdentifierException;

/**
 * PSR compatible container implementation, compatible with the ArrayAccess usage in zms
 */
class Container extends \ArrayObject implements ContainerInterface
{
    /**
     * @param string $identifier
     * @return mixed
     *
     * @throws NotFoundExceptionInterface
     */
    public function get(string $identifier)
    {
        if (!$this->has($identifier)) {
            throw new UnknownIdentifierException('The container has no value identified by ' . $identifier);
        }

        return $this->offsetGet($identifier);
    }

    /**
     * @param string $identifier The value identifier
     *
     * @return bool
     */
    public function has(string $identifier): bool
    {
        return $this->offsetExists($identifier);
    }

    /**
     * @param string $identifier
     * @param mixed $value
     */
    public function set(string $identifier, $value): void
    {
        $this->offsetSet($identifier, $value);
    }
}
