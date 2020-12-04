<?php


namespace LightPAY\Framework;


class RequestHeader
{
    public $headers = [];
    
    public function __construct(array $headers = [])
    {
        if ($headers) {
            $this->headers = [];
        }
    }
    
    /**
     * Set a key pair header
     * @param string $key
     * @param $value
     */
    public function set(string $key, $value): void
    {
        $this->headers[$key] = $value;
    }
    
    /**
     * Get a header by its key
     * @param string $key
     */
    public function get(string $key)
    {
        $this->headers[$key];
    }
    
    /**
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }
}