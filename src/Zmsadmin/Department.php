<?php
/**
 * @package 115Mandant
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

use BO\Zmsentities\Department as Entity;
use BO\Mellon\Validator;
use \BO\Zmsentities\Schema\Schema;

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
        $workstation = \App::$http->readGetResult('/workstation/', ['resolveReferences' => 1])->getEntity();
        $success = $request->getAttribute('validator')->getParameter('success')->isString()->getValue();
        $entityId = Validator::value($args['id'])->isNumber()->getValue();
        $entity = \App::$http->readGetResult('/department/'. $entityId .'/', ['resolveReferences' => 1])->getEntity();
        $organisation = \App::$http->readGetResult('/department/' . $entityId . '/organisation/')->getEntity();
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
                'success' => 'department_saved'
            ]);
        }

        return \BO\Slim\Render::withHtml(
            $response,
            'page/department.twig',
            array(
                'title' => 'Standort',
                'workstation' => $workstation,
                'organisation' => $organisation,
                'department' => (new Schema($entity))->toSanitizedArray(),
                'menuActive' => 'owner',
                'success' => $success,
            )
        );
    }

    protected function cleanupLinks(array $input)
    {
        $links = $input['links'];

        $input['links'] = array_filter($links, function ($link) {
            return !($link['name'] === '' && $link['url'] == '');
        });

        foreach ($input['links'] as $index => $link) {
            $input['links'][$index]['target'] = ($link['target']) ? 1 : 0;
        }

        return $input;
    }
}
