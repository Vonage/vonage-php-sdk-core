<?php

namespace Vonage\Messages\MessageType\Messenger;

trait MessengerObjectTrait
{
    private string $category;
    private string $tag;
    private static array $validCategories = [
        'response',
        'update',
        'message_tag'
    ];

    /**
     * @return string
     */
    public function getCategory(): string
    {
        return $this->category;
    }

    public static function validateCategory(string $category): bool
    {
        return in_array($category, self::$validCategories, true);
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
    public function getTag(): string
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
