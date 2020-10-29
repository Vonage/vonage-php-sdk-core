<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace Vonage\Voice\NCCO\Action;

use Vonage\Voice\Webhook;

use function array_key_exists;
use function filter_var;
use function is_array;
use function is_null;

class Conversation implements ActionInterface
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var ?string
     */
    protected $musicOnHoldUrl;

    /**
     * @var bool
     */
    protected $startOnEnter;

    /**
     * @var bool
     */
    protected $endOnExit;

    /**
     * @var bool
     */
    protected $record;

    /**
     * @var ?array<string>
     */
    protected $canSpeak;

    /**
     * @var ?array<string>
     */
    protected $canHear;

    /**
     * @var Webhook
     */
    protected $eventWebhook;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getMusicOnHoldUrl(): ?string
    {
        return $this->musicOnHoldUrl;
    }

    /**
     * @return $this
     */
    public function setMusicOnHoldUrl(string $musicOnHoldUrl): self
    {
        $this->musicOnHoldUrl = $musicOnHoldUrl;

        return $this;
    }

    public function getStartOnEnter(): ?bool
    {
        return $this->startOnEnter;
    }

    /**
     * @return $this
     */
    public function setStartOnEnter(bool $startOnEnter): self
    {
        $this->startOnEnter = $startOnEnter;

        return $this;
    }

    public function getEndOnExit(): ?bool
    {
        return $this->endOnExit;
    }

    /**
     * @return $this
     */
    public function setEndOnExit(bool $endOnExit): self
    {
        $this->endOnExit = $endOnExit;

        return $this;
    }

    public function getRecord(): ?bool
    {
        return $this->record;
    }

    /**
     * @return $this
     */
    public function setRecord(bool $record): self
    {
        $this->record = $record;

        return $this;
    }

    /**
     * @return ?array<string>
     */
    public function getCanSpeak(): ?array
    {
        return $this->canSpeak;
    }

    /**
     * @param array<string> $canSpeak
     *
     * @return Conversation
     */
    public function setCanSpeak(array $canSpeak): self
    {
        $this->canSpeak = $canSpeak;

        return $this;
    }

    /**
     * @return $this
     */
    public function addCanSpeak(string $uuid): self
    {
        $this->canSpeak[] = $uuid;

        return $this;
    }

    /**
     * @return ?array<string>
     */
    public function getCanHear(): ?array
    {
        return $this->canHear;
    }

    /**
     * @param array<string> $canHear
     *
     * @return Conversation
     */
    public function setCanHear(array $canHear): self
    {
        $this->canHear = $canHear;

        return $this;
    }

    /**
     * @return $this
     */
    public function addCanHear(string $uuid): self
    {
        $this->canHear[] = $uuid;

        return $this;
    }

    /**
     * @param array{
     *      musicOnHoldUrl?: string,
     *      startOnEnter?: bool,
     *      endOnExit?: bool,
     *      record?: bool,
     *      canSpeak?: array,
     *      canHear?: array
     *  } $data
     */
    public static function factory(string $name, array $data): Conversation
    {
        $talk = new Conversation($name);

        if (array_key_exists('musicOnHoldUrl', $data)) {
            $talk->setMusicOnHoldUrl($data['musicOnHoldUrl']);
        }

        if (array_key_exists('startOnEnter', $data)) {
            $talk->setStartOnEnter(
                filter_var($data['startOnEnter'], FILTER_VALIDATE_BOOLEAN, ['flags' => FILTER_NULL_ON_FAILURE])
            );
        }

        if (array_key_exists('endOnExit', $data)) {
            $talk->setEndOnExit(
                filter_var($data['endOnExit'], FILTER_VALIDATE_BOOLEAN, ['flags' => FILTER_NULL_ON_FAILURE])
            );
        }

        if (array_key_exists('record', $data)) {
            $talk->setRecord(
                filter_var($data['record'], FILTER_VALIDATE_BOOLEAN, ['flags' => FILTER_NULL_ON_FAILURE])
            );
        }

        if (array_key_exists('canSpeak', $data)) {
            $talk->setCanSpeak($data['canSpeak']);
        }

        if (array_key_exists('canHear', $data)) {
            $talk->setCanHear($data['canHear']);
        }

        if (array_key_exists('eventUrl', $data)) {
            if (is_array($data['eventUrl'])) {
                $data['eventUrl'] = $data['eventUrl'][0];
            }

            if (array_key_exists('eventMethod', $data)) {
                $webhook = new Webhook($data['eventUrl'], $data['eventMethod']);
            } else {
                $webhook = new Webhook($data['eventUrl']);
            }

            $talk->setEventWebhook($webhook);
        }

        return $talk;
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return $this->toNCCOArray();
    }

    /**
     * @return array<string, mixed>
     */
    public function toNCCOArray(): array
    {
        $data = [
            'action' => 'conversation',
            'name' => $this->getName(),
        ];

        if (!is_null($this->getStartOnEnter())) {
            $data['startOnEnter'] = $this->getStartOnEnter() ? 'true' : 'false';
        }

        if (!is_null($this->getEndOnExit())) {
            $data['endOnExit'] = $this->getEndOnExit() ? 'true' : 'false';
        }

        if (!is_null($this->getRecord())) {
            $data['record'] = $this->getRecord() ? 'true' : 'false';
        }

        $music = $this->getMusicOnHoldUrl();

        if ($music) {
            $data['musicOnHoldUrl'] = [$music];
        }

        $canSpeak = $this->getCanSpeak();

        if ($canSpeak) {
            $data['canSpeak'] = $canSpeak;
        }

        $canHear = $this->getCanHear();

        if ($canHear) {
            $data['canHear'] = $canHear;
        }

        if ($this->getEventWebhook()) {
            $data['eventUrl'] = [$this->getEventWebhook()->getUrl()];
            $data['eventMethod'] = $this->getEventWebhook()->getMethod();
        }

        return $data;
    }

    public function getEventWebhook(): ?Webhook
    {
        return $this->eventWebhook;
    }

    /**
     * @return $this
     */
    public function setEventWebhook(Webhook $eventWebhook): Conversation
    {
        $this->eventWebhook = $eventWebhook;

        return $this;
    }
}
