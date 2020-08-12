<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Vonage, Inc. (http://vonage.com)
 * @license   https://github.com/vonage/vonage-php/blob/master/LICENSE MIT License
 */

namespace Vonage\Message;

use Vonage\Entity\Hydrator\ArrayHydrateInterface;
use Vonage\Entity\JsonResponseTrait;
use Vonage\Entity\Psr7Trait;
use Psr\Http\Message\ServerRequestInterface;

class InboundMessage implements MessageInterface, \ArrayAccess, ArrayHydrateInterface
{
    use Psr7Trait;
    use JsonResponseTrait;
    use CollectionTrait;

    protected $id;

    /**
     * @var array<string, mixed>
     */
    protected $data = [];

    /**
     * InboundMessage constructor.
     *
     * @todo Find a cleaner way to create this object
     *
     * @param string|ServerRequestInterface $idOrRequest Message ID, or inbound HTTP request.
     */
    public function __construct($idOrRequest)
    {
        if ($idOrRequest instanceof ServerRequestInterface) {
            trigger_error(
                'Passing a Request object into ' . get_class($this) . ' has been deprectated. Please use fromArray() instead',
                E_USER_DEPRECATED
            );
            @$this->setRequest($idOrRequest);
            return;
        }

        if (is_string($idOrRequest)) {
            $this->id = $idOrRequest;
            return;
        }

        throw new \RuntimeException(sprintf(
            '`%s` must be constructed with a server request or a message id',
            self::class
        ));
    }

    public static function createFromGlobals()
    {
        $serverRequest = \Zend\Diactoros\ServerRequestFactory::fromGlobals();
        return new self($serverRequest);
    }

    /**
     * Create a matching reply to the inbound message. Currently only supports text replies.
     *
     * @param string $body
     * @return Text
     */
    public function createReply($body)
    {
        return new Text($this->getFrom(), $this->getTo(), $body);
    }

    public function getRequestData($sent = true)
    {
        $request = $this->getRequest();

        if (is_null($request)) {
            return [];
        }

        if (!($request instanceof ServerRequestInterface)) {
            throw new \RuntimeException('inbound message request should only ever be `' . ServerRequestInterface::class . '`');
        }

        // Check our incoming content type
        $isApplicationJson = false;
        $contentTypes = $request->getHeader('Content-Type');
        // We only respect application/json if it's the first entry without any preference weighting
        // as that's what Vonage send
        if (count($contentTypes) && $contentTypes[0] === 'application/json') {
            $isApplicationJson = true;
        }

        switch ($request->getMethod()) {
            case 'POST':
                $params = $isApplicationJson ? json_decode((string)$request->getBody(), true) : $request->getParsedBody();
                break;
            case 'GET':
                $params = $request->getQueryParams();
                break;
            default:
                $params = [];
                break;
        }

        return $params;
    }

    public function getFrom()
    {
        if (@$this->getRequest()) {
            return $this->data['msisdn'];
        } else {
            return $this->data['from'];
        }
    }

    public function getTo()
    {
        return $this->data['to'];
    }

    public function getMessageId()
    {
        if (isset($this->id)) {
            return $this->id;
        }

        return @$this->data['messageId'];
    }

    public function isValid()
    {
        return (bool) $this->getMessageId();
    }

    public function getBody()
    {
        if (@$this->getRequest()) {
            return $this->data['text'];
        } else {
            return $this->data['body'];
        }
    }

    public function getType()
    {
        return $this->data['type'];
    }

    public function getAccountId()
    {
        return $this->data['account-id'];
    }

    public function getNetwork()
    {
        return $this->data['network'];
    }

    /**
     * Allow the object to access the data from the API response, a sent API request, or the user set data that the
     * request will be created from - in that order.
     *
     * @param mixed $offset
     * @return bool
     * @throws \Exception
     */
    public function offsetExists($offset)
    {
        trigger_error(
            "Array access for " . get_class($this) . " is deprecated, please use getter methods",
            E_USER_DEPRECATED
        );
        $response = @$this->getResponseData();

        if (isset($this->index)) {
            $response = $response['items'][$this->index];
        }

        $request  = @$this->getRequestData();
        $dirty    = @$this->getRequestData(false);
        return isset($response[$offset]) || isset($request[$offset]) || isset($dirty[$offset]);
    }

    /**
     * Allow the object to access the data from the API response, a sent API request, or the user set data that the
     * request will be created from - in that order.
     *
     * @param mixed $offset
     * @return mixed
     * @throws \Exception
     */
    public function offsetGet($offset)
    {
        trigger_error(
            "Array access for " . get_class($this) . " is deprecated, please use getter methods",
            E_USER_DEPRECATED
        );
        $response = @$this->getResponseData();

        if (isset($this->index)) {
            $response = $response['items'][$this->index];
        }

        $request  = @$this->getRequestData();
        $dirty    = @$this->getRequestData(false);

        if (isset($response[$offset])) {
            return $response[$offset];
        }

        if (isset($request[$offset])) {
            return $request[$offset];
        }

        if (isset($dirty[$offset])) {
            return $dirty[$offset];
        }
    }

    /**
     * All properties are read only.
     *
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value)
    {
        throw $this->getReadOnlyException($offset);
    }

    /**
     * All properties are read only.
     *
     * @param mixed $offset
     */
    public function offsetUnset($offset)
    {
        throw $this->getReadOnlyException($offset);
    }

    /**
     * All properties are read only.
     *
     * @param $offset
     * @return \RuntimeException
     */
    protected function getReadOnlyException($offset)
    {
        return new \RuntimeException(sprintf(
            'can not modify `%s` using array access',
            $offset
        ));
    }

    public function fromArray(array $data)
    {
        $this->data = $data;
    }

    public function toArray(): array
    {
        return $this->data;
    }
}
