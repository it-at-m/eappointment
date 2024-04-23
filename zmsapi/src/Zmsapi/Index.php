<?php
/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use BO\Slim\Request;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class Index extends BaseController
{
    /**
     * @SuppressWarnings(Param)
     *
     * @param RequestInterface|Request $request
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function readResponse(
        RequestInterface $request,
        ResponseInterface $response,
        array $args
    ) {
        $base = $request->getBaseUrl();
        error_log($base);
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

        return Render::withJson($response, $schema, 200);
        ;
    }
}
