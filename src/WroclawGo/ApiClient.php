<?php


namespace App\WroclawGo;


use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Class ApiClient
 * @package App\WroclawGo
 */
class ApiClient
{
    const BASE_URL = 'https://go.wroclaw.pl/api/v2.0';
    const CULTURAL_OFFERS = '/offers';
    const CULTURAL_OFFERS_CATEGORIES = '/offers/categories';

    /**
     * @var HttpClientInterface
     */
    private $client;

    /**
     * ApiClient constructor.
     * @param HttpClientInterface $client
     */
    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * @return array
     */
    public function getCulturalOffersCategories(): array
    {
        try {
            $response = $this->client->request('GET', self::BASE_URL . self::CULTURAL_OFFERS_CATEGORIES, [
                'query' => [
                    'key' => $_ENV['WROCLAW_GO_API_KEY'],
                ]
            ]);

            return $response->getStatusCode() === 200 ? $response->toArray() : [];
        } catch (TransportExceptionInterface | ClientExceptionInterface | DecodingExceptionInterface | RedirectionExceptionInterface | ServerExceptionInterface $e) {
            return [];
        }
    }

    /**
     * @param int $page
     * @param int $size
     * @param int|null $categoryId
     * @param string|null $dateFrom
     * @return array
     */
    public function getCulturalOffers(int $page = 1, int $size = 20, int $categoryId = null, string $dateFrom = null): array
    {
        $parameters = [
            'query' => [
                'key' => $_ENV['WROCLAW_GO_API_KEY'],
                'page' => $page - 1, // because API starts counting pages from 0
                'size' => $size,
            ]
        ];

        if (!is_null($categoryId)) {
            $parameters['query']['categories'] = $categoryId;
        }

        if (!is_null($dateFrom)) {
            $parameters['query']['dateFrom'] = $dateFrom;
        }

        try {
            $response = $this->client->request('GET', self::BASE_URL . self::CULTURAL_OFFERS, $parameters);

            return $response->getStatusCode() === 200 ? $response->toArray() : [];
        } catch (TransportExceptionInterface | ClientExceptionInterface | DecodingExceptionInterface | RedirectionExceptionInterface | ServerExceptionInterface $e) {
            return [];
        }
    }
}
