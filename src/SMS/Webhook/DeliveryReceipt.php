<?php
declare(strict_types=1);

namespace Vonage\SMS\Webhook;

class DeliveryReceipt
{
    /**
     * Message was delivered successfully
     */
    const CODE_DELIVERED = 0;

    /**
     * Message was not delivered, and no reason could be determined
     */
    const CODE_UKNOWN = 1;

    /**
     * Message was not delivered because handset was temporarily unavailable - retry
     */
    const CODE_ABSENT_TEMPORARY = 2;

    /**
     * The number is no longer active and should be removed from your database
     */
    const CODE_ABSENT_PERMANENT = 3;

    /**
     * This is a permanent error.
     * The number should be removed from your database and the user must
     * contact their network operator to remove the bar
     */
    const CODE_BARRED = 4;

    /**
     * There is an issue relating to portability of the number and you should contact the network operator to resolve it
     */
    const CODE_PORTABILITY_ERROR = 5;

    /**
     * The message has been blocked by a carrier's anti-spam filter
     */
    const CODE_SPAM_REJECTION = 6;

    /**
     * The handset was not available at the time the message was sent - retry
     */
    const CODE_HANDSET_BUSY = 7;

    /**
     * The message failed due to a network error - retry
     */
    const CODE_NETWORK_ERROR = 8;

    /**
     * The user has specifically requested not to receive messages from a specific service
     */
    const CODE_ILLEGAL_NUMBER = 9;

    /**
     * There is an error in a message parameter, e.g. wrong encoding flag
     */
    const CODE_ILLEGAL_MESSAGE = 10;

    /**
     * Vonage cannot find a suitable route to deliver the message
     * Contact support@Vonage.com
     */
    const CODE_UNROUTABLE = 11;

    /**
     * A route to the number cannot be found - confirm the recipient's number
     */
    const CODE_UNREACHABLE = 12;

    /**
     * The target cannot receive your message due to their age
     */
    const CODE_AGE_RESTRICTION = 13;

    /**
     * The recipient should ask their carrier to enable SMS on their plan
     */
    const CODE_CARRIER_BLOCK = 14;

    /**
     * The recipient is on a prepaid plan and does not have enough credit to receive your message
     */
    const CODE_INSUFFICIENT_FUNDS = 15;

    /**
     * Typically refers to an error in the route
     * Contact support@Vonage.com
     */
    const CODE_GENERAL_ERROR = 99;

    /**
     * Message has been accepted for delivery, but has not yet been delivered
     */
    const STATUS_ACCEPTED = 'accepted';

    /**
     * Message has been delivered
     */
    const STATUS_DELIVERED = 'delivered';

    /**
     * Message has been buffered for later delivery
     */
    const STATUS_BUFFERED = 'buffered';

    /**
     * Message was held at downstream carrier's retry scheme and could not be delivered within the expiry time
     */
    const STATUS_EXPIRED = 'expired';

    /**
     * Message not delivered
     */
    const STATUS_FAILED = 'failed';

    /**
     * Downstream carrier refuses to deliver message
     */
    const STATUS_REJECTED = 'rejected';

    /**
     * No useful information available
     */
    const STATUS_UNKNOWN = 'unknown';
    
    public static $requiredFields = [
        'err-code', 'message-timestamp', 'messageId', 'msisdn', 'network-code',
        'price', 'scts', 'status', 'to'
    ];

    /**
     * @var int
     */
    protected $errCode;

    /**
     * @var \DateTimeImmutable
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
     * @param array<string, string> $data
     */
    public function __construct(array $data)
    {
        foreach (static::$requiredFields as $key) {
            if (!array_key_exists($key, $data)) {
                throw new \InvalidArgumentException('Delivery Receipt missing required data `' . $key . '`');
            }
        }

        $this->errCode = filter_var($data['err-code'], FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE);
        $this->messageTimestamp = new \DateTimeImmutable($data['message-timestamp']);
        $this->messageId = $data['messageId'];
        $this->msisdn = $data['msisdn'];
        $this->networkCode = $data['network-code'];
        $this->price = $data['price'];
        $this->scts = $data['scts'];
        $this->status = $data['status'];
        $this->to = $data['to'];
        $this->apiKey = $data['api-key'];
    }

    public function getErrCode() : int
    {
        return $this->errCode;
    }

    public function getMessageTimestamp() : \DateTimeImmutable
    {
        return $this->messageTimestamp;
    }

    public function getMessageId() : string
    {
        return $this->messageId;
    }

    public function getMsisdn() : string
    {
        return $this->msisdn;
    }

    public function getNetworkCode() : string
    {
        return $this->networkCode;
    }

    public function getPrice() : string
    {
        return $this->price;
    }

    public function getScts() : string
    {
        return $this->scts;
    }

    public function getStatus() : string
    {
        return $this->status;
    }

    public function getTo() : string
    {
        return $this->to;
    }

    public function getApiKey() : string
    {
        return $this->apiKey;
    }
}
