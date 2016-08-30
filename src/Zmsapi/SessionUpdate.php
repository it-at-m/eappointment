<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/
namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Mellon\Validator;
use \BO\Zmsdb\Session as Query;

class SessionUpdate extends BaseController
{
    /**
     *
     * @return String
     */
    public static function render()
    {
        $message = Response\Message::create(Render::$request);
        $input = Validator::input()->isJson()->getValue();
        $session = new \BO\Zmsentities\Session($input);
        $session->getUnSerializedContent();
        $message->data = null;
        if ($session->getProviders() && false === Helper\Matching::isProviderExisting($session)) {
            $message->meta->statuscode = 500;
            $message->meta->error = true;
            $message->meta->message = "Ein ausgewählter Dienstleister exisistiert nicht";
            $message->meta->exception = "BO/Zmsapi/Helper/Matching";
        } elseif ($session->getRequests() && false === Helper\Matching::isRequestExisting($session)) {
            $message->meta->statuscode = 500;
            $message->meta->error = true;
            $message->meta->message = "Eine ausgewählte Dienstleistung exisistiert nicht";
            $message->meta->exception = "BO/Zmsapi/Helper/Matching";
        } elseif ($session->getProviders() && false === Helper\Matching::hasProviderRequest($session)) {
            $message->meta->statuscode = 500;
            $message->meta->error = true;
            $message->meta->message = "Die Diensleistung wird nicht vom Dienstleister angeboten";
            $message->meta->exception = "BO/Zmsapi/Helper/Matching";
        } else {
            $session->getSerializedContent();
            $message->data = (new Query())->updateEntity($session);
        }

        Render::lastModified(time(), '0');
        Render::json($message->setUpdatedMetaData(), $message->getStatuscode());
    }
}
