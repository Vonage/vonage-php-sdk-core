<?php

namespace Vonage\Messages\Channel\RCS;

use Vonage\Messages\MessageObjects\FileObject;
use Vonage\Messages\Channel\BaseMessage;

class RcsFile extends RcsBase
{
    protected string $subType = BaseMessage::MESSAGES_SUBTYPE_FILE;
    protected FileObject $file;

    public function __construct(
        string $to,
        string $from,
        FileObject $file
    ) {
        $this->to = $to;
        $this->from = $from;
        $this->file = $file;
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
        $returnArray = parent::toArray();

        $returnArray['file'] = $this->getFile()->toArray();

        return $returnArray;
    }
}
