<?php

namespace TelegramBot\Http;

use Exception;

final class HttpClient
{
    private string $baseUrl = '';
    private array $headers = [];
    private array $options = [];
    private int $timeout = 30;
    private int $retries = 0;
    private int $retryDelay = 100;

    private function __construct(string $baseUrl = '')
    {
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->options = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 5,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ];
        $this->withHeader('charset', 'utf-8');
    }

    public static function baseUrl(string $url): self
    {
        return new self($url);
    }

    public function withHeader(string $key, string $value): self
    {
        $this->headers[$key] = $value;
        return $this;
    }

    public function timeout(int $seconds): self
    {
        $this->timeout = $seconds;
        return $this;
    }

    public function retry(int $times, int $delayMs = 100): self
    {
        $this->retries = $times;
        $this->retryDelay = $delayMs;
        return $this;
    }

    public function acceptJson(): self
    {
        return $this->withHeader('Accept', 'application/json');
    }

    public function asJson(): self
    {
        return $this->withHeader('Content-Type', 'application/json');
    }

    public function asForm(): self
    {
        return $this->withHeader('Content-Type', 'application/x-www-form-urlencoded');
    }

    public function asMultipart(): self
    {
        return $this->withHeader('Content-Type', 'multipart/form-data');
    }

    /**
     * @throws Exception
     */
    public function post(string $url, array $data = []): Response
    {
        return $this->send('POST', $url, $data);
    }

    /**
     * @throws Exception
     */
    public function head(string $url): Response
    {
        return $this->send('HEAD', $url);
    }

    /**
     * @throws Exception
     */
    private function send(string $method, string $url, array $data = []): Response
    {
        $fullUrl = $this->buildUrl($url);
        $attempt = 0;
        $lastException = null;

        while ($attempt <= $this->retries) {
            try {
                $ch = curl_init();

                // Set URL and method
                curl_setopt($ch, CURLOPT_URL, $fullUrl);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

                // Set timeout
                curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->timeout);

                // Set headers
                if (!empty($this->headers)) {
                    $headerList = [];
                    foreach ($this->headers as $key => $value) {
                        $headerList[] = "{$key}: {$value}";
                    }
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $headerList);
                }

                // Set request body
                if (!empty($data) && in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'])) {
                    $body = $this->prepareBody($data);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
                }

                // Set additional options
                foreach ($this->options as $option => $value) {
                    curl_setopt($ch, $option, $value);
                }

                // Capture response headers
                $responseHeaders = [];
                curl_setopt($ch, CURLOPT_HEADERFUNCTION, function($curl, $header) use (&$responseHeaders) {
                    $len = strlen($header);
                    $header = explode(':', $header, 2);
                    if (count($header) < 2) {
                        return $len;
                    }
                    $responseHeaders[strtolower(trim($header[0]))] = trim($header[1]);
                    return $len;
                });

                // Execute request
                $body = curl_exec($ch);

                if ($body === false) {
                    $error = curl_error($ch);
                    $errno = curl_errno($ch);
                    curl_close($ch);
                    throw new Exception("cURL Error ({$errno}): {$error}");
                }

                $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                return new Response($statusCode, $responseHeaders, $body);

            } catch (Exception $e) {
                $lastException = $e;
                $attempt++;

                if ($attempt <= $this->retries) {
                    usleep($this->retryDelay * 1000);
                }
            }
        }

        throw $lastException;
    }

    private function buildUrl(string $url): string
    {
        if (filter_var($url, FILTER_VALIDATE_URL)) {
            return $url;
        }

        return $this->baseUrl . '/' . ltrim($url, '/');
    }

    private function prepareBody(array $data): string
    {
        $contentType = $this->headers['Content-Type'] ?? '';

        if (str_contains($contentType, 'application/json')) {
            return json_encode($data);
        }

        if (str_contains($contentType, 'application/x-www-form-urlencoded')) {
            return http_build_query($data);
        }

        return json_encode($data);
    }
}