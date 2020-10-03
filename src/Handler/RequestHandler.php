<?php
/**
 * User: Wajdi Jurry
 * Date: 22/02/19
 * Time: 04:40 م
 */

namespace Jurry\RabbitMQ\Handler;


use GuzzleHttp\Client;

class RequestHandler
{
    /**
     * @var $this
     */
    private static $instance;

    private $httpClient;

    public function __construct()
    {
        $this->httpClient = new Client([
            'base_uri' => env('JURRY_BASE_API_URI'),
            'timeout' => env('JURRY_HTTP_CLIENT_TIMEOUT')
        ]);
    }

    /**
     * @return $this
     */
    public function getInstance(): self
    {
        return self::$instance ?? self::$instance = new self;
    }

    /**
     * @param string $route
     * @param string $method
     * @param array $headers
     * @param array $query
     * @param array $body
     * @return string|null
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function process(string $route = '', string $method = '', array $headers = [], array $query = [], array $body = [])
    {
        if (!empty($route)) {
            $request = $this->httpClient->request($method, $route, [
                'headers' => $headers,
                'json' => $body,
                'query' => $query
            ]);

            return $request->getBody()->getContents();
        }

        return null;
    }
}
