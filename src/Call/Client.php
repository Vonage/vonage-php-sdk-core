<?php

namespace Nexmo\Call;

use Nexmo\Call\NCCO\NCCOInterface;
use Nexmo\Client\ClientAwareInterface;
use Nexmo\Client\ClientAwareTrait;
use Nexmo\Client\OpenAPIResource;
use Nexmo\Entity\Collection;
use Nexmo\Entity\FilterInterface;
use Nexmo\Entity\HydratorInterface;

class Client implements ClientAwareInterface
{
    use ClientAwareTrait;

    /**
     * @var OpenAPIResource
     */
    protected $api;

    /**
     * @var HydratorInterface
     */
    protected $hydrator;

    public function __construct(OpenAPIResource $api, HydratorInterface $hydrator)
    {
        $this->api = $api;
        $this->hydrator = $hydrator;
    }

    /**
     * Create a new call
     */
    public function create(Call $call) : Call
    {
        $data = $call->toArray();
        $response = $this->api->create($data);

        return $this->hydrator->hydrate($response);
    }

    /**
     * Deletes a call
     */
    public function delete(Call $call) : void
    {
        $this->api->delete($call->getId());
    }

    /**
     * Play DTMF into an existing call
     */
    public function dtmf(Call $call, string $digits)
    {
        $api = clone $this->api;
        $api->setBaseUri($this->api->getBaseUri() . '/' . $call->getId());

        $api->update('dtmf', ['digits' => $digits]);
    }

    /**
     * Return a call with a given UUID
     */
    public function get(string $id) : Call
    {
        $response = $this->api->get($id);
        return $this->hydrator->hydrate($response);
    }

    /**
     * Search and return calls that match the filter criteroa
     */
    public function search(FilterInterface $filter = null) : Collection
    {
        $collection = $this->api->search($filter);
        $collection->setHydrator($this->hydrator);

        return $collection;
    }

    /**
     * Stream audio into a call
     * 
     * @param array<string> $urls Array of URLs to stream
     */
    public function streamAudio(Call $call, array $urls, int $loop = 1, float $volumeLevel = 0.0)
    {
        $api = clone $this->api;
        $api->setBaseUri($this->api->getBaseUri() . '/' . $call->getId());

        $api->update('stream', [
            'stream_url' => $urls,
            'loop' => $loop,
            'level' => $volumeLevel,
        ]);
    }

    /**
     * Stop currently streaming audio in a call
     */
    public function streamAudioStop(Call $call)
    {
        $api = clone $this->api;
        $api->setBaseUri($this->api->getBaseUri() . '/' . $call->getId());

        $api->delete('stream');
    }

    /**
     * Play TTS into an existing call
     */
    public function talk(
        Call $call,
        string $text,
        string $voiceName = 'Kimberly',
        int $loop = 1,
        float $volumeLevel = 0.0
    )
    {
        $api = clone $this->api;
        $api->setBaseUri($this->api->getBaseUri() . '/' . $call->getId());

        $api->update('talk', [
            'text' => $text,
            'voice_name' => $voiceName,
            'loop' => $loop,
            'level' => $volumeLevel,
        ]);
    }

    /**
     * Stops any TTS in an existing call
     */
    public function talkStop(Call $call)
    {
        $api = clone $this->api;
        $api->setBaseUri($this->api->getBaseUri() . '/' . $call->getId());

        $api->delete('talk');
    }

    /**
     * @deprecated See create()
     */
    public function post(Call $call)
    {
        return $this->create($call);
    }

    /**
     * Updates an existing, running call with an NCCO
     */
    public function put(Call $call, NCCOInterface $ncco)
    {
        $this->api->update($call->getId(), $ncco->toArray());
    }
}