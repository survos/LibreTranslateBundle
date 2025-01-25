<?php // interacts with the translation-server

namespace Survos\LibreTranslateBundle\Service;

use Psr\Log\LoggerInterface;
use Survos\LibreTranslateBundle\Dto\TranslationPayload;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class TranslationClientService
{
    public const ROUTE='/batch-translate';
    public function __construct(
        private LoggerInterface $logger,
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
        $url = $this->translationServer . self::ROUTE;
        $payload = new TranslationPayload(
            from: $from,
            engine: 'libre',
            insertNewStrings: !$fetchOnly,
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
            $this->logger->error(json_encode($payload, JSON_PRETTY_PRINT));
            $results = [
                'status' => $response->getStatusCode(),
                'msg' => $response->getContent(false),
            ];
        } else {
            $results = $response->toArray();
        }
        return $results;
    }

}
