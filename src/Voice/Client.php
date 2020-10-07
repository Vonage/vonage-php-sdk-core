<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license   MIT <https://github.com/vonage/vonage-php/blob/master/LICENSE>
 */
declare(strict_types=1);

namespace Vonage\Voice;

use DateTimeImmutable;
use DateTimeZone;
use Exception;
use Psr\Http\Client\ClientExceptionInterface;
use Vonage\Client\APIClient;
use Vonage\Client\APIResource;
use Vonage\Entity\Filter\FilterInterface;
use Vonage\Entity\Hydrator\ArrayHydrator;
use Vonage\Entity\IterableAPICollection;
use Vonage\Voice\NCCO\Action\Talk;
use Vonage\Voice\NCCO\NCCO;
use Vonage\Voice\Webhook\Event;

class Client implements APIClient
{
    /**
     * @var APIResource
     */
    protected $api;

    /**
     * Client constructor.
     *
     * @param APIResource $api
     */
    public function __construct(APIResource $api)
    {
        $this->api = $api;
    }

    /**
     * @return APIResource
     */
    public function getAPIResource(): APIResource
    {
        return $this->api;
    }

    /**
     * @param OutboundCall $call
     * @return Event {uuid: string, conversation_uuid: string, status: string, direction: string}
     * @throws ClientExceptionInterface
     * @throws \Vonage\Client\Exception\Exception
     * @throws Exception
     * @throws Exception
     */
    public function createOutboundCall(OutboundCall $call): Event
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
            $json['length_timer'] = (string)$call->getLengthTimer();
        }

        if (!is_null($call->getRingingTimer())) {
            $json['ringing_timer'] = (string)$call->getRingingTimer();
        }

        $event = $this->api->create($json);
        $event['to'] = $call->getTo()->getId();
        $event['from'] = $call->getFrom()->getId();
        $event['timestamp'] = (new DateTimeImmutable("now", new DateTimeZone("UTC")))->format(DATE_ATOM);

        return new Event($event);
    }

    /**
     * @param string $callId
     * @throws ClientExceptionInterface
     * @throws \Vonage\Client\Exception\Exception
     */
    public function earmuffCall(string $callId): void
    {
        $this->modifyCall($callId, CallAction::EARMUFF);
    }

    /**
     * @param string $callId
     * @return Call
     * @throws ClientExceptionInterface
     * @throws \Vonage\Client\Exception\Exception
     * @throws Exception
     */
    public function get(string $callId): Call
    {
        return (new CallFactory())->create($this->api->get($callId));
    }

    /**
     * @param string $callId
     * @throws ClientExceptionInterface
     * @throws \Vonage\Client\Exception\Exception
     */
    public function hangupCall(string $callId): void
    {
        $this->modifyCall($callId, CallAction::HANGUP);
    }

    /**
     * @param string $callId
     * @param string $action
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
     * @param string $callId
     * @throws ClientExceptionInterface
     * @throws \Vonage\Client\Exception\Exception
     */
    public function muteCall(string $callId): void
    {
        $this->modifyCall($callId, CallAction::MUTE);
    }

    /**
     * @param string $callId
     * @param string $digits
     * @return array{uuid: string, message: string}
     * @throws ClientExceptionInterface
     * @throws \Vonage\Client\Exception\Exception
     */
    public function playDTMF(string $callId, string $digits): array
    {
        return $this->api->update($callId . '/dtmf', [
            'digits' => $digits
        ]);
    }

    /**
     * @param string $callId
     * @param Talk $action
     * @return array{uuid: string, message: string}
     * @throws ClientExceptionInterface
     * @throws \Vonage\Client\Exception\Exception
     */
    public function playTTS(string $callId, Talk $action): array
    {
        $payload = $action->toNCCOArray();
        unset($payload['action']);

        return $this->api->update($callId . '/talk', $payload);
    }

    /**
     * @param FilterInterface|null $filter
     * @return IterableAPICollection
     */
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
     * @param string $callId
     * @return array{uuid: string, message: string}
     * @throws ClientExceptionInterface
     * @throws \Vonage\Client\Exception\Exception
     */
    public function stopStreamAudio(string $callId): array
    {
        return $this->api->delete($callId . '/stream');
    }

    /**
     * @param string $callId
     * @return array{uuid: string, message: string}
     * @throws ClientExceptionInterface
     * @throws \Vonage\Client\Exception\Exception
     */
    public function stopTTS(string $callId): array
    {
        return $this->api->delete($callId . '/talk');
    }

    /**
     * @param string $callId
     * @param string $url
     * @param int $loop
     * @param float $volumeLevel
     * @return array{uuid: string, message: string}
     * @throws ClientExceptionInterface
     * @throws \Vonage\Client\Exception\Exception
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
     * @param string $callId
     * @param NCCO $ncco
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
     * @param string $callId
     * @param string $url
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
     * @param string $callId
     * @throws ClientExceptionInterface
     * @throws \Vonage\Client\Exception\Exception
     */
    public function unearmuffCall(string $callId): void
    {
        $this->modifyCall($callId, CallAction::UNEARMUFF);
    }

    /**
     * @param string $callId
     * @throws ClientExceptionInterface
     * @throws \Vonage\Client\Exception\Exception
     */
    public function unmuteCall(string $callId): void
    {
        $this->modifyCall($callId, CallAction::UNMUTE);
    }
}
