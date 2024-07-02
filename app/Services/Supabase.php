<?php

namespace App\Services;

use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

class Supabase
{
    private $apiKey;

    private $uriBase;

    private Http $httpClient;

    private $error;

    private $response;

    private array $headers = [
        'Content-Type' => 'application/json',
    ];

    /**
     * Construct method (Set the API key, URI base and instance GuzzleHttp client)
     *
     * @param  $apiKey  String The Supabase project API Key
     * @param  $uriBase  String API URI base (Ex: "https://abcdefgh.supabase.co/rest/v1/" OR "https://abcdefgh.supabase.co/auth/v1/")
     * @return void
     */
    public function __construct()
    {
        $this->apiKey = config('supabase.key');
        $this->uriBase = $this->formatUriBase(env('SUPABASE_URL'));

        $this->httpClient = new Http();
        $this->headers['apikey'] = $this->apiKey;
    }

    /**
     * Set bearerToken to be added into headers and to be used for future requests
     *
     * @param  $bearerToken  String The bearer user token (generated in sign in process)
     * @return Supabase
     */
    public function setBearerToken(string $bearerToken): static
    {
        $this->setHeader('Authorization', 'Bearer '.$bearerToken);

        return $this;
    }

    /**
     * Format URI base with slash at end
     *
     * @param  $uriBase  String API URI base (Ex: "https://abcdefgh.supabase.co/rest/v1/" OR "https://abcdefgh.supabase.co/auth/v1/")
     * @return void
     */
    private function formatUriBase(string $uriBase): string
    {
        return (substr($uriBase, -1) == '/')
            ? $uriBase
            : $uriBase.'/';
    }

    /**
     * Returns the API key
     */
    public function getApiKey(): string
    {
        return $this->apiKey;
    }

    /**
     * Returns the URI base
     *
     * @param  $endPoint  (optional) String The end point to concatenate to URI base
     */
    public function getUriBase(string $endPoint = ''): string
    {
        $parseUrl = parse_url($this->uriBase);

        return $parseUrl['scheme'].'://'.$parseUrl['host'].'/'.$endPoint;
    }

    public function getAuthUriBase(string $endPoint = ''): string
    {
        $parseUrl = parse_url($this->uriBase);
        $authUrl = config('supabase.auth_url');

        return config('supabase.auth_url').'/'.$endPoint;
    }

    /**
     * Returns the HTTP Client (GuzzleHttp)
     */
    public function getHttpClient($endpoint): \Illuminate\Http\Client\Response
    {
        return $this->httpClient->get($endpoint);
    }

    /**
     * Returns the Response of last request
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Set a header to be use in the API request
     *
     * @param  $header  String The header key to be set
     * @param  $value  String The value of header
     */
    public function setHeader(string $header, string $value): Supabase
    {
        $this->headers[$header] = $value;

        return $this;
    }

    /**
     * Returns a specific header or null if it doesn't exist
     *
     * @param  $header  String The header key to be set
     * @return string|null
     */
    public function getHeader(string $header)
    {
        return (isset($this->headers[$header]))
            ? $this->headers[$header]
            : null;
    }

    /**
     * Returns the set headers
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * Returns the error generated
     */
    public function getError(): string
    {
        return $this->error;
    }

    /**
     * Returns a new instance of Auth class
     */
    public function createAuth(): Auth
    {
        return new Auth($this);
    }

    /**
     * Returns a new instance of Database class
     *
     * @param  $tableName  String The table to be used
     * @param  $primaryKey  (optional) String The table primary key (usually "id")
     */
    public function initializeDatabase(string $tableName, string $primaryKey = 'id'): Database
    {
        return new Database($this, $tableName, $primaryKey);
    }

    /**
     * Returns a new instance of QueryBuilder class
     */
    public function initializeQueryBuilder(): QueryBuilder
    {
        return new QueryBuilder($this);
    }

    /**
     * Format the exception thrown by GuzzleHttp, formatting the error message
     *
     * @param  $e  \GuzzleHttp\Exception\RequestException The exception thrown by GuzzleHttp
     */
    public function formatRequestException(\GuzzleHttp\Exception\RequestException $e): void
    {
        if ($e->hasResponse()) {
            $res = json_decode($e->getResponse()->getBody());
            $searchItems = ['msg', 'message', 'error_description'];

            foreach ($searchItems as $item) {
                if (isset($res->$item)) {
                    $this->error = $res->$item;
                    break;
                }
            }
        }
    }

    /**
     * Execute a Http request in Supabase API
     *
     * @param  $method  String The request method (GET, POST, PUT, DELETE, PATCH, ...)
     * @param  $uri  String The URI to be requested (including the endpoint)
     * @param  $options  array Requisition options (header, body, ...)
     * @return array|object|null
     */
    public function executeHttpRequest(string $method, string $uri, array $options)
    {
        try {
            $this->response = $this->httpClient
                ->withHeaders($this->headers);

            $this->response = $this->httpClient->request(
                $method,
                $uri,
                $options
            );

            return json_decode($this->response->getBody());
        } catch (RequestException $e) {
            $this->formatRequestException($e);
            throw $e;
        }
    }
}
