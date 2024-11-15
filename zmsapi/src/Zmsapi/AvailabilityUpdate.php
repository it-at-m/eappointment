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
        
        $newCollection = new Collection();
        foreach ($input['availabilityList'] as $item) {
            $entity = new Entity($item);
            $entity->testValid();
            $newCollection->addEntity($entity);
        }

        $scopeData = $input['availabilityList']['scope'];
        $scope = new \BO\Zmsentities\Scope($scopeData);

        $availabilityRepo = new AvailabilityRepository();
        $existingCollection = $availabilityRepo->readAvailabilityListByScope($scope, 1);

        $mergedCollection = new Collection();
        foreach ($existingCollection as $existingAvailability) {
            $mergedCollection->addEntity($existingAvailability);
        }

        foreach ($newCollection as $newAvailability) {
            $startDate = (new \DateTimeImmutable())->setTimestamp($newAvailability->startDate)->format('Y-m-d');
            $endDate = (new \DateTimeImmutable())->setTimestamp($newAvailability->endDate)->format('Y-m-d');
            
            $selectedDate = \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $input['selectedDate'] . ' 00:00:00');
            
            $startDateTime = new \DateTimeImmutable("{$startDate} {$newAvailability->startTime}");
            $endDateTime = new \DateTimeImmutable("{$endDate} {$newAvailability->endTime}");
            
            $validation = $mergedCollection->validateInputs(
                $startDateTime,
                $endDateTime,
                $selectedDate,
                $newAvailability->kind ?? 'default'
            );

            $mergedCollection->addEntity($newAvailability);
        }

        if (count($validation) > 0) {
            $validation = json_decode(json_encode($validation), true);
            throw new AvailabilityUpdateFailed();
        }        
    
        [$earliestStartDateTime, $latestEndDateTime] = $this->getDateTimeRangeFromCollection($mergedCollection);
        $conflicts = $mergedCollection->getConflicts($earliestStartDateTime, $latestEndDateTime);
        if ($conflicts->count() > 0) {
            error_log(json_encode($conflicts));
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

    /**
     * Get the earliest startDateTime and latest endDateTime from a Collection
     *
     * @param Collection $collection
     * @return array
     */
    private function getDateTimeRangeFromCollection(Collection $collection): array
    {
        $earliestStartDateTime = null;
        $latestEndDateTime = null;

        foreach ($collection as $availability) {
            // Convert Unix timestamp to a date string before concatenating with the time
            $startDate = (new \DateTimeImmutable())->setTimestamp($availability->startDate)->format('Y-m-d');
            $endDate = (new \DateTimeImmutable())->setTimestamp($availability->endDate)->format('Y-m-d');
            
            $startDateTime = new \DateTimeImmutable("{$startDate} {$availability->startTime}");
            $endDateTime = new \DateTimeImmutable("{$endDate} {$availability->endTime}");

            if (is_null($earliestStartDateTime) || $startDateTime < $earliestStartDateTime) {
                $earliestStartDateTime = $startDateTime;
            }
            if (is_null($latestEndDateTime) || $endDateTime > $latestEndDateTime) {
                $latestEndDateTime = $endDateTime;
            }
        }

        return [$earliestStartDateTime, $latestEndDateTime];
    }

    protected function writeEntityUpdate($entity, $resolveReferences): Entity
    {
        $repository = new AvailabilityRepository();
        $updatedEntity = null;

        if ($entity->id) {
            $oldEntity = $repository->readEntity($entity->id);
            if ($oldEntity && $oldEntity->hasId()) {
                $this->writeSpontaneousEntity($oldEntity);
                $updatedEntity = $repository->updateEntity($entity->id, $entity, $resolveReferences);
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
