<?php
/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Mellon\Validator;
use \BO\Zmsdb\Session;

class SessionUpdate extends BaseController
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

        $message = Response\Message::create($request);
        $message->data = (new Session())->updateEntity($session);

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
        return $response;
    }

    protected function testMatching($session)
    {
        if (false === Helper\Matching::isProviderExisting($session)) {
            throw new Exception\Matching\ProviderNotFound();
        } elseif (false === Helper\Matching::isRequestExisting($session)) {
            throw new Exception\Matching\RequestNotFound();
        } elseif (false === Helper\Matching::hasProviderRequest($session)) {
            throw new Exception\Matching\MatchingNotFound();
        }
    }
}
