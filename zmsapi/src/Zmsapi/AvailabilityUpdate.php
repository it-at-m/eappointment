<?php
/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use BO\Slim\Render;
use BO\Mellon\Validator;

use BO\Zmsdb\Availability as AvailabilityRepository;
use BO\Zmsdb\Connection\Select as DbConnection;

use BO\Zmsentities\Availability as Entity;
use BO\Zmsentities\Collection\AvailabilityList as Collection;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

use BO\Zmsapi\AvailabilitySlotsUpdate;
use BO\Zmsapi\Exception\Availability\AvailabilityNotFound as NotFoundException;
use BO\Zmsapi\Exception\Availability\AvailabilityUpdateFailed;

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

        $availabilityRepo = new AvailabilityRepository();
        $availability = $availabilityRepo->readEntity($args['id'], $resolveReferences);
        if (! $availability->hasId()) {
            throw new NotFoundException();
        }

        $scopeData = $entity->scope;
        $scope = new \BO\Zmsentities\Scope($scopeData);

        $startDate = new \DateTimeImmutable('now');
        $endDate = (new \DateTimeImmutable('now'))->modify('+1 month');
        $existingCollection = $availabilityRepo->readAvailabilityListByScope($scope, 1, $startDate, $endDate);

        $mergedCollection = new Collection();
        foreach ($existingCollection as $existingAvailability) {
            if ($existingAvailability->id !== $entity->id) {
                $mergedCollection->addEntity($existingAvailability);
            }
        }
        $mergedCollection->addEntity($entity);

        $validation = $mergedCollection->validateInputs($startDate, $endDate);
        error_log(json_encode($validation));
        if (count($validation) > 0) {
            $validation = json_decode(json_encode($validation), true);
            throw new AvailabilityUpdateFailed();
        }  

        $conflicts = $mergedCollection->getConflicts($startDate, $endDate);

        if ($conflicts->count() > 0) {
            $conflictsArray = json_decode(json_encode($conflicts), true);
            throw new AvailabilityUpdateFailed();
        }

        DbConnection::getWriteConnection();
        $this->writeSpontaneousEntity($availability);
        $updatedEntity = $availabilityRepo->updateEntity($args['id'], $entity, $resolveReferences);
        AvailabilitySlotsUpdate::writeCalculatedSlots($updatedEntity, true);

        $message = Response\Message::create($request);
        $message->data = $updatedEntity;

        $response = Render::withLastModified($response, time(), '0');
        return Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
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
