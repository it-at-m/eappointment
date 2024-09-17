<?php
/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Mellon\Validator;
use \BO\Zmsdb\MailTemplates as MailTemplatesQuery;
use \BO\Zmsapi\Helper\User;

class MailMergedTemplatesGet extends BaseController
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
        try {
            (new Helper\User($request))->checkRights('basic');
        } catch (\Exception $exception) {
            $token = $request->getHeader('X-Token');
            if (\App::SECURE_TOKEN != current($token)) {
                throw new Exception\Config\ConfigAuthentificationFailed();
            }
        }

        $providerId = $args['providerId'];
        
        $mailtemplates = (new MailTemplatesQuery())->readCustomizedListForProvider($providerId);
        
        $message = Response\Message::create($request);
        $message->data = $mailtemplates;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message, $message->getStatuscode());
        return $response;
    }
}
