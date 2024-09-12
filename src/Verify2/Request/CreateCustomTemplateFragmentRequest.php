<?php

namespace Vonage\Verify2\Request;

use Vonage\Entity\Hydrator\ArrayHydrateInterface;

class CreateCustomTemplateFragmentRequest implements ArrayHydrateInterface
{
    protected const SMS_CHANNEL = 'sms';
    protected const VOICE_CHANNEL = 'voice';
    protected const EMAIL_CHANNEL = 'email';

    protected array $permittedChannels = [
        self::SMS_CHANNEL,
        self::VOICE_CHANNEL,
        self::EMAIL_CHANNEL,
    ];

    public function __construct(
        protected string $channel,
        protected string $locale,
        protected string $text,
    ) {
        if (!in_array($channel, $this->permittedChannels)) {
            throw new \InvalidArgumentException('Given channel not supported');
        }
    }

    public function getChannel(): string
    {
        return $this->channel;
    }

    public function setChannel(string $channel): CreateCustomTemplateFragmentRequest
    {
        if (!in_array($channel, $this->permittedChannels)) {
            throw new \InvalidArgumentException('Given channel not supported');
        }

        $this->channel = $channel;
        return $this;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function setLocale(string $locale): CreateCustomTemplateFragmentRequest
    {
        $this->locale = $locale;
        return $this;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function setText(string $text): CreateCustomTemplateFragmentRequest
    {
        $this->text = $text;
        return $this;
    }

    public function fromArray(array $data): static
    {
        $this->setChannel($data['channel']);
        $this->setLocale($data['locale']);
        $this->setText($data['text']);

        return $this;
    }

    public function toArray(): array
    {
        return [
            'channel' => $this->getChannel(),
            'locale' => $this->getLocale(),
            'text' => $this->getText(),
        ];
    }
}
