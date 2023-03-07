<?php
namespace Curly;

use Curly\Exceptions\CurlException;

class UrlRequest
{
    const METHOD_GET = 'GET';
    const METHOD_POST = 'POST';
    const METHOD_PUT = 'PUT';
    const METHOD_DELETE = 'DELETE';

    /**
     * @var \CurlHandle
     */
    private ?\CurlHandle $connection = null;

    /**
     * @var int
     */
    private int $requestTimeout = 0; // in seconds

    /**
     * @var int
     */
    private int $connectionTimeout = 0; // in seconds

    /**
     * @var int
     */
    private int $verifyHost = 0; // should be changed to 2 in production

    /**
     * @var bool
     */
    private bool $verifyPeer = false; // should be change to true in production

    /**
     * @var string|null
     */
    private ?string $method = null;

    /**
     * @var int|null
     */
    private ?int $resonseCode = null;

    /**
     * @var array|string|null
     */
    private array|string|null $response = null;

    /**
     * @var array
     */
    private array $responseHeaders = [];

    /**
     * @return UrlRequest
     */
    private function init() : UrlRequest
    {
        if (!$this->isActive()) {
            $this->connection = curl_init();
        }

        return $this;
    }

    public function send(string $url, string $method = self::METHOD_GET, array $param = [], array $data = [], array $headers = []) : UrlRequest
    {
        $this->init();

        $body = '';

        if (!empty($data)) {
            if (strtoupper($method) === self::METHOD_GET) {
                $param = array_unique(array_merge($param, $data));
            } else {
                $body = json_encode($data);
                $_headers['content-type'] = 'application/json';
            }
        }

        if (!empty($param)) {
            $url .= implode(['?', http_build_query($param)]);
        }

        $tempHeaders = [];
        $tempHeaders = array_merge($tempHeaders, $headers);
        $headers = [];
        foreach($tempHeaders as $id => $value) {
            $headers[] = implode(': ', [$id, $value]);
        }

        if (strtoupper($method) !== self::METHOD_GET) {
            curl_setopt($this->connection, CURLOPT_CUSTOMREQUEST, strtoupper($this->method));
            curl_setopt($this->connection, CURLOPT_POSTFIELDS, $body);
        }

        curl_setopt($this->connection, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($this->connection, CURLOPT_URL, $url);
        curl_setopt($this->connection, CURLOPT_SSL_VERIFYHOST, $this->verifyHost);
        curl_setopt($this->connection, CURLOPT_SSL_VERIFYPEER, $this->verifyPeer);
        curl_setopt($this->connection, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->connection, CURLOPT_TIMEOUT, $this->requestTimeout);
        curl_setopt($this->connection, CURLOPT_CONNECTTIMEOUT, $this->connectionTimeout);
        curl_setopt($this->connection, CURLOPT_HEADER, true);

        $this->response = curl_exec($this->connection);

        $headerSize = curl_getinfo($this->connection, CURLINFO_HEADER_SIZE);
        $this->extractHeader(substr($this->response, 0, $headerSize));
        $this->response = substr($this->response, $headerSize);

        $this->resonseCode = (integer) curl_getinfo($this->connection, CURLINFO_RESPONSE_CODE);

        $errorMessage = curl_error($this->connection);
        $errorCode = (integer) curl_errno($this->connection);

        if ($errorCode !== 0) {
            throw new CurlException($errorMessage, $errorCode);
        }

        return $this;
    }

    public function getResponse() : string
    {
        return $this->response;
    }

    /**
     * @return int|null
     */
    public function getResponseCode() : ?int
    {
        return $this->resonseCode;
    }

    public function isActive()
    {
        return $this->connection instanceof \CurlHandle;
    }

    public function getResponseHeader(?string $id = null)
    {
        if ($id !== null) {
            return $this->responseHeaders[strtolower($id)] ?? null;
        } 
        return $this->responseHeaders;
    }

    private function extractHeader(string $headers)
    {
        $headersAsArray = [];
        $breakLoop = false;
        
        $pos = 0;

        while (!$breakLoop) {
            $startPos = $pos;
            $pos = strpos($headers, "\n", $pos);

            if ($pos === false) {
                $line = substr($headers, $startPos);
                $breakLoop = true;
            } else {
                $line = substr($headers, $startPos, $pos - $startPos);
            }

            $pos += 1;

            if (trim($line) === '') continue;

            $segment = explode(':', $line);
            if (count($segment) < 2) continue; // ignore, could be an invalid header value

            $headersAsArray[trim($segment[0])] = trim($segment[1]);
        }

        $this->responseHeaders = $headersAsArray;

        return $this;
    }

    /**
     * @return UrlRequest
     */
    public function end() : UrlRequest
    {
        if ($this->isActive()) {
            curl_close($this->connection);
            $this->connection = null;
        }

        return $this;
    }

    /**
     * @param int|null $requestTimeout
     * @return UrlRequest
     */
    public function setRequestTimeout(int $requestTimeout) : UrlRequest
    {
        $this->requestTimeout = $requestTimeout;
        return $this;
    }

    /**
     * @param int $connectionTimeout
     * @return UrlRequest
     */
    public function setConnectionTimeout(int $connectionTimeout) : UrlRequest
    {
        $this->connectionTimeout = $connectionTimeout;
        return $this;
    }
}