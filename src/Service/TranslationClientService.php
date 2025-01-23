<?php // interacts with the translation-server

namespace Survos\LibreTranslateBundle\Service;

use Survos\LibreTranslateBundle\Dto\TranslationPayload;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class TranslationClientService
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private string $translationServer = 'https://translation-server.survos.com'
    )
    {

    }

    public static function calcHash(string $string, string $locale): string
    {
        return hash('xxh3', $string . $locale);
    }

    public static function textToCodes(array $text, string $locale): array
    {
        return array_map(fn($string) => self::calcHash($string, $locale), $text);
    }
    public function requestTranslations(string $from, string|array $to, array $text, $fetchOnly=false): array
    {


        // for debugging...
            $debugUrl = $this->translationServer . '/get-translations?keys=' . join(',', self::textToCodes($text, $from));
        if ($fetchOnly) {
//            dd($debugUrl);
        }
//        $url .= '?' . http_build_query(['to' => is_string($to) ? [$to]: $to, 'text' => $text]);

        $route = $fetchOnly ? '/fetch-translation' : '/queue-translation';
        $url = $this->translationServer . $route;
//        $url .= '?' . http_build_query(['to' => is_string($to) ? [$to]: $to, 'text' => $text]);
        $payload = new TranslationPayload(
            from: $from,
            engine: 'libre',
            to: $to,
            text: $text
        );

        $response = $this->httpClient->request('POST', $url, [
            'json' => $payload,
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
            dump($response->getStatusCode(), $debugUrl);
        } else {
            $results = $response->toArray();
        }
        return $results;
    }

}
