<?php

 namespace BO\Zmsadmin\Helper;

 use BO\Zmsadmin\BaseController;
 use BO\Zmsadmin\Helper\MailTemplateArrayProvider;
 use BO\Zmsentities\Config;
 use BO\Zmsentities\Mail;
 use BO\Zmsentities\Process;
 use BO\Zmsentities\Request;

class MailTemplatePreviewMail extends BaseController
{
    #[\Override]
    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {

        $mailStatus = $args['mailStatus'];
        $validator = $request->getAttribute('validator');
        $input = $validator->getInput()->isJson()->getValue();

        $mainProcessExample = ((new Process())->getExample());
        $mainProcessExample->id = 987654;
        $dateTime = new \DateTimeImmutable("2015-10-23 08:00:00", new \DateTimeZone('Europe/Berlin'));
        $mainProcessExample->getFirstAppointment()->setDateTime($dateTime);
        $mainProcessExample->requests[] = (new Request())->getExample();

        $templates = array();

        foreach ($input['templates'] as $template) {
            $templates[$template['templateName']] = $template['templateContent'];
        };

        $templateProvider = new MailTemplateArrayProvider();
        $templateProvider->setTemplates($templates);

        $config = new Config();

        try {
            $mail = (new Mail())
            ->setTemplateProvider($templateProvider)
            ->toResolvedEntity($mainProcessExample, $config, $mailStatus);
        } catch (\Exception $e) {
            return \BO\Slim\Render::withJson(
                $response,
                array(
                    'error' => $e->getMessage()
                )
            );
        }

        return \BO\Slim\Render::withJson(
            $response,
            array(
                'previewHtml' => $mail->getHtmlPart(),
                'previewPlain' => $mail->getPlainPart()
            )
        );
    }
}
