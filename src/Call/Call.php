<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace Vonage\Call;

use InvalidArgumentException;
use JsonSerializable;
use Laminas\Diactoros\Request;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;
use Vonage\Client\ClientAwareInterface;
use Vonage\Client\ClientAwareTrait;
use Vonage\Client\Exception as ClientException;
use Vonage\Conversations\Conversation;
use Vonage\Entity\EntityInterface;
use Vonage\Entity\JsonResponseTrait;
use Vonage\Entity\JsonSerializableTrait;
use Vonage\Entity\JsonUnserializableInterface;
use Vonage\Entity\NoRequestResponseTrait;

use function array_merge;
use function call_user_func_array;
use function is_null;
use function json_decode;
use function json_encode;
use function trigger_error;
use function ucfirst;

/**
 * Class Call
 *
 * @deprecated Please use Vonage\Voice\OutboundCall or Vonage\Voice\Call instead
 *
 * @property Stream $stream
 * @property Talk $talk
 * @property Dtmf $dtmf
 *
 * @method Stream stream($stream = null)
 * @method Talk   talk($talk = null)
 * @method Dtmf   dtmf($dtmf = null)
 */
class Call implements EntityInterface, JsonSerializable, JsonUnserializableInterface, ClientAwareInterface
{
    use NoRequestResponseTrait;
    use JsonSerializableTrait;
    use JsonResponseTrait;
    use ClientAwareTrait;

    public const WEBHOOK_ANSWER = 'answer';
    public const WEBHOOK_EVENT = 'event';

    public const TIMER_LENGTH = 'length';
    public const TIMER_RINGING = 'ringing';

    public const TIMEOUT_MACHINE = 'machine';

    /**
     * @var string|null
     */
    protected $id;

    /**
     * @var Endpoint|string|null
     */
    protected $to;

    /**
     * @var Endpoint|string|null
     */
    protected $from;

    /**
     * @var array
     */
    protected $webhooks = [];

    /**
     * @var array
     */
    protected $data = [];

    /**
     * @var array
     */
    protected $subresources = [];

    public function __construct(?string $id = null)
    {
        trigger_error(
            'Vonage\Call\Call is deprecated, please use Vonage\Voice\Client for functionality instead',
            E_USER_DEPRECATED
        );

        $this->id = $id;
    }

    /**
     * @throws ClientException\Exception
     * @throws ClientException\Request
     * @throws ClientException\Server
     * @throws ClientExceptionInterface
     */
    public function get(): self
    {
        $request = new Request(
            $this->getClient()->getApiUrl() . Collection::getCollectionPath() . '/' . $this->getId(),
            'GET'
        );

        $response = $this->getClient()->send($request);

        if ((int)$response->getStatusCode() !== 200) {
            throw $this->getException($response);
        }

        $data = json_decode($response->getBody()->getContents(), true);
        $this->jsonUnserialize($data);

        return $this;
    }

    /**
     * @return ClientException\Exception|ClientException\Request|ClientException\Server
     */
    protected function getException(ResponseInterface $response)
    {
        $body = json_decode($response->getBody()->getContents(), true);
        $status = $response->getStatusCode();

        if ($status >= 400 && $status < 500) {
            $e = new ClientException\Request($body['error_title'], $status);
        } elseif ($status >= 500 && $status < 600) {
            $e = new ClientException\Server($body['error_title'], $status);
        } else {
            $e = new ClientException\Exception('Unexpected HTTP Status Code');
        }

        return $e;
    }

