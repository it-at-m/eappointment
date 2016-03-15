<?php
/**
 * @package 115Mandant
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;

/**
  * Handle requests concerning services
  */
class Index extends BaseController
{
    /**
     * @return String
     */
    public static function render()
    {
        $base = \App::$slim->request->getScheme();
        $base .= '://';
        $base .= \App::$slim->request->getHost();
        $base .= \App::$slim->request->getRootUri();
        $schema = [
            '$schema' => "http://json-schema.org/draft-04/schema#",
            'meta' => [
                'type' => 'object',
                '$ref' => "$base/doc/schema/metaresult.json",
            ],
            'swagger' => [
                'type' => 'object',
                '$ref' => "$base/doc/swagger.json",
            ],
            'data' => [
                'type' => 'array',
                'items' => [
                    'type' => 'object',
                    'oneOf' => [
                        '$ref' => "$base/doc/schema/calendar.json",
                    ]
                ]
            ],
        ];
        Render::json($schema);
    }
}
