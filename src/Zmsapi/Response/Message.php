<?php

namespace BO\Zmsapi\Response;

/**
 * example class to generate a response
 */
class Message implements \JsonSerializable
{
    /**
     * @var \BO\Zmsentities\Metaresult $meta
     */
    public $meta = null;

    /**
     * @var Mixed $data
     */
    public $data = null;

    public static function create()
    {
        $message = new self();
        $message->meta = new \BO\Zmsentities\Metaresult();
        $message->meta->error = false;
        $message->setUpdateMetaData();
        return $message;
    }

    public function setUpdateMetaData()
    {
        $this->meta->generated = date('c');
        $this->meta->server = \App::IDENTIFIER;
        return $this;
    }

    public function jsonSerialize()
    {
        $message = [
            "meta" => $this->meta,
            "data" => $this->data,
        ];
        return $message;
    }
}
