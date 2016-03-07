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
        $this->setResponse($response);
    }

    /**
     * Parse response and the object values
     * @param \Psr\Http\Message\ResponseInterface $response
     * @return self
     */
    public function setResponse(\Psr\Http\Message\ResponseInterface $response)
    {
        $this->response = $response;
        $this->testMeta();
        $body = Validator::value((string)$response->getBody())->isJson();
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
    protected function testMeta()
    {
        $response = $this->response;
        $body = Validator::value((string)$response->getBody())->isJson();
        if ($body->hasFailed()) {
            throw new Exception(
                'API-Call failed, JSON parsing with error: ' . implode('; ', $body->getMessages())
                    . ' - Snippet: ' .substr((string)$response->getBody(), 0, 255) . '...',
                $response,
                $this->request
            );
        }
        $result = $body->getValue();
        if (!array_key_exists("meta", $result)) {
            throw new Exception(
                'Missing "meta" value on result, API-Call failed.',
                $response,
                $this->request
            );
        }
        $entity = Factory::create($result['meta'])->getEntity();
        if ($entity->error == true) {
            throw new Exception(
                'API-Error: ' . $entity->message,
                $response,
                $this->request
            );
        }
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
        return reset($this->data);
    }
    /**
     * Description
     *
     * @return Array (\BO\Zmsentities\Schema\Entity)
     */
    public function getData()
    {
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
