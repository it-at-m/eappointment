<?php
/**
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

declare(strict_types=1);

namespace BO\Zmsdb\Helper;

use BO\Zmsdb\Config;
use BO\Zmsentities\Client;
use BO\Zmsentities\Helper\Messaging;
use BO\Zmsentities\Mail;
use BO\Zmsentities\Mimepart;

class MailHelper
{
    /**
     * @SuppressWarnings()
     */
    public function createProcessListSummaryMail($processList, Client $client): Mail
    {
        $mailInstance = new Mail();
        $twigInstance = Messaging::twigView();
        $htmlContent  = new Mimepart();
        $htmlContent->mime = 'text/html';
        $htmlContent->content = $twigInstance->render(
            'messaging/mail_processlist_summary_de.html.twig',
            array(
                'client' => $client,
                'processList' => $processList,
                'config' => (new Config)->readEntity(),
            )
        );

        $mailInstance->subject = 'Terminübersicht';
        $mailInstance->client  = $client;
        $mailInstance->process   = null;
        $mailInstance->multipart = [
            $htmlContent
        ];

        return $mailInstance;
    }
}