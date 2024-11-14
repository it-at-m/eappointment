<?php
/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use BO\Slim\Render;
use BO\Mellon\Validator;

use BO\Zmsentities\Availability as Entity;
use BO\Zmsentities\Collection\AvailabilityList as Collection;

use BO\Zmsdb\Availability as AvailabilityRepository;
use BO\Zmsdb\Connection\Select as DbConnection;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

use BO\Zmsapi\AvailabilitySlotsUpdate;
use BO\Zmsapi\Exception\BadRequest as BadRequestException;
use BO\Zmsapi\Exception\Availability\AvailabilityUpdateFailed;


/**
 * @SuppressWarnings(Coupling)
 */
class AvailabilityAdd extends BaseController
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
        if (!$input || count($input) === 0) {
            throw new BadRequestException();
        }
    
        $newCollection = new Collection();
        DbConnection::getWriteConnection();
    
        foreach ($input as $item) {
            $entity = new Entity($item);
            $entity->testValid();
            $newCollection->addEntity($entity);
        }
    
        $scopeData = $input[0]['scope'];
        $scope = new \BO\Zmsentities\Scope($scopeData);
        $scopeId = $scope->id;
    
        $startDate = new \DateTimeImmutable('now');
        $endDate = (new \DateTimeImmutable('now'))->modify('+1 month');
        $availabilityRepo = new AvailabilityRepository();
        $existingCollection = $availabilityRepo->readAvailabilityListByScope($scope, 1, $startDate, $endDate);
    
        $mergedCollection = new Collection();
        foreach ($existingCollection as $existingAvailability) {
            $mergedCollection->addEntity($existingAvailability);
        }
        foreach ($newCollection as $newAvailability) {
            $mergedCollection->addEntity($newAvailability);
        }

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

        $updatedCollection = new Collection();
        foreach ($newCollection as $entity) {
            $updatedEntity = $this->writeEntityUpdate($entity);
            AvailabilitySlotsUpdate::writeCalculatedSlots($updatedEntity, true);
            $updatedCollection->addEntity($updatedEntity);
        }
    
        $message = Response\Message::create($request);
        $message->data = $updatedCollection->getArrayCopy();
    
        $response = Render::withLastModified($response, time(), '0');
        return Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
    }
    
    

    protected function writeEntityUpdate($entity): Entity
    {
        $repository = new AvailabilityRepository();
        $updatedEntity = null;
        if ($entity->id) {
            $oldEntity = $repository->readEntity($entity->id);
            if ($oldEntity && $oldEntity->hasId()) {
                $this->writeSpontaneousEntity($oldEntity);
                $updatedEntity = $repository->updateEntity($entity->id, $entity, 2);
            }
        } else {
            $updatedEntity = $repository->writeEntity($entity, 2);
        }
        if (!$updatedEntity) {
            throw new AvailabilityUpdateFailed();
        }
        return $updatedEntity;
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
