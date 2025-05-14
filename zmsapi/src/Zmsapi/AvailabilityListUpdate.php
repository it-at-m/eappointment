<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use BO\Slim\Render;
use BO\Mellon\Validator;
use BO\Zmsapi\Exception\Availability\AvailabilityListUpdateFailed;
use BO\Zmsentities\Availability as Entity;
use BO\Zmsentities\Collection\AvailabilityList as Collection;
use BO\Zmsdb\Availability as AvailabilityRepository;
use BO\Zmsdb\Connection\Select as DbConnection;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use BO\Zmsapi\AvailabilitySlotsUpdate;
use BO\Zmsapi\Exception\BadRequest as BadRequestException;
use App;

/**
 * @SuppressWarnings(Coupling)
 */
class AvailabilityListUpdate extends BaseController
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
        $resolveReferences = Validator::param('resolveReferences')->isNumber()->setDefault(2)->getValue();
        if (!$input || count($input) === 0) {
            throw new BadRequestException();
        }

        $newCollection = $this->createNewCollection($input['availabilityList']);
        $selectedDate = $this->createSelectedDateTime($input['selectedDate']);
        $scope = new \BO\Zmsentities\Scope($input['availabilityList'][0]['scope']);
        $mergedCollection = $this->getMergedCollection($scope);

        $validationErrors = $this->validateNewAvailabilities($newCollection, $mergedCollection, $selectedDate);

        if (count($validationErrors) > 0) {
            App::$log->warning('AvailabilityListUpdateFailed: Validation failed', [
                'errors' => $validationErrors
            ]);
            $message = Response\Message::create($request);
            $message->data = [];
            $message->status = 'error';
            $message->error = $validationErrors;
            return Render::withJson($response, $message, 400);
        }

        DbConnection::getWriteConnection();
        $result = $this->updateEntities($newCollection, $resolveReferences);
        return $this->generateResponse($request, $response, $result);
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

    protected function writeEntityUpdate($entity, $resolveReferences): ?Entity
    {
        $repository = new AvailabilityRepository();
        $updatedEntity = null;
        if ($entity->id) {
            $oldentity = $repository->readEntity($entity->id);
            if ($oldentity && $oldentity->hasId()) {
                $this->writeSpontaneousEntity($oldentity);
                $updatedEntity = $repository->updateEntity($entity->id, $entity, $resolveReferences);
            }
        } else {
            $updatedEntity = $repository->writeEntity($entity, $resolveReferences);
        }
        if (!$updatedEntity) {
            throw new AvailabilityListUpdateFailed();
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

    private function validateNewAvailabilities(
        Collection $newCollection,
        Collection $mergedCollection,
        \DateTimeImmutable $selectedDate
    ): array {
        $validationErrors = [];
        foreach ($newCollection as $newAvailability) {
            $errors = $this->validateSingleAvailability($newAvailability, $mergedCollection, $selectedDate);
            if (count($errors) > 0) {
                $validationErrors = array_merge($validationErrors, $errors);
            } else {
                $mergedCollection->addEntity($newAvailability);
            }
        }
        return $validationErrors;
    }

    private function validateSingleAvailability(
        Entity $availability,
        Collection $mergedCollection,
        \DateTimeImmutable $selectedDate
    ): array {
        $startDate = (new \DateTimeImmutable())->setTimestamp($availability->startDate);
        $endDate = (new \DateTimeImmutable())->setTimestamp($availability->endDate);
        $startDateTime = new \DateTimeImmutable(
            "{$startDate->format('Y-m-d')} {$availability->startTime}"
        );
        $endDateTime = new \DateTimeImmutable(
            "{$endDate->format('Y-m-d')} {$availability->endTime}"
        );

        return $mergedCollection->validateTimeRangesAndRules(
            $startDateTime,
            $endDateTime,
            $selectedDate,
            $availability->kind ?? 'default',
            $availability->bookable['startInDays'],
            $availability->bookable['endInDays'],
            $availability->weekday
        );
    }

    private function createNewCollection(array $availabilityList): Collection
    {
        $newCollection = new Collection();
        foreach ($availabilityList as $item) {
            $entity = new Entity($item);
            $entity->testValid();
            $newCollection->addEntity($entity);
        }
        return $newCollection;
    }

    private function createSelectedDateTime(string $selectedDate): \DateTimeImmutable
    {
        return \DateTimeImmutable::createFromFormat(
            'Y-m-d H:i:s',
            $selectedDate . ' 00:00:00'
        );
    }

    private function getMergedCollection(\BO\Zmsentities\Scope $scope): Collection
    {
        $availabilityRepo = new AvailabilityRepository();
        $existingCollection = $availabilityRepo->readAvailabilityListByScope($scope, 1);

        $mergedCollection = new Collection();
        foreach ($existingCollection as $existingAvailability) {
            $mergedCollection->addEntity($existingAvailability);
        }
        return $mergedCollection;
    }
}
