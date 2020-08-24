<?php
declare(strict_types=1);

namespace Vonage\Voice\NCCO\Action;

use Vonage\Voice\Webhook;

class Record implements ActionInterface
{
    const FORMAT_MP3 = "mp3";
    const FORMAT_WAV = "wav";
    const FORMAT_OGG = "ogg";

    const SPLIT = 'conversation';

    /**
     * @var string Record::FORMAT_*
     */
    protected $format = 'mp3';

    /**
     * @var string Record::SPLIT
     */
    protected $split;

    /**
     * @var int
     */
    protected $channels;

    /**
     * @var int
     */
    protected $endOnSilence;

    /**
     * @var string '*'|'#'|1'|2'|'3'|'4'|'5'|'6'|'7'|'8'|'9'|'0'
     */
    protected $endOnKey;

    /**
     * @var int
     */
    protected $timeOut = 7200;

    /**
     * @var bool
     */
    protected $beepStart = false;

    /**
     * @var Webhook
     */
    protected $eventWebhook;
    
    public static function factory(array $data) : self
    {
        $action = new Record();

        if (array_key_exists('format', $data)) {
            $action->setFormat($data['format']);
        }

        if (array_key_exists('split', $data)) {
            $action->setSplit($data['split']);
        }

        if (array_key_exists('channels', $data)) {
            $action->setChannels($data['channels']);
        }

        if (array_key_exists('endOnSilence', $data)) {
            $action->setEndOnSilence(
                filter_var($data['endOnSilence'], FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE)
            );
        }

        if (array_key_exists('endOnKey', $data)) {
            $action->setEndOnKey($data['endOnKey']);
        }

        if (array_key_exists('timeOut', $data)) {
            $action->setTimeout($data['timeOut']);
        }

        if (array_key_exists('beepStart', $data)) {
            $action->setBeepStart(
                filter_var($data['beepStart'], FILTER_VALIDATE_BOOLEAN, ['flags' => FILTER_NULL_ON_FAILURE])
            );
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
    public function jsonSerialize() : array
    {
        return $this->toNCCOArray();
    }

    /**
     * @return array<string, mixed>
     */
    public function toNCCOArray(): array
    {
        $data = [
            'action' => 'record',
            'format' => $this->getFormat(),
            'timeOut' => (string) $this->getTimeout(),
            'beepStart' => $this->getBeepStart() ? 'true' : 'false',
        ];

        if ($this->getEndOnSilence()) {
            $data['endOnSilence'] = (string) $this->getEndOnSilence();
        }

        if ($this->getEndOnKey()) {
            $data['endOnKey'] = $this->getEndOnKey();
        }

        if ($this->getChannels()) {
            $data['channels'] = (string) $this->getChannels();
        }

        if ($this->getSplit()) {
            $data['split'] = $this->getSplit();
        }

        if ($this->getEventWebhook()) {
            $data['eventUrl'] = [$this->getEventWebhook()->getUrl()];
            $data['eventMethod'] = $this->getEventWebhook()->getMethod();
        }
        return $data;
    }

    public function getFormat() : string
    {
        return $this->format;
    }

    public function setFormat(string $format) : self
    {
        $this->format = $format;

        return $this;
    }

    public function getSplit() : ?string
    {
        return $this->split;
    }

    public function setSplit(string $split) : self
    {
        if ($split !== 'conversation') {
            throw new \InvalidArgumentException('Split value must be "conversation" if enabling');
        }

        $this->split = $split;
        return $this;
    }

    public function getEndOnKey() : ?string
    {
        return $this->endOnKey;
    }

    public function setEndOnKey(string $endOnKey) : self
    {
        $match = preg_match('/^[*#0-9]{1}$/', $endOnKey);
        if ($match === 0 || $match === false) {
            throw new \InvalidArgumentException('Invalid End on Key character');
        }

        $this->endOnKey = $endOnKey;
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

    public function getEndOnSilence() : ?int
    {
        return $this->endOnSilence;
    }

    public function setEndOnSilence(int $endOnSilence) : self
    {
        if ($endOnSilence > 10 || $endOnSilence < 3) {
            throw new \InvalidArgumentException('End On Silence value must be between 3 and 10 seconds, inclusive');
        }

        $this->endOnSilence = $endOnSilence;
        return $this;
    }

    public function getTimeout() : int
    {
        return $this->timeOut;
    }

    public function setTimeout(int $timeOut) : self
    {
        if ($timeOut > 7200 || $timeOut < 3) {
            throw new \InvalidArgumentException('TimeOut value must be between 3 and 7200 seconds, inclusive');
        }

        $this->timeOut = $timeOut;
        return $this;
    }

    public function getBeepStart() : bool
    {
        return $this->beepStart;
    }

    public function setBeepStart(bool $beepStart) : self
    {
        $this->beepStart = $beepStart;
        return $this;
    }

    public function getChannels() : ?int
    {
        return $this->channels;
    }

    public function setChannels(int $channels) : self
    {
        if ($channels > 32) {
            throw new \InvalidArgumentException('Number of channels must be 32 or less');
        }

        if ($channels > 1) {
            $this->channels = $channels;
            $this->setSplit(self::SPLIT);
            $this->format = self::FORMAT_WAV;
        } else {
            $this->channels = null;
            $this->split = null;
        }

        return $this;
    }
}
