<?php

namespace Vonage\Messages\Channel\RCS\Suggestions;

class OpenUrlWebView extends OpenUrl
{
    public const VIEW_MODE_FULL = 'FULL';
    public const VIEW_MODE_TALL = 'TALL';
    public const VIEW_MODE_HALF = 'HALF';

    protected string $viewMode;

    public function __construct(
        string $text,
        string $postbackData,
        string $url,
        ?string $description,
        ?string $viewMode,
    ) {
        parent::__construct($text, $postbackData, $url, $description);
        $this->setViewMode($viewMode);
    }

    public function toArray(): array
    {
        $returnArray = parent::toArray();
        if ($this->getViewMode() !== null) {
            $returnArray['view_mode'] = $this->getViewMode();
        }

        return $returnArray;
    }

    public function getType(): string
    {
        return Suggestion::SUGGESTION_TYPE_OPEN_URL_WEBVIEW;
    }

    public function setViewMode(string $viewMode): void
    {
        if (!in_array($viewMode, [self::VIEW_MODE_HALF, self::VIEW_MODE_TALL, self::VIEW_MODE_FULL])) {
            throw new RcsInvalidWebViewModeException('View mode ' . $viewMode . ' is not valid');
        }

        $this->viewMode = $viewMode;
    }

    public function getViewMode(): string
    {
        return $this->viewMode;
    }
}
