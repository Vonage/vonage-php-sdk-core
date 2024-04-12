<?php

declare(strict_types=1);

namespace Vonage\Voice;

use DateTimeImmutable;
use DateTimeZone;
use Exception;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\StreamInterface;
use Vonage\Client\APIClient;
use Vonage\Client\APIResource;
use Vonage\Entity\Filter\FilterInterface;
use Vonage\Entity\Hydrator\ArrayHydrator;
use Vonage\Entity\IterableAPICollection;
use Vonage\Voice\NCCO\Action\Talk;
use Vonage\Voice\NCCO\NCCO;
use Vonage\Voice\Webhook\Event;

use function is_null;

class Client implements APIClient
{
    public function __construct(protected APIResource $api)
    {
    }

    public function getAPIResource(): APIResource
    {
        return $this->api;
    }

    /**
     * @throws ClientExceptionInterface
     * @throws \Vonage\Client\Exception\Exception
     * @throws Exception
     * @throws Exception
     *
     * @return Event {uuid: string, conversation_uuid: string, status: string, direction: string}
     */
    public function createOutboundCall(OutboundCall $call): Event
    {
        $json = [
            'to' => [$call->getTo()],
        ];

        if ($call->getFrom()) {
            $json['from'] = $call->getFrom();
        } else {
            $json['random_from_number'] = true;
        }

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
            $json['length_timer'] = (string)$call->getLengthTimer();
        }

        if (!is_null($call->getRingingTimer())) {
            $json['ringing_timer'] = (string)$call->getRingingTimer();
        }

        if (!is_null($call->getAdvancedMachineDetection())) {
            $json['advanced_machine_detection'] = $call->getAdvancedMachineDetection()->toArray();
        }

        $event = $this->api->create($json);
        $event['to'] = $call->getTo()->getId();
        if ($call->getFrom()) {
            $event['from'] = $call->getFrom()->getId();
        }
        $event['timestamp'] = (new DateTimeImmutable("now", new DateTimeZone("UTC")))->format(DATE_ATOM);

        return new Event($event);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws \Vonage\Client\Exception\Exception
     */
    public function earmuffCall(string $callId): void
    {
        $this->modifyCall($callId, CallAction::EARMUFF);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws \Vonage\Client\Exception\Exception
     * @throws Exception
     */
    public function get(string $callId): Call
    {
        return (new CallFactory())->create($this->api->get($callId));
    }

    /**
     * @throws ClientExceptionInterface
     * @throws \Vonage\Client\Exception\Exception
     */
    public function hangupCall(string $callId): void
    {
        $this->modifyCall($callId, CallAction::HANGUP);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws \Vonage\Client\Exception\Exception
     */
    public function modifyCall(string $callId, string $action): void
    {
        $this->api->update($callId, [
            'action' => $action,
        ]);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws \Vonage\Client\Exception\Exception
     */
    public function muteCall(string $callId): void
    {
        $this->modifyCall($callId, CallAction::MUTE);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws \Vonage\Client\Exception\Exception
     *
     * @return array{uuid: string, message: string}
     */
    public function playDTMF(string $callId, string $digits): array
    {
        return $this->api->update($callId . '/dtmf', [
            'digits' => $digits
        ]);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws \Vonage\Client\Exception\Exception
     *
     * @return array{uuid: string, message: string}
     */
    public function playTTS(string $callId, Talk $action): array
    {
        $payload = $action->toNCCOArray();
        unset($payload['action']);

        return $this->api->update($callId . '/talk', $payload);
    }

    public function search(FilterInterface $filter = null): IterableAPICollection
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
     * @throws ClientExceptionInterface
     * @throws \Vonage\Client\Exception\Exception
     *
     * @return array{uuid: string, message: string}
     */
    public function stopStreamAudio(string $callId): array
    {
        return $this->api->delete($callId . '/stream');
    }

    /**
     * @throws ClientExceptionInterface
     * @throws \Vonage\Client\Exception\Exception
     *
     * @return array{uuid: string, message: string}
     */
    public function stopTTS(string $callId): array
    {
        return $this->api->delete($callId . '/talk');
    }

    /**
     * @throws ClientExceptionInterface
     * @throws \Vonage\Client\Exception\Exception
     *
     * @return array{uuid: string, message: string}
     */
    public function streamAudio(string $callId, string $url, int $loop = 1, float $volumeLevel = 0.0): array
    {
        return $this->api->update($callId . '/stream', [
            'stream_url' => [$url],
            'loop' => (string)$loop,
            'level' => (string)$volumeLevel,
        ]);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws \Vonage\Client\Exception\Exception
     */
    public function transferCallWithNCCO(string $callId, NCCO $ncco): void
    {
        $this->api->update($callId, [
            'action' => 'transfer',
            'destination' => [
                'type' => 'ncco',
                'ncco' => $ncco->toArray()
            ],
        ]);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws \Vonage\Client\Exception\Exception
     */
    public function transferCallWithUrl(string $callId, string $url): void
    {
        $this->api->update($callId, [
            'action' => 'transfer',
            'destination' => [
                'type' => 'ncco',
                'url' => [$url]
            ]
        ]);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws \Vonage\Client\Exception\Exception
     */
    public function unearmuffCall(string $callId): void
    {
        $this->modifyCall($callId, CallAction::UNEARMUFF);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws \Vonage\Client\Exception\Exception
     */
    public function unmuteCall(string $callId): void
    {
        $this->modifyCall($callId, CallAction::UNMUTE);
    }

    public function getRecording(string $url): StreamInterface
    {
        return $this->getAPIResource()->get($url, [], [], false, true);
    }
}
