<?php
/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

use BO\Zmsentities\Availability;
use BO\Zmsentities\Collection\AvailabilityList;

/**
 * Update availabilites, API proxy
 *
 */
class AvailabilityUpdate extends BaseController
{
    /**
     * @SuppressWarnings(UnusedFormalParameter)
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function __invoke(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        $validator = $request->getAttribute('validator');
        $input = $validator->getInput()->isJson()->assertValid()->getValue();
        $result = \App::$http->readPostResult('/availability/', $input);
        return $result->getResponse();
    }
}
