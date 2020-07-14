<?php
declare(strict_types=1);

namespace Nexmo\Voice\NCCO\Action;

use Nexmo\Voice\Webhook;

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

    public function __construct(array $payload)
    {
        $this->payload = $payload;
    }

    /**
     * @param array<array, mixed> $data
     */
    public static function factory(array $payload, array $data): Notify
    {
        $action = new Notify($payload);

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
            'action' => 'notify',
            'payload' => $this->getPayload(),
        ];

        $eventWebhook = $this->getEventWebhook();
        if ($eventWebhook) {
            $data['eventUrl'] = $eventWebhook->getUrl();
            $data['eventMethod'] = $eventWebhook->getMethod();
        }

        return $data;
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
