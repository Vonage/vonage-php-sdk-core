<?php
declare(strict_types=1);

namespace Nexmo\Voice;

use Nexmo\Voice\Endpoint\EndpointFactory;
use Nexmo\Voice\Endpoint\EndpointInterface;
use Nexmo\Entity\Hydrator\ArrayHydrateInterface;

class Call implements ArrayHydrateInterface
{
    /**
     * @var string
     */
    protected $conversationUuid;

    /**
     * @var string
     */
    protected $direction;

    /**
     * @var int
     */
    protected $duration;

    /**
     * @var \DateTime
     */
    protected $endTime;

    /**
     * @var EndpointInterface
     */
    protected $from;

    /**
     * @var string
     */
    protected $network;

    /**
     * @var float
     */
    protected $price;

    /**
     * @var float
     */
    protected $rate;

    /**
     * @var \DateTime
     */
    protected $startTime;

    /**
     * @var string
     */
    protected $status;

    /**
     * @var EndpointInterface
     */
    protected $to;

    /**
     * @var string
     */
    protected $uuid;

    public function __construct(array $data = [])
    {
        if (!empty($data)) {
            $this->fromArray($data);
        }
    }

    public function fromArray(array $data) : void
    {
        if (array_key_exists('to', $data)) {
            $this->to = (new EndpointFactory())->create($data['to'][0]);
        }

        if (array_key_exists('from', $data)) {
            $this->from = (new EndpointFactory())->create($data['from'][0]);
        }
        
        $this->uuid = $data['uuid'];
        $this->conversationUuid = $data['conversation_uuid'];
        $this->status = $data['status'];
        $this->direction = $data['direction'];
        $this->rate = (float) $data['rate'];
        $this->duration = (int) $data['duration'];
        $this->price = (float) $data['price'];
        $this->startTime = new \DateTime($data['start_time']);
        $this->endTime = new \DateTime($data['end_time']);
        $this->network = $data['network'];
    }

    public function getUuid() : string
    {
        return $this->uuid;
    }

    public function toArray() : array
    {
        $data = [
            'uuid' => $this->uuid,
            'conversation_uuid' => $this->conversationUuid,
            'status' => $this->status,
            'direction' => $this->direction,
            'rate' => $this->rate,
            'duration' => $this->duration,
            'price' => $this->price,
            'start_time' => $this->startTime->format('Y-m-d H:i:s'),
            'end_time' => $this->endTime->format('Y-m-d H:i:s'),
            'network' => $this->network,
        ];

        $to = $this->getTo();
        if ($to) {
            $data['to'][] = $to->toArray();
        }

        $from = $this->getFrom();
        if ($from) {
            $data['from'][] = $from->toArray();
        }

        return $data;
    }

    public function getTo() : EndpointInterface
    {
        return $this->to;
    }

    public function getRate() : float
    {
        return $this->rate;
    }

    public function getFrom() : EndpointInterface
    {
        return $this->from;
    }

    public function getStatus() : string
    {
        return $this->status;
    }

    public function getDirection() : string
    {
        return $this->direction;
    }

    public function getPrice() : float
    {
        return $this->price;
    }

    public function getDuration() : int
    {
        return $this->duration;
    }

    public function getStartTime() : \DateTime
    {
        return $this->startTime;
    }

    public function getEndTime() : \DateTime
    {
        return $this->endTime;
    }

    public function getNetwork() : string
    {
        return $this->network;
    }
}
