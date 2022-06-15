<?php

namespace Vonage\Messages\Channel\Messenger;

trait MessengerObjectTrait
{
    private ?string $category;
    private ?string $tag;

    /**
     * @return string
     */
    public function getCategory(): ?string
    {
        return $this->category;
    }


    public function requiresMessengerObject(): bool
    {
        return $this->getTag() || $this->getCategory();
    }

    /**
     * @param string $category
     */
    public function setCategory(string $category): void
    {
        $this->category = $category;
    }

    /**
     * @return string
     */
    public function getTag(): ?string
    {
        return $this->tag;
    }

    /**
     * @param string $tag
     */
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
