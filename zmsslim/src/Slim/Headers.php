<?php

/**
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

declare(strict_types=1);

namespace BO\Slim;

class Headers extends \Slim\Psr7\Headers
{
    public const string MEDIA_TYPE_APPLICATION_XML = 'application/xml';
    public const string MEDIA_TYPE_APPLICATION_JSON = 'application/json';
    public const string MEDIA_TYPE_TEXT_XML = 'text/xml';
    public const string MEDIA_TYPE_TEXT_HTML = 'text/html';
    public const string MEDIA_TYPE_TEXT_PLAIN = 'text/plain';
}
