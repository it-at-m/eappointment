<?php
/**
 *
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */
namespace BO\Zmsticketprinter\Helper;

class DialogHandler extends \BO\Zmsticketprinter\BaseController
{

    /**
     * @SuppressWarnings(UnusedFormalParameter)
     * @return String
     */
    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        $validator = $request->getAttribute('validator');
        $template = $validator->getParameter('template')->isString()->getValue();
        $parameter = $validator->getParameter('parameter')->isArray()->getValue();
        $parameter = ($parameter) ? $parameter : array();

        return \BO\Slim\Render::withHtml(
            $response,
            'element/helper/dialog/'. $template .'.twig',
            $parameter
        );
    }
}
