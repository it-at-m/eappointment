<?php
/**
 * @package 115Mandant
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Mellon\Validator;
use \BO\Zmsdb\Provider as Query;

/**
  * Handle requests concerning services
  */
class ProviderGet extends BaseController
{
    /**
     * @return String
     */
    public static function render($source, $itemId)
    {
        $message = Response\Message::create(Render::$request);
        $resolveReferences = Validator::param('resolveReferences')->isNumber()->setDefault(0)->getValue();
        $provider = (new Query())->readEntity($source, $itemId, $resolveReferences);
        if (! $provider->hasId()) {
            throw new Exception\Provider\ProviderNotFound();
        }

        $message->data = $provider;
        Render::lastModified(time(), '0');
        Render::json($message->setUpdatedMetaData(), $message->getStatuscode());
    }
}
