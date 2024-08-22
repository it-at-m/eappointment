<?php

 namespace BO\Zmsadmin\Helper;

 use BO\Zmsadmin\BaseController;

 class MailTemplateDummyConfirmation extends BaseController
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
        
        //print_r($result->getResponse()->getBody()->getContents());
        //print_r($data);
        //print_r($data->previewHtml);
        //die('123410549261');
        
        // Twig-Template rendern
        return \BO\Slim\Render::withHtml(
            $response,
            'block/page/dummyconfirmation.twig',
            array(
                'title' => 'Preview',
                'previewHtml' => $data->previewHtml,
                'previewPlain' => $data->previewPlain
            )
        );
    }
 }