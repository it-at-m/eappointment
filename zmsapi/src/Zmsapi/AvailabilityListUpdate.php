<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use BO\Slim\Render;
use BO\Mellon\Validator;
use BO\Zmsapi\Exception\Availability\AvailabilityListUpdateFailed;
use BO\Zmsentities\Availability;
use BO\Zmsentities\Collection\AvailabilityList;
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
        self::validateClientData($input);
        self::validateScopeConsistency($input['availabilityList']);
        $newAvailabilities = $this->createAvailabilityList($input['availabilityList']);
        $selectedDate = $this->createSelectedDateTime($input['selectedDate']);
        $scope = new \BO\Zmsentities\Scope($input['availabilityList'][0]['scope']);
        $existingAvailabilities = $this->createMergedAvailabilityList($scope);

        $validationErrors = $this->validateAvailabilityList($newAvailabilities, $existingAvailabilities, $selectedDate);

        if (count($validationErrors) > 0) {
            $availabilityIds = array_map(
                function ($availability) {
                    return $availability->id ?? null;
                },
                $newAvailabilities->getArrayCopy()
            );
            App::$log->warning('AvailabilityListUpdateFailed: Validation failed', [
                'ids' => array_filter($availabilityIds),
                'scope_id' => $scope->getId(),
                'errors' => $validationErrors
            ]);
            $message = Response\Message::create($request);
            $message->data = [];
            $message->meta->error = true;
            $message->meta->message = json_encode(['errors' => $validationErrors]);
            $message->meta->exception = 'BO\\Zmsapi\\Exception\\Availability\\AvailabilityListUpdateFailed';
            return Render::withJson($response, $message, 400);
        }

        DbConnection::getWriteConnection();
        $updatedAvailabilities = $this->updateAvailabilityList($newAvailabilities, $resolveReferences);
        return $this->generateResponse($request, $response, $updatedAvailabilities);
    }

    private function generateResponse(
        RequestInterface $request,
        ResponseInterface $response,
        AvailabilityList $availabilities
    ): ResponseInterface {
        $message = Response\Message::create($request);
        $message->data = $availabilities->getArrayCopy();

        $response = Render::withLastModified($response, time(), '0');
        return Render::withJson(
            $response,
            $message->setUpdatedMetaData(),
            $message->getStatuscode()
        );
    }

    private function updateAvailabilityList(AvailabilityList $availabilities, int $resolveReferences): AvailabilityList
    {
        $updatedAvailabilities = new AvailabilityList();
        foreach ($availabilities as $availability) {
            $updatedAvailability = $this->updateAvailability($availability, $resolveReferences);
            AvailabilitySlotsUpdate::writeCalculatedSlots($updatedAvailability, true);
            $updatedAvailabilities->addEntity($updatedAvailability);
        }
        return $updatedAvailabilities;
    }

    protected function updateAvailability($availability, $resolveReferences): ?Availability
    {
        $repository = new AvailabilityRepository();
        $updatedAvailability = null;
        if ($availability->id) {
            $existingAvailability = $repository->readEntity($availability->id);
            if ($existingAvailability && $existingAvailability->hasId()) {
                $availability->version = $existingAvailability->version + 1;
                $this->resetOpeningHours($existingAvailability);
                $updatedAvailability = $repository->updateEntity($availability->id, $availability, $resolveReferences);
                App::$log->info('Updated availability', [
                    'id' => $availability->id,
                    'scope_id' => $availability->scope['id'],
                    'version' => $availability->version,
                    'startDate' => $availability->startDate,
                    'endDate' => $availability->endDate,
                    'startTime' => $availability->startTime,
                    'endTime' => $availability->endTime,
                    'type' => $availability->type,
                    'operation' => 'update'
                ]);
            }
        } else {
            $updatedAvailability = $repository->writeEntity($availability, $resolveReferences);
            App::$log->info('Created new availability', [
                'id' => $updatedAvailability->id,
                'scope_id' => $availability->scope['id'],
                'startDate' => $availability->startDate,
                'endDate' => $availability->endDate,
                'startTime' => $availability->startTime,
                'endTime' => $availability->endTime,
                'type' => $availability->type,
                'operation' => 'create'
            ]);
        }
        if (!$updatedAvailability) {
            throw new AvailabilityListUpdateFailed();
        }
        return $updatedAvailability;
    }

    protected function resetOpeningHours(Availability $availability): void
    {
        $doubleTypeAvailability = (new AvailabilityRepository())->readEntityDoubleTypes($availability->id);
        if ($doubleTypeAvailability) {
            $doubleTypeAvailability->workstationCount['intern'] = 0;
            $doubleTypeAvailability->workstationCount['callcenter'] = 0;
            $doubleTypeAvailability->workstationCount['public'] = 0;
            $doubleTypeAvailability['description'] = '';
            $doubleTypeAvailability['type'] = 'openinghours';
            (new AvailabilityRepository())->writeEntity($doubleTypeAvailability);
        }
    }

    private function validateAvailabilityList(
        AvailabilityList $newAvailabilities,
        AvailabilityList $existingAvailabilities,
        \DateTimeImmutable $selectedDate
    ): array {
        $validationErrors = [];
        foreach ($newAvailabilities as $newAvailability) {
            $errors = $this->validateAvailability($newAvailability, $existingAvailabilities, $selectedDate);
            if (count($errors) > 0) {
                $validationErrors = array_merge($validationErrors, $errors);
            } else {
                $existingAvailabilities->addEntity($newAvailability);
            }
        }
        return $validationErrors;
    }

    private function validateAvailability(
        Availability $availability,
        AvailabilityList $existingAvailabilities,
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

        return $existingAvailabilities->validateTimeRangesAndRules(
            $startDateTime,
            $endDateTime,
            $selectedDate,
            $availability->kind ?? 'default',
            $availability->bookable['startInDays'],
            $availability->bookable['endInDays'],
            $availability->weekday
        );
    }

    private function createAvailabilityList(array $availabilityData): AvailabilityList
    {
        $availabilities = new AvailabilityList();
        foreach ($availabilityData as $data) {
            $availability = new Availability($data);
            $availability->testValid();
            $availabilities->addEntity($availability);
        }
        return $availabilities;
    }

    private function createSelectedDateTime(string $selectedDate): \DateTimeImmutable
    {
        return \DateTimeImmutable::createFromFormat(
            'Y-m-d H:i:s',
            $selectedDate . ' 00:00:00'
        );
    }

    private function createMergedAvailabilityList(\BO\Zmsentities\Scope $scope): AvailabilityList
    {
        $availabilityRepo = new AvailabilityRepository();
        $existingAvailabilities = $availabilityRepo->readAvailabilityListByScope($scope, 1);

        $mergedAvailabilities = new AvailabilityList();
        foreach ($existingAvailabilities as $availability) {
            $mergedAvailabilities->addEntity($availability);
        }
        return $mergedAvailabilities;
    }

    private static function validateClientData(array $input): void
    {
        if (empty($input)) {
            App::$log->warning('No input data provided');
            throw new BadRequestException('No input data provided');
        }

        if (!isset($input['availabilityList']) || !is_array($input['availabilityList']) || empty($input['availabilityList'])) {
            App::$log->warning('Invalid availabilityList', [
                'has_availabilityList' => isset($input['availabilityList']),
                'is_array' => isset($input['availabilityList']) ? is_array($input['availabilityList']) : false,
                'is_empty' => isset($input['availabilityList']) ? empty($input['availabilityList']) : true
            ]);
            throw new BadRequestException('Invalid availabilityList');
        }
    }

    private static function validateScopeConsistency(array $availabilityList): void
    {
        if (empty($availabilityList)) {
            return;
        }

        $firstScope = null;
        foreach ($availabilityList as $index => $availability) {
            $currentScope = $availability['scope']['id'] ?? null;
            if (!$currentScope) {
                App::$log->warning('Missing scope id in availability', ['index' => $index]);
                throw new BadRequestException('Missing scope id in availability list');
            }
            if ($firstScope === null) {
                $firstScope = $currentScope;
            } elseif ($currentScope !== $firstScope) {
                App::$log->warning('Inconsistent scopes in availability list', [
                    'first_scope' => $firstScope,
                    'different_scope' => $currentScope,
                    'index' => $index
                ]);
                throw new BadRequestException('All availabilities must belong to the same scope');
            }
        }
    }
}
