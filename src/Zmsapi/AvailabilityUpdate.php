<?php
/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use BO\Slim\Render;
use BO\Mellon\Validator;

use BO\Zmsdb\Availability as AvailabilityRepository;
use BO\Zmsdb\Slot as SlotRepository;
use BO\Zmsdb\Helper\CalculateSlots as CalculateSlotsHelper;
use BO\Zmsdb\Connection\Select as DbConnection;

use BO\Zmsentities\Availability as Entity;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

use BO\Zmsapi\Exception\Availability\AvailabilityNotFound as NotfoundException;

/**
 * @SuppressWarnings(Coupling)
 */
class AvailabilityUpdate extends BaseController
{
    /**
     * @return ResponseInterface
     */
    public function readResponse(
        RequestInterface $request,
        ResponseInterface $response,
        array $args
    ): ResponseInterface {
        (new Helper\User($request))->checkRights();
        $resolveReferences = Validator::param('resolveReferences')->isNumber()->setDefault(2)->getValue();
        $input = Validator::input()->isJson()->assertValid()->getValue();
        $entity = new Entity($input);
        $entity->testValid();

        $availability = (new AvailabilityRepository())->readEntity($args['id'], $resolveReferences);
        if (! $availability->hasId()) {
            throw new NotfoundException();
        }

        //Workaround for openinghours migration, remove after AP13
        $this->writeSpontaneousEntity($availability);

        $updatedEntity = (new AvailabilityRepository())->updateEntity($args['id'], $entity, $resolveReferences);
        $this->writeCalculatedSlots($updatedEntity);

        $message = Response\Message::create($request);
        $message->data = $updatedEntity;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
        return $response;
    }

    protected function writeCalculatedSlots($updatedEntity)
    {
        (new SlotRepository)->writeByAvailability($updatedEntity, \App::$now);
        (new CalculateSlotsHelper(\App::DEBUG))
            ->writePostProcessingByScope($updatedEntity->scope, \App::$now);
        DbConnection::writeCommit();
    }

    protected function writeSpontaneousEntity(Entity $entity): void
    {
        $doubleTypesEntity = (new AvailabilityRepository())->readEntityDoubleTypes($entity->id);
        if ($doubleTypesEntity) {
            $doubleTypesEntity->workstationCount['intern'] = 0;
            $doubleTypesEntity->workstationCount['callcenter'] = 0;
            $doubleTypesEntity->workstationCount['public'] = 0;
            $doubleTypesEntity['description'] = '';
            $doubleTypesEntity['type'] = 'openinghours';
            (new AvailabilityRepository())->writeEntity($doubleTypesEntity);
        }
    }
}
