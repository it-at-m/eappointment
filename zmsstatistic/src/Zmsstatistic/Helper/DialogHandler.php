<?php

/**
 *
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */

namespace BO\Zmsstatistic\Helper;

use BO\Slim\Render;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class DialogHandler extends \BO\Zmsstatistic\BaseController
{
    protected bool $withAccess = false;

    /**
     * @SuppressWarnings(UnusedFormalParameter)
     * @return ResponseInterface
     */
    #[\Override]
    public function readResponse(
        RequestInterface $request,
        ResponseInterface $response,
        array $args
    ): mixed {
        /** @var \Psr\Http\Message\ServerRequestInterface $request */
        $validator = $request->getAttribute('validator');
        $template = $validator->getParameter('template')->isString()->getValue();
        $parameter = $validator->getParameter('parameter')->isArray()->getValue();
        $parameter = $parameter !== null ? $parameter : array();

        return Render::withHtml(
            $response,
            'element/helper/dialog/' . $template . '.twig',
            $parameter
        );
    }
}
