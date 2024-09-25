<?php

namespace Vonage\Voice\VoiceObjects;

use Vonage\Entity\Hydrator\ArrayHydrateInterface;

class AdvancedMachineDetection implements ArrayHydrateInterface
{
    public const MACHINE_BEHAVIOUR_CONTINUE = 'continue';
    public const MACHINE_BEHAVIOUR_HANGUP = 'hangup';
    public const MACHINE_MODE_DETECT = 'detect';
    public const MACHINE_MODE_DETECT_BEEP = 'detect_beep';
    public const MACHINE_MODE_DEFAULT = 'default';
    public const BEEP_TIMEOUT_MIN = 45;
    public const BEEP_TIMEOUT_MAX = 120;
    protected array $permittedBehaviour = [self::MACHINE_BEHAVIOUR_CONTINUE, self::MACHINE_BEHAVIOUR_HANGUP];
    protected array $permittedModes = [
        self::MACHINE_MODE_DETECT,
        self::MACHINE_MODE_DETECT_BEEP,
        self::MACHINE_MODE_DEFAULT
    ];

    public function __construct(
        protected string $behaviour,
        protected int $beepTimeout,
        protected string $mode = 'detect'
    ) {
        if (!$this->isValidBehaviour($behaviour)) {
            throw new \InvalidArgumentException($behaviour . ' is not a valid behavior string');
        }

        if (!$this->isValidMode($mode)) {
            throw new \InvalidArgumentException($mode . ' is not a valid mode string');
        }

        if (!$this->isValidTimeout($beepTimeout)) {
            throw new \OutOfBoundsException('Timeout ' . $beepTimeout . ' is not valid');
        }
    }

    protected function isValidBehaviour(string $behaviour): bool
    {
        if (in_array($behaviour, $this->permittedBehaviour, true)) {
            return true;
        }

        return false;
    }

    protected function isValidMode(string $mode): bool
    {
        if (in_array($mode, $this->permittedModes, true)) {
            return true;
        }

        return false;
    }

    protected function isValidTimeout(int $beepTimeout): bool
    {
        $range = [
            'options' => [
                'min_range' => self::BEEP_TIMEOUT_MIN,
                'max_range' => self::BEEP_TIMEOUT_MAX
            ]
        ];

        if (filter_var($beepTimeout, FILTER_VALIDATE_INT, $range)) {
            return true;
        }

        return false;
    }

    public function fromArray(array $data): static
    {
        if (!$this->isArrayValid($data)) {
            throw new \InvalidArgumentException('Invalid payload');
        };

        $this->behaviour = $data['behaviour'];
        $this->mode = $data['mode'];
        $this->beepTimeout = $data['beep_timeout'];

        return $this;
    }

    public function toArray(): array
    {
        return [
            'behavior' => $this->behaviour,
            'mode' => $this->mode,
            'beep_timeout' => $this->beepTimeout
        ];
    }

    protected function isArrayValid(array $data): bool
    {
        if (
            !array_key_exists('behaviour', $data)
            || !array_key_exists('mode', $data)
            || !array_key_exists('beep_timeout', $data)
        ) {
            return false;
        }

        if (
            $this->isValidBehaviour($data['behaviour'])
               && $this->isValidMode($data['mode'])
               && $this->isValidTimeout($data['beep_timeout'])
        ) {
            return true;
        };

        return false;
    }
}
