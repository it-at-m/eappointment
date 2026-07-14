<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsbackend\Availability\Api;

use BO\Slim\Render;
use BO\Mellon\Validator;
use BO\Zmsbackend\Availability\Service\Closure;
use BO\Zmsentities\Closure as ClosureEntity;
use DateTime;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use BO\Zmsbackend\Exception\BadRequest as BadRequestException;

/**
 * @SuppressWarnings(Coupling)
 */
class AvailabilityClosureToggle extends \BO\Zmsbackend\Api\BaseController
{
    /**
     * @SuppressWarnings(Param)
     * @return ResponseInterface
     */
    #[\Override]
    public function readResponse(
        RequestInterface $request,
        ResponseInterface $response,
        array $args
    ): ResponseInterface {
        (new \BO\Zmsbackend\Helper\User($request))->checkPermissions('availability');
        $input = Validator::input()->isJson()->assertValid()->getValue();
        if (! $input || count($input) === 0) {
            throw new BadRequestException();
        }

        $scopeId = $args['id'];
        $date = $args['date'];

        try {
            $closure = (new \BO\Zmsbackend\Availability\Service\Closure())->readByScopeIdAndDate($scopeId, new DateTime($date));
        } catch (\Exception $e) {
            $closure = new ClosureEntity();
        }

        if (empty($closure->getId())) {
            $closure = (new \BO\Zmsbackend\Availability\Service\Closure())->createOne($scopeId, new DateTime($date));
            $closure->existing = true;
        } else {
            (new \BO\Zmsbackend\Availability\Service\Closure())->deleteEntity($closure);
            $closure->existing = false;
        }

        $message = \BO\Zmsbackend\Api\Response\Message::create($request);
        $message->data = $closure;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
        return $response;
    }
}
