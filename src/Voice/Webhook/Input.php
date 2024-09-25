<?php

declare(strict_types=1);

namespace Vonage\Voice\Webhook;

use DateTimeImmutable;
use Exception;

use function is_string;
use function json_decode;

class Input
{
    protected ?array $speech = null;

    protected ?array $dtmf = null;

    protected ?string $from = null;

    protected ?string $to = null;

    protected ?string $uuid = null;

    protected ?string $conversationUuid = null;

    protected ?DateTimeImmutable $timestamp = null;

    /**
     * @throws Exception
     */
    public function __construct(array $data)
    {
        // GET requests push this in as a string
        if (is_string($data['speech'])) {
            $data['speech'] = json_decode($data['speech'], true);
        }

        $this->speech = $data['speech'];

        // GET requests push this in as a string
        if (is_string($data['dtmf'])) {
            $data['dtmf'] = json_decode($data['dtmf'], true);
        }

        $this->dtmf = $data['dtmf'];
        $this->to = $data['to'];
        $this->from = $data['from'];
        $this->uuid = $data['uuid'];
        $this->conversationUuid = $data['conversation_uuid'];
        $this->timestamp = new DateTimeImmutable($data['timestamp']);
    }

    public function getSpeech(): array
    {
        return $this->speech;
    }

    public function getDtmf(): array
    {
        return $this->dtmf;
    }

    public function getFrom(): string
    {
        return $this->from;
    }

    public function getTo(): string
    {
        return $this->to;
    }

    public function getUuid(): string
    {
        return $this->uuid;
    }

    public function getConversationUuid(): string
    {
        return $this->conversationUuid;
    }

    public function getTimestamp(): DateTimeImmutable
    {
        return $this->timestamp;
    }
}
