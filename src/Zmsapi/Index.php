<?php
/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;

class Index extends BaseController
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
        $uri = $request->getUri();
        $base = $uri->getScheme();
        $base .= '://';
        $base .= $uri->getHost();
        $base .= $uri->getBasePath();
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

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $schema, 200);
        return $response;
    }
}
