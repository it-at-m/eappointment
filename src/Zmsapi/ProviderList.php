<?php
/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Mellon\Validator;
use \BO\Zmsdb\Provider as Query;

/**
  * Handle requests concerning services
  */
class ProviderList extends BaseController
{
    /**
     * @return String
     */
    public static function render($source, $requestIds = null)
    {
        $resolveReferences = Validator::param('resolveReferences')->isNumber()->setDefault(0)->getValue();
        $isAssigned = Validator::param('isAssigned')->isBool()->getValue();
        if (null !== $requestIds) {
            $providerList = (new Query())->readListByRequest($source, $requestIds, $resolveReferences);
        } else {
            $providerList = (new Query())->readList($source, $isAssigned, $resolveReferences);
        }
        $message = Response\Message::create(Render::$request);
        $message->data = $providerList;
        Render::lastModified(time(), '0');
        Render::json($message);
    }
}
