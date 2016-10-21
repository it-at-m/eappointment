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
        $input = Validator::input()->isJson()->assertValid()->getValue();
        $session = new \BO\Zmsentities\Session($input);
        $session->getUnSerializedContent();
        $session->testValid();
        $message->data = null;

        if (false === Helper\Matching::isProviderExisting($session)) {
            throw new Exception\Matching\ProviderNotFound();
        } elseif (false === Helper\Matching::isRequestExisting($session)) {
            throw new Exception\Matching\RequestNotFound();
        } elseif (false === Helper\Matching::hasProviderRequest($session)) {
            throw new Exception\Matching\MatchingNotFound();
        } elseif (!$session->hasStatus()) {
            throw new Exception\Session\InvalidSession('Es konnte keine Session ermittelt werden');
        } else {
            $session->getSerializedContent();
            $message->data = (new Query())->updateEntity($session);
        }

        Render::lastModified(time(), '0');
        Render::json($message->setUpdatedMetaData(), $message->getStatuscode());
    }
}
