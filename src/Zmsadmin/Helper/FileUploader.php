<?php
/**
 *
 * @package Events\Xmas
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */
namespace BO\Zmsadmin\Helper;

use \BO\Zmsentities\MailPart as Image;

class FileUploader
{
    public $imageData;

    protected static $allowedTypes = array('image/gif','image/jpeg','image/png');

    protected static $files = null;

    protected static $uploadFile = null;

    protected static $mediaType = '';

    public function __construct($request, $entityId, $source = 'scope', $task = 'uploadCallDisplayImage')
    {
        $this->imageData = static::createImage($request, $task);
        if ($this->imageData && 'scope' == $source && $task = 'uploadCallDisplayImage') {
            $this->imageData = \App::$http->readPostResult(
                '/scope/'. $entityId .'/imagedata/calldisplay/',
                $this->imageData
            )->getEntity();
        }
        if ($this->imageData && 'cluster' == $source && $task = 'uploadCallDisplayImage') {
            $this->imageData = \App::$http->readPostResult(
                '/cluster/'. $entityId .'/imagedata/calldisplay/',
                $this->imageData
            )->getEntity();
        }
    }

    public function getEntity()
    {
        return $this->imageData;
    }

    protected static function createImage($request, $task)
    {
        $image = null;
        self::$files = $request->getUploadedFiles();
        self::$uploadFile = self::$files[$task];
        if (self::$uploadFile && self::$uploadFile->getError() === UPLOAD_ERR_OK) {
            self::$mediaType = self::$uploadFile->getClientMediaType();
            if (in_array(self::$mediaType, self::$allowedTypes)) {
                $image = new Image();
                $type = explode('/', self::$mediaType);
                $image->mime = end($type);
                $image->base64 = true;
                $data = file_get_contents(self::$uploadFile->file);
                $image->content = 'data:image/' . self::$mediaType . ';base64,' . base64_encode($data);
            } else {
                throw new \Exception('Wrong Mediatype given, use gif, jpg or png');
            }
        }
        return $image;
    }
}
