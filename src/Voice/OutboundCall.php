<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace Vonage\Voice;

use InvalidArgumentException;
use Vonage\Voice\Endpoint\EndpointInterface;
use Vonage\Voice\Endpoint\Phone;
use Vonage\Voice\NCCO\NCCO;

class OutboundCall
{
    public const MACHINE_CONTINUE = 'continue';
    public const MACHINE_HANGUP = 'hangup';

    /**
     * @var Webhook
     */
    protected $answerWebhook;

    /**
     * @var Webhook
     */
    protected $eventWebhook;

    /**
     * @var Phone
     */
    protected $from;

    /**
     * Length of seconds before Vonage hangs up after going into `in_progress` status
     *
     * @var int
     */
    protected $lengthTimer;

    /**
     * What to do when Vonage detects an answering machine.
     *
     * @var ?string
     */
    protected $machineDetection;
    /**
     * @var NCCO
     */
    protected $ncco;

    /**
     * Length of time Vonage will allow a phone number to ring before hanging up
     *
     * @var int
     */
    protected $ringingTimer;

    /**
     * @var EndpointInterface
     */
    protected $to;

    public function __construct(EndpointInterface $to, Phone $from)
    {
        $this->to = $to;
        $this->from = $from;
    }

    public function getAnswerWebhook(): ?Webhook
    {
        return $this->answerWebhook;
    }

    public function getEventWebhook(): ?Webhook
    {
        return $this->eventWebhook;
    }

    public function getFrom(): Phone
    {
        return $this->from;
    }

    public function getLengthTimer(): ?int
    {
        return $this->lengthTimer;
    }

    public function getMachineDetection(): ?string
    {
        return $this->machineDetection;
    }

    public function getNCCO(): ?NCCO
    {
        return $this->ncco;
    }

    public function getRingingTimer(): ?int
    {
        return $this->ringingTimer;
    }

    public function getTo(): EndpointInterface
    {
        return $this->to;
    }

    /**
     * @return $this
     */
    public function setAnswerWebhook(Webhook $webhook): self
    {
        $this->answerWebhook = $webhook;

        return $this;
    }

    /**
     * @return $this
     */
    public function setEventWebhook(Webhook $webhook): self
    {
        $this->eventWebhook = $webhook;

        return $this;
    }

    /**
     * @return $this
     */
    public function setLengthTimer(int $timer): self
    {
        $this->lengthTimer = $timer;

        return $this;
    }

    /**
     * @return $this
     */
    public function setMachineDetection(string $action): self
    {
        if ($action === self::MACHINE_CONTINUE || $action === self::MACHINE_HANGUP) {
            $this->machineDetection = $action;

            return $this;
        }

        throw new InvalidArgumentException('Unknown machine detection action');
    }

    /**
     * @return $this
     */
    public function setNCCO(NCCO $ncco): self
    {
        $this->ncco = $ncco;

        return $this;
    }

    /**
     * @return $this
     */
    public function setRingingTimer(int $timer): self
    {
        $this->ringingTimer = $timer;

        return $this;
    }
}
