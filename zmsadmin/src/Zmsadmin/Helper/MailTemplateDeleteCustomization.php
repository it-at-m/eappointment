<?php

/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin\Helper;

use BO\Zmsadmin\BaseController;

class MailTemplateDeleteCustomization extends BaseController
{
    /**
     * @SuppressWarnings(Param)
     * @return \Psr\Http\Message\ResponseInterface
     */
    #[\Override]
    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {

        $validator = $request->getAttribute('validator');
        $input = $validator->getInput()->isJson()->getValue();

        $result = \App::$http->readDeleteResult('/mailtemplates/' . $input['templateId'] . '/');

        return \BO\Slim\Render::withJson(
            $response,
            $result
        );
    }
}
