<?php
namespace BO\Dldb\Plz;

/**
 * Translate a german zip code into wgs84 coordinates
 */
class Coordinates
{
    /**
     * Store data
     * @var Array $data
     */
    public $data = array();

    /**
     * @param Int $plz
     */
    public function getLatLon($plz)
    {
        if (array_key_exists($plz, $this->data)) {
            return $this->data[$plz];
        }
        return false;
    }

    public static function zip2LatLon($plz)
    {
        $coordinates = new self();
        return $coordinates->getLatLon($plz);
    }

    public function loadData($file = null)
    {
        if (null === $file) {
            $file = __DIR__ . DIRECTORY_SEPARATOR . 'plz_geodb.json';
        }
        if (!is_readable($file) || !is_file($file)) {
            throw new \Exception("Cannot read file $file");
        }
        $this->data = json_decode(file_get_contents($file), 1);
    }

    /**
     * @param String $file (optional) Path to json file with data
     *
     */
    public function __construct($file = null)
    {
        $this->loadData($file);
    }
}
