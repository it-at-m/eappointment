<?php
/**
 *
 * @package zmsslim
 *
 */
namespace BO\Slim\Tests\Controller;

class Post extends BaseController
{
    /**
     * {@inheritDoc}
     */
    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {

        $input = 'failed';
        if ($request->getMethod() === 'POST') {
            $input = (string)$request->getBody();
            $input = json_decode($input, 1);
        }
        
        return \BO\Slim\Render::withHtml(
            $response,
            'unittest.twig',
            array(
                'message' => $input['message'],
                'title' => 'POST test title'
            )
        );
    }
}
