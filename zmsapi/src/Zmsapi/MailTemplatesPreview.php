<?php
/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Mellon\Validator;
use \BO\Zmsdb\Mail;
use \BO\Zmsdb\Config;
use \BO\Zmsdb\MailTemplates as MailTemplatesQuery;
use \BO\Zmsapi\Helper\User;

class MailTemplatesPreview extends BaseController
{
    /**
     * @SuppressWarnings(Param)
     * @return String
     */
    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        //(new Helper\User($request))->checkRights('superuser');

        //$providerId = $args['providerId'];
        
        //$config = (new MailTemplatesQuery())->readListByProvider($providerId);
        
        $mailStatus = $args['mailStatus'];
        $providerId = $args['providerId'];

        $mainProcessExample = ((new \BO\Zmsentities\Process)->getExample());
        $mainProcessExample->id = 987654;
        $mainProcessExample->scope->provider['id'] = $providerId;
        $dateTime = new \DateTimeImmutable("2015-10-23 08:00:00", new \DateTimeZone('Europe/Berlin'));
        $mainProcessExample->getFirstAppointment()->setDateTime($dateTime);
        $mainProcessExample->requests[] = (new \BO\Zmsentities\Request())->getExample();

        // Folgendes stammt aus zmsapi/ProcessConfirm.php
        $config = (new Config())->readEntity();
        $mail = (new \BO\Zmsentities\Mail())
            ->setTemplateProvider(new \BO\Zmsdb\Helper\MailTemplateProvider($mainProcessExample))
            ->toResolvedEntity($mainProcessExample, $config, $mailStatus);

        $message = Response\Message::create($request);
        $message->data = array('xy'=>'hallo','previewHtml'=>$mail->getHtmlPart(),'previewPlain'=>$mail->getPlainPart());

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message, $message->getStatuscode());

        return $response;
    }
}
