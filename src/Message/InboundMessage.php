<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */
declare(strict_types=1);

namespace Vonage\Message;

use ArrayAccess;
use Exception;
use Laminas\Diactoros\ServerRequestFactory;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;
use Vonage\Entity\Hydrator\ArrayHydrateInterface;
use Vonage\Entity\JsonResponseTrait;
use Vonage\Entity\Psr7Trait;

class InboundMessage implements MessageInterface, ArrayAccess, ArrayHydrateInterface
{
    use Psr7Trait;
    use JsonResponseTrait;
    use CollectionTrait;

    /**
     * @var ?string
     */
    protected $id;

    /**
     * @var array<string, mixed>
     */
    protected $data = [];

    /**
     * InboundMessage constructor.
     *
     * @param string|ServerRequestInterface $idOrRequest Message ID, or inbound HTTP request.
     * @todo Find a cleaner way to create this object
     *
     */
    public function __construct($idOrRequest)
    {
        if ($idOrRequest instanceof ServerRequestInterface) {
            trigger_error(
                'Passing a Request object into ' . get_class($this) . ' has been deprecated. ' .
                'Please use fromArray() instead',
                E_USER_DEPRECATED
            );

            @$this->setRequest($idOrRequest);

            return;
        }

        if (is_string($idOrRequest)) {
            $this->id = $idOrRequest;

            return;
        }

        throw new RuntimeException(sprintf(
            '`%s` must be constructed with a server request or a message id',
            self::class
        ));
    }

    /**
     * @return $this
     */
    public static function createFromGlobals(): self
    {
        return new self(ServerRequestFactory::fromGlobals());
    }

    /**
     * Create a matching reply to the inbound message. Currently only supports text replies.
     *
     * @param $body
     * @return Text
     */
    public function createReply($body): Text
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
            throw new RuntimeException(
                'inbound message request should only ever be `' . ServerRequestInterface::class . '`'
            );
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
                $params = $isApplicationJson ?
                    json_decode((string)$request->getBody(), true) :
                    $request->getParsedBody();
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

    /**
     * @return mixed
     */
    public function getFrom()
    {
        if (@$this->getRequest()) {
            return $this->data['msisdn'];
        }

        return $this->data['from'];
    }

    /**
     * @return mixed
     */
    public function getTo()
    {
        return $this->data['to'];
    }

    /**
     * @return string|null
     */
    public function getMessageId(): ?string
    {
        return $this->id ?? @$this->data['messageId'];
    }

    /**
     * @return bool
     */
    public function isValid(): bool
    {
        return (bool)$this->getMessageId();
    }

    /**
     * @return mixed
     */
    public function getBody()
    {
        if (@$this->getRequest()) {
            return $this->data['text'];
        }

        return $this->data['body'];
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->data['type'];
    }

    /**
     * @return mixed
     */
    public function getAccountId()
    {
        return $this->data['account-id'];
    }

    /**
     * @return mixed
     */
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
     * @throws Exception
     */
    public function offsetExists($offset): bool
    {
        trigger_error(
            "Array access for " . get_class($this) . " is deprecated, please use getter methods",
            E_USER_DEPRECATED
        );

        $response = $this->getResponseData();

        if (isset($this->index)) {
            $response = $response['items'][$this->index];
        }

        $request = @$this->getRequestData();
        $dirty = @$this->getRequestData(false);

        return isset($response[$offset]) || isset($request[$offset]) || isset($dirty[$offset]);
    }

    /**
     * Allow the object to access the data from the API response, a sent API request, or the user set data that the
     * request will be created from - in that order.
     *
     * @param mixed $offset
     * @return mixed
     * @throws Exception
     */
    public function offsetGet($offset)
    {
        trigger_error(
            "Array access for " . get_class($this) . " is deprecated, please use getter methods",
            E_USER_DEPRECATED
        );

        $response = $this->getResponseData();

        if (isset($this->index)) {
            $response = $response['items'][$this->index];
        }

        $request = $this->getRequestData();
        $dirty = $this->getRequestData(false);

        return $response[$offset] ?? $request[$offset] ?? $dirty[$offset] ?? null;
    }

    /**
     * All properties are read only.
     *
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value): void
    {
        throw $this->getReadOnlyException($offset);
    }

    /**
     * All properties are read only.
     *
     * @param mixed $offset
     */
    public function offsetUnset($offset): void
    {
        throw $this->getReadOnlyException($offset);
    }

    /**
     * All properties are read only.
     *
     * @param $offset
     * @return RuntimeException
     */
    protected function getReadOnlyException($offset): RuntimeException
    {
        return new RuntimeException(sprintf(
            'can not modify `%s` using array access',
            $offset
        ));
    }

    /**
     * @param array $data
     */
    public function fromArray(array $data): void
    {
        $this->data = $data;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return $this->data;
    }
}