    /**
     * @throws ClientExceptionInterface
     * @throws ClientException\Exception
     * @throws ClientException\Request
     * @throws ClientException\Server
     */
    public function put($payload): self
    {
        $request = new Request(
            $this->getClient()->getApiUrl() . Collection::getCollectionPath() . '/' . $this->getId(),
            'PUT',
            'php://temp',
            ['content-type' => 'application/json']
        );

        $request->getBody()->write(json_encode($payload));
        $response = $this->client->send($request);
        $responseCode = (int)$response->getStatusCode();

        if ($responseCode !== 200 && $responseCode !== 204) {
            throw $this->getException($response);
        }

        return $this;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @param Endpoint|string|null $endpoint
     */
    public function setTo($endpoint): self
    {
        if (!($endpoint instanceof Endpoint)) {
            $endpoint = new Endpoint($endpoint);
        }

        $this->to = $endpoint;
        return $this;
    }

    /**
     * @throws ClientExceptionInterface
     * @throws ClientException\Exception
     * @throws ClientException\Request
     * @throws ClientException\Server
     */
    public function getTo(): Endpoint
    {
        if ($this->lazyLoad()) {
            return new Endpoint($this->data['to']['number'], $this->data['to']['type']);
        }

        return $this->to;
    }

    /**
     * @param Endpoint|string|null $endpoint
     *
     * @return $this
     */
    public function setFrom($endpoint): self
    {
        if (!($endpoint instanceof Endpoint)) {
            $endpoint = new Endpoint($endpoint);
        }

        $this->from = $endpoint;

        return $this;
    }

    /**
     * @throws ClientExceptionInterface
     * @throws ClientException\Exception
     * @throws ClientException\Request
     * @throws ClientException\Server
     */
    public function getFrom(): Endpoint
    {
        if ($this->lazyLoad()) {
            return new Endpoint($this->data['from']['number'], $this->data['from']['type']);
        }

        return $this->from;
    }

    public function setWebhook($type, ?string $url = null, string $method = null): ?self
    {
        if ($type instanceof Webhook) {
            $this->webhooks[$type->getType()] = $type;

            return $this;
        }

        if (is_null($url)) {
            throw new InvalidArgumentException('must provide `Vonage\Call\Webhook` object, ' .
                'or a type and url: missing url');
        }

        $this->webhooks[$type] = new Webhook($type, $url, $method);

        return $this;
    }

    /**
     * @param string|int $length
     */
    public function setTimer(string $type, $length): void
    {
        $this->data[$type . '_timer'] = $length;
    }

    /**
     * @param string|int $length
     */
    public function setTimeout(string $type, $length): void
    {
        $this->data[$type . '_timeout'] = $length;
    }

    public function setNcco($ncco): self
    {
        $this->data['ncco'] = $ncco;

        return $this;
    }

    /**
     * @throws ClientExceptionInterface
     * @throws ClientException\Exception
     * @throws ClientException\Request
     * @throws ClientException\Server
     */
    public function getStatus()
    {
        if ($this->lazyLoad()) {
            return $this->data['status'];
        }

        return null;
    }

    /**
     * @throws ClientExceptionInterface
     * @throws ClientException\Exception
     * @throws ClientException\Request
     * @throws ClientException\Server
     */
    public function getDirection()
    {
        if ($this->lazyLoad()) {
            return $this->data['direction'];
        }

        return null;
    }

    /**
     * @throws ClientExceptionInterface
     * @throws ClientException\Exception
     * @throws ClientException\Request
     * @throws ClientException\Server
     */
    public function getConversation(): ?Conversation
    {
        if ($this->lazyLoad()) {
            return new Conversation($this->data['conversation_uuid']);
        }

        return null;
    }

    /**
     * Returns true if the resource data is loaded.
     *
     * Will attempt to load the data if it's not already.
     *
     * @throws ClientExceptionInterface
     * @throws ClientException\Exception
     * @throws ClientException\Request
     * @throws ClientException\Server
     */
    protected function lazyLoad(): bool
    {
        if (!empty($this->data)) {
            return true;
        }

        if (isset($this->id)) {
            $this->get();

            return true;
        }

        return false;
    }

    /**
     * @param $name
     *
     * @noinspection MagicMethodsValidityInspection
     */
    public function __get(string $name)
    {
        switch ($name) {
            case 'stream':
            case 'talk':
            case 'dtmf':
                return $this->lazySubresource(ucfirst($name));
            default:
                throw new RuntimeException('property does not exist: ' . $name);
        }
    }

    /**
     * @param $name
     * @param $arguments
     */
    public function __call($name, $arguments)
    {
        switch ($name) {
            case 'stream':
            case 'talk':
            case 'dtmf':
                $entity = $this->lazySubresource(ucfirst($name));
                return call_user_func_array($entity, $arguments);
            default:
                throw new RuntimeException('method does not exist: ' . $name);
        }
    }

    protected function lazySubresource($type)
    {
        if (!isset($this->subresources[$type])) {
            $class = 'Vonage\Call\\' . $type;
            $instance = new $class($this->getId());
            $instance->setClient($this->getClient());
            $this->subresources[$type] = $instance;
        }

        return $this->subresources[$type];
    }

    public function jsonSerialize(): array
    {
        $dataA = $this->data;
        $dataB = [];

        if (isset($this->to)) {
            $dataA['to'] = [$this->to->jsonSerialize()];
        }

        if (isset($this->from)) {
            $dataA['from'] = $this->from->jsonSerialize();
        }

        foreach ($this->webhooks as $webhook) {
            $dataB[] = $webhook->jsonSerialize();
        }

        return array_merge($dataA, $dataB);
    }

    public function jsonUnserialize(array $json): void
    {
        $this->data = $json;
        $this->id = $json['uuid'];
    }
}
