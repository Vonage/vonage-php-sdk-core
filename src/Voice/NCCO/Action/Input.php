<?php

declare(strict_types=1);

namespace Vonage\Voice\NCCO\Action;

use phpDocumentor\Reflection\Types\This;
use RuntimeException;
use Vonage\Voice\Webhook;

use function array_key_exists;
use function filter_var;
use function is_array;
use function is_null;

class Input implements ActionInterface
{
    public const ASYNCHRONOUS_MODE = 'asynchronous';
    public const SYNCHRONOUS_MODE = 'synchronous';

    public array $allowedModes = [
        self::SYNCHRONOUS_MODE,
        self::ASYNCHRONOUS_MODE,
    ];
    protected ?int $dtmfTimeout = null;

    protected ?int $dtmfMaxDigits = null;

    protected ?bool $dtmfSubmitOnHash = null;

    protected ?string $speechUUID = null;

    protected ?int $speechEndOnSilence = null;

    protected ?string $speechLanguage = null;

    protected ?string $mode = null;

    /**
     * @var ?array<string>
     */
    protected ?array $speechContext = null;

    protected ?int $speechStartTimeout = null;

    protected ?int $speechMaxDuration = null;

    protected ?Webhook $eventWebhook = null;

    protected bool $enableSpeech = false;

    protected bool $enableDtmf = false;

    /**
     * @param array<array, mixed> $data
     */
    public static function factory(array $data): Input
    {
        $action = new self();

        if (array_key_exists('dtmf', $data)) {
            $dtmf = $data['dtmf'];
            $action->setEnableDtmf(true);
            if (is_object($dtmf)) {
                $dtmf = (array)$dtmf;
            }

            if (array_key_exists('timeOut', $dtmf)) {
                $action->setDtmfTimeout((int)$dtmf['timeOut']);
            }

            if (array_key_exists('maxDigits', $dtmf)) {
                $action->setDtmfMaxDigits((int)$dtmf['maxDigits']);
            }

            if (array_key_exists('submitOnHash', $dtmf)) {
                $action->setDtmfSubmitOnHash(
                    filter_var($dtmf['submitOnHash'], FILTER_VALIDATE_BOOLEAN, ['flags' => FILTER_NULL_ON_FAILURE])
                );
            }
        }

        if (array_key_exists('mode', $data)) {
            $action->setMode($data['mode']);
        }

        if (array_key_exists('speech', $data)) {
            $speech = $data['speech'];
            $action->setEnableSpeech(true);
            if (is_object($speech)) {
                $speech = (array)$speech;
            }

            if (array_key_exists('uuid', $speech)) {
                $action->setSpeechUUID($speech['uuid'][0]);
            }

            if (array_key_exists('endOnSilence', $speech)) {
                $action->setSpeechEndOnSilence((int)$speech['endOnSilence']);
            }

            if (array_key_exists('language', $speech)) {
                $action->setSpeechLanguage($speech['language']);
            }

            if (array_key_exists('context', $speech)) {
                $action->setSpeechContext($speech['context']);
            }

            if (array_key_exists('startTimeout', $speech)) {
                $action->setSpeechStartTimeout((int)$speech['startTimeout']);
            }

            if (array_key_exists('maxDuration', $speech)) {
                $action->setSpeechMaxDuration((int)$speech['maxDuration']);
            }
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

            $action->setEventWebhook($webhook);
        }

        return $action;
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
            'action' => 'input',
        ];

        if (
            $this->getEnableDtmf() === false && $this->getEnableSpeech() === false && $this->getMode() !==
            self::ASYNCHRONOUS_MODE
        ) {
            throw new RuntimeException('Input NCCO action must have either speech or DTMF enabled');
        }

        if ($this->getEnableDtmf()) {
            $dtmf = [];

            if ($this->getDtmfTimeout()) {
                $dtmf['timeOut'] = $this->getDtmfTimeout();
            }

            if ($this->getDtmfMaxDigits()) {
                $dtmf['maxDigits'] = $this->getDtmfMaxDigits();
            }

            if (!is_null($this->getDtmfSubmitOnHash())) {
                $dtmf['submitOnHash'] = $this->getDtmfSubmitOnHash() ? 'true' : 'false';
            }

            $data['dtmf'] = (object)$dtmf;
        }

        if ($this->getEnableSpeech()) {
            $speech = [];

            if ($this->getSpeechUUID()) {
                $speech['uuid'] = [$this->getSpeechUUID()];
            }

            if ($this->getSpeechEndOnSilence()) {
                $speech['endOnSilence'] = $this->getSpeechEndOnSilence();
            }

            if ($this->getSpeechLanguage()) {
                $speech['language'] = $this->getSpeechLanguage();
            }

            if ($this->getSpeechMaxDuration()) {
                $speech['maxDuration'] = $this->getSpeechMaxDuration();
            }

            $context = $this->getSpeechContext();

            if (!empty($context)) {
                $speech['context'] = $context;
            }

            $startTimeout = $this->getSpeechStartTimeout();
            if ($startTimeout) {
                $speech['startTimeout'] = $startTimeout;
            }

            $data['speech'] = (object)$speech;
        }

