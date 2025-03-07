<?php

namespace App\Service;
use DateTimeImmutable;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class MongoDBService {

    private HttpClientInterface $httpClient;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    public function insertVisit(string $nomPage){

        $this->httpClient->request('POST', 'https://us-east-2.aws.neurelo.com/rest/visites/__one', [
            'headers' => [
                'X-API-KEY' => 'eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXVCIsImtpZCI6ImFybjphd3M6a21zOnVzLWVhc3QtMjowMzczODQxMTc5ODQ6YWxpYXMvYjJjYWNlYWItQXV0aC1LZXkifQ.eyJlbnZpcm9ubWVudF9pZCI6ImRkZWQ5MWFjLTNlYjUtNGU3YS1iOGY4LTQ3OGM3ZjNiZGNhNiIsImdhdGV3YXlfaWQiOiJnd19iMmNhY2VhYi0yYTRlLTQ3YzYtOTlkZS1iNDM3M2I4NWE2MjIiLCJwb2xpY2llcyI6WyJSRUFEIiwiV1JJVEUiLCJVUERBVEUiLCJERUxFVEUiLCJDVVNUT00iXSwiaWF0IjoiMjAyNS0wMi0yNFQxOTowNjoyNS44MjIxNjg2MDRaIiwianRpIjoiYWZlMmI5ZjQtODY5My00OGY4LTgzMGUtYWQ0NTU5ZDE5ZTgzIn0.f2bP3zdu2wMFa5Oeh8h-x-xqKumhp6INFKyq7xgT_IMj6vWjRTtHQsGynVwmB7fUmHTrpSR1tIywDY36k2-u8Zlyum_XODCV2qp3q58fogriZ0lQyh5FPqW1Tf5jZF-ETo7A9N9AEQCf0KRGYVkR_hCgKGC2AXf_Ao20Y4rClpYC9CAihqj8AF5T7KsoLBCLTsyUMSn4DNpu6CfPI3hdL0fHaL923stA_5yr3DFB3OeESkQiW8FqqEJExlNT85TBvcbNj_FdxhNn5JchhcGpbjjxjrjXLI6Wwszpu8OPqSkDXeOfsge1fY6s-Lmf_mxCTRgUjqvfk-79xt8fTk9pqQ',
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'nomPage' => $nomPage,
                'dateVisite' => (new DateTimeImmutable())->modify('+1 hour')->format('Y-m-d H:i:s'),
            ],
        ]);
    }
}



