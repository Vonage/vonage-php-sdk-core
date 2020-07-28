<?php
declare(strict_types=1);

namespace Nexmo\Voice\NCCO\Action;

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
    protected $startOnEnter = true;

    /**
     * @var bool
     */
    protected $endOnExit = false;

    /**
     * @var bool
     */
    protected $record = false;

    /**
     * @var ?array<string>
     */
    protected $canSpeak;

    /**
     * @var ?array<string>
     */
    protected $canHear;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function getName() : string
    {
        return $this->name;
    }

    public function getMusicOnHoldUrl() : ?string
    {
        return $this->musicOnHoldUrl;
    }

    public function setMusicOnHoldUrl(string $musicOnHoldUrl) : self
    {
        $this->musicOnHoldUrl = $musicOnHoldUrl;
        return $this;
    }

    public function getStartOnEnter() : bool
    {
        return $this->startOnEnter;
    }

    public function setStartOnEnter(bool $startOnEnter) : self
    {
        $this->startOnEnter = $startOnEnter;
        return $this;
    }

    public function getEndOnExit() : bool
    {
        return $this->endOnExit;
    }

    public function setEndOnExit(bool $endOnExit) : self
    {
        $this->endOnExit = $endOnExit;
        return $this;
    }

    public function getRecord() : bool
    {
        return $this->record;
    }

    public function setRecord(bool $record) : self
    {
        $this->record = $record;
        return $this;
    }

    /**
     * @return ?array<string>
     */
    public function getCanSpeak() : ?array
    {
        return $this->canSpeak;
    }

    /**
     * @param array<string> $canSpeak
     */
    public function setCanSpeak(array $canSpeak) : self
    {
        $this->canSpeak = $canSpeak;
        return $this;
    }

    public function addCanSpeak(string $uuid) : self
    {
        $this->canSpeak[] = $uuid;
        return $this;
    }

    /**
     * @return ?array<string>
     */
    public function getCanHear() : ?array
    {
        return $this->canHear;
    }

    /**
     * @param array<string> $canHear
     */
    public function setCanHear(array $canHear) : self
    {
        $this->canHear = $canHear;
        return $this;
    }

    public function addCanHear(string $uuid) : self
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
        
        return $talk;
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize() : array
    {
        return $this->toNCCOArray();
    }

    /**
     * @return array<string, mixed>
     */
    public function toNCCOArray() : array
    {
        $data = [
            'action' => 'conversation',
            'name' => $this->getName(),
            'startOnEnter' => $this->getStartOnEnter() ? 'true' : 'false',
            'endOnExit' => $this->getEndOnExit() ? 'true' : 'false',
            'record' => $this->getRecord() ? 'true' : 'false',
        ];

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

        return $data;
    }
}
