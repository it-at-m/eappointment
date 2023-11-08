<?php

namespace BO\Zmsapi\Response;

use \BO\Zmsdb\Connection\Select;

use \BO\Zmsclient\GraphQL\GraphQLInterpreter;

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
     * @var Mixed $data
     */
    public $statuscode = 200;


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
        $this->meta->exception = null;
        $this->setUpdatedMetaData();
    }


    public static function create(\Psr\Http\Message\RequestInterface $request)
    {
        $message = new self($request);
        return $message;
    }

    public function hasData()
    {
        return (
            ($this->data instanceof \BO\Zmsentities\Schema\Entity && $this->data->hasId())
            || ($this->data instanceof \BO\Zmsentities\Collection\Base && count($this->data))
            || (is_array($this->data) && count($this->data))
        );
    }

    protected function getJsonCompressLevel()
    {
        $jsonCompressLevel = 0;
        $validator = $this->request->getAttribute('validator');
        if ($validator) {
            $jsonCompressLevel = $validator->getParameter('compress')
                ->isNumber()
                ->setDefault(0)
                ->getValue();
        }
        $header = intval($this->request->getHeaderLine('X-JsonCompressLevel'));
        return ($header) ? $header : $jsonCompressLevel;
    }

    protected function getGraphQL()
    {
        $validator = $this->request->getAttribute('validator');
        if ($validator) {
            $gqlString = $validator->getParameter('gql')
                ->isString()
                ->getValue();
            if ($gqlString) {
                $graphqlInterpreter = new GraphQLInterpreter($gqlString);
                return $graphqlInterpreter;
            }
        }
        return null;
    }

    /**
     * Update meta-data
     * check for data in response
     *
     */
    public function setUpdatedMetaData()
    {
        $this->meta->generated = date('c');
        $version = \BO\Zmsapi\Helper\Version::getString();
        $this->meta->server = \App::IDENTIFIER . ' (' . $version . ')';
        if ($this->data !== null && $this->statuscode == 200 && !$this->hasData()) {
            $this->statuscode = 404;
            $this->meta->error = true;
            $this->meta->message = 'Not found';
        }
        return $this;
    }

    public function getStatuscode()
    {
        return $this->statuscode;
    }

    public function jsonSerialize()
    {
        $schema = $this->request->getUri()->getScheme();
        $schema .= '://';
        $schema .= $this->request->getUri()->getHost();
        $schema .= \App::$slim->urlFor('index');
        $jsonCompressLevel = $this->getJsonCompressLevel();
        
        if ($jsonCompressLevel > 0 && $this->data && is_object($this->data)) {
            $this->data->setJsonCompressLevel($jsonCompressLevel);
        }
        $graphqlInterpreter = $this->getGraphQL();
        if ($graphqlInterpreter) {
            $this->data = $graphqlInterpreter->setJson(json_encode($this->data));
        }
        $message = [
            '$schema' => $schema,
            "meta" => $this->meta,
            "data" => $this->data,
        ];
        if (\App::DEBUG) {
            // @codeCoverageIgnoreStart
            $message['profiler'] = $this->getProfilerData();
            // @codeCoverageIgnoreEnd
        }
        return $message;
    }

    /**
     * @codeCoverageIgnore
     *
     */
    protected function getProfilerData()
    {
        $profiler = null;
        if (Select::hasWriteConnection()) {
            $profiler = Select::getWriteConnection()->getProfiler();
        }
        if (Select::hasReadConnection()) {
            $profiler = Select::getReadConnection()->getProfiler();
        }

        if ($profiler === null) {
            return [];
        }

        $logger = $profiler->getLogger();

        if (method_exists($logger, 'getMessages')) {
            return $logger->getMessages();
        }

        return [];
    }
}
