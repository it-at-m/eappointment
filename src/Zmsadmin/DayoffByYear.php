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
        $collection = \App::$http->readGetResult('/dayoff/'. $year .'/')->getCollection();

        $input = $request->getParsedBody();
        if (array_key_exists('save', (array) $input)) {
            $data = $input['daysOff'];
            $collection = new Collection($data);
            $collection = \App::$http->readPostResult(
                '/dayoff/'. $year .'/',
                $collection->toDateWithTimestamp()
            )->getCollection();
        }

        $response = \BO\Slim\Render::withLastModified($response, time(), '0');
        return \BO\Slim\Render::withHtml(
            $response,
            'page/dayoffByYear.twig',
            array(
                'title' => 'Allgemein gültige Feiertage',
                'year' => '2016',
                'menuActive' => 'dayoff',
                'workstation' => $workstation,
                'dayoffList' => array_values($collection->sortByCustomKey('date')->getArrayCopy())
            )
        );
    }
}
