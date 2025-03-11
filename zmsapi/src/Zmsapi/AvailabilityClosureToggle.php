<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use BO\Slim\Render;
use BO\Mellon\Validator;
use BO\Zmsdb\Closure;
use BO\Zmsentities\Closure as ClosureEntity;
use DateTime;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use BO\Zmsapi\Exception\BadRequest as BadRequestException;

/**
 * @SuppressWarnings(Coupling)
 */
class AvailabilityClosureToggle extends BaseController
{
    /**
     * @SuppressWarnings(Param)
     * @return ResponseInterface
     */
    public function readResponse(
        RequestInterface $request,
        ResponseInterface $response,
        array $args
    ): ResponseInterface {
        (new Helper\User($request))->checkRights();
        $input = Validator::input()->isJson()->assertValid()->getValue();
        if (! $input || count($input) === 0) {
            throw new BadRequestException();
        }

        $scopeId = $args['id'];
        $date = $args['date'];
        $closure = null;

        try {
            $closure = (new Closure())->readByScopeIdAndDate($scopeId, new DateTime($date));
        } catch (\Exception $e) {
            $closure = new ClosureEntity();
        }

        if (empty($closure->getId())) {
            $closure = (new Closure())->createOne($scopeId, new DateTime($date));
            $closure->existing = true;
        } else {
            (new Closure())->deleteEntity($closure->getId());
            $closure->existing = false;
        }

        $message = Response\Message::create($request);
        $message->data = $closure;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
        return $response;
    }
}
