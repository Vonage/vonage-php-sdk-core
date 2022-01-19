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

use function count;
use function get_class;
use function is_null;
use function is_string;
use function json_decode;
use function sprintf;
use function trigger_error;

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
     * @var array
     */
    protected $data = [];

    /**
     * @param string|ServerRequestInterface $idOrRequest Message ID, or inbound HTTP request.
     *
     * @todo Find a cleaner way to create this object
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

        throw new RuntimeException(
            sprintf(
                '`%s` must be constructed with a server request or a message id',
                self::class
            )
        );
    }

    public static function createFromGlobals(): self
    {
        return new self(ServerRequestFactory::fromGlobals());
    }

    /**
     * Create a matching reply to the inbound message. Currently only supports text replies.
     */
    public function createReply(string $body): Text
    {
        return new Text($this->getFrom(), $this->getTo(), $body);
    }

    public function getRequestData(bool $sent = true)
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

    public function getFrom()
    {
        if (@$this->getRequest()) {
            return $this->data['msisdn'];
        }

        return $this->data['from'];
    }

    public function getTo()
    {
        return $this->data['to'];
    }

    public function getMessageId(): ?string
    {
        return $this->id ?? @$this->data['messageId'];
    }

    public function isValid(): bool
    {
        return (bool)$this->getMessageId();
    }

    public function getBody()
    {
        if (@$this->getRequest()) {
            return $this->data['text'];
        }

        return $this->data['body'];
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
     * @throws Exception
     */
    #[\ReturnTypeWillChange]
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
     */
    public function offsetSet($offset, $value): void
    {
        throw $this->getReadOnlyException($offset);
    }

    /**
     * All properties are read only.
     */
    public function offsetUnset($offset): void
    {
        throw $this->getReadOnlyException($offset);
    }

    /**
     * All properties are read only.
     */
    protected function getReadOnlyException($offset): RuntimeException
    {
        return new RuntimeException(
            sprintf(
                'can not modify `%s` using array access',
                $offset
            )
        );
    }

    public function fromArray(array $data): void
    {
        $this->data = $data;
    }

    public function toArray(): array
    {
        return $this->data;
    }
}
