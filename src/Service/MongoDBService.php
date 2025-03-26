<?php

namespace App\Service;
use DateTimeImmutable;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class MongoDBService {

    private HttpClientInterface $httpClient;
    private string $apiKey;

    public function __construct(HttpClientInterface $httpClient, string $apiKey)
    {
        $this->httpClient = $httpClient;
        $this->apiKey = $apiKey;
    }

    public function insertVisit(string $nomPage){

        $this->httpClient->request('POST', 'https://us-east-2.aws.neurelo.com/rest/visites/__one', [
            'headers' => [
                'X-API-KEY' => $this->apiKey,
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'nomPage' => $nomPage,
                'dateVisite' => (new DateTimeImmutable())->modify('+1 hour')->format('Y-m-d H:i:s'),
            ],
        ]);
    }
}



