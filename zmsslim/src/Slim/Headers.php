<?php
/**
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

declare(strict_types=1);

namespace BO\Slim;

class Headers extends \Slim\Psr7\Headers
{
    public const MEDIA_TYPE_APPLICATION_XML = 'application/xml';
    public const MEDIA_TYPE_APPLICATION_JSON = 'application/json';
    public const MEDIA_TYPE_TEXT_XML = 'text/xml';
    public const MEDIA_TYPE_TEXT_HTML = 'text/html';
    public const MEDIA_TYPE_TEXT_PLAIN = 'text/plain';
}
