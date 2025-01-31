<?php
/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co.
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
use BO\Zmsapi\Exception\BadRequest as BadRequestException;
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

        if (!$input || count($input) === 0) {
            throw new BadRequestException();
        }

        DbConnection::getWriteConnection();

        if (!isset($input['availabilityList']) || !is_array($input['availabilityList'])) {
            throw new BadRequestException('Missing or invalid availabilityList.');
        } else if(!isset($input['availabilityList'][0]['scope'])){
            throw new BadRequestException('Missing or invalid scope.');
        } else if (!isset($input['selectedDate'])) {
            throw new BadRequestException("'selectedDate' is required.");
        }    
        $availabilityRepo = new AvailabilityRepository();
        $newCollection = new Collection();
        foreach ($input['availabilityList'] as $item) {
            $entity = new Entity($item);
            $entity->testValid();
            if (isset($entity->id)) {
                $existingEntity = $availabilityRepo->readEntity($entity->id, $resolveReferences);
                if (!$existingEntity || !$existingEntity->hasId()) {
                    throw new NotFoundException("Availability with ID {$entity->id} not found.");
                }
            }

            $newCollection->addEntity($entity);
        }

        $scopeData = $input['availabilityList'][0]['scope'];
        $scope = new \BO\Zmsentities\Scope($scopeData);
        $selectedDate = \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $input['selectedDate'] . ' 00:00:00');

        $conflicts = new \BO\Zmsentities\Collection\ProcessList();
        $newVsNewConflicts = $newCollection->hasNewVsNewConflicts($selectedDate);
        $conflicts->addList($newVsNewConflicts);

        $existingCollection = $availabilityRepo->readAvailabilityListByScope($scope, 1);

        $mergedCollection = new Collection();
        foreach ($existingCollection as $existingAvailability) {
            $mergedCollection->addEntity($existingAvailability);
        }

        $validations = [];
        foreach ($newCollection as $newAvailability) {
            $startDate = (new \DateTimeImmutable())->setTimestamp($newAvailability->startDate)->format('Y-m-d');
            $endDate = (new \DateTimeImmutable())->setTimestamp($newAvailability->endDate)->format('Y-m-d');
            $startDateTime = new \DateTimeImmutable("{$startDate} {$newAvailability->startTime}");
            $endDateTime = new \DateTimeImmutable("{$endDate} {$newAvailability->endTime}");
            
            $validations = $mergedCollection->validateInputs(
                $startDateTime,
                $endDateTime,
                $selectedDate,
                $newAvailability->kind ?? 'default',
                $newAvailability->bookable['startInDays'],
                $newAvailability->bookable['endInDays']
            );

            $mergedCollection->addEntity($newAvailability);
        }

        if (count($validations) > 0) {
            throw new AvailabilityUpdateFailed();
        }        
    
        $originId = null;
        foreach ($mergedCollection as $availability) {
            if (isset($availability->kind) && $availability->kind === 'origin' && isset($availability->id)) {
                $originId = $availability->id;
                break;
            }
        }
        
        $mergedCollectionWithoutExclusions = new Collection();
        foreach ($mergedCollection as $availability) {
            if ((!isset($availability->kind) || $availability->kind !== 'exclusion') && 
                (!isset($availability->id) || $availability->id !== $originId)) {
                $mergedCollectionWithoutExclusions->addEntity($availability);
            }
        }
        
        [$earliestStartDateTime, $latestEndDateTime] = $mergedCollectionWithoutExclusions->getDateTimeRangeFromList(
            \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $input['selectedDate'] . ' 00:00:00')
        );
        $existingConflicts = $mergedCollectionWithoutExclusions->checkAllVsExistingConflicts($earliestStartDateTime, $latestEndDateTime);
        $conflicts->addList($existingConflicts);
        if ($conflicts->count() > 0) {
            throw new AvailabilityUpdateFailed();
        }

        $updatedCollection = new Collection();
        foreach ($newCollection as $entity) {
            $updatedEntity = $this->writeEntityUpdate($entity, $resolveReferences);
            AvailabilitySlotsUpdate::writeCalculatedSlots($updatedEntity, true);
            $updatedCollection->addEntity($updatedEntity);
        }

        $message = Response\Message::create($request);
        $message->data = $updatedCollection->getArrayCopy();

        $response = Render::withLastModified($response, time(), '0');
        return Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
    }

    protected function writeEntityUpdate($entity, $resolveReferences): Entity
    {
        $repository = new AvailabilityRepository();
        $updatedEntity = null;

        if ($entity->id) {
            $oldEntity = $repository->readEntity($entity->id);
            if ($oldEntity !== null && $oldEntity->hasId()) {
                $this->writeSpontaneousEntity($oldEntity);
                $updatedEntity = $repository->updateEntity($entity->id, $entity, $resolveReferences);
            } else {
                throw new AvailabilityUpdateFailed("Entity with ID {$entity->id} not found.");
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
