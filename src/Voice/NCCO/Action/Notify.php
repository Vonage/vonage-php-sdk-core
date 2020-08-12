<?php
declare(strict_types=1);

namespace Vonage\Voice\NCCO\Action;

use Vonage\Voice\Webhook;

class Notify implements ActionInterface
{
    /**
     * @var array
     */
    protected $payload = [];

    /**
     * @var ?Webhook
     */
    protected $eventWebhook;

    public function __construct(array $payload, Webhook $eventWebhook)
    {
        $this->payload = $payload;
        $this->eventWebhook = $eventWebhook;
    }

    /**
     * @param array<array, mixed> $data
     */
    public static function factory(array $payload, array $data): Notify
    {
        if (array_key_exists('eventUrl', $data)) {
            if (array_key_exists('eventMethod', $data)) {
                $webhook = new Webhook($data['eventUrl'], $data['eventMethod']);
            } else {
                $webhook = new Webhook($data['eventUrl']);
            }
        } else {
            throw new \InvalidArgumentException('Must supply at least an eventUrl for Notify NCCO');
        }

        return new Notify($payload, $webhook);
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
        return [
            'action' => 'notify',
            'payload' => $this->getPayload(),
            'eventUrl' => [$this->getEventWebhook()->getUrl()],
            'eventMethod' => $this->getEventWebhook()->getMethod(),
        ];
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

    public function getPayload() : array
    {
        return $this->payload;
    }

    public function addToPayload(string $key, string $value) : self
    {
        $this->payload[$key] = $value;
        return $this;
    }
}
