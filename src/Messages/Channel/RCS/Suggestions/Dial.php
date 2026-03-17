<?php

declare(strict_types=1);

namespace Vonage\Messages\Channel\RCS\Suggestions;

class Dial extends Suggestion
{
    use FallbackUrlTrait;

    protected string $phoneNumber;

    public function __construct(
        string $text,
        string $postbackData,
        string $phoneNumber,
        string $fallbackUrl,
    ) {
        parent::__construct($text, $postbackData);
        $this->setPhoneNumber($phoneNumber);
        $this->setFallbackUrl($fallbackUrl);
    }

    public function getType(): string
    {
        return Suggestion::SUGGESTION_TYPE_DIAL;
    }

    public function toArray(): array
    {
        $returnArray = parent::toArray();

        $returnArray['phone_number'] = $this->getPhoneNumber();

        if ($this->getFallbackUrl() !== null) {
            $returnArray['fallback_url'] = $this->getFallbackUrl();
        }

        return $returnArray;
    }

    public function setPhoneNumber(string $phoneNumber): void
    {
        $this->phoneNumber = $phoneNumber;
    }

    public function getPhoneNumber(): string
    {
        return $this->phoneNumber;
    }
}
