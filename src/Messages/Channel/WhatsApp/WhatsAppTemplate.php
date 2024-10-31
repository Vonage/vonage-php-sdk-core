<?php

namespace Vonage\Messages\Channel\WhatsApp;

use Vonage\Messages\MessageObjects\FileObject;
use Vonage\Messages\MessageObjects\TemplateObject;
use Vonage\Messages\Channel\BaseMessage;
use Vonage\Messages\MessageTraits\ContextTrait;

class WhatsAppTemplate extends BaseMessage
{
    use ContextTrait;

    protected string $channel = 'whatsapp';
    protected string $subType = BaseMessage::MESSAGES_SUBTYPE_TEMPLATE;
    protected bool $validatesE164 = true;

    public function __construct(
        string $to,
        string $from,
        protected TemplateObject $templateObject,
        protected string $locale
    ) {
        $this->to = $to;
        $this->from = $from;
    }

    public function validatesE164(): bool
    {
        return $this->validatesE164;
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

        $returnArray['context'] = $this->context ?? null;

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
