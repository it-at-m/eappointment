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
    public static function render($source, $requestIdCsv = null)
    {
        $resolveReferences = Validator::param('resolveReferences')->isNumber()->setDefault(0)->getValue();
        $query = new Query();
        $isAssigned = Validator::param('isAssigned')->isBool()->getValue();

        if (null !== $requestIdCsv) {
            $providerList = $query->readListByRequest($source, $requestIdCsv, $resolveReferences);
        } else {
            $providerList = $query->readList($source, $resolveReferences, $isAssigned);
        }

        if (0 == count($providerList)) {
            throw new Exception\Provider\ProviderNotFound();
        }

        $message = Response\Message::create(Render::$request);
        $message->data = $providerList;
        Render::lastModified(time(), '0');
        Render::json($message->setUpdatedMetaData(), $message->getStatuscode());
    }
}
