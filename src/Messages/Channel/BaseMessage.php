<?php

namespace Vonage\Messages\Channel;

abstract class BaseMessage implements Message
{
    protected string $subType;
    protected string $to;
    protected string $from;
    protected string $channel;
    protected ?string $clientRef = null;
    protected ?string $webhookUrl = null;
    protected ?string $webhookVersion = null;

    protected array $permittedVersions = [
        'v0.1',
        'v1'
    ];

    public const MESSAGES_SUBTYPE_TEXT = 'text';
    public const MESSAGES_SUBTYPE_IMAGE = 'image';
    public const MESSAGES_SUBTYPE_VCARD = 'vcard';
    public const MESSAGES_SUBTYPE_AUDIO = 'audio';
    public const MESSAGES_SUBTYPE_VIDEO = 'video';
    public const MESSAGES_SUBTYPE_FILE = 'file';
    public const MESSAGES_SUBTYPE_TEMPLATE = 'template';
    public const MESSAGES_SUBTYPE_STICKER = 'sticker';
    public const MESSAGES_SUBTYPE_CUSTOM = 'custom';

    public function getClientRef(): ?string
    {
        return $this->clientRef;
    }

    public function setClientRef(string $clientRef): void
    {
        $this->clientRef = $clientRef;
    }

    public function getSubType(): string
    {
        return $this->subType;
    }

    public function getFrom(): string
    {
        return $this->from;
    }

    public function setFrom(string $from): void
    {
        $this->from = $from;
    }

    public function getChannel(): string
    {
        return $this->channel;
    }

    public function getTo(): string
    {
        return $this->to;
    }

    public function setTo(string $to): void
    {
        $this->to = $to;
    }

    public function setWebhookUrl(string $url): void
    {
        $this->webhookUrl = $url;
    }

    public function getWebhookUrl(): ?string
    {
        return $this->webhookUrl;
    }

    public function setWebhookVersion(string $version): void
    {
        if (! in_array($version, $this->permittedVersions, true)) {
            throw new \InvalidArgumentException($version . ' is not a valid webhook version');
        }

        $this->webhookVersion = $version;
    }

    public function getWebhookVersion(): ?string
    {
        return $this->webhookVersion;
    }

    public function getBaseMessageUniversalOutputArray(): array
    {
        $returnArray = [
            'message_type' => $this->getSubType(),
            'to' => $this->getTo(),
            'from' => $this->getFrom(),
            'channel' => $this->getChannel(),
        ];

        if ($this->getClientRef()) {
            $returnArray['client_ref'] = $this->getClientRef();
        }

        if ($this->getWebhookUrl()) {
            $returnArray['webhook_url'] = $this->getWebhookUrl();
        }

        if ($this->getWebhookVersion()) {
            $returnArray['webhook_version'] = $this->getWebhookVersion();
        }

        return $returnArray;
    }
}
