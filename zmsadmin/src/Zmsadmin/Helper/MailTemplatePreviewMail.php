<?php

 namespace BO\Zmsadmin\Helper;

 use BO\Zmsadmin\BaseController;
 use BO\Zmsadmin\Helper\MailTemplateArrayProvider;
 use Twig\Error\LoaderError;
 use Twig\Error\RuntimeError;
 use Twig\Error\SyntaxError;
 use Twig\Environment;
 use Symfony\Bridge\Twig\Extension\TranslationExtension;
 use Twig\Extra\Intl\IntlExtension;

 class MailTemplatePreviewMail extends BaseController
 {
    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {

        $mailStatus = $args['mailStatus'];
        //$workstation = \App::$http->readGetResult('/workstation/', ['resolveReferences' => 1])->getEntity();
        //$providerId = $workstation->scope['provider']['id'];
        //$result = \App::$http->readGetResult("/preview-mailtemplates/$mailStatus/$providerId/", ['resolveReferences' => 0]);
        //$data = json_decode($result->getResponse()->getBody()->getContents())->data;
        $validator = $request->getAttribute('validator');
        $input = $validator->getInput()->isJson()->getValue();
        
        $mainProcessExample = ((new \BO\Zmsentities\Process)->getExample());
        $mainProcessExample->id = 987654;
        $dateTime = new \DateTimeImmutable("2015-10-23 08:00:00", new \DateTimeZone('Europe/Berlin'));
        $mainProcessExample->getFirstAppointment()->setDateTime($dateTime);
        $mainProcessExample->requests[] = (new \BO\Zmsentities\Request())->getExample();

        $templates = array();

        foreach ($input['templates'] as $template) {
            $templates[$template['templateName']] = $template['templateContent'];
        };

        $templateProvider = new MailTemplateArrayProvider();
        $templateProvider->setTemplates($templates);

        $config = new \BO\Zmsentities\Config();

        try {
            $mail = (new \BO\Zmsentities\Mail())
            ->setTemplateProvider($templateProvider)
            ->toResolvedEntity($mainProcessExample, $config, $mailStatus);
        }
        catch( \Exception $e) {
            return \BO\Slim\Render::withJson(
                $response,
                array(
                    'error'=>$e->getMessage()
                )
            );
        }

        return \BO\Slim\Render::withJson(
            $response,
            array(
                //'5'=>'hallo',
                //'input'=>$input,
                'previewHtml'=>$mail->getHtmlPart(),
                'previewPlain'=>$mail->getPlainPart()
            )
        );

    }
 }