<?php

 namespace BO\Zmsadmin\Helper;

 use BO\Zmsadmin\BaseController;
 use Twig\Error\LoaderError;
 use Twig\Error\RuntimeError;
 use Twig\Error\SyntaxError;

class MailTemplateDummyPreview extends BaseController
{
    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {

        $mailStatus = $args['mailStatus'];
        $workstation = \App::$http->readGetResult('/workstation/', ['resolveReferences' => 1])->getEntity();
        $providerId = $workstation->scope['provider']['id'];
        $result = \App::$http->readGetResult("/preview-mailtemplates/$mailStatus/$providerId/", ['resolveReferences' => 0]);
        $data = json_decode($result->getResponse()->getBody()->getContents())->data;

        try {
            // Twig-Template rendern
            return \BO\Slim\Render::withHtml(
                $response,
                'block/page/dummypreview.twig',
                array(
                    'title' => 'Preview',
                    'previewHtml' => $data->previewHtml,
                    'previewPlain' => $data->previewPlain
                )
            );
        } catch (LoaderError | RuntimeError | SyntaxError $e) {
            return \BO\Slim\Render::withHtml(
                $response,
                'block/page/dummypreviewerror.twig',
                array(
                    'title' => 'Fehler',
                    'errorMessage' => 'Das Template konnte nicht gerendert werden: ' . $e->getMessage(),
                )
            );
        }
    }
}
