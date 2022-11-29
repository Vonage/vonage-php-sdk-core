<?php

namespace Vonage\Messages\Channel\WhatsApp;

use Vonage\Messages\MessageObjects\FileObject;
use Vonage\Messages\MessageObjects\TemplateObject;
use Vonage\Messages\Channel\BaseMessage;

class WhatsAppTemplate extends BaseMessage
{
    protected string $channel = 'whatsapp';
    protected string $subType = BaseMessage::MESSAGES_SUBTYPE_TEMPLATE;

    public function __construct(
        string $to,
        string $from,
        protected TemplateObject $templateObject,
        protected string $locale
    ) {
        $this->to = $to;
        $this->from = $from;
    }

    public function toArray(): array
    {
        $returnArray = [
            'template' => $this->templateObject->toArray(),
            'whatsapp' => [
                'policy' => 'deterministic',
                'locale' => $this->getLocale()
            ]
        ];

        return array_merge($this->getBaseMessageUniversalOutputArray(), $returnArray);
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function setLocale($locale): void
    {
        $this->locale = $locale;
    }
}