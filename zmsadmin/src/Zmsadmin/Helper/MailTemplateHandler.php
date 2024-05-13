<?php
/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin\Helper;

use BO\Zmsadmin\BaseController;
use BO\Zmsentities\Availability as Entity;
use BO\Zmsentities\Collection\AvailabilityList as Collection;
use BO\Zmsentities\Collection\ProcessList;

class MailTemplateHandler extends BaseController
{
    /**
     * @SuppressWarnings(Param)
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {

        $validator = $request->getAttribute('validator');
        $input = $validator->getInput()->isJson()->getValue();
        
        $template = \App::$http->readPostResult('/mailtemplates/', array(
            "templateName" => $input['templateName'],
            "templateContent" => $input['templateContent']
        ))->getEntity();


        return \BO\Slim\Render::withJson(
            $response,
            $template
        );
    }
}
