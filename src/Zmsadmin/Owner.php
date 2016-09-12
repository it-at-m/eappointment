<?php

/**
 *
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */
namespace BO\Zmsadmin;

use BO\Zmsentities\Owner as Entity;
use BO\Mellon\Validator;

class Owner extends BaseController
{

    /**
     *
     * @return String
     */
    public function __invoke(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {

        $entityId = Validator::value($args['id'])->isNumber()
            ->getValue();
        $entity = \App::$http->readGetResult('/owner/' . $entityId . '/')
            ->getEntity();

        if (! $entity->hasId()) {
            return \BO\Slim\Render::withHtml($response, 'page/404.twig', array ());
        }

        $input = $request->getParsedBody();
        if (array_key_exists('save', (array) $input)) {
            try {
                $entity = new Entity($input);
                $entity->id = $entityId;
                $entity = \App::$http->readPostResult('/owner/' . $entity->id . '/', $entity)
                    ->getEntity();
                // self::$errorHandler->success = 'owner_saved';
            } catch (\Exception $exception) {
                return Helper\Render::error($exception);
            }
        }

        return \BO\Slim\Render::withHtml(
            $response,
            'page/owner.twig',
            array (
                'title' => 'Kunde','workstation' => $this->workstation->getArrayCopy(),'menuActive' => 'owner',
                'owner' => $entity->getArrayCopy(),
                'workstation' => $this->workstation->getArrayCopy()
            )
        );
    }
}
