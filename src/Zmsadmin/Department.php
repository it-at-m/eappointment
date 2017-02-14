<?php
/**
 * @package 115Mandant
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

use BO\Zmsentities\Department as Entity;
use BO\Mellon\Validator;

/**
  * Handle requests concerning services
  *
  */
class Department extends BaseController
{
    /**
     * @return String
     */

    public function __invoke(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        $workstation = \App::$http->readGetResult('/workstation/')->getEntity();
        $entityId = Validator::value($args['id'])->isNumber()->getValue();
        $entity = \App::$http->readGetResult('/department/'. $entityId .'/', ['resolveReferences' => 1])->getEntity();

        if (!$entity->hasId()) {
            return \BO\Slim\Render::withHtml($response, 'page/404.twig', array());
        }

        $input = $request->getParsedBody();
        if (array_key_exists('save', (array) $input)) {
            $input = $this->cleanupLinks($input);
            $entity = new Entity($input);
            $entity->id = $entityId;
            $entity->dayoff = $entity->getDayoffList()->withTimestampFromDateformat();
            $entity = \App::$http->readPostResult(
                '/department/'. $entity->id .'/',
                $entity
            )->getEntity();
        }

        return \BO\Slim\Render::withHtml(

            $response,
            'page/department.twig',
            array(
                'title' => 'Standort',
                'workstation' => $workstation,
                'department' => $entity->getArrayCopy(),
                'menuActive' => 'owner'
            )
        );
    }

    protected function cleanupLinks(array $input)
    {
        $links = $input['links'];

        $input['links'] = array_filter($links, function ($link) {
            return !($link['name'] === '' && $link['link'] == '');
        });

        return $input;
    }
}
