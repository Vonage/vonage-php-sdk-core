<?php

declare(strict_types=1);

namespace Vonage\Messages\Channel\RCS\Suggestions;

class OpenUrl extends Suggestion
{
    protected string $url;
    protected string $desription;

    public function __construct(
        string $text,
        string $postbackData,
        string $url,
        ?string $description,
    ) {
        parent::__construct($text, $postbackData);
        $this->setUrl($url);
        $this->setDesription($description);
    }

    public function toArray(): array
    {
        $returnArray = parent::toArray();

        $returnArray['url'] = $this->getUrl();

        if ($this->getDesription() !== null) {
            $returnArray['description'] = $this->getDesription();
        }

        return $returnArray;
    }

    public function getType(): string
    {
        return Suggestion::SUGGESTION_TYPE_OPEN_URL;
    }

    public function setDesription(string $desription): void
    {
        $this->desription = $desription;
    }

    public function getDesription(): string
    {
        return $this->desription;
    }

    public function setUrl(string $url): void
    {
        $this->url = $url;
    }

    public function getUrl(): string
    {
        return $this->url;
    }
}
