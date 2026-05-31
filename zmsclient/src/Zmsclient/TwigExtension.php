<?php

/**
 * @package   BO Slim
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsclient;

use BO\Mellon\Validator;
use Psr\Container\ContainerInterface;

/**
  * Extension for Twig and Slim
  *
  *  @SuppressWarnings(PublicMethod)
  *  @SuppressWarnings(TooManyMethods)
  */
class TwigExtension extends \Twig\Extension\AbstractExtension
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function getName(): string
    {
        return 'bozmsclientExtension';
    }

    public function getFunctions()
    {
        $safe = array('is_safe' => array('html'));
        return array(
            new \Twig\TwigFunction('dumpHttpLog', array($this, 'dumpHttpLog'), $safe),
        );
    }

    public function dumpHttpLog(): string
    {
        $output = '<h2>HTTP API-Log</h2>'
            . ' <p>For debugging: This log contains HTTP calls. <strong>DISABLE FOR PRODUCTION!</strong></p>';
        foreach (Http::$log as $entry) {
            if ($entry instanceof \Psr\Http\Message\RequestInterface) {
                $entry = $this->formatRequest($entry);
            } elseif ($entry instanceof \Psr\Http\Message\ResponseInterface) {
                $entry = $this->formatResponse($entry);
            }

            $output .= \Tracy\Debugger::dump($entry, true);
        }
        return $output;
    }

    protected function parseBody(\Psr\Http\Message\StreamInterface $body, bool $allowEmpty = false)
    {
        $content = Validator::value((string)$body)->isJson();
        if ($content->hasFailed() && !$allowEmpty) {
            $output =
                'API-Call failed, JSON parsing with error: ' . $content->getMessages()
                    . ' - Snippet: ' . substr(\strip_tags((string)$body), 0, 2000) . '...'
            ;
        } else {
            $output = $content->getValue();
        }
        return $output;
    }

    /**
     * @return string[]
     *
     * @psalm-return array<string>
     */
    protected function parseHeaders(\Psr\Http\Message\MessageInterface $message): array
    {
        $headers = [];
        foreach ($message->getHeaders() as $name => $header) {
            $headers[$name] = implode("|doubled|", $header);
        }
        return $headers;
    }

    /**
     * @psalm-return array{header: mixed, body: mixed}
     */
    protected function formatRequest(\Psr\Http\Message\RequestInterface $request): array
    {
        $output = [];
        $output['header'] = $this->parseHeaders($request);
        $output['body'] = $this->parseBody($request->getBody(), true);
        return $output;
    }

    /**
     * @psalm-return array{header: mixed, body: mixed}
     */
    protected function formatResponse(\Psr\Http\Message\ResponseInterface $response): array
    {
        $output = [];
        $output['header'] = $this->parseHeaders($response);
        $output['body'] = $this->parseBody($response->getBody());
        return $output;
    }
}
