<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use BO\Slim\Render;
use BO\Mellon\Validator;
use BO\Zmsdb\Source as Query;
use BO\Zmsentities\Source as Entity;

class SourceUpdate extends BaseController
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
        (new Helper\User($request))->checkRights('useraccount');
        $resolveReferences = Validator::param('resolveReferences')->isNumber()->setDefault(0)->getValue();
        $input = Validator::input()->isJson()->assertValid()->getValue();

        $entity = new Entity($input);
        $this->testEntity($entity, $input);

        $message = Response\Message::create($request);
        $message->data = (new Query())->writeEntity($entity, $resolveReferences);

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
        return $response;
    }

    protected function testEntity($entity, $input)
    {
        try {
            $entity->testValid('de_DE', 1);
        } catch (\Exception $exception) {
            $exception->data['input'] = $input;
            throw $exception;
        }
    }
}
