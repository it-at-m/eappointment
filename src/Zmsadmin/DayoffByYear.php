<?php
/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

use BO\Zmsentities\Collection\DayoffList as Collection;
use BO\Mellon\Validator;

class DayoffByYear extends BaseController
{

    /**
     *
     * @return String
     */
    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        $workstation = \App::$http->readGetResult('/workstation/', ['resolveReferences' => 1])->getEntity();
        $success = $request->getAttribute('validator')->getParameter('success')->isString()->getValue();
        $year = Validator::value($args['year'])->isNumber()->getValue();
        $collection = \App::$http->readGetResult('/dayoff/'. $year .'/')->getCollection();
        $dayOffList = ($collection) ? array_values($collection->sortByCustomKey('date')->getArrayCopy()) : null;

        $updated = false;
        $input = $request->getParsedBody();
        if (array_key_exists('save', (array) $input)) {
            $data = (array_key_exists('dayoff', $input)) ? $input['dayoff'] : [];
            $collection = (new Collection($data))->withTimestampFromDateformat();
            \App::$http->readPostResult('/dayoff/'. $year .'/', $collection);
            $updated = true;
            return \BO\Slim\Render::redirect('dayoffByYear', ['year' => $year], [
                'success' => 'dayoff_saved'
            ]);
        }

        return \BO\Slim\Render::withHtml(
            $response,
            'page/dayoffByYear.twig',
            array(
                'title' => 'Allgemein gültige Feiertage',
                'year' => $year,
                'updated' => $updated,
                'menuActive' => 'dayoff',
                'workstation' => $workstation,
                'success' => $success,
                'dayoffList' => $dayOffList
            )
        );
    }
}
