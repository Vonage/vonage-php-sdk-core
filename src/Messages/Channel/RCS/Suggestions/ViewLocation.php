<?php

declare(strict_types=1);

namespace Vonage\Messages\Channel\RCS\Suggestions;

class ViewLocation extends Suggestion
{
    use FallbackUrlTrait;

    protected string $latitude;
    protected string $longitude;
    protected string $pinLabel;

    public function __construct(
        string $text,
        string $postbackData,
        string $latitude,
        string $longitude,
        string $pinLabel,
        string $fallbackUrl,
    ) {
        parent::__construct($text, $postbackData);
        $this->setLatitude($latitude);
        $this->setLongitude($longitude);
        $this->setPinLabel($pinLabel);
        $this->setFallbackUrl($fallbackUrl);
    }

    public function getType(): string
    {
        return Suggestion::SUGGESTION_TYPE_VIEW_LOCATION;
    }

    public function toArray(): array
    {
        $returnArray = parent::toArray();
        $returnArray['latitude'] = $this->getLatitude();
        $returnArray['longitude'] = $this->getLongitude();
        $returnArray['pin_label'] = $this->getPinLabel();

        if ($this->getFallbackUrl() !== null) {
            $returnArray['fallback_url'] = $this->getFallbackUrl();
        }

        return $returnArray;
    }

    public function setLatitude(string $latitude): void
    {
        $this->latitude = $latitude;
    }

    public function getLatitude(): string
    {
        return $this->latitude;
    }

    public function setLongitude(string $longitude): void
    {
        $this->longitude = $longitude;
    }

    public function getLongitude(): string
    {
        return $this->longitude;
    }

    public function getPinLabel(): string
    {
        return $this->pinLabel;
    }

    public function setPinLabel(string $pinLabel): void
    {
        $this->pinLabel = $pinLabel;
    }
}
