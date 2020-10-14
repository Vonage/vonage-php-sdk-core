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
use Vonage\Client\Exception;
use Vonage\Conversations\Conversation;
use Vonage\Entity\EntityInterface;
use Vonage\Entity\JsonResponseTrait;
use Vonage\Entity\JsonSerializableTrait;
use Vonage\Entity\JsonUnserializableInterface;
use Vonage\Entity\NoRequestResponseTrait;

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

    protected $id;

    protected $to;

    protected $from;

    /**
     * @var Webhook[]
     */
    protected $webhooks = [];

    protected $data = [];

    protected $subresources = [];

    /**
     * Call constructor.
     *
     * @param null $id
     */
    public function __construct($id = null)
    {
        trigger_error(
            'Vonage\Call\Call is deprecated, please use Vonage\Voice\Client for functionality instead',
            E_USER_DEPRECATED
        );

        $this->id = $id;
    }

    /**
     * @return $this
     * @throws Exception\Exception
     * @throws Exception\Request
     * @throws Exception\Server
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
     * @param ResponseInterface $response
     * @return Exception\Exception|Exception\Request|Exception\Server
     */
    protected function getException(ResponseInterface $response)
    {
        $body = json_decode($response->getBody()->getContents(), true);
        $status = $response->getStatusCode();

        if ($status >= 400 && $status < 500) {
            $e = new Exception\Request($body['error_title'], $status);
        } elseif ($status >= 500 && $status < 600) {
            $e = new Exception\Server($body['error_title'], $status);
        } else {
            $e = new Exception\Exception('Unexpected HTTP Status Code');
        }

        return $e;
    }

    /**
     * @param $payload
     * @return $this
     * @throws ClientExceptionInterface
     * @throws Exception\Exception
     * @throws Exception\Request
     * @throws Exception\Server
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

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param $endpoint
     * @return $this
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
     * @return Endpoint
     * @throws ClientExceptionInterface
     * @throws Exception\Exception
     * @throws Exception\Request
     * @throws Exception\Server
     */
    public function getTo(): Endpoint
    {
        if ($this->lazyLoad()) {
            return new Endpoint($this->data['to']['number'], $this->data['to']['type']);
        }

        return $this->to;
    }

    /**
     * @param $endpoint
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
     * @return Endpoint
     * @throws ClientExceptionInterface
     * @throws Exception\Exception
     * @throws Exception\Request
     * @throws Exception\Server
     */
    public function getFrom(): Endpoint
    {
        if ($this->lazyLoad()) {
            return new Endpoint($this->data['from']['number'], $this->data['from']['type']);
        }

        return $this->from;
    }

    /**
     * @param $type
     * @param null $url
     * @param null $method
     * @return $this
     */
    public function setWebhook($type, $url = null, $method = null): self
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
     * @param $type
     * @param $length
     */
    public function setTimer($type, $length): void
    {
        $this->data[$type . '_timer'] = $length;
    }

    /**
     * @param $type
     * @param $length
     */
    public function setTimeout($type, $length): void
    {
        $this->data[$type . '_timeout'] = $length;
    }

    /**
     * @param $ncco
     * @return $this
     */
    public function setNcco($ncco): Call
    {
        $this->data['ncco'] = $ncco;
        return $this;
    }

    /**
     * @return mixed|null
     * @throws ClientExceptionInterface
     * @throws Exception\Exception
     * @throws Exception\Request
     * @throws Exception\Server
     */
    public function getStatus()
    {
        if ($this->lazyLoad()) {
            return $this->data['status'];
        }

        return null;
    }

    /**
     * @return mixed|null
     * @throws ClientExceptionInterface
     * @throws Exception\Exception
     * @throws Exception\Request
     * @throws Exception\Server
     */
    public function getDirection()
    {
        if ($this->lazyLoad()) {
            return $this->data['direction'];
        }

        return null;
    }

    /**
     * @return Conversation|null
     * @throws ClientExceptionInterface
     * @throws Exception\Exception
     * @throws Exception\Request
     * @throws Exception\Server
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
     * @return bool
     * @throws ClientExceptionInterface
     * @throws Exception\Exception
     * @throws Exception\Request
     * @throws Exception\Server
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
     * @return mixed
     * @noinspection MagicMethodsValidityInspection
     */
    public function __get($name)
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
     * @return mixed
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

    /**
     * @param $type
     * @return mixed
     */
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

    /**
     * @return array|mixed
     */
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

    /**
     * @param array $json
     */
    public function jsonUnserialize(array $json): void
    {
        $this->data = $json;
        $this->id = $json['uuid'];
    }
}
