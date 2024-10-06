<?php

namespace App\Helpers;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

class Claude
{
    public string $version;

    protected string $apiKey;

    protected string $apiUrl;

    protected string $anthropic_version;

    protected string $model_version;

    public function __construct($version)
    {
        $this->model_version = $version;
        $this->apiKey = env('SONNET_API_KEY');
        $this->apiUrl = env('SONNET_API_URL');
        $this->anthropic_version = env('SONNET_ANTHROPIC_VERSION');
    }

    /**
     * @throws RequestException
     * @throws ConnectionException
     */
    public function chat($system, $messages): \GuzzleHttp\Promise\PromiseInterface|\Illuminate\Http\Client\Response
    {
        $data = [
            'system' => $system,
            'messages' => $messages,
            'max_tokens' => 1024,
            'model' => $this->model_version,
        ];

        logger()->info('Claude Request: '.json_encode($data));

        return Http::withHeaders([
            'Accept' => 'application/json',
            'x-api-key' => $this->apiKey,
            'anthropic-version' => $this->anthropic_version,
        ])
            ->post($this->apiUrl.'/messages', $data)->throw();
    }
}