        $eventWebhook = $this->getEventWebhook();

        if ($eventWebhook) {
            $data['eventUrl'] = [$eventWebhook->getUrl()];
            $data['eventMethod'] = $eventWebhook->getMethod();
        }

        if ($this->getMode()) {
            $data['mode'] = $this->getMode();
        }

        return $data;
    }

    public function getDtmfTimeout(): ?int
    {
        return $this->dtmfTimeout;
    }

    /**
     * @return $this
     */
    public function setDtmfTimeout(int $dtmfTimeout): self
    {
        $this->setEnableDtmf(true);
        $this->dtmfTimeout = $dtmfTimeout;

        return $this;
    }

    public function getDtmfMaxDigits(): ?int
    {
        return $this->dtmfMaxDigits;
    }

    public function setDtmfMaxDigits(int $dtmfMaxDigits): self
    {
        $this->setEnableDtmf(true);
        $this->dtmfMaxDigits = $dtmfMaxDigits;

        return $this;
    }

    public function getDtmfSubmitOnHash(): ?bool
    {
        return $this->dtmfSubmitOnHash;
    }

    public function setDtmfSubmitOnHash(bool $dtmfSubmitOnHash): self
    {
        $this->setEnableDtmf(true);
        $this->dtmfSubmitOnHash = $dtmfSubmitOnHash;

        return $this;
    }

    public function getSpeechUUID(): ?string
    {
        return $this->speechUUID;
    }

    public function setSpeechUUID(string $speechUUID): self
    {
        $this->setEnableSpeech(true);
        $this->speechUUID = $speechUUID;

        return $this;
    }

    public function getSpeechEndOnSilence(): ?int
    {
        return $this->speechEndOnSilence;
    }

    public function setSpeechEndOnSilence(int $speechEndOnSilence): self
    {
        $this->setEnableSpeech(true);
        $this->speechEndOnSilence = $speechEndOnSilence;

        return $this;
    }

    public function getSpeechLanguage(): ?string
    {
        return $this->speechLanguage;
    }

    public function setSpeechLanguage(string $speechLanguage): self
    {
        $this->setEnableSpeech(true);
        $this->speechLanguage = $speechLanguage;

        return $this;
    }

    /**
     * @return ?array<string>
     */
    public function getSpeechContext(): ?array
    {
        return $this->speechContext;
    }

    /**
     * @param array<string> $speechContext Array of words to help with speech recognition
     */
    public function setSpeechContext(array $speechContext): self
    {
        $this->setEnableSpeech(true);
        $this->speechContext = $speechContext;

        return $this;
    }

    public function getSpeechStartTimeout(): ?int
    {
        return $this->speechStartTimeout;
    }

    public function setSpeechStartTimeout(int $speechStartTimeout): self
    {
        $this->setEnableSpeech(true);
        $this->speechStartTimeout = $speechStartTimeout;

        return $this;
    }

    public function getSpeechMaxDuration(): ?int
    {
        return $this->speechMaxDuration;
    }

    public function setSpeechMaxDuration(int $speechMaxDuration): self
    {
        $this->setEnableSpeech(true);
        $this->speechMaxDuration = $speechMaxDuration;

        return $this;
    }

    public function getEventWebhook(): ?Webhook
    {
        return $this->eventWebhook;
    }

    /**
     * @return $this
     */
    public function setEventWebhook(Webhook $eventWebhook): self
    {
        $this->eventWebhook = $eventWebhook;

        return $this;
    }

    public function getEnableSpeech(): bool
    {
        return $this->enableSpeech;
    }

    public function setEnableSpeech(bool $enableSpeech): Input
    {
        $this->enableSpeech = $enableSpeech;

        return $this;
    }

    public function getEnableDtmf(): bool
    {
        return $this->enableDtmf;
    }

    public function setEnableDtmf(bool $enableDtmf): Input
    {
        $this->enableDtmf = $enableDtmf;

        return $this;
    }

    public function getMode(): ?string
    {
        return $this->mode;
    }

    public function setMode(?string $mode): self
    {
        if ($this->getEnableDtmf()) {
            if ($mode == self::ASYNCHRONOUS_MODE) {
                throw new \InvalidArgumentException('Cannot have DTMF input when using Asynchronous mode.');
            }
        }

        if (!in_array($mode, $this->allowedModes)) {
            throw new \InvalidArgumentException('Mode not a valid string');
        }

        $this->mode = $mode;
        return $this;
    }
}
