<?php

declare(strict_types=1);

namespace Vonage\Messages\Channel\RCS;

use Vonage\Messages\MessageObjects\ImageObject;
use Vonage\Messages\Channel\BaseMessage;

class RcsImage extends RcsBase
{
    protected string $subType = BaseMessage::MESSAGES_SUBTYPE_IMAGE;
    protected ImageObject $image;

    public function __construct(
        string $to,
        string $from,
        ImageObject $image
    ) {
        $this->to = $to;
        $this->from = $from;
        $this->image = $image;
    }

    public function getImage(): ImageObject
    {
        return $this->image;
    }

    public function setImage(ImageObject $image): RcsImage
    {
        $this->image = $image;
        return $this;
    }

    public function toArray(): array
    {
        $returnArray = parent::toArray();

        $returnArray['image'] = $this->getImage()->toArray();

        return $returnArray;
    }
}
