<?php
/**
 *
 * @package zmsslim
 *
 */
namespace BO\Slim\Tests\Controller;

class Filter extends BaseController
{

    /**
     * @return String
     */
    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        return \BO\Slim\Render::withHtml(
            $response,
            'dldb/filtertest.twig',
            array()
        );
    }
}
