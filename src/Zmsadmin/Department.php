<?php
/**
 * @package 115Mandant
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

use BO\Zmsentities\Department as Entity;
use BO\Mellon\Validator;
use \BO\Zmsentities\Schema\Schema;

/**
  * Handle requests concerning services
  *
  */
class Department extends BaseController
{
    /**
     * @return String
     */

    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        $workstation = \App::$http->readGetResult('/workstation/')->getEntity();
        $confirm_success = $request->getAttribute('validator')->getParameter('confirm_success')->isString()->getValue();
        $entityId = Validator::value($args['id'])->isNumber()->getValue();
        $entity = \App::$http->readGetResult('/department/'. $entityId .'/', ['resolveReferences' => 1])->getEntity();

        if (!$entity->hasId()) {
            return \BO\Slim\Render::withHtml($response, 'page/404.twig', array());
        }

        $input = $request->getParsedBody();
        if (array_key_exists('save', (array) $input)) {
            $input = $this->cleanupLinks($input);
            $entity =  new Entity($input);
            $entity->id = $entityId;
            $entity->dayoff = $entity->getDayoffList()->withTimestampFromDateformat();
            $entity = \App::$http->readPostResult(
                '/department/'. $entity->id .'/',
                $entity
            )->getEntity();
            return \BO\Slim\Render::redirect('department', ['id' => $entityId], [
                'confirm_success' => \App::$now->getTimeStamp()
            ]);
        }

        $response = \BO\Slim\Render::withLastModified($response, time(), '0');
        return \BO\Slim\Render::withHtml(

            $response,
            'page/department.twig',
            array(
                'title' => 'Standort',
                'workstation' => $workstation,
                'department' => (new Schema($entity))->toSanitizedArray(),
                'menuActive' => 'owner',
                'confirm_success' => $confirm_success,
            )
        );
    }

    protected function cleanupLinks(array $input)
    {
        $links = $input['links'];

        $input['links'] = array_filter($links, function ($link) {
            return !($link['name'] === '' && $link['url'] == '');
        });

        return $input;
    }
}
