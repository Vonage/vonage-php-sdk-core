<?php

declare(strict_types=1);

namespace Vonage\Conversation\ConversationObjects;

use phpDocumentor\Reflection\Types\This;
use Vonage\Entity\Hydrator\ArrayHydrateInterface;

class Channel implements ArrayHydrateInterface
{
    public const CHANNEL_TYPE_APP = 'app';
    public const CHANNEL_TYPE_PHONE = 'phone';
    public const CHANNEL_TYPE_SMS = 'sms';
    public const CHANNEL_TYPE_MMS = 'mms';
    public const CHANNEL_TYPE_WHATSAPP = 'whatsapp';
    public const CHANNEL_TYPE_VIBER = 'viber';
    public const CHANNEL_TYPE_MESSENGER = 'messenger';

    protected static array $allowedTypes = [
        self::CHANNEL_TYPE_MESSENGER,
        self::CHANNEL_TYPE_APP,
        self::CHANNEL_TYPE_MMS,
        self::CHANNEL_TYPE_PHONE,
        self::CHANNEL_TYPE_VIBER,
        self::CHANNEL_TYPE_SMS,
        self::CHANNEL_TYPE_WHATSAPP
    ];

    protected array $data;

    public function __construct()
    {
    }

    public function fromArray(array $data): void
    {
        $this->data = $data;
    }

    public function toArray(): array
    {
        return $this->data;
    }

    public function __get(string $name)
    {
        return $this->data[$name];
    }

    public function __set(string $name, $value)
    {
        $this->data[$name] = $value;
    }

    public function __isset(string $name): bool
    {
        return isset($this->data[$name]);
    }

    public function addUserFromTypes(array $fromTypes): Channel
    {
        $this->data['from']['type'] = implode(',', $fromTypes);

        return $this;
    }

    public function addUserToField(string $userId): Channel
    {
        if (!isset($this->data['type'])) {
            throw new \InvalidArgumentException('Cannot set -To- fields when channel type is not defined');
        }

        $this->data['to']['type'] = $this->data['type'];
        $this->data['to']['user'] = $userId;

        return $this;
    }

    public static function createChannel(string $channelType): Channel
    {
        if (! in_array($channelType, self::$allowedTypes, true)) {
            throw new \InvalidArgumentException($channelType . 'not an allowed type');
        }

        $channel = new Channel();
        $channel->type = $channelType;

        return $channel;
    }
}
