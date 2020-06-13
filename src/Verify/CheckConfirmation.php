<?php
/**
 * Nexmo Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Nexmo, Inc. (http://nexmo.com)
 * @license   https://github.com/Nexmo/nexmo-php/blob/master/LICENSE.txt MIT License
 */

namespace Nexmo\Verify;

class CheckConfirmation
{
    /**
     * @var array
     */
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function getRequestId() : string
    {
        return $this->data['request_id'];
    }

    public function getEventId() : string
    {
        return $this->data['event_id'];
    }

    public function getStatus() : int
    {
        return (int) $this->data['status'];
    }

    public function getPrice() : string
    {
        return $this->data['price'];
    }

    public function getCurrency() : string
    {
        return $this->data['currency'];
    }

    /**
     * Estimated price for the messages sent as part of the Verify process
     * This field may not be present, depending on your pricing model. The
     * value indicates the cost (in EUR) of the calls made and messages sent
     * for the verification process. This value may be updated during and
     * shortly after the request completes because user input events can
     * overlap with message/call events. When this field is present, the total
     * cost of the verification is the sum of this field and the price field.
     * A return value of null indicates this field does not exist.
     */
    public function getEstimatedMessagePrice() : ?string
    {
        if (array_key_exists('estimated_price_messages_sent', $this->data)) {
            return $this->data['estimated_price_messages_sent'];
        }
        
        return null;
    }
}
