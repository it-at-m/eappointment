<?php
/**
 * @package   BO Slim
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsentities\Helper;

/**
  * Extension for Twig
  *
  *  @SuppressWarnings(PublicMethod)
  *  @SuppressWarnings(TooManyMethods)
  */
class TwigExtension extends \Twig_Extension
{
    public function getName()
    {
        return 'boZmsEntities';
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('toGermanDateFromTs', array($this, 'toGermanDateFromTs')),
        );
    }

    public function toGermanDateFromTs($timestamp)
    {
        $datetime = \DateTime::createFromFormat('U', $timestamp);
        $datetime->setTimezone(new \DateTimeZone('Europe/Berlin'));
        return array(
            'date' => strftime('%a. %d. %B %Y', $datetime->getTimestamp()),
            'time' => strftime('%H:%M Uhr', $datetime->getTimestamp())
        );
    }
}
