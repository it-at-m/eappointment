<?php
/**
 *
 * @package Zmsappointment
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */
namespace BO\Zmscalldisplay\Helper;

use \BO\Mellon\Validator;
use \BO\Zmsentities\Calldisplay as Entity;

class Calldisplay
{
    public $entity;

    public $collections = '';

    const DEFAULT_STATUS = ['called', 'pickup'];

    public function __construct($request)
    {
        $this->entity = static::createInstance($request);
        $this->entity = \App::$http->readPostResult('/calldisplay/', $this->entity)->getEntity();
        $this->collections = static::getCollections($request);
    }

    /**
     * Get status for queue
     *
     * @return array
     */
    public static function getRequestedQueueStatus($request)
    {
        $validator = $request->getAttribute('validator');
        $queue = $validator->getParameter('queue')->isArray()->getValue();
        $status = Validator::value($queue['status'])->isString()->getValue();
        return ($status) ? explode(',', $status) : static::DEFAULT_STATUS;
    }

    public function getEntity()
    {
        return $this->entity;
    }

    protected static function createInstance($request)
    {
        $calldisplay = new Entity();
        if ($calldisplay instanceof \BO\Zmsentities\Schema\Entity) {
            $calldisplay->withResolvedCollections(static::getCollections($request));
        }
        return $calldisplay;
    }

    protected static function getCollections($request)
    {
        $validator = $request->getAttribute('validator');
        return $validator->getParameter('collections')->isArray()->getValue();
    }
}
