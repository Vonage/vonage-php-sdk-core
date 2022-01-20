<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace Vonage\Voice\NCCO\Action;

use function array_key_exists;
use function filter_var;
use function is_null;

class Talk implements ActionInterface
{
    /**
     * @var bool
     */
    protected $bargeIn;

    /**
     * @var string
     */
    protected $language;

    /**
     * @var int
     */
    protected $languageStyle = 0;

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

        if (array_key_exists('language', $data)) {
            if (array_key_exists('style', $data)) {
                $talk->setLanguage($data['language'], (int) $data['style']);
            } else {
                $talk->setLanguage($data['language']);
            }
        }

        return $talk;
    }

    public function getBargeIn(): ?bool
    {
        return $this->bargeIn;
    }

    public function getLevel(): ?float
    {
        return $this->level;
    }

    public function getLoop(): ?int
    {
        return $this->loop;
    }

    public function getText(): string
    {
        return $this->text;
    }

    /**
     * @return array{action: string, bargeIn: bool, level: float, loop: int, text: string, voiceName: string}
     */
    public function jsonSerialize(): array
    {
        return $this->toNCCOArray();
    }

    /**
     * @return $this
     */
    public function setBargeIn(bool $value): self
    {
        $this->bargeIn = $value;
        return $this;
    }

    /**
     * @return $this
     */
    public function setLevel(float $level): self
    {
        $this->level = $level;

        return $this;
    }

    /**
     * @return $this
     */
    public function setLoop(int $times): self
    {
        $this->loop = $times;

        return $this;
    }

    /**
     * @return array{action: string, bargeIn: bool, level: string, loop: string, text: string, voiceName: string, language: string, style: string}
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

        if ($this->getLanguage()) {
            $data['language'] = $this->getLanguage();
            $data['style'] = (string) $this->getLanguageStyle();
        }

        return $data;
    }

    public function setLanguage(string $language, int $style = 0): self
    {
        $this->language = $language;
        $this->languageStyle = $style;

        return $this;
    }

    public function getLanguage(): ?string
    {
        return $this->language;
    }

    public function getLanguageStyle(): int
    {
        return $this->languageStyle;
    }
}
