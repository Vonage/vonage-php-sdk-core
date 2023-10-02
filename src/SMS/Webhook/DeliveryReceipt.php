<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2022 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace Vonage\SMS\Webhook;

use DateTimeImmutable;
use Exception;
use InvalidArgumentException;

use function array_key_exists;
use function filter_var;

class DeliveryReceipt
{
    /**
     * Message was delivered successfully
     */
    public const CODE_DELIVERED = 0;

    /**
     * Message was not delivered, and no reason could be determined
     */
    public const CODE_UNKNOWN = 1;

    /**
     * Message was not delivered because handset was temporarily unavailable - retry
     */
    public const CODE_ABSENT_TEMPORARY = 2;

    /**
     * The number is no longer active and should be removed from your database
     */
    public const CODE_ABSENT_PERMANENT = 3;

    /**
     * This is a permanent error.
     * The number should be removed from your database and the user must
     * contact their network operator to remove the bar
     */
    public const CODE_BARRED = 4;

    /**
     * There is an issue relating to portability of the number and you should contact the network operator to resolve it
     */
    public const CODE_PORTABILITY_ERROR = 5;

    /**
     * The message has been blocked by a carrier's anti-spam filter
     */
    public const CODE_SPAM_REJECTION = 6;

    /**
     * The handset was not available at the time the message was sent - retry
     */
    public const CODE_HANDSET_BUSY = 7;

    /**
     * The message failed due to a network error - retry
     */
    public const CODE_NETWORK_ERROR = 8;

    /**
     * The user has specifically requested not to receive messages from a specific service
     */
    public const CODE_ILLEGAL_NUMBER = 9;

    /**
     * There is an error in a message parameter, e.g. wrong encoding flag
     */
    public const CODE_ILLEGAL_MESSAGE = 10;

    /**
     * Vonage cannot find a suitable route to deliver the message
     * Contact support@Vonage.com
     */
    public const CODE_UNROUTABLE = 11;

    /**
     * A route to the number cannot be found - confirm the recipient's number
     */
    public const CODE_UNREACHABLE = 12;

    /**
     * The target cannot receive your message due to their age
     */
    public const CODE_AGE_RESTRICTION = 13;

    /**
     * The recipient should ask their carrier to enable SMS on their plan
     */
    public const CODE_CARRIER_BLOCK = 14;

    /**
     * The recipient is on a prepaid plan and does not have enough credit to receive your message
     */
    public const CODE_INSUFFICIENT_FUNDS = 15;

    /**
     * Typically refers to an error in the route
     * Contact support@Vonage.com
     */
    public const CODE_GENERAL_ERROR = 99;

    /**
     * Message has been accepted for delivery, but has not yet been delivered
     */
    public const STATUS_ACCEPTED = 'accepted';

    /**
     * Message has been delivered
     */
    public const STATUS_DELIVERED = 'delivered';

    /**
     * Message has been buffered for later delivery
     */
    public const STATUS_BUFFERED = 'buffered';

    /**
     * Message was held at downstream carrier's retry scheme and could not be delivered within the expiry time
     */
    public const STATUS_EXPIRED = 'expired';

    /**
     * Message not delivered
     */
    public const STATUS_FAILED = 'failed';

    /**
     * Downstream carrier refuses to deliver message
     */
    public const STATUS_REJECTED = 'rejected';

    /**
     * No useful information available
     */
    public const STATUS_UNKNOWN = 'unknown';

    public static $requiredFields = [
        'err-code',
        'message-timestamp',
        'messageId',
        'msisdn',
        'price',
        'status',
        'to'
    ];

    /**
     * @var int
     */
    protected $errCode;

    /**
     * @var DateTimeImmutable
     */
    protected $messageTimestamp;

    /**
     * @var string
     */
    protected $messageId;

    /**
     * @var string
     */
    protected $msisdn;

    /**
     * @var string
     */
    protected $networkCode;

    /**
     * @var string
     */
    protected $price;

    /**
     * @var string
     */
    protected $scts;

    /**
     * @var string
     */
    protected $status;

    /**
     * @var string
     */
    protected $to;

    /**
     * @var string
     */
    protected $apiKey;

    /**
     * @var mixed|string
     */
    protected $clientRef;

    /**
     * @param array<string, string> $data
     *
     * @throws Exception
     */
    public function __construct(array $data)
    {
        foreach (static::$requiredFields as $key) {
            if (!array_key_exists($key, $data)) {
                throw new InvalidArgumentException('Delivery Receipt missing required data `' . $key . '`');
            }
        }

        $this->errCode = filter_var($data['err-code'], FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE);
        $this->messageTimestamp = new DateTimeImmutable($data['message-timestamp']);
        $this->messageId = $data['messageId'];
        $this->msisdn = $data['msisdn'];
        $this->price = $data['price'];
        $this->status = $data['status'];
        $this->to = $data['to'];
        $this->apiKey = $data['api-key'];

        if (isset($data['network-code'])) {
            $this->networkCode = $data['network-code'];
        }

        if (isset($data['client-ref'])) {
            $this->clientRef = $data['client-ref'];
        }

        if (isset($data['scts'])) {
            $this->scts = $data['scts'];
        }
    }

    public function getErrCode(): int
    {
        return $this->errCode;
    }

    public function getMessageTimestamp(): DateTimeImmutable
    {
        return $this->messageTimestamp;
    }

    public function getMessageId(): string
    {
        return $this->messageId;
    }

    public function getMsisdn(): string
    {
        return $this->msisdn;
    }

    public function getNetworkCode(): string
    {
        return $this->networkCode;
    }

    public function getPrice(): string
    {
        return $this->price;
    }

    public function getScts(): ?string
    {
        return $this->scts;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getTo(): string
    {
        return $this->to;
    }

    public function getApiKey(): string
    {
        return $this->apiKey;
    }

    public function getClientRef(): ?string
    {
        return $this->clientRef ?? null;
    }
}
