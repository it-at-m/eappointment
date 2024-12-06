<?php

namespace BO\Zmsclient;

use BO\Mellon\Valid;
use BO\Mellon\Validator;
use BO\Zmsentities\Metaresult;
use BO\Zmsentities\Collection\Base as BaseCollection;
use BO\Zmsentities\Schema\Entity;
use BO\Zmsentities\Schema\Factory;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Handle default response
 */
class Result
{
    /**
     * @var ResponseInterface
     */
    protected $response;

    /**
     * @var RequestInterface|null
     */
    protected $request;

    /**
     * @var Entity[]|null
     */
    protected $data = null;

    /**
     * @var Metaresult|null
     */
    protected $meta = null;

    /**
     * @param ResponseInterface $response
     * @param RequestInterface|null $request (optional) reference for better error messages
     */
    public function __construct(
        ResponseInterface $response,
        RequestInterface $request = null
    ) {
        $this->request = $request;
        $this->response = $response;
    }

    /**
     * Parse response and the object values
     * @param ResponseInterface $response
     * @return self
     */
    public function setResponse(ResponseInterface $response)
    {
        $body = Validator::value((string) $response->getBody())->isJson();
        $this->testMeta($body, $response);
        $result = $body->getValue();
        if (array_key_exists("data", $result)) {
            $this->setData($result['data']);
        }
        return $this;
    }

    /**
     * Test meta data on errors
     *
     * @param Valid $body
     * @param ResponseInterface $response
     * @throws Exception
     */
    protected function testMeta($body, ResponseInterface $response)
    {
        if ($body->hasFailed()) {
            $content = (string) $response->getBody();
            throw new Exception\ApiFailed(
                'API-Call failed, JSON parsing with error: ' . $body->getMessages()
                . ' - Snippet: ' . substr(\strip_tags($content), 0, 2000) . '[...]',
                $response,
                $this->request
            );
        }
        $result = $body->getValue();
        if (!$result || !array_key_exists("meta", $result)) {
            throw new Exception(
                'Missing "meta" value on result, API-Call failed.',
                $response,
                $this->request
            );
        }
        $entity = Factory::create($result['meta'])->getEntity();
        $this->meta = $entity;
        if ($entity->error == true) {
            $message = $entity->message ? $entity->message : $entity->exception;
            $exception = new Exception(
                'API-Error: ' . $message,
                $response,
                $this->request
            );
            if (isset($entity->trace)) {
                $exception->trace = $entity['trace'];
            }
            $exception->originalMessage = $entity->message;
            if (array_key_exists('data', $result)) {
                $exception->data = $result['data'];
            }
            if (isset($entity->exception)) {
                $exception->template = $entity->exception;
            }
            throw $exception;
        }
    }

    /**
     * Get the origin request
     *
     * @return RequestInterface|null
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Get the origin response
     *
     * @return ResponseInterface
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Get the origin response
     *
     * @return bool
     */
    public function isStatus($statuscode)
    {
        return $this->getResponse()->getStatusCode() == $statuscode;
    }

    /**
     * Description
     *
     * @return Entity|null|false
     */
    public function getEntity()
    {
        $entity = null;
        if (null !== $this->getData()) {
            $data = $this->getData();
            $entity = reset($data);
        }
        return $entity;
    }

    /**
     * Description
     *
     * @return BaseCollection|null
     */
    public function getCollection()
    {
        $collection = null;
        $entity = $this->getEntity();
        if (null !== $entity) {
            $class = get_class($entity);
            $alias = ucfirst(preg_replace('#^.*\\\#', '', $class));
            $className = "\\BO\\Zmsentities\\Collection\\" . $alias . "List";
            $collection = new $className($this->getData());
        }
        return $collection;
    }

    /**
     * Description
     *
     * @return Entity[]
     */
    public function getData()
    {
        if (null === $this->data) {
            $this->setResponse($this->response);
        }
        return $this->data;
    }

    /**
     * Description
     *
     * @return Metaresult|null
     */
    public function getMeta()
    {
        if (null === $this->meta) {
            $this->setResponse($this->response);
        }
        return $this->meta;
    }

    /**
     * Get the list of IDs from the data
     *
     * @return string
     */
    public function getIds()
    {
        $data = $this->getData();
        $idList = [];

        foreach ($data as $item) {
            if (is_object($item) && method_exists($item, 'getId')) {
                $idList[] = $item->getId();
            } elseif (is_array($item) && array_key_exists('id', $item)) {
                $idList[] = $item['id'];
            } else {
                throw new \UnexpectedValueException('Item is neither array nor object with getId() method');
            }
        }

        return implode(',', array_unique($idList));
    }

    /**
     * Set entity from response
     *
     * @param array $data
     *
     * @return self
     */
    public function setData(array $data)
    {
        if (array_key_exists('$schema', $data)) {
            $data = [$data];
        }
        foreach ($data as $entityData) {
            if (!array_key_exists('$schema', $entityData)) {
                $entityData['$schema'] = $data[0]['$schema'];
            }
            $this->data[] = Factory::create($entityData)->getEntity();
        }
        return $this;
    }
}
