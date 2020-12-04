<?php

namespace LightPAY\Framework;


use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class Request
{

    /**
     * @var string
     */
    //public $endpoint = "https://demo.lunch-digi-pay.lu";
    //public $testEndpoint = "https://webdemo.lunch-digi-pay.lu";
    public $endpoint = "http://localhost:9004";
    public $testEndpoint = "http://localhost:9004";

    /**
     * @var Client
     */
    public $client;


    public $config = [];

    /**
     * @var string
     */
    public $api_key;

    /**
     * @var string
     */
    public $consumer_key;

    /**
     * @var string
     */
    public $secret_key;

    /**
     * @var string test|prod
     */
    public $mode;


    public $debug = null;
    /**
     * @var string
     */

    private $successUrl;

    /**
     * @var string
     */
    private $errorUrl;

    public function __construct(
        string $api_key,
        string $consumer_key,
        string $secret_key,
        string $successUrl,
        string $errorUrl,
        string $mode = "test",
        array $config = []
    )
    {
        $config['base_uri'] = $mode === "prod" ? $this->endpoint : $this->testEndpoint;

        if (!$this->client) {
            $this->client = new Client($config);
        }

        $this->api_key = $api_key;
        $this->consumer_key = $consumer_key;
        $this->secret_key = $secret_key;
        $this->mode = $mode;
        $this->config = $config;
        $this->successUrl = $successUrl;
        $this->errorUrl = $errorUrl;
    }

    /**
     * @param string $method
     * @param string $path
     * @param array $content
     * @param array|null $headers
     * @return array|null|object
     */
    public function request(string $method, string $path, array $content, ?array $headers = []): ?Responder
    {
        try {
            $key = $method === "GET" ? "query" : "form_params";
            $url = $method === "GET" ? $this->endpoint . $path . "?" . http_build_query($content['query']) : $this->endpoint . $path;
            $now = time();

            /**
             * Set boolean to integer
             *  Issue with Guzzlehttp send boolean as string!
             */
            foreach ($content[$key] as $k => $v) {
                //error_log($v . " " . gettype($v));
                if (gettype($v) === "boolean") {
                    if ($v == 1) {
                        $content[$key][$k] = (string)"true";
                    } else {
                        $content[$key][$k] = (string)"false";
                    }
                }
                //error_log($content[$key][$k]);
            }

            ksort($content[$key], SORT_NATURAL);

            $body = json_encode($content[$key], JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

            $toSign = [$this->api_key, $this->secret_key, $this->consumer_key, $method, $url, $body, $now];
            $signature = $this->sign($toSign);

            $options = [
                "debug" => false,
                "http_errors" => false,
                "auth" => [
                    $this->consumer_key,
                    $this->api_key
                ],
                "headers" => [
                    "X-LightPAY-Credentials" => $this->sign([$this->api_key, $this->secret_key]),
                    "X-LightPAY-Signature" => $signature,
                    "X-LightPAY-Timestamp" => $now,
                    "Content-Type" => "application/x-www-form-urlencoded"
                ],
                $key => $content[$key]
            ];
            //error_log(json_encode($options));
            $res = $this->client->request($method, $path, $options);
            $m = \GuzzleHttp\json_decode($res->getBody()->getContents());
            return new Responder($m);
        } catch (GuzzleException $e) {
            error_log("[Request] : " . $e->getMessage());
            return null;
        }
    }

    public function post(string $path, array $content, ?array $headers = []): ?Responder
    {
        $params = [
            "form_params" => $content
        ];
        return $this->request('POST', $path, $params, $headers);
    }

    public function get(string $path, array $content, ?array $headers = [])
    {
        $params = [
            "query" => $content
        ];
        return $this->request('GET', $path, $params, $headers);
    }

    public function put(string $path, array $content, ?array $headers = []): ?Responder
    {
        $content['_method'] = "PUT";
        $params = [
            "form_params" => $content
        ];
        return $this->request('POST', $path, $params, $headers);
    }

    public function delete(string $path, array $content, ?array $headers = []): ?Responder
    {
        $content['_method'] = "DELETE";
        $params = [
            "form_params" => $content
        ];
        return $this->request('DELETE', $path, $params, $headers);
    }

    /**
     * Encrypt data to be sent with request headers
     * @param $data
     * @return string|null
     */
    public function sign($data): ?string
    {
        if (!$data)
            return "";

        try {
            $toSign = null;

            if (gettype($data) === "array")
                $toSign = '$1$' . implode("+", $data);
            else
                $toSign = '$1$' . $data;

            $key = pack('H*', substr($this->secret_key, 0, 32));
            $iv = hex2bin(substr($this->consumer_key, 0, 32));
            $encrypted = openssl_encrypt(
                $toSign,
                "AES-128-CBC",
                $key,
                0,
                $iv
            );

            return (string)$encrypted;
        } catch (Exception $err) {
            error_log("[ERROR] : encryption error");
            error_log($err);
            return null;
        }
    }
}
