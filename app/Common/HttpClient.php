<?php
declare(strict_types=1);

namespace App\Common;

use Hyperf\Guzzle\ClientFactory;
use Hyperf\Utils\ApplicationContext;
use Psr\Http\Message\ResponseInterface;
use \GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class HttpClient
{

    /**
     * @var array
     */
    private array $requestData = [];

    /**
     * @var Client
     */
    private Client $client;

    /**
     * @var ClientFactory
     */
    private ClientFactory $clientFactory;

    /**
     * HttpClient constructor.
     * @param ClientFactory $clientFactory
     */
    public function __construct(ClientFactory $clientFactory)
    {
        $this->clientFactory = $clientFactory;
    }

    /**
     * @return HttpClient
     */
    public static function getClient(): HttpClient
    {
        $container = ApplicationContext::getContainer();
        $clientFactory = $container->get(ClientFactory::class);
        return new HttpClient($clientFactory);
    }

    /**
     * 设置头信息
     * @param array $params
     */
    public function setHeader(array $params)
    {
        $this->requestData = array_merge($this->requestData, $params);
    }

    /**
     * @param string $url
     * @param array $params
     * @return string
     * @throws
     */
    public function get(string $url, array $params): string
    {
        $this->createClient();
        $data = [
            'query' => $params,
        ];
        return $this->getContent($this->client->get($url, $data));
    }

    /**
     * 初始化client
     */
    private function createClient()
    {
        $this->client = self::create();
    }

    /**
     * 生成一个http客户端
     *
     * @return Client
     */
    public function create(): Client
    {
        $options = [];
        return $this->clientFactory->create($options);
    }

    /**
     * @param ResponseInterface $response
     * @return string
     */
    private function getContent(ResponseInterface $response): string
    {
        $body = $response->getBody();
        $content = $body->getContents();
        $body->close();
        return $content;
    }

    /**
     * @param string $url
     * @param array $params
     * @return string
     * @throws
     */
    public function getByJson(string $url, array $params): string
    {
        $this->createClient();
        $data = [
            'query' => $params,
            'headers' => [
                //'Accept' => 'application/json',
                'Accept' => '*/*', // application/json',
                'Content-Type' => 'application/json',
            ],
        ];
        return $this->getContent($this->client->get($url, $data));
    }

    /**
     * @param string $url
     * @param array $params
     * @return string
     * @throws
     */
    public function post(string $url, array $params): string
    {
        $this->createClient();
        $data = [
            'form_params' => $params,
            'allow_redirects' => TRUE,
        ];
        return $this->getContent($this->client->post($url, $data));
    }

    /**
     * @param string $url
     * @param array $params
     * @return string
     * @throws
     */
    public function postByJson(string $url, array $params): string
    {
        $this->createClient();
        $data = [
            'form_params' => $params,
            'headers' => [
                'Accept' => '*/*', // application/json',
                'Content-Type' => 'application/json',
            ],
            'allow_redirects' => TRUE,
        ];
        return $this->getContent($this->client->post($url, $data));
    }

    /**
     * @param string $url
     * @param array $params
     * @return string
     * @throws GuzzleException
     */
    public function postByBody(string $url, array $params): string
    {
        $this->createClient();
        $data = [
            'body' => json_encode($params),
            'headers' => [
                'Accept' => '*/*', // application/json',
                'Content-Type' => 'application/json',
            ],
            'allow_redirects' => true,
        ];
        return $this->getContent($this->client->post($url, $data));
    }
}
