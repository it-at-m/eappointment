<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsbackend\Session\Api;

use BO\Slim\Render;
use BO\Mellon\Validator;
use BO\Zmsbackend\Session\Service\Session;

class SessionUpdate extends \BO\Zmsbackend\Api\BaseController
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
        $input = Validator::input()->isJson()->assertValid()->getValue();
        $session = new \BO\Zmsentities\Session($input);
        //overwrite sessions content without frontend parameter like basket and human
        if (Validator::param('oidc')->isBool()->setDefault(false)->getValue()) {
            $session->content = $input['content'];
        }
        $session->testValid();
        if (isset($session->getContent()['error']) && 'isDifferent' != $session->getContent()['error']) {
            $this->testMatching($session);
        }

        $message = \BO\Zmsbackend\Api\Response\Message::create($request);
        $message->data = (new \BO\Zmsbackend\Session\Service\Session())->updateEntity($session);

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
        return $response;
    }

    protected function testMatching($session)
    {
        if (false === \BO\Zmsbackend\Helper\Matching::isProviderExisting($session)) {
            throw new \BO\Zmsbackend\Matching\Exception\ProviderNotFound();
        } elseif (false === \BO\Zmsbackend\Helper\Matching::isRequestExisting($session)) {
            throw new \BO\Zmsbackend\Matching\Exception\RequestNotFound();
        } elseif (false === \BO\Zmsbackend\Helper\Matching::hasProviderRequest($session)) {
            throw new \BO\Zmsbackend\Matching\Exception\MatchingNotFound();
        }
    }
}
