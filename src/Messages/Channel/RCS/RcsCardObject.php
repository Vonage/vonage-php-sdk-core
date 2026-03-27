<?php

declare(strict_types=1);

namespace Vonage\Messages\Channel\RCS;

use Vonage\Entity\Hydrator\ArrayHydrateInterface;
use Vonage\Messages\Channel\RCS\Suggestions\RcsSuggestionCollection;

class RcsCardObject implements ArrayHydrateInterface
{
    public const HEIGHT_SHORT = 'SHORT';
    public const HEIGHT_MEDIUM = 'MEDIUM';
    public const HEIGHT_TALL = 'TALL';

    public function __construct(
        protected string $title,
        protected string $text,
        protected string $mediaUrl,
        protected ?string $mediaDescription = '',
        protected ?string $mediaHeight = self::HEIGHT_SHORT
            | self::HEIGHT_MEDIUM
            | self::HEIGHT_TALL,
        protected ?string $thumbnailUrl = '',
        protected ?bool $mediaForceRefresh = false,
        protected ?RcsSuggestionCollection $suggestions = null
    ) {}

    public function fromArray(array $data): RcsCardObject
    {
        $this->title = $data['title'];
        $this->text = $data['text'];
        $this->mediaUrl = $data['mediaUrl'];

        if (isset($data['mediaDescription'])) {
            $this->mediaDescription = $data['mediaDescription'];
        }

        if (isset($data['mediaHeight'])) {
            $this->mediaHeight = $data['mediaHeight'];
        }

        if (isset($data['thumbnailUrl'])) {
            $this->thumbnailUrl = $data['thumbnailUrl'];
        }

        if (isset($data['mediaForceRefresh'])) {
            $this->mediaForceRefresh = $data['mediaForceRefresh'];
        }

        if (isset($data['suggestions'])) {
            $this->suggestions = new RcsSuggestionCollection($data['suggestions']);
        }

        return $this;
    }

    public function toArray(): array
    {
        $returnArray = [
            'title' => $this->getTitle(),
            'text' => $this->getText(),
            'media_url' => $this->getMediaUrl(),
        ];

        if (isset($this->mediaDescription)) {
            $returnArray['media_description'] = $this->getMediaDescription();
        }

        if (isset($this->mediaHeight)) {
            $returnArray['media_height'] = $this->getMediaHeight();
        }

        if (isset($this->thumbnailUrl)) {
            $returnArray['thumbnail_url'] = $this->getThumbnailUrl();
        }

        if (isset($this->mediaForceRefresh)) {
            $returnArray['media_force_refresh'] = $this->getMediaForceRefresh();
        }

        if (isset($this->suggestions)) {
            $returnArray['suggestions'] = $this->suggestions->toArray();
        }

        return $returnArray;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function setText(string $text): void
    {
        $this->text = $text;
    }

    public function getMediaUrl(): string
    {
        return $this->mediaUrl;
    }

    public function setMediaUrl(string $mediaUrl): void
    {
        $this->mediaUrl = $mediaUrl;
    }

    public function getMediaDescription(): ?string
    {
        return $this->mediaDescription;
    }

    public function setMediaDescription(?string $mediaDescription): void
    {
        $this->mediaDescription = $mediaDescription;
    }

    public function getThumbnailUrl(): ?string
    {
        return $this->thumbnailUrl;
    }

    public function setThumbnailUrl(?string $thumbnailUrl): void
    {
        $this->thumbnailUrl = $thumbnailUrl;
    }

    public function getMediaHeight(): ?string
    {
        return $this->mediaHeight;
    }

    public function setMediaHeight(?string $mediaHeight): void
    {
        $this->mediaHeight = $mediaHeight;
    }

    public function getMediaForceRefresh(): bool
    {
        return $this->mediaForceRefresh;
    }

    public function setMediaForceRefresh(?bool $mediaForceRefresh): void
    {
        $this->mediaForceRefresh = $mediaForceRefresh;
    }

    public function getSuggestions(): ?RcsSuggestionCollection
    {
        if (!isset($this->suggestions)) {
            $this->suggestions = new RcsSuggestionCollection();
        }

        return $this->suggestions;
    }

    public function setSuggestions(?RcsSuggestionCollection $suggestions): void
    {
        $this->suggestions = $suggestions;
    }
}
