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

    /**
     * @var \Psr\Http\Message\RequestInterface $request;
     *
     */
    protected $request = null;

    protected function __construct(\Psr\Http\Message\RequestInterface $request)
    {
        $this->request = $request;
        $this->meta = new \BO\Zmsentities\Metaresult();
        $this->meta->error = false;
        $this->setUpdateMetaData();
    }


    public static function create(\Psr\Http\Message\RequestInterface $request)
    {
        $message = new self($request);
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
        $schema = $this->request->getUri()->getScheme();
        $schema .= '://';
        $schema .= $this->request->getUri()->getHost();
        $schema .= \App::$slim->urlFor('index');
        $message = [
            '$schema' => $schema,
            "meta" => $this->meta,
            "data" => $this->data,
        ];
        if (\App::DEBUG) {
            $message['profiler'] = [
                'DB_RO' => \BO\Zmsdb\Connection\Select::getReadConnection()->getProfiler()->getProfiles(),
                'DB_RW' => \BO\Zmsdb\Connection\Select::getWriteConnection()->getProfiler()->getProfiles(),
            ];
        }
        return $message;
    }
}
