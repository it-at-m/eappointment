<?php
/**
 *
 * @package Zmscalldisplay
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */
namespace BO\Zmscalldisplay\Helper;

use \BO\Mellon\Validator;
use \BO\Zmsentities\Calldisplay as Entity;

class Calldisplay
{
    protected $entity;
    protected $isEntityResolved = false;

    const DEFAULT_STATUS = ['called', 'pickup', 'processing'];

    public function __construct($request)
    {
        $this->entity = static::createInstance($request);
    }

    /**
     * Get status for queue
     *
     * @return array
     */
    public static function getRequestedQueueStatus($request)
    {
        /** @var Validator $validator */
        $validator = $request->getAttribute('validator');
        $queue = $validator->getParameter('queue')->isArray()->getValue();
        $status = (is_array($queue) && isset($queue['status'])) ? $queue['status'] : null;
        return is_string($status) ? explode(',', $status) : static::DEFAULT_STATUS;
    }

    public function getEntity($resolveEntity = true)
    {
        if (!$this->isEntityResolved && $resolveEntity) {
            $this->entity = \App::$http->readPostResult('/calldisplay/', $this->entity)->getEntity();
            $this->isEntityResolved = true;
        }
        return $this->entity;
    }

    public function getSingleScope()
    {
        $scope = null;
        if (1 == $this->getEntity(false)->getScopeList()->count()) {
            $scopeId = $this->getEntity(false)->getScopeList()->getFirst()->getId();
            $scope = \App::$http
                ->readGetResult('/scope/'. $scopeId .'/', ['keepLessData' => ['status']])
                ->getEntity();
        }
        return $scope;
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
