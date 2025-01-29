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
use BO\Zmsapi\Exception\Availability\AvailabilityAddFailed;

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
        $resolveReferences = Validator::param('resolveReferences')->isNumber()->setDefault(2)->getValue();
        $input = Validator::input()->isJson()->assertValid()->getValue();
        if (!$input || count($input) === 0) {
            throw new BadRequestException();
        }

        DbConnection::getWriteConnection();

        if (!isset($input['availabilityList']) || !is_array($input['availabilityList'])) {
            throw new BadRequestException('Missing or invalid availabilityList.');
        } else if (!isset($input['availabilityList'][0]['scope'])) {
            throw new BadRequestException('Missing or invalid scope.');
        } else if (!isset($input['selectedDate'])) {
            throw new BadRequestException("'selectedDate' is required.");
        }

        $newCollection = new Collection();
        foreach ($input['availabilityList'] as $item) {
            $entity = new Entity($item);
            $entity->testValid();
            $newCollection->addEntity($entity);
        }

        $scopeData = $input['availabilityList'][0]['scope'];
        $scope = new \BO\Zmsentities\Scope($scopeData);

        // First check overlaps within new availabilities being added
        $selectedDate = \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $input['selectedDate'] . ' 00:00:00');
        $this->checkNewVsNewConflicts($newCollection, $selectedDate);

        $availabilityRepo = new AvailabilityRepository();
        $existingCollection = $availabilityRepo->readAvailabilityListByScope($scope, 1);

        $mergedCollection = new Collection();
        foreach ($existingCollection as $existingAvailability) {
            $mergedCollection->addEntity($existingAvailability);
        }

        $validations = [];
        foreach ($newCollection as $newAvailability) {
            $startDate = (new \DateTimeImmutable())->setTimestamp($newAvailability->startDate);
            $endDate = (new \DateTimeImmutable())->setTimestamp($newAvailability->endDate);
            $startDateTime = new \DateTimeImmutable("{$startDate->format('Y-m-d')} {$newAvailability->startTime}");
            $endDateTime = new \DateTimeImmutable("{$endDate->format('Y-m-d')} {$newAvailability->endTime}");

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
            throw new AvailabilityAddFailed();
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
            if (
                (!isset($availability->kind) || $availability->kind !== 'exclusion') &&
                (!isset($availability->id) || $availability->id !== $originId)
            ) {
                $mergedCollectionWithoutExclusions->addEntity($availability);
            }
        }

        [$earliestStartDateTime, $latestEndDateTime] = $mergedCollectionWithoutExclusions->getDateTimeRangeFromList(
            \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $input['selectedDate'] . ' 00:00:00')
        );
        $conflicts = $mergedCollectionWithoutExclusions->checkAllVsExistingConflicts($earliestStartDateTime, $latestEndDateTime);
        if ($conflicts->count() > 0) {
            throw new AvailabilityAddFailed();
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
                throw new AvailabilityAddFailed("Entity with ID {$entity->id} not found.");
            }
        } else {
            $updatedEntity = $repository->writeEntity($entity, 2);
        }
        if (!$updatedEntity) {
            throw new AvailabilityAddFailed();
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

    protected function checkNewVsNewConflicts(Collection $collection, \DateTimeImmutable $selectedDate): void
    {
        foreach ($collection as $availability1) {
            foreach ($collection as $availability2) {
                $scope1Id = is_array($availability1->scope) ? ($availability1->scope['id'] ?? null) : ($availability1->scope->id ?? null);
                $scope2Id = is_array($availability2->scope) ? ($availability2->scope['id'] ?? null) : ($availability2->scope->id ?? null);
                
                if ($availability1 !== $availability2 && 
                    $availability1->type == $availability2->type &&
                    $scope1Id == $scope2Id &&
                    $availability1->hasSharedWeekdayWith($availability2)) {
                    
                    $start1 = (new \DateTimeImmutable())->setTimestamp($availability1->startDate)
                        ->modify($availability1->startTime);
                    $end1 = (new \DateTimeImmutable())->setTimestamp($availability1->endDate)
                        ->modify($availability1->endTime);
                    $start2 = (new \DateTimeImmutable())->setTimestamp($availability2->startDate)
                        ->modify($availability2->startTime);
                    $end2 = (new \DateTimeImmutable())->setTimestamp($availability2->endDate)
                        ->modify($availability2->endTime);
    
                    if ($start1 < $end2 && $start2 < $end1) {
                        throw new AvailabilityAddFailed('Neue Öffnungszeiten überschneiden sich.');
                    }
                }
            }
        }
    }
}