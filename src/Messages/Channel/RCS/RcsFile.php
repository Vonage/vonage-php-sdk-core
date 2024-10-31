<?php

namespace Vonage\Messages\Channel\RCS;

use Vonage\Messages\MessageObjects\FileObject;
use Vonage\Messages\Channel\BaseMessage;
use Vonage\Messages\MessageTraits\TtlTrait;

class RcsFile extends BaseMessage
{
    use TtlTrait;

    protected const RCS_TEXT_MIN_TTL = 300;
    protected const RCS_TEXT_MAX_TTL = 259200;

    protected string $subType = BaseMessage::MESSAGES_SUBTYPE_FILE;
    protected string $channel = 'rcs';
    protected FileObject $file;
    protected bool $validatesE164 = true;

    public function __construct(
        string $to,
        string $from,
        FileObject $file
    ) {
        $this->to = $to;
        $this->from = $from;
        $this->file = $file;
    }

    public function validatesE164(): bool
    {
        return $this->validatesE164;
    }

    public function setTtl(?int $ttl): void
    {
        $range = [
            'options' => [
                'min_range' => self::RCS_TEXT_MIN_TTL,
                'max_range' => self::RCS_TEXT_MAX_TTL
            ]
        ];

        if (!filter_var($ttl, FILTER_VALIDATE_INT, $range)) {
            throw new RcsInvalidTtlException('Timeout ' . $ttl . ' is not valid');
        }

        $this->ttl = $ttl;
    }

    public function getFile(): FileObject
    {
        return $this->file;
    }

    public function setFile(FileObject $fileObject): RcsFile
    {
        $this->file = $fileObject;
        return $this;
    }

    public function toArray(): array
    {
        $returnArray = $this->getBaseMessageUniversalOutputArray();

        $returnArray['file'] = $this->getFile()->toArray();

        if ($this->getClientRef()) {
            $returnArray['client_ref'] = $this->getClientRef();
        }

        if ($this->getWebhookUrl()) {
            $returnArray['webhook_url'] = $this->getWebhookUrl();
        }

        if ($this->getTtl()) {
            $returnArray['ttl'] = $this->getTtl();
        }

        return $returnArray;
    }
}
