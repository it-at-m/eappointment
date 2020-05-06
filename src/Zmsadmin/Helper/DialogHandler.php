<?php
/**
 *
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */
namespace BO\Zmsadmin\Helper;

class DialogHandler extends \BO\Zmsadmin\BaseController
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

        if (isset($parameter['id'])) {
            $result = \App::$http->readGetResult('/process/'. $parameter['id'] .'/');
            if ($result) {
                $parameter['id'] = $result->getEntity()->queue->number;
            }
        }

        return \BO\Slim\Render::withHtml(
            $response,
            'element/helper/dialog/'. $template .'.twig',
            $parameter
        );
    }
}
