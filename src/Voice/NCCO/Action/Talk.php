<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */
declare(strict_types=1);

namespace Vonage\Voice\NCCO\Action;

class Talk implements ActionInterface
{
    /**
     * @var bool
     */
    protected $bargeIn;

    /**
     * @var float
     */
    protected $level;

    /**
     * @var int
     */
    protected $loop;

    /**
     * @var string
     */
    protected $text;

    /**
     * @var string
     */
    protected $voiceName;

    /**
     * Talk constructor.
     *
     * @param string|null $text
     */
    public function __construct(string $text = null)
    {
        $this->text = $text;
    }

    /**
     * @param string $text
     * @param array{text: string, bargeIn?: bool, level?: float, loop?: int, voiceName?: string} $data
     * @return Talk
     */
    public static function factory(string $text, array $data): Talk
    {
        $talk = new Talk($text);

        if (array_key_exists('bargeIn', $data)) {
            $talk->setBargeIn(
                filter_var($data['bargeIn'], FILTER_VALIDATE_BOOLEAN, ['flags' => FILTER_NULL_ON_FAILURE])
            );
        }

        if (array_key_exists('level', $data)) {
            $talk->setLevel(
                filter_var($data['level'], FILTER_VALIDATE_FLOAT, ['flags' => FILTER_NULL_ON_FAILURE])
            );
        }

        if (array_key_exists('loop', $data)) {
            $talk->setLoop(
                filter_var($data['loop'], FILTER_VALIDATE_INT, ['flags' => FILTER_NULL_ON_FAILURE])
            );
        }

        if (array_key_exists('voiceName', $data)) {
            $talk->setVoiceName($data['voiceName']);
        }

        return $talk;
    }

    /**
     * @return bool|null
     */
    public function getBargeIn(): ?bool
    {
        return $this->bargeIn;
    }

    /**
     * @return float|null
     */
    public function getLevel(): ?float
    {
        return $this->level;
    }

    /**
     * @return int|null
     */
    public function getLoop(): ?int
    {
        return $this->loop;
    }

    /**
     * @return string
     */
    public function getText(): string
    {
        return $this->text;
    }

    /**
     * @return string|null
     */
    public function getVoiceName(): ?string
    {
        return $this->voiceName;
    }

    /**
     * @return array{action: string, bargeIn: bool, level: float, loop: int, text: string, voiceName: string}
     */
    public function jsonSerialize(): array
    {
        return $this->toNCCOArray();
    }

    /**
     * @param bool $value
     * @return $this
     */
    public function setBargeIn(bool $value): self
    {
        $this->bargeIn = $value;
        return $this;
    }

    /**
     * @param float $level
     * @return $this
     */
    public function setLevel(float $level): self
    {
        $this->level = $level;

        return $this;
    }

    /**
     * @param int $times
     * @return $this
     */
    public function setLoop(int $times): self
    {
        $this->loop = $times;

        return $this;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setVoiceName(string $name): self
    {
        $this->voiceName = $name;

        return $this;
    }

    /**
     * @return array{action: string, bargeIn: bool, level: float, loop: int, text: string, voiceName: string}
     */
    public function toNCCOArray(): array
    {
        $data = [
            'action' => 'talk',
            'text' => $this->getText(),
        ];

        if (!is_null($this->getBargeIn())) {
            $data['bargeIn'] = $this->getBargeIn() ? 'true' : 'false';
        }

        if (!is_null($this->getLevel())) {
            $data['level'] = (string)$this->getLevel();
        }

        if (!is_null($this->getLoop())) {
            $data['loop'] = (string)$this->getLoop();
        }

        if ($this->getVoiceName()) {
            $data['voiceName'] = $this->getVoiceName();
        }

        return $data;
    }
}
