<?php
/**
 *
 * @package Events\Xmas
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */
namespace BO\Zmsadmin\Helper;

use \BO\Zmsentities\MailPart as Entity;

class FileUploader
{
    protected $allowedTypes = array('image/gif','image/jpeg','image/png');

    public function __construct($request)
    {
        $files = $request->getUploadedFiles();
        $callDisplayFile = $files['uploadCallDisplay'];
        if ($callDisplayFile->getError() === UPLOAD_ERR_OK) {
            $mediaType = $callDisplayFile->getClientMediaType();
            if (in_array($mediaType, $this->allowedTypes)) {
                $entity = new Entity();
                $type = explode('/', $mediaType);
                $entity->mime = end($type);
                $entity->base64 = true;
                $data = file_get_contents($callDisplayFile->file);
                $entity->content = 'data:image/' . $mediaType . ';base64,' . base64_encode($data);
            } else {
                throw new \Exception('Wrong Mediatype given, use gif, jpg or png');
            }
        }
        return $entity;
    }
}
