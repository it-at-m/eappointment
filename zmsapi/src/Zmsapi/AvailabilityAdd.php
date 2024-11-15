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

        
    
    
        foreach ($input['availabilityList'] as $item) {
            $entity = new Entity($item);
            $entity->testValid();
            error_log("here");
            $newCollection->addEntity($entity);
        }

        

        
        $scopeData = $input['availabilityList'][0]['scope'];
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
            // Log the original values
            error_log("Original startDate (timestamp): " . $newAvailability->startDate);
            error_log("Original endDate (timestamp): " . $newAvailability->endDate);
            error_log("Original startTime: " . $newAvailability->startTime);
            error_log("Original endTime: " . $newAvailability->endTime);
            error_log("SelectedDate: " . $input['selectedDate']);

            error_log(json_encode($newAvailability));
        
            // Convert timestamps to DateTimeImmutable objects
            $startDate = (new \DateTimeImmutable())->setTimestamp($newAvailability->startDate);
            $endDate = (new \DateTimeImmutable())->setTimestamp($newAvailability->endDate);
            $selectedDate = \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $input['selectedDate'] . ' 00:00:00');
        
            // Log the converted dates
            error_log("Converted startDate: " . $startDate->format('Y-m-d'));
            error_log("Converted endDate: " . $endDate->format('Y-m-d'));
            error_log("Converted selectedDate: " . $selectedDate->format('Y-m-d'));
        
            // Combine date and time for start and end
            $startDateTime = new \DateTimeImmutable("{$startDate->format('Y-m-d')} {$newAvailability->startTime}");
            $endDateTime = new \DateTimeImmutable("{$endDate->format('Y-m-d')} {$newAvailability->endTime}");
        
            // Log the combined DateTimeImmutable objects for debugging
            error_log("Combined startDateTime: " . $startDateTime->format('Y-m-d H:i:s'));
            error_log("Combined endDateTime: " . $endDateTime->format('Y-m-d H:i:s'));
        
            // Pass the combined DateTimeImmutable objects to validateInputs
            $validation = $mergedCollection->validateInputs($startDateTime, $endDateTime, $selectedDate, $newAvailability->kind);
            error_log(''. json_encode($validation));


            // Add new availability to the merged collection after validation
            $mergedCollection->addEntity($newAvailability);
        }
        
        

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
