<?php

namespace BO\Zmsclient;

use \BO\Mellon\Validator;
use \BO\Zmsentities\Schema\Factory;

/**
 * Handle default response
 */
class Result
{
    /**
     * @var \Psr\Http\Message\ResponseInterface $response
     */
    protected $response;

    /**
     * @var \Psr\Http\Message\RequestInterface $request
     */
    protected $request;

    /**
     * @var Array $data Type \BO\Zmsentities\Schema\entity
     */
    protected $data = null;

    /**
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param \Psr\Http\Message\RequestInterface $request (optional) reference for better error messages
     */
    public function __construct(
        \Psr\Http\Message\ResponseInterface $response,
        \Psr\Http\Message\RequestInterface $request = null
    ) {
        $this->request = $request;
        $this->response = $response;
    }

    /**
     * Parse response and the object values
     * @param \Psr\Http\Message\ResponseInterface $response
     * @return self
     */
    public function setResponse(\Psr\Http\Message\ResponseInterface $response)
    {
        $body = Validator::value((string)$response->getBody())->isJson();
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
     * @throws Exception
     */
    protected function testMeta($body, \Psr\Http\Message\ResponseInterface $response)
    {
        if ($body->hasFailed()) {
            $content = (string)$response->getBody();
            throw new Exception\ApiFailed(
                'API-Call failed, JSON parsing with error: ' . $body->getMessages()
                    . ' - Snippet: ' .substr(\strip_tags($content), 0, 2000) . '[...]',
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
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Get the origin response
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Description
     *
     * @return \BO\Zmsentities\Schema\Entity
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
     * @return \BO\Zmsentities\Schema\Entity
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
     * @return Array (\BO\Zmsentities\Schema\Entity)
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
     * @return String
     */
    public function getIds()
    {
        $data = $this->getData();
        $idList = array();
        foreach ($data as $item) {
            if (array_key_exists('id', $item)) {
                $idList[] = $item['id'];
            }
        }
        return join(',', array_unique($idList));
    }

    /**
     * Set entity from response
     *
     * @param Array $data
     *
     * @return self
     */
    public function setData(array $data)
    {
        if (array_key_exists('$schema', $data)) {
            $data = [$data];
        }
        foreach ($data as $entityData) {
            $this->data[] = Factory::create($entityData)->getEntity();
        }
        return $this;
    }
}
