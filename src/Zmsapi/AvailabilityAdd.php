<?php
/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Mellon\Validator;
use \BO\Zmsdb\Availability as Query;

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
        if (0 == count($input)) {
            throw new Exception\Availability\AvailabilityNotFound();
        }
        $collection = new \BO\Zmsentities\Collection\AvailabilityList();
        foreach ($input as $availability) {
            $entity = new \BO\Zmsentities\Availability($availability);
            if ($entity->id) {
                $oldentity = (new Query())->readEntity($entity->id);
                if ($oldentity->hasId()) {
                    $entity = (new Query())->updateEntity($entity->id, $entity);
                } else {
                    //Workaround for openinghours migration, remove after AP13
                    $entity = (new Query())->writeEntity($entity);
                }
            } else {
                $entity = (new Query())->writeEntity($entity);
            }
            $collection[] = $entity;
        }

        $message = Response\Message::create($request);
        $message->data = $collection->getArrayCopy();

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
        return $response;
    }
}
