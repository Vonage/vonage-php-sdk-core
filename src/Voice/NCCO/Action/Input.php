<?php
declare(strict_types=1);

namespace Nexmo\Voice\NCCO\Action;

use Nexmo\Voice\Webhook;

class Input implements ActionInterface
{
    /**
     * @var int
     */
    protected $dtmfTimeout = 3;

    /**
     * @var int
     */
    protected $dtmfMaxDigits = 4;

    /**
     * @var bool
     */
    protected $dtmfSubmitOnHash = false;

    /**
     * @var ?string
     */
    protected $speechUUID;

    /**
     * @var int
     */
    protected $speechEndOnSilence = 2;

    /**
     * @var string
     */
    protected $speechLanguage = 'en-US';

    /**
     * @var array<string>
     */
    protected $speechContext = [];

    /**
     * @var ?int
     */
    protected $speechStartTimeout;

    /**
     * @var int
     */
    protected $speechMaxDuration = 60;

    /**
     * @var ?Webhook
     */
    protected $eventWebhook;

    /**
     * @param array<array, mixed> $data
     */
    public static function factory(array $data): Input
    {
        $action = new Input();

        if (array_key_exists('dtmf', $data)) {
            $dtmf = $data['dtmf'];

            if (array_key_exists('timeout', $dtmf)) {
                $action->setDtmfTimeout($dtmf['timeout']);
            }

            if (array_key_exists('maxDigits', $dtmf)) {
                $action->setDtmfMaxDigits($dtmf['maxDigits']);
            }

            if (array_key_exists('submitOnHash', $dtmf)) {
                $action->setDtmfSubmitOnHash($dtmf['submitOnHash']);
            }
        }

        if (array_key_exists('speech', $data)) {
            $speech = $data['speech'];

            if (array_key_exists('uuid', $speech)) {
                $action->setSpeechUUID($speech['uuid'][0]);
            }

            if (array_key_exists('endOnSilence', $speech)) {
                $action->setSpeechEndOnSilence($speech['endOnSilence']);
            }

            if (array_key_exists('language', $speech)) {
                $action->setSpeechLanguage($speech['language']);
            }

            if (array_key_exists('context', $speech)) {
                $action->setSpeechContext($speech['context']);
            }

            if (array_key_exists('startTimeout', $speech)) {
                $action->setSpeechStartTimeout($speech['startTimeout']);
            }

            if (array_key_exists('maxDuration', $speech)) {
                $action->setSpeechMaxDuration($speech['maxDuration']);
            }
        }

        if (array_key_exists('eventUrl', $data)) {
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
    public function jsonSerialize()
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
            'dtmf' => [
                'timeOut' => $this->getDtmfTimeout(),
                'maxDigits' => $this->getDtmfMaxDigits(),
                'submitOnHash' => $this->getDtmfSubmitOnHash(),
            ]
        ];

        $speechUUID = $this->getSpeechUUID();
        if ($speechUUID) {
            $data['speech'] = [
                'uuid' => [$speechUUID],
                'endOnSilence' => $this->getSpeechEndOnSilence(),
                'language' => $this->getSpeechLanguage(),
                'maxDuration' => $this->getSpeechMaxDuration(),
            ];

            $context = $this->getSpeechContext();
            if (!empty($context)) {
                $data['speech']['context'] = $context;
            }

            $startTimeout = $this->getSpeechStartTimeout();
            if ($startTimeout) {
                $data['speech']['startTimeout'] = $startTimeout;
            }
        }

        $eventWebhook = $this->getEventWebhook();
        if ($eventWebhook) {
            $data['eventUrl'] = $eventWebhook->getUrl();
            $data['eventMethod'] = $eventWebhook->getMethod();
        }

        return $data;
    }

    public function getDtmfTimeout() : int
    {
        return $this->dtmfTimeout;
    }

    public function setDtmfTimeout(int $dtmfTimeout) : self
    {
        $this->dtmfTimeout = $dtmfTimeout;
        return $this;
    }

    public function getDtmfMaxDigits() : int
    {
        return $this->dtmfMaxDigits;
    }

    public function setDtmfMaxDigits(int $dtmfMaxDigits) : self
    {
        $this->dtmfMaxDigits = $dtmfMaxDigits;
        return $this;
    }

    public function getDtmfSubmitOnHash() : bool
    {
        return $this->dtmfSubmitOnHash;
    }

    public function setDtmfSubmitOnHash(bool $dtmfSubmitOnHash) : self
    {
        $this->dtmfSubmitOnHash = $dtmfSubmitOnHash;
        return $this;
    }

    public function getSpeechUUID() : ?string
    {
        return $this->speechUUID;
    }

    public function setSpeechUUID(string $speechUUID) : self
    {
        $this->speechUUID = $speechUUID;
        return $this;
    }

    public function getSpeechEndOnSilence() : int
    {
        return $this->speechEndOnSilence;
    }

    public function setSpeechEndOnSilence(int $speechEndOnSilence) : self
    {
        $this->speechEndOnSilence = $speechEndOnSilence;
        return $this;
    }

    public function getSpeechLanguage() : string
    {
        return $this->speechLanguage;
    }

    public function setSpeechLanguage(string $speechLanguage) : self
    {
        $this->speechLanguage = $speechLanguage;
        return $this;
    }

    /**
     * @return array<string>
     */
    public function getSpeechContext() : array
    {
        return $this->speechContext;
    }

    /**
     * @param array<string> $speechContext Array of words to help with speech recognition
     */
    public function setSpeechContext(array $speechContext) : self
    {
        $this->speechContext = $speechContext;
        return $this;
    }

    public function getSpeechStartTimeout() : ?int
    {
        return $this->speechStartTimeout;
    }

    public function setSpeechStartTimeout(int $speechStartTimeout) : self
    {
        $this->speechStartTimeout = $speechStartTimeout;
        return $this;
    }

    public function getSpeechMaxDuration() : int
    {
        return $this->speechMaxDuration;
    }

    public function setSpeechMaxDuration(int $speechMaxDuration) : self
    {
        $this->speechMaxDuration = $speechMaxDuration;
        return $this;
    }

    public function getEventWebhook() : ?Webhook
    {
        return $this->eventWebhook;
    }

    public function setEventWebhook(Webhook $eventWebhook) : self
    {
        $this->eventWebhook = $eventWebhook;
        return $this;
    }
}
