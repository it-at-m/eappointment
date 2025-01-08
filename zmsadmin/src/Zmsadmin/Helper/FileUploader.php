<?php

/**
 *
 * @package Events\Xmas
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */

namespace BO\Zmsadmin\Helper;

use BO\Zmsentities\Mimepart;

class FileUploader
{
    public $imageData;

    protected $allowedTypes = array('image/gif','image/jpeg','image/png','image/svg+xml');

    protected $files = null;

    protected $uploadFile = null;

    protected $mediaType = '';

    protected $request = null;

    public function __construct(\Psr\Http\Message\RequestInterface $request, $imageName)
    {
        $this->request = $request;
        $this->imageData = $this->createImage($imageName);
    }

    public function writeUploadToScope($entityId)
    {
        $this->imageData = \App::$http->readPostResult(
            '/scope/' . $entityId . '/imagedata/calldisplay/',
            $this->imageData
        )->getEntity();
        return $this;
    }

    public function writeUploadToCluster($entityId)
    {
        $this->imageData = \App::$http->readPostResult(
            '/cluster/' . $entityId . '/imagedata/calldisplay/',
            $this->imageData
        )->getEntity();
        return $this;
    }

    protected function getUploadedFile($imageName)
    {
        $files = $this->request->getUploadedFiles();
        return (isset($files[$imageName])) ? $files[$imageName] : false;
    }

    protected function createImage($imageName)
    {
        $image = null;
        $this->uploadFile = $this->getUploadedFile($imageName);
        if ($this->uploadFile && $this->uploadFile->getError() === UPLOAD_ERR_OK) {
            $this->mediaType = $this->uploadFile->getClientMediaType();
            if (in_array($this->mediaType, $this->allowedTypes)) {
                $image = new Mimepart();
                $image->mime = $this->mediaType;
                $image->base64 = true;
                $data = file_get_contents($this->uploadFile->getFilePath());
                $image->content = base64_encode($data);
            } else {
                throw new \Exception('Wrong Mediatype given, use gif, jpg, svg or png');
            }
        }
        return $image;
    }
}
