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
        } else if (empty($input['availabilityList']) || !isset($input['availabilityList'][0]['scope'])) {
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
        $selectedDate = \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $input['selectedDate'] . ' 00:00:00');
        $weekday = (int)$selectedDate->format('N');

        $conflicts = new \BO\Zmsentities\Collection\ProcessList();
        $newVsNewConflicts = $newCollection->hasNewVsNewConflicts($selectedDate);
        $conflicts->addList($newVsNewConflicts);

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

            $currentValidation = $mergedCollection->validateInputs(
                $startDateTime,
                $endDateTime,
                $selectedDate,
                $newAvailability->kind ?? 'default',
                $newAvailability->bookable['startInDays'],
                $newAvailability->bookable['endInDays'],
                $newAvailability->weekday
            );
            $validations = array_merge($validations, $currentValidation);

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
        $existingConflicts = $mergedCollectionWithoutExclusions->checkAllVsExistingConflicts($earliestStartDateTime, $latestEndDateTime);
        $conflicts->addList($existingConflicts);

        // Filter conflicts by weekday
        $filteredConflicts = new \BO\Zmsentities\Collection\ProcessList();
        foreach ($conflicts as $conflict) {
            $availability1 = $conflict->getFirstAppointment()->getAvailability();
            $availability2 = null;
            foreach ($mergedCollectionWithoutExclusions as $avail) {
                if ($avail->id === $availability1->id || 
                    (isset($avail->tempId) && isset($availability1->tempId) && $avail->tempId === $availability1->tempId)) {
                    $availability2 = $avail;
                    break;
                }
            }

            // Check if either availability has the weekday bit set
            $affectsSelectedDay = false;
            if (isset($availability1->weekday[$weekday]) && (int)$availability1->weekday[$weekday] > 0) {
                $affectsSelectedDay = true;
            }
            if ($availability2 && isset($availability2->weekday[$weekday]) && (int)$availability2->weekday[$weekday] > 0) {
                $affectsSelectedDay = true;
            }

            // Only keep conflicts that affect the selected day
            if ($affectsSelectedDay) {
                $filteredConflicts->addEntity($conflict);
            }
        }

        if ($filteredConflicts->count() > 0) {
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

}