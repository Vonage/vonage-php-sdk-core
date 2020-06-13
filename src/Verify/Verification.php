<?php
/**
 * Nexmo Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Nexmo, Inc. (http://nexmo.com)
 * @license   https://github.com/Nexmo/nexmo-php/blob/master/LICENSE.txt MIT License
 */

namespace Nexmo\Verify;

use Nexmo\Entity\Hydrator\ArrayHydrateInterface;

class Verification implements \Serializable, ArrayHydrateInterface
{
    /**
     * Possible verification statuses.
     */
    const FAILED = 'FAILED';
    const SUCCESSFUL = 'SUCCESSFUL';
    const EXPIRED = 'EXPIRED';
    const IN_PROGRESS = 'IN PROGRESS';

    /**
     * @var string
     */
    protected $requestId;

    /**
     * @var string
     */
    protected $accountId;

    /**
     * @var string
     */
    protected $status;

    /**
     * @var string
     */
    protected $number;

    /**
     * @var string
     */
    protected $price;

    /**
     * @var string
     */
    protected $currency;

    /**
     * @var string
     */
    protected $senderId;

    /**
     * @var \DateTimeImmutable
     */
    protected $dateSubmitted;

    /**
     * @var \DateTimeImmutable
     */
    protected $dateFinalized;

    /**
     * @var \DateTimeImmutable
     */
    protected $firstEventDate;

    /**
     * @var \DateTimeImmutable
     */
    protected $lastEventDate;

    /**
     * @var array
     */
    protected $checks = [];

    public function __construct(array $data)
    {
        $this->fromArray($data);
    }

    public function getRequestId() : ?string
    {
        return $this->requestId;
    }

    public function getNumber() : string
    {
        return $this->number;
    }

    public function getAccountId() : string
    {
        return $this->accountId;
    }

    public function getSenderId() : string
    {
        return $this->senderId;
    }

    public function getPrice() : string
    {
        return $this->price;
    }

    public function getCurrency() : string
    {
        return $this->currency;
    }

    public function getStatus() : string
    {
        return $this->status;
    }

    public function getChecks() : array
    {
        $checks = $this->checks;
        if (!$checks) {
            return [];
        }

        foreach ($checks as $i => $check) {
            $checks[$i] = new Check($check);
        }

        return $checks;
    }

    public function getSubmitted() : ?\DateTimeInterface
    {
        return $this->dateSubmitted;
    }

    public function getFinalized() : ?\DateTimeInterface
    {
        return $this->dateFinalized;
    }

    public function getFirstEvent() : ?\DateTimeInterface
    {
        return $this->firstEventDate;
    }

    public function getLastEvent() : ?\DateTimeInterface
    {
        return $this->lastEventDate;
    }

    public function serialize() : string
    {
        return serialize($this->toArray());
    }

    public function unserialize($serialized)
    {
        $this->fromArray(unserialize($serialized));
    }

    /**
     * @return array<string, scalar>
     */
    public function toArray() : array
    {
        $data = [
            'request_id' => $this->getRequestId(),
            'account_id' => $this->getAccountId(),
            'status' => $this->getStatus(),
            'number' => $this->getNumber(),
            'price' => $this->getPrice(),
            'currency' => $this->getCurrency(),
            'sender_id' => $this->getSenderId(),
            'date_submitted' => '',
            'date_finalized' => '',
            'first_event_date' => '',
            'last_event_date' => '',
            'checks' => $this->getChecks(),
        ];

        if ($this->getSubmitted()) {
            $data['date_submitted'] = $this->getSubmitted()->format(\DateTime::ISO8601);
        }
        
        if ($this->getFinalized()) {
            $data['date_finalized'] = $this->getFinalized()->format(\DateTime::ISO8601);
        }

        if ($this->getFirstEvent()) {
            $data['first_event_date'] = $this->getFirstEvent()->format(\DateTime::ISO8601);
        }

        if ($this->getLastEvent()) {
            $data['last_event_date'] = $this->getLastEvent()->format(\DateTime::ISO8601);
        }

        return $data;
    }

    /**
     * @param array<string, scalar> $data
     */
    public function fromArray(array $data) : void
    {
        if ($this->getRequestId()) {
            throw new \RuntimeException("Unable to reset data for this verification, please make a new object");
        }

        $this->requestId = $data['request_id'];
        $this->accountId = $data['account_id'];
        $this->status = $data['status'];
        $this->number = $data['number'];
        $this->price = $data['price'];
        $this->currency = $data['currency'];
        $this->senderId = $data['sender_id'];
        $this->dateSubmitted = empty($data['date_submitted']) ? null : new \DateTimeImmutable($data['date_submitted']);
        $this->dateFinalized = empty($data['date_finalized']) ? null : new \DateTimeImmutable($data['date_finalized']);
        $this->firstEventDate = empty($data['first_event_date']) ? null : new \DateTimeImmutable($data['first_event_date']);
        $this->lastEventDate = empty($data['last_event_date']) ? null : new \DateTimeImmutable($data['last_event_date']);
        $this->checks = $data['checks'];
    }
}
