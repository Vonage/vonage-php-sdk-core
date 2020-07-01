<?php
declare(strict_types=1);

namespace Nexmo\SMS;

use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\ServerRequestFactory;

class InboundSMS
{
    /**
     * @var string
     */
    protected $apiKey;

    /**
     * @var bool
     */
    protected $concat = false;

    /**
     * @var ?int
     */
    protected $concatPart;

    /**
     * @var ?string
     */
    protected $concatRef;

    /**
     * @var ?int
     */
    protected $concatTotal;

    /**
     * @var ?string
     */
    protected $data;

    /**
     * @var string
     */
    protected $keyword;

    /**
     * @var string
     */
    protected $messageId;

    /**
     * @var \DateTimeImmutable
     */
    protected $messageTimestamp;

    /**
     * @var string
     */
    protected $msisdn;

    /**
     * @var ?string
     */
    protected $nonce;

    /**
     * @var string
     */
    protected $signature;

    /**
     * @var string
     */
    protected $text;

    /**
     * @var ?int
     */
    protected $timestamp;

    /**
     * @var string
     */
    protected $to;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var ?string
     */
    protected $udh;

    public function __construct(array $data)
    {
        $required = [
            'msisdn', 'to', 'messageId', 'text', 'type', 'keyword',
            'message-timestamp'
        ];

        foreach ($required as $key) {
            if (!array_key_exists($key, $data)) {
                throw new \InvalidArgumentException('Incoming SMS missing required data `' . $key . '`');
            }
        }

        $this->apiKey = $data['api-key'] ?? null;
        $this->keyword = $data['keyword'];
        $this->messageId = $data['messageId'];
        $this->messageTimestamp = new \DateTimeImmutable($data['message-timestamp']);
        $this->msisdn = $data['msisdn'];
        $this->nonce = $data['nonce'] ?? null;
        $this->signature = $data['sig'] ?? null;
        $this->text = $data['text'];
        $this->to = $data['to'];
        $this->type = $data['type'];
        
        if (array_key_exists('concat', $data)) {
            $this->concat = true;
            $this->concatPart = (int) $data['concat-part'];
            $this->concatRef = $data['concat-ref'];
            $this->concatTotal = (int) $data['concat-total'];
        }

        if ($this->type === 'binary' && array_key_exists('data', $data)) {
            $this->data = $data['data'];
            $this->udh = $data['udh'];
        }

        if (array_key_exists('timestamp', $data)) {
            $this->timestamp = (int) $data['timestamp'];
        }
    }

    public static function createFromGlobals() : InboundSMS
    {
        $request = ServerRequestFactory::fromGlobals();
        return self::createFromRequest($request);
    }

    public static function createFromRequest(ServerRequestInterface $request) : InboundSMS
    {
        $contentTypes = $request->getHeader('Content-Type');

        $isApplicationJson = false;
        if (count($contentTypes) && $contentTypes[0] === 'application/json') {
            $isApplicationJson = true;
        }

        switch ($request->getMethod()) {
            case 'POST':
                $params = $isApplicationJson ? json_decode($request->getBody()->getContents(), true) : $request->getParsedBody();
                break;
            case 'GET':
                $params = $request->getQueryParams();
                break;
            default:
                throw new \RuntimeException("Invalid request method for incoming SMS");
        }

        return new self($params);
    }

    public function getApiKey()
    {
        return $this->apiKey;
    }

    public function getConcat() : bool
    {
        return $this->concat;
    }

    public function getConcatPart() : ?int
    {
        return $this->concatPart;
    }

    public function getConcatRef() : ?string
    {
        return $this->concatRef;
    }

    public function getConcatTotal() : ?int
    {
        return $this->concatTotal;
    }

    public function getData() : ?string
    {
        return $this->data;
    }

    public function getKeyword() : string
    {
        return $this->keyword;
    }

    public function getMessageId() : string
    {
        return $this->messageId;
    }

    /**
     * Time the message was accepted and delivery receipt was generated
     */
    public function getMessageTimestamp() : \DateTimeImmutable
    {
        return $this->messageTimestamp;
    }

    public function getMsisdn() : string
    {
        return $this->msisdn;
    }

    public function getFrom() : string
    {
        return $this->msisdn;
    }

    public function getNonce() : string
    {
        return $this->nonce;
    }

    public function getText() : string
    {
        return $this->text;
    }

    /**
     * Return the timestmap used for signature verification
     * If you are looking for the time of message creation, please use
     * `getMessageTimestamp()`
     */
    public function getTimestamp() : ?int
    {
        return $this->timestamp;
    }

    public function getTo() : string
    {
        return $this->to;
    }

    public function getType() : string
    {
        return $this->type;
    }

    public function getUdh() : ?string
    {
        return $this->udh;
    }

    public function getSignature() : string
    {
        return $this->signature;
    }
}
