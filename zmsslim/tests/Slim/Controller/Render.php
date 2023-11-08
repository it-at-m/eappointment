<?php
/**
 *
 * @package zmsslim
 *
 */
namespace BO\Slim\Tests\Controller;

class Render extends BaseController
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
            'unittest.twig',
            array(
                'message' => 'unit tests are an awesome possibility to test code',
                'title' => 'Title for my unit testing'
            )
        );
    }
}
