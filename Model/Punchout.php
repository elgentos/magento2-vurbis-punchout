<?php

declare(strict_types=1);

namespace Vurbis\Punchout\Model;

use GuzzleHttp\Exception\GuzzleException;
use Magento\Framework\Exception\LocalizedException;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Psr7\Request;

/**
 * Punchout Model
 */
class Punchout
{
    private GuzzleClient $client;

    public function __construct()
    {
        $this->client = new GuzzleClient(
            [
                'verify' => false,
            ]
        );
    }

    /**
     * @throws GuzzleException
     * @throws LocalizedException
     */
    public function post(
        string $url,
        mixed $data,
        string $format = 'json',
        string $responseFormat = 'json'
    ): string|object {
        $headers = [
            'Accept' => 'application/' . $responseFormat,
            'Content-Type' => 'application/' . $format,
        ];

        if ($format === 'json' && !empty($data) && is_array($data)) {
            $data = json_encode($data);
        }

        try {
            $request = new Request('POST', $url, $headers, $data);
            $response = $this->client->send($request);
            if ($response->getStatusCode() !== 200 && $response->getStatusCode() !== 201) {
                throw new LocalizedException(__('Request failed with status code %1', $response->getStatusCode()));
            }

            if ($responseFormat === 'json') {
                return json_decode($response->getBody()->getContents());
            }

            return $response->getBody()->getContents();
        } catch (\Exception $e) {
            throw new LocalizedException(__($e->getMessage()));
        }
    }

    /**
     * @throws LocalizedException
     * @throws GuzzleException
     */
    public function get($url): string
    {
        try {
            $response = $this->client->get($url);
            if ($response->getStatusCode() !== 200 && $response->getStatusCode() !== 201) {
                throw new LocalizedException(__('Request failed with status code %1', $response->getStatusCode()));
            }

            return $response->getBody()->getContents();
        } catch (\Exception $e) {
            throw new LocalizedException(__($e->getMessage()));
        }
    }
}
