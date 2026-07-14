<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsbackend\Availability\Api;

use BO\Slim\Render;
use BO\Mellon\Validator;
use BO\Zmsentities\Availability as Entity;
use BO\Zmsentities\Collection\AvailabilityList as Collection;
use BO\Zmsbackend\Availability\Service\Availability as AvailabilityRepository;
use BO\Zmsbackend\Slot\Service\Slot as SlotRepository;
use BO\Zmsbackend\Config\Service\Config as ConfigRepository;
use BO\Zmsbackend\Helper\CalculateSlots as CalculateSlotsHelper;
use BO\Zmsbackend\Connection\Select as DbConnection;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use BO\Zmsbackend\Exception\BadRequest as BadRequestException;
use BO\Zmsbackend\Availability\Exception\AvailabilityNotFound as NotfoundException;

/**
 * @SuppressWarnings(Coupling)
 */
class AvailabilitySlotsUpdate extends \BO\Zmsbackend\Api\BaseController
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
        $collection = new Collection();
        DbConnection::getWriteConnection();
        foreach ($input as $item) {
            $entity = new Entity($item);
            $entity->testValid();
            $availability = (new AvailabilityRepository())->readEntity($entity->getId(), 2);
            if (! $availability->hasId()) {
                throw new NotfoundException();
            }
            static::writeCalculatedSlots($availability);
            $collection->addEntity($availability);
        }

        $message = \BO\Zmsbackend\Api\Response\Message::create($request);
        $message->data = $collection->getArrayCopy();

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
        return $response;
    }

    public static function writeCalculatedSlots(Entity $availability, bool $checkConfigOnSave = false)
    {
        $config = (new ConfigRepository())->readEntity();
        if (
            $checkConfigOnSave && in_array(
                getenv('ZMS_ENV'),
                explode(',', $config->getPreference('availability', 'calculateSlotsOnSave'))
            )
        ) {
            (new SlotRepository())->writeByAvailability($availability, \App::$now);
            (new CalculateSlotsHelper(\App::DEBUG))
                ->writePostProcessingByScope($availability->scope, \App::$now);
        }

        if (
            ! $checkConfigOnSave && in_array(
                getenv('ZMS_ENV'),
                explode(',', $config->getPreference('availability', 'calculateSlotsOnDemand'))
            )
        ) {
            (new SlotRepository())->writeByAvailability($availability, \App::$now);
            (new CalculateSlotsHelper(\App::DEBUG))
                ->writePostProcessingByScope($availability->scope, \App::$now);
        }
    }
}
