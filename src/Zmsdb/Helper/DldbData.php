<?php
namespace BO\Zmsdb\Helper;

/**
 *
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 *            A class to extends request and provider data from clientdldb
 */
class DldbData
{

    public static $dataPath = '';
    public static $dldbData = null;

    /**
     * Returns dldb data repository
     *
     * @param string $locale language
     * @return \BO\Dldb\FileAccess
     */
    public static function getDataRepository()
    {
        // configure clientdldb data access
        if (null === self::$dldbData) {
            self::$dldbData = new \BO\Dldb\FileAccess();
            self::$dldbData->loadFromPath(self::$dataPath);
        }
        return self::$dldbData;
    }

    /**
     * Returns service data object from dldb client
     *
     * @param string $locale language
     * @param integer $requestId number of request (service in dldb)
     *
     * @return \BO\Dldb\Entity\Service
     */
    public static function readExtendedRequestData($source, $requestId, $locale = 'de')
    {
        $data = null;
        if ($source == 'dldb') {
            $data = self::getDataRepository()->fromService($locale)->fetchId($requestId);
        }
        return $data;
    }

    /**
     * Returns scope data object from dldb client
     *
     * @param string $locale language
     * @param integer $providerId number of provider (location in dldb)
     * @return \BO\Dldb\Entity\Location
     */
    public static function readExtendedProviderData($source, $providerId, $locale = 'de')
    {
        $data = null;
        if ($source == 'dldb') {
            $data = self::getDataRepository()->fromLocation($locale)->fetchId($providerId);
        }
        return $data;
    }
}
