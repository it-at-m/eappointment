<?php
/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

use BO\Zmsentities\Collection\DayOffList as Collection;
use BO\Mellon\Validator;

/**
 * Handle requests concerning services
 *
 */
class DayoffByYear extends BaseController
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
        $workstation = \App::$http->readGetResult('/workstation/')->getEntity();
        $year = Validator::value($args['year'])->isNumber()->getValue();
        $entity = \App::$http->readGetResult('/dayoff/'. $year .'/')->getCollection();

        $input = $request->getParsedBody();
        $input = $input['daysOff'];
        if (array_key_exists('save', (array) $input)) {
            try {
                $entity = new Collection($input);
                $entity = \App::$http->readPostResult(
                    '/dayoff/'. $year .'/',
                    $entity
                )->getCollection();
            } catch (\Exception $exception) {
                return Helper\Render::error($request, $exception);
            }
        }

        return \BO\Slim\Render::withHtml(
            $response,
            'page/dayoffByYear.twig',
            array(
                'title' => 'Allgemein gültige Feiertage',
                'year' => '2016',
                'menuActive' => 'dayoff',
                'workstation' => $workstation,
                'dayoffList' => $entity->getArrayCopy()
            )
        );
    }
}
