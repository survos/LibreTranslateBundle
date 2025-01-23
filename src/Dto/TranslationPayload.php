<?php

namespace Survos\LibreTranslateBundle\Dto;
use Symfony\Component\Validator\Constraints as Assert;

class TranslationPayload
{
    public const ENGINES = ['libre','bing','piglatin'];

    public function __construct(
//        #[Assert\Locale(canonicalize: true)]
        public string $from,
//        #[Assert\Choice(choices: self::ENGINES, message: 'Choose a valid engine.')]
        public string $engine,
        #[Assert\NotBlank()]
        public array $to = [],
        #[Assert\NotBlank()]
        public array $text = [],
        public ?string $callbackUrl = null,
    ) {
    }
}
