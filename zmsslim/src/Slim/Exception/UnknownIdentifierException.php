<?php
/**
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

declare(strict_types=1);

namespace BO\Slim\Exception;

use Psr\Container\NotFoundExceptionInterface;

class UnknownIdentifierException extends \Exception implements NotFoundExceptionInterface
{
}
