<?php

namespace BO\Zmsapi;

use BO\Slim\Render;
use BO\Mellon\Validator;
use BO\Zmsdb\Availability as AvailabilityRepository;
use BO\Zmsdb\Connection\Select as DbConnection;
use BO\Zmsentities\Availability as Entity;
use BO\Zmsentities\Collection\AvailabilityList as Collection;
use BO\Zmsentities\Collection\ProcessList;
use BO\Zmsentities\Scope;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use BO\Zmsapi\AvailabilitySlotsUpdate;
use BO\Zmsapi\Exception\BadRequest as BadRequestException;
use BO\Zmsapi\Exception\Availability\AvailabilityNotFound as NotFoundException;
use BO\Zmsapi\Exception\Availability\AvailabilityUpdateFailed;
use DateTimeImmutable;

class AvailabilityUpdate extends BaseController
{
    public function readResponse(
        RequestInterface $request,
        ResponseInterface $response,
        array $args
    ): ResponseInterface {
        (new Helper\User($request))->checkRights();
        $input = $this->validateAndGetInput();
        $resolveReferences = $this->getResolveReferences();

        DbConnection::getWriteConnection();

        $result = $this->processAvailabilityUpdate($input, $resolveReferences);

        return $this->generateResponse($request, $response, $result);
    }

    private function validateAndGetInput(): array
    {
        $input = Validator::input()->isJson()->assertValid()->getValue();
        if (!$input || count($input) === 0) {
            throw new BadRequestException();
        }

        $this->validateInputStructure($input);
        return $input;
    }

    private function validateInputStructure(array $input): void
    {
        if (!isset($input['availabilityList']) || !is_array($input['availabilityList'])) {
            throw new BadRequestException('Missing or invalid availabilityList.');
        }
        if (empty($input['availabilityList']) || !isset($input['availabilityList'][0]['scope'])) {
            throw new BadRequestException('Missing or invalid scope.');
        }
        if (!isset($input['selectedDate'])) {
            throw new BadRequestException("'selectedDate' is required.");
        }
    }

    private function getResolveReferences(): int
    {
        return Validator::param('resolveReferences')->isNumber()->setDefault(2)->getValue();
    }

    private function processAvailabilityUpdate(array $input, int $resolveReferences): Collection
    {
        $newCollection = $this->createAndValidateCollection($input['availabilityList'], $resolveReferences);
        $selectedDate = $this->createSelectedDateTime($input['selectedDate']);
        $scope = new Scope($input['availabilityList'][0]['scope']);

        $this->validateAndCheckConflicts($newCollection, $scope, $selectedDate);

        return $this->updateEntities($newCollection, $resolveReferences);
    }

    private function createAndValidateCollection(array $availabilityList, int $resolveReferences): Collection
    {
        $availabilityRepo = new AvailabilityRepository();
        $newCollection = new Collection();

        foreach ($availabilityList as $item) {
            $entity = new Entity($item);
            $entity->testValid();

            if (isset($entity->id)) {
                $this->validateExistingEntity($entity, $availabilityRepo, $resolveReferences);
            }

            $newCollection->addEntity($entity);
        }

        return $newCollection;
    }

    private function validateExistingEntity(Entity $entity, AvailabilityRepository $repo, int $resolveReferences): void
    {
        $existingEntity = $repo->readEntity($entity->id, $resolveReferences);
        if (!$existingEntity || !$existingEntity->hasId()) {
            throw new NotFoundException("Availability with ID {$entity->id} not found.");
        }
    }

    private function createSelectedDateTime(string $selectedDate): DateTimeImmutable
    {
        return DateTimeImmutable::createFromFormat(
            'Y-m-d H:i:s',
            $selectedDate . ' 00:00:00'
        );
    }

    private function validateAndCheckConflicts(
        Collection $newCollection,
        Scope $scope,
        DateTimeImmutable $selectedDate
    ): void {
        $conflicts = $this->getInitialConflicts($newCollection);
        $mergedCollection = $this->getMergedCollection($scope);

        $this->validateNewAvailabilities($newCollection, $mergedCollection, $selectedDate);

        $filteredCollection = $this->getFilteredCollection($mergedCollection);
        $this->checkExistingConflicts($conflicts, $filteredCollection, $selectedDate);

        $weekday = (int)$selectedDate->format('N');
        $filteredConflicts = $this->filterConflictsByWeekday(
            $conflicts,
            $filteredCollection,
            $weekday
        );

        if ($filteredConflicts->count() > 0) {
            throw new AvailabilityUpdateFailed();
        }
    }

    private function getInitialConflicts(Collection $newCollection): ProcessList
    {
        $conflicts = new ProcessList();
        $newVsNewConflicts = $newCollection->hasNewVsNewConflicts();
        $conflicts->addList($newVsNewConflicts);
        return $conflicts;
    }

    private function getMergedCollection(Scope $scope): Collection
    {
        $availabilityRepo = new AvailabilityRepository();
        $existingCollection = $availabilityRepo->readAvailabilityListByScope($scope, 1);

        $mergedCollection = new Collection();
        foreach ($existingCollection as $existingAvailability) {
            $mergedCollection->addEntity($existingAvailability);
        }
        return $mergedCollection;
    }

