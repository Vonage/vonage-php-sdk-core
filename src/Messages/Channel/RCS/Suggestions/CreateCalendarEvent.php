<?php

declare(strict_types=1);

namespace Vonage\Messages\Channel\RCS\Suggestions;

class CreateCalendarEvent extends Suggestion
{
    use FallbackUrlTrait;

    protected string $title;
    protected string $startTime;
    protected string $endTime;
    protected string $description;

    public function __construct(
        string $text,
        string $title,
        string $postbackData,
        string $startTime,
        string $endTime,
        string $description,
        string $fallbackUrl,
    ) {
        parent::__construct($text, $postbackData);
        $this->setTitle($title);
        $this->setStartTime($startTime);
        $this->setEndTime($endTime);
        $this->setDescription($description);
        $this->setFallbackUrl($fallbackUrl);
    }

    public function toArray(): array
    {
        $returnArray = parent::toArray();

        $returnArray['title'] = $this->getTitle();
        $returnArray['start_time'] = $this->getStartTime();
        $returnArray['end_time'] = $this->getEndTime();
        $returnArray['description'] = $this->getDescription();

        if ($this->getFallbackUrl() !== null) {
            $returnArray['fallback_url'] = $this->getFallbackUrl();
        }

        return $returnArray;
    }

    public function getType(): string
    {
        return Suggestion::SUGGESTION_TYPE_CREATE_CALENDAR_EVENT;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setEndTime(string $endTime): void
    {
        $this->endTime = $endTime;
    }

    public function getEndTime(): string
    {
        return $this->endTime;
    }

    public function setStartTime(string $startTime): void
    {
        $this->startTime = $startTime;
    }

    public function getStartTime(): string
    {
        return $this->startTime;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getTitle(): string
    {
        return $this->title;
    }
}
