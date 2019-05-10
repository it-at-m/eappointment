<?php
/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Mellon\Validator;
use \BO\Zmsdb\Availability as Query;

/**
 * @SuppressWarnings(Coupling)
 */
class AvailabilityAdd extends BaseController
{
    /**
     * @SuppressWarnings(Param)
     * @return String
     */
    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        (new Helper\User($request))->checkRights();
        $input = Validator::input()->isJson()->assertValid()->getValue();
        $useMigrationFix = Validator::param('migrationfix')->isNumber()->setDefault(1)->getValue();
        $newEntity = null;
        if (0 == count($input)) {
            throw new Exception\Availability\AvailabilityNotFound();
        }
        $collection = new \BO\Zmsentities\Collection\AvailabilityList();
        \BO\Zmsdb\Connection\Select::getWriteConnection();
        foreach ($input as $availability) {
            $entity = new \BO\Zmsentities\Availability($availability);
            $entity->testValid();
            if ($entity->id) {
                $oldentity = (new Query())->readEntity($entity->id);
                if ($oldentity->hasId()) {
                    $newEntity = (new Query())->updateEntity($entity->id, $entity, 2);
                } elseif ($useMigrationFix) {
                    //Workaround for openinghours migration, remove after AP13
                    $newEntity = (new Query())->writeEntity($entity, 2);
                }
            } else {
                $newEntity = (new Query())->writeEntity($entity, 2);
            }
            if (! $newEntity) {
                throw new Exception\Availability\AvailabilityUpdateFailed();
            }
            (new \BO\Zmsdb\Slot)->writeByAvailability($newEntity, \App::$now);
            (new \BO\Zmsdb\Helper\CalculateSlots(\App::DEBUG))
                ->writePostProcessingByScope($newEntity->scope, \App::$now);
            $collection[] = $newEntity;
        }

        $message = Response\Message::create($request);
        $message->data = $collection->getArrayCopy();

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
        return $response;
    }
}
