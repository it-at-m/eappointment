<?php
/**
 *
 * @package Zmsappointment
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */
namespace BO\Zmsticketprinter;

/**
 * Handle requests concerning services
 */
class Index extends BaseController
{

    /**
     * @SuppressWarnings(UnusedFormalParameter)
     * @return String
     */
    public function __invoke(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        return \BO\Slim\Render::withHtml(
            $response,
            'page/index.twig',
            array(
                'debug' => \App::DEBUG,
                'title' => 'Wartennumer ziehen',
            )
        );
    }
}
