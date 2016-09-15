<?php
/**
 * @package   BO Slim
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsclient;

use \BO\Mellon\Validator;

/**
  * Extension for Twig and Slim
  *
  *  @SuppressWarnings(PublicMethod)
  *  @SuppressWarnings(TooManyMethods)
  */
class TwigExtension extends \Twig_Extension
{
    /**
     * @var \Slim\Http\Container
     */
    private $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function getName()
    {
        return 'bozmsclientExtension';
    }

    public function getFunctions()
    {
        $safe = array('is_safe' => array('html'));
        return array(
            new \Twig_SimpleFunction('dumpHttpLog', array($this, 'dumpHttpLog'), $safe),
        );
    }

    public function dumpHttpLog()
    {
        \D::config([
            "display.show_call_info" => false,
            "display.show_version" => false,
            "sorting.arrays" => false,
            "display.cascade" => [5,10,10],
        ]);
        $output = '<h2>HTTP API-Log</h2>'
            .' <p>For debugging: This log contains HTTP calls. <strong>DISABLE FOR PRODUCTION!</strong></p>';
        foreach (Http::$log as $entry) {
            if ($entry instanceof \Psr\Http\Message\RequestInterface) {
                $settings = new \D\DumpSettings(\D::OB, "Request " . $entry->getMethod() . " " . $entry->getUri());
                $entry = $this->formatRequest($entry);
            } elseif ($entry instanceof \Psr\Http\Message\ResponseInterface) {
                $settings = new \D\DumpSettings(\D::OB, "Response");
                $entry = $this->formatResponse($entry);
            } else {
                $settings = new \D\DumpSettings(\D::OB);
            }
            $output .= \D::UMP($entry, $settings);
        }
        return $output;
    }

    protected function parseBody($body)
    {
        $content = Validator::value((string)$body)->isJson();
        if ($content->hasFailed()) {
            $output =
                'API-Call failed, JSON parsing with error: ' . implode('; ', $content->getMessages())
                    . ' - Snippet: ' .substr(\strip_tags((string)$body), 0, 2000) . '...'
            ;
        } else {
            $output = $content->getValue();
        }
        return $output;
    }

    protected function parseHeaders(\Psr\Http\Message\MessageInterface $message)
    {
        $headers = [];
        foreach ($message->getHeaders() as $name => $header) {
            $headers[$name] = implode("|doubled|", $header);
        }
        return $headers;
    }

    protected function formatRequest(\Psr\Http\Message\RequestInterface $request)
    {
        $output = [];
        $output['header'] = $this->parseHeaders($request);
        $output['body'] = $this->parseBody($request->getBody());
        return $output;
    }

    protected function formatResponse(\Psr\Http\Message\ResponseInterface $response)
    {
        $output = [];
        $output['header'] = $this->parseHeaders($response);
        $output['body'] = $this->parseBody($response->getBody());
        return $output;
    }
}
