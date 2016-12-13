<?php
/**
 * @package 115Mandant
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Mellon\Validator;
use \BO\Zmsdb\Calldisplay as Query;
use \BO\Zmsentities\Calldisplay as Entity;

/**
  * Handle requests concerning services
  */
class CalldisplayGet extends BaseController
{
    /**
     * @return String
     */
    public static function render()
    {
        $message = Response\Message::create(Render::$request);
        $query = new Query();
        $input = Validator::input()->isJson()->assertValid()->getValue();

        $entity = new Entity($input);
        if (! $entity->hasScopeList() && ! $entity->hasClusterList()) {
            throw new Exception\Calldisplay\ScopeAndClusterNotFound();
        }
        $message->data = $query->readResolvedEntity($entity, \App::getNow());
        Render::lastModified(time(), '0');
        Render::json($message->setUpdatedMetaData(), $message->getStatuscode());
    }
}