    private function validateNewAvailabilities(
        Collection $newCollection,
        Collection $mergedCollection,
        DateTimeImmutable $selectedDate
    ): void {
        $validations = [];
        foreach ($newCollection as $newAvailability) {
            $validations = array_merge(
                $validations,
                $this->validateSingleAvailability($newAvailability, $mergedCollection, $selectedDate)
            );
            $mergedCollection->addEntity($newAvailability);
        }

        if (count($validations) > 0) {
            throw new AvailabilityUpdateFailed();
        }
    }

    private function validateSingleAvailability(
        Entity $availability,
        Collection $mergedCollection,
        DateTimeImmutable $selectedDate
    ): array {
        $startDate = (new DateTimeImmutable())->setTimestamp($availability->startDate);
        $endDate = (new DateTimeImmutable())->setTimestamp($availability->endDate);
        $startDateTime = new DateTimeImmutable(
            "{$startDate->format('Y-m-d')} {$availability->startTime}"
        );
        $endDateTime = new DateTimeImmutable(
            "{$endDate->format('Y-m-d')} {$availability->endTime}"
        );

        return $mergedCollection->validateInputs(
            $startDateTime,
            $endDateTime,
            $selectedDate,
            $availability->kind ?? 'default',
            $availability->bookable['startInDays'],
            $availability->bookable['endInDays'],
            $availability->weekday
        );
    }

    private function getFilteredCollection(Collection $mergedCollection): Collection
    {
        $originId = $this->findOriginId($mergedCollection);

        $filtered = new Collection();
        foreach ($mergedCollection as $availability) {
            if ($this->shouldIncludeAvailability($availability, $originId)) {
                $filtered->addEntity($availability);
            }
        }
        return $filtered;
    }

    private function findOriginId(Collection $collection): ?string
    {
        foreach ($collection as $availability) {
            if (
                isset($availability->kind) &&
                $availability->kind === 'origin' &&
                isset($availability->id)
            ) {
                return $availability->id;
            }
        }
        return null;
    }

    private function shouldIncludeAvailability(Entity $availability, ?string $originId): bool
    {
        return (!isset($availability->kind) || $availability->kind !== 'exclusion') &&
            (!isset($availability->id) || $availability->id !== $originId);
    }

    private function checkExistingConflicts(
        ProcessList $conflicts,
        Collection $filteredCollection,
        DateTimeImmutable $selectedDate
    ): void {
        [$earliestStartDateTime, $latestEndDateTime] = $filteredCollection
            ->getDateTimeRangeFromList($selectedDate);

        $existingConflicts = $filteredCollection->checkAllVsExistingConflicts(
            $earliestStartDateTime,
            $latestEndDateTime
        );
        $conflicts->addList($existingConflicts);
    }

    private function filterConflictsByWeekday(
        ProcessList $conflicts,
        Collection $filteredCollection,
        int $weekday
    ): ProcessList {
        $filteredConflicts = new ProcessList();

        foreach ($conflicts as $conflict) {
            $availability1 = $conflict->getFirstAppointment()->getAvailability();
            $availability2 = $this->findMatchingAvailability(
                $availability1,
                $filteredCollection
            );

            if ($this->doesConflictAffectWeekday($availability1, $availability2, $weekday)) {
                $filteredConflicts->addEntity($conflict);
            }
        }

        return $filteredConflicts;
    }

    private function findMatchingAvailability(
        Entity $availability1,
        Collection $collection
    ): ?Entity {
        foreach ($collection as $avail) {
            if (
                $avail->id === $availability1->id ||
                (isset($avail->tempId) &&
                 isset($availability1->tempId) &&
                 $avail->tempId === $availability1->tempId)
            ) {
                return $avail;
            }
        }
        return null;
    }

    private function doesConflictAffectWeekday(
        Entity $availability1,
        ?Entity $availability2,
        int $weekday
    ): bool {
        $weekdayKey = strtolower(date('l', strtotime("Sunday +{$weekday} days")));

        if (
            isset($availability1->weekday[$weekdayKey]) &&
            (int)$availability1->weekday[$weekdayKey] > 0
        ) {
            return true;
        }

        if (
            $availability2 &&
            isset($availability2->weekday[$weekdayKey]) &&
            (int)$availability2->weekday[$weekdayKey] > 0
        ) {
            return true;
        }

        return false;
    }

    private function updateEntities(Collection $newCollection, int $resolveReferences): Collection
    {
        $updatedCollection = new Collection();
        foreach ($newCollection as $entity) {
            $updatedEntity = $this->writeEntityUpdate($entity, $resolveReferences);
            AvailabilitySlotsUpdate::writeCalculatedSlots($updatedEntity, true);
            $updatedCollection->addEntity($updatedEntity);
        }
        return $updatedCollection;
    }

    private function generateResponse(
        RequestInterface $request,
        ResponseInterface $response,
        Collection $updatedCollection
    ): ResponseInterface {
        $message = Response\Message::create($request);
        $message->data = $updatedCollection->getArrayCopy();

        $response = Render::withLastModified($response, time(), '0');
        return Render::withJson(
            $response,
            $message->setUpdatedMetaData(),
            $message->getStatuscode()
        );
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
            $updatedEntity = $repository->writeEntity($entity, $resolveReferences);
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
