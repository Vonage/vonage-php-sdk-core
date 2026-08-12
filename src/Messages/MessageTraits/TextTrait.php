<?php

declare(strict_types=1);

namespace Vonage\Messages\MessageTraits;

trait TextTrait
{
    private string $text;

    public function getText(): string
    {
        return $this->text;
    }

    public function setText(string $text): void
    {
        $this->text = $text;
    }
}
