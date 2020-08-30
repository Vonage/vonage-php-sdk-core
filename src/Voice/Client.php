<?php
declare(strict_types=1);

namespace Vonage\Voice;

use Vonage\Client\APIClient;
use Vonage\Client\APIResource;
use Vonage\Voice\NCCO\Action\Talk;
use Vonage\Voice\NCCO\Action\Transfer;
use Vonage\Voice\Webhook\Event;
use Vonage\Entity\IterableAPICollection;
use Vonage\Entity\Filter\FilterInterface;
use Vonage\Entity\Hydrator\ArrayHydrator;
use Vonage\Voice\NCCO\NCCO;

class Client implements APIClient
{
    /**
     * @var APIResource
     */
    protected $api;

    public function __construct(APIResource $api)
    {
        $this->api = $api;
    }

    public function getAPIResource(): APIResource
    {
        return $this->api;
    }

    /**
     * @return array{uuid: string, conversation_uuid: string, status: string, direction: string}
     */
    public function createOutboundCall(OutboundCall $call) : Event
    {
        $json = [
            'to' => [$call->getTo()],
            'from' => $call->getFrom(),
        ];

        if (null !== $call->getAnswerWebhook()) {
            $json['answer_url'] = [$call->getAnswerWebhook()->getUrl()];
            $json['answer_method'] = $call->getAnswerWebhook()->getMethod();
        }

        if (null !== $call->getEventWebhook()) {
            $json['event_url'] = [$call->getEventWebhook()->getUrl()];
            $json['event_method'] = $call->getEventWebhook()->getMethod();
        }

        if (null !== $call->getNCCO()) {
            $json['ncco'] = $call->getNCCO();
        }

        if ($call->getMachineDetection()) {
            $json['machine_detection'] = $call->getMachineDetection();
        }

        if (!is_null($call->getLengthTimer())) {
            $json['length_timer'] = (string) $call->getLengthTimer();
        }

        if (!is_null($call->getRingingTimer())) {
            $json['ringing_timer'] = (string) $call->getRingingTimer();
        }

        $event = $this->api->create($json);
        $event['to'] = $call->getTo()->getId();
        $event['from'] = $call->getFrom()->getId();
        $event['timestamp'] = (new \DateTimeImmutable("now", new \DateTimeZone("UTC")))->format(DATE_ATOM);

        $event = new Event($event);
        return $event;
    }

    public function earmuffCall(string $callId) : void
    {
        $this->modifyCall($callId, CallAction::EARMUFF);
    }

    public function get(string $callId) : Call
    {
        $data = $this->api->get($callId);
        $call = (new CallFactory())->create($data);

        return $call;
    }

    public function hangupCall(string $callId) : void
    {
        $this->modifyCall($callId, CallAction::HANGUP);
    }

    public function modifyCall(string $callId, string $action) : void
    {
        $this->api->update($callId, [
            'action' => $action,
        ]);
    }

    public function muteCall(string $callId) : void
    {
        $this->modifyCall($callId, CallAction::MUTE);
    }

    /**
     * @return array{uuid: string, message: string}
     */
    public function playDTMF(string $callId, string $digits) : array
    {
        $response = $this->api->update($callId . '/dtmf', [
            'digits' => $digits
        ]);

        return $response;
    }

    /**
     * @return array{uuid: string, message: string}
     */
    public function playTTS(string $callId, Talk $action) : array
    {
        $payload = $action->toNCCOArray();
        unset($payload['action']);

        $response = $this->api->update($callId . '/talk', $payload);

        return $response;
    }

    public function search(FilterInterface $filter = null) : IterableAPICollection
    {
        $response = $this->api->search($filter);
        $response->setApiResource(clone $this->api);
        $response->setNaiveCount(true);

        $hydrator = new ArrayHydrator();
        $hydrator->setPrototype(new Call());

        $response->setHydrator($hydrator);
        return $response;
    }

    /**
     * @return array{uuid: string, message: string}
     */
    public function stopStreamAudio(string $callId) : array
    {
        return $this->api->delete($callId . '/stream');
    }

    /**
     * @return array{uuid: string, message: string}
     */
    public function stopTTS(string $callId) : array
    {
        return $this->api->delete($callId . '/talk');
    }

    /**
     * @return array{uuid: string, message: string}
     */
    public function streamAudio(string $callId, string $url, int $loop = 1, float $volumeLevel = 0.0) : array
    {
        return $this->api->update($callId . '/stream', [
            'stream_url' => [$url],
            'loop' => (string) $loop,
            'level' => (string) $volumeLevel,
        ]);
    }

    public function transferCallWithNCCO(string $callId, NCCO $ncco) : void
    {
        $this->api->update($callId, [
            'action' => 'transfer',
            'destination' => [
                'type' => 'ncco',
                'ncco' => $ncco->toArray()
            ],
        ]);
    }

    public function transferCallWithUrl(string $callId, string $url) : void
    {
        $this->api->update($callId, [
            'action' => 'transfer',
            'destination' => [
                'type' => 'ncco',
                'url' => [$url]
            ]
        ]);
    }

    public function unearmuffCall(string $callId) : void
    {
        $this->modifyCall($callId, CallAction::UNEARMUFF);
    }

    public function unmuteCall(string $callId) : void
    {
        $this->modifyCall($callId, CallAction::UNMUTE);
    }
}
