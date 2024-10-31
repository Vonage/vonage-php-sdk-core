<?php

namespace Vonage\Messages\Channel\Messenger;

trait MessengerObjectTrait
{
    private ?string $category;
    private ?string $tag;

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function requiresMessengerObject(): bool
    {
        return $this->getTag() || $this->getCategory();
    }

    public function setCategory(string $category): void
    {
        $this->category = $category;
    }

    public function getTag(): ?string
    {
        return $this->tag;
    }

    public function setTag(string $tag): void
    {
        $this->tag = $tag;
    }

    public function getMessengerObject(): array
    {
        $messengerObject = [
            'category' => $this->getCategory(),
        ];

        if ($this->getTag()) {
            $messengerObject['tag'] = $this->getTag();
        }

        return $messengerObject;
    }
}
