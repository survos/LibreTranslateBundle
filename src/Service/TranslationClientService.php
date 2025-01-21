<?php // interacts with the translation-server

namespace Survos\LibreTranslateBundle\Service;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class TranslationClientService
{
    public function __construct(
        private HttpClientInterface $httpClient,
    )
    {

    }

    public function requestTranslations(string $from, string $to, array $text): array
    {
        $url = sprintf('https://trans.wip/queue-translation/%s/%s', 'libre', $from);
        $url .= '?' . http_build_query(['text' => $text, 'to' => [$to]]);
        $response = $this->httpClient->request('GET', $url, [
            'json' => $text,
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ],
            'proxy' => '127.0.0.1:7080',

        ]);
        if ($response->getStatusCode() !== 200) {
            $results = [
                'status' => $response->getStatusCode(),
                'msg' => $response->getContent(false),
            ];
//            dd($response->getStatusCode(), $response);
        } else {
            $results = $response->toArray();
        }
        return $results;
    }

}
