<?php
/**
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

declare(strict_types=1);

namespace BO\Zmsentities\Helper;

use BO\Zmsentities\Client;
use BO\Zmsentities\Config;
use BO\Zmsentities\Mail;
use BO\Zmsentities\Mimepart;

/**
 * Mailing Helper (certain kind of Messaging)
 */
class Mailing extends Messaging
{
    /** @var Config */
    private $config;

    /**
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    public function createProcessListSummaryMail($processList, Client $client): Mail
    {
        $mailInstance = new Mail();
        $twigInstance = self::twigView();
        $htmlContent  = new Mimepart();
        $htmlContent->mime = 'text/html';
        $htmlContent->content = $twigInstance->render(
            'messaging/mail_processlist_summary.twig',
            array(
                'client' => $client,
                'processList' => $processList,
                'config' => $this->config,
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