<?php

declare(strict_types=1);

namespace Vonage\Messages\Channel\RCS\Suggestions;

abstract class Suggestion
{
    public const SUGGESTION_TYPE_REPLY = 'reply';
    public const SUGGESTION_TYPE_DIAL = 'dial';
    public const SUGGESTION_TYPE_VIEW_LOCATION = 'view_location';
    public const SUGGESTION_TYPE_SHARE_LOCATION = 'share_location';
    public const SUGGESTION_TYPE_OPEN_URL = 'open_url';
    public const SUGGESTION_TYPE_OPEN_URL_WEBVIEW = 'open_url_in_webview';
    public const SUGGESTION_TYPE_CREATE_CALENDAR_EVENT = 'create_calendar_event';

    protected string $text;
    protected string $postbackData;

    abstract public function getType(): string;

    public function __construct(
        string $text,
        string $postbackData,
    ) {
        $this->setText($text);
        $this->setPostbackData($postbackData);
    }

    /**
     * @return array<string,string>
     */
    public function toArray(): array
    {
        return [
            'text' => $this->getText(),
            'type' => $this->getType(),
            'postback_data' => $this->getPostbackData(),
        ];
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function setText(string $text): void
    {
        $this->text = $text;
    }

    public function setPostbackData(string $postbackData): void
    {
        $this->postbackData = $postbackData;
    }

    public function getPostbackData(): string
    {
        return $this->postbackData;
    }
}
