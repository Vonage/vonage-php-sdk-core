<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2022 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace Vonage\Voice;

use DateTime;
use Exception;
use Vonage\Entity\Hydrator\ArrayHydrateInterface;
use Vonage\Voice\Endpoint\EndpointFactory;
use Vonage\Voice\Endpoint\EndpointInterface;

use function array_key_exists;

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
     * @var string
     */
    protected $duration;

    /**
     * @var DateTime
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
     * @var string
     */
    protected $price;

    /**
     * @var string
     */
    protected $rate;

    /**
     * @var DateTime
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

    /**
     * @throws Exception
     */
    public function __construct(array $data = [])
    {
        if (!empty($data)) {
            $this->fromArray($data);
        }
    }

    /**
     * @throws Exception
     */
    public function fromArray(array $data): void
    {
        if (array_key_exists('to', $data)) {
            $to = $data['to'][0] ?? $data['to'];
            $this->to = (new EndpointFactory())->create($to);
        }

        if (array_key_exists('from', $data)) {
            $from = $data['from'][0] ?? $data['from'];
            $this->from = (new EndpointFactory())->create($from);
        }

        $this->uuid = $data['uuid'];
        $this->conversationUuid = $data['conversation_uuid'];
        $this->status = $data['status'];
        $this->direction = $data['direction'];
        $this->rate = $data['rate'] ?? null;
        $this->duration = $data['duration'] ?? null;
        $this->price = $data['price'] ?? null;
        $this->startTime = new DateTime($data['start_time']);
        $this->endTime = new DateTime($data['end_time']);
        $this->network = $data['network'] ?? null;
    }

    public function getUuid(): string
    {
        return $this->uuid;
    }

    public function toArray(): array
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
        $from = $this->getFrom();

        if ($to) {
            $data['to'][] = $to->toArray();
        }

        if ($from) {
            $data['from'][] = $from->toArray();
        }

        return $data;
    }

    public function getTo(): EndpointInterface
    {
        return $this->to;
    }

    public function getRate(): string
    {
        return $this->rate;
    }

    public function getFrom(): EndpointInterface
    {
        return $this->from;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getDirection(): string
    {
        return $this->direction;
    }

    public function getPrice(): string
    {
        return $this->price;
    }

    public function getDuration(): string
    {
        return $this->duration;
    }

    public function getStartTime(): DateTime
    {
        return $this->startTime;
    }

    public function getEndTime(): DateTime
    {
        return $this->endTime;
    }

    public function getNetwork(): string
    {
        return $this->network;
    }
}
