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
use BO\Zmsentities\Availability as Entity;
use BO\Zmsentities\Collection\AvailabilityList as Collection;
use BO\Zmsdb\Availability as AvailabilityRepository;
use BO\Zmsdb\Slot as SlotRepository;
use BO\Zmsdb\Config as ConfigRepository;
use BO\Zmsdb\Helper\CalculateSlots as CalculateSlotsHelper;
use BO\Zmsdb\Connection\Select as DbConnection;
use BO\Zmsentities\Collection\ClosureList;
use DateTime;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use BO\Zmsapi\Exception\BadRequest as BadRequestException;
use BO\Zmsapi\Exception\Availability\AvailabilityNotFound as NotfoundException;

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
        $data = [];
        $scopeId = $args['id'];
        $date = $args['date'];
        $closure = null;

        try {
            $closure = (new Closure())->readByScopeIdAndDate($scopeId, new DateTime($date));
        } catch (\Exception $e) {
        }

        if (empty($closure->getId())) {
            $closure = (new Closure())->createOne($scopeId, new DateTime($date));
            $closure->existing = true;
            $data['message'] = 'Closure has been created';
        } else {
            (new Closure())->deleteEntity($closure->getId());
            $closure->existing = false;
            $data['message'] = 'Closure has been deleted';
        }

        $message = Response\Message::create($request);
        $message->data = $closure;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
        return $response;
    }
}
