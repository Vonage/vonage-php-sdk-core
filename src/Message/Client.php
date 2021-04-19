<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace Vonage\Message;

use InvalidArgumentException;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\ResponseInterface;
use ReflectionClass;
use ReflectionException;
use RuntimeException;
use Vonage\Client\APIResource;
use Vonage\Client\ClientAwareInterface;
use Vonage\Client\ClientAwareTrait;
use Vonage\Client\Exception as ClientException;
use Vonage\Client\Exception\ThrottleException;
use Vonage\Entity\Filter\FilterInterface;
use Vonage\Entity\Filter\KeyValueFilter;

use function class_exists;
use function count;
use function get_class;
use function is_array;
use function is_null;
use function is_string;
use function json_decode;
use function preg_match;
use function sleep;
use function sprintf;
use function strpos;
use function strtolower;
use function substr;
use function trigger_error;
use function ucfirst;

/**
 * Class Client
 *
 * @deprecated Use \Vonage\SMS\Client instead
 *
 * @method Text sendText(string $to, string $from, string $text, array $additional = []) Send a Test Message
 */
class Client implements ClientAwareInterface
{
    /**
     * @deprecated This service will no longer be directly ClientAware
     */
    use ClientAwareTrait;

    /**
     * @var APIResource
     */
    protected $api;

    public function __construct(APIResource $api = null)
    {
        $this->api = $api;
    }

    /**
     * Shim to handle older instantiations of this class
     *
     * @deprecated Will remove in v3
     */
    protected function getApiResource(): APIResource
    {
        if (is_null($this->api)) {
            $api = new APIResource();
            $api->setClient($this->getClient())
                ->setBaseUrl($this->getClient()->getRestUrl())
                ->setIsHAL(false)
                ->setErrorsOn200(true);
            $api->setExceptionErrorHandler(
                function (ResponseInterface $response) {
                    //check for valid data, as well as an error response from the API
                    if ((int)$response->getStatusCode() === 429) {
                        throw new ClientException\Request('too many concurrent requests', $response->getStatusCode());
                    }

                    $data = json_decode($response->getBody()->getContents(), true);

                    if (!isset($data['messages'])) {
                        if (isset($data['error-code'], $data['error-code-label'])) {
                            $e = new ClientException\Request($data['error-code-label'], (int)$data['error-code']);
                        } else {
                            $e = new ClientException\Request('unexpected response from API');
                        }

                        $e->setEntity($data);
                        throw $e;
                    }

                    //normalize errors (client vrs server)
                    foreach ($data['messages'] as $part) {
                        switch ($part['status']) {
                            case '0':
                                break; //all okay
                            case '1':
                                $e = new ThrottleException($part['error-text']);
                                $e->setTimeout(1);
                                $e->setEntity($data);

                                if (preg_match('#Throughput Rate Exceeded - please wait \[\s+(\d+)\s+] and retry#', $part['error-text'], $match)) {
                                    $seconds = max((int)$match[1] / 1000, 1);
                                    $e->setTimeout($seconds);
                                }

                                throw $e;
                            case '5':
                                $e = new ClientException\Server($part['error-text'], (int)$part['status']);
                                $e->setEntity($data);
                                throw $e;
                            default:
                                $e = new ClientException\Request($part['error-text'], (int)$part['status']);
                                $e->setEntity($data);
                                throw $e;
                        }
                    }
                }
            );

            $this->api = $api;
        }

        return clone $this->api;
    }

    /**
     * @param Message|array $message
     *
     * @throws ClientException\Exception
     * @throws ClientException\Request
     * @throws ClientException\Server
     * @throws ClientExceptionInterface
     */
    public function send($message): Message
    {
        if (!($message instanceof MessageInterface)) {
            trigger_error(
                'Passing an array to Vonage\Messages\Client::send() is deprecated, ' .
                'please pass a MessageInterface object instead',
                E_USER_DEPRECATED
            );

            $message = $this->createMessageFromArray($message);
        }

        $params = @$message->getRequestData(false);

        try {
            $api = $this->getApiResource();
            $api->setBaseUri('/sms/json');
            $api->create($params);

            @$message->setRequest($api->getLastRequest());
            @$message->setResponse($api->getLastResponse());
        } catch (ThrottleException $e) {
            sleep($e->getTimeout());

            $this->send($message);
        } catch (ClientException\Request $e) {
            $e->setEntity($message);

            throw $e;
        }

        return $message;
    }

    /**
     * @throws ClientExceptionInterface
     * @throws ClientException\Exception
     * @throws ClientException\Request
     */
    public function sendShortcode($message): array
    {
        if (!($message instanceof Shortcode)) {
            $message = Shortcode::createMessageFromArray($message);
        }

        $params = $message->getRequestData();

        try {
            $api = $this->getApiResource();
            $api->setBaseUri('/sc/us/' . $message->getType() . '/json');

            $body = $api->create($params);
        } catch (ClientException\Request $e) {
            $e->setEntity($message);
            throw $e;
        }

        foreach ($body['messages'] as $m) {
            if ((int)$m['status'] !== 0) {
                $e = new ClientException\Request($m['error-text'], $m['status']);
                $e->setEntity($message);
                throw $e;
            }
        }

        return $body;
    }

    /**
     * @todo Fix all this error detection so it's standard
     *
     * @throws ClientExceptionInterface
     * @throws ClientException\Exception
     * @throws ClientException\Request
     *
     * @deprecated Please use the Reports API instead
     */
    public function get($query): array
    {
        if ($query instanceof Query) {
            $params = $query->getParams();
        } elseif ($query instanceof FilterInterface) {
            $params = $query->getQuery();
        } elseif ($query instanceof MessageInterface) {
            $params = ['ids' => [$query->getMessageId()]];
        } elseif (is_string($query)) {
            $params = ['ids' => [$query]];
        } elseif (is_array($query)) {
            $params = ['ids' => $query];
        } else {
            throw new InvalidArgumentException(
                'query must be an instance of Query, ' .
                'MessageInterface, string ID, or array of IDs.'
            );
        }

        $api = $this->getApiResource();

        try {
            $data = $api->get('/search/messages', (new KeyValueFilter($params))->getQuery());
        } catch (ClientException\Request $e) {
            $response = $api->getLastResponse();

            if (null !== $response) {
                $response->getBody()->rewind();

                if (null !== $api->getLastResponse()) {
                    $body = $api->getLastResponse()->getBody()->getContents();
                }
            }

            if (empty($body)) {
                $e = new ClientException\Request('error status from API', $e->getCode());
                $response->getBody()->rewind();
                $e->setEntity($response);

                throw $e;
            }

            throw $e;
        }

        if (!isset($data['items'])) {
            // Check if we just got a single result instead of a list
            if (isset($data['message-id'])) {
                $newData = [];
                $newData['items'][] = $data;
                $data = $newData;
            } else {
                // Otherwise we got an unexpected response from the API
                $e = new ClientException\Request('unexpected response from API');
                $e->setEntity($data);
                throw $e;
            }
        }

        if (count($data['items']) === 0) {
            return [];
        }

        $collection = [];

        foreach ($data['items'] as $index => $item) {
            switch ($item['type']) {
                case 'MT':
                    $new = new Message($item['message-id']);
                    break;
                case 'MO':
                    $new = new InboundMessage($item['message-id']);
                    break;
                default:
                    $e = new ClientException\Request('unexpected response from API');
                    $e->setEntity($data);
                    throw $e;
            }

            @$new->setResponse($api->getLastResponse());
            $new->setIndex($index);
            $collection[] = $new;
        }

        return $collection;
    }

    /**
     * @todo Fix all this error detection so it's standard
     *
     * @param string|MessageInterface $idOrMessage
     *
     * @throws ClientExceptionInterface
     * @throws ClientException\Exception
     * @throws ClientException\Request
     *
     * @return InboundMessage|Message|MessageInterface|null
     *
     * @deprecated Please use the Reports API instead
     */
    public function search($idOrMessage)
    {
        if ($idOrMessage instanceof MessageInterface) {
            $id = $idOrMessage->getMessageId();
            $message = $idOrMessage;
        } else {
            $id = $idOrMessage;
        }

        $api = $this->getApiResource();

        try {
            $data = $api->get('/search/messages', (new KeyValueFilter(['id' => $id]))->getQuery());
        } catch (ClientException\Request $e) {
            if ($e->getCode() !== 200) {
                // This method had a different error, so switch to the expected error message
                if ($e->getMessage() === 'unexpected response from API') {
                    $entity = $e->getEntity();
                    $e = new ClientException\Request('error status from API', $e->getCode());
                    $e->setEntity($entity);
                    throw $e;
                }

                throw $e;
            }
        }

        $response = $api->getLastResponse();

        if (null !== $response) {
            $status = (int)$response->getStatusCode();

            if ($status !== 200 && isset($data['error-code'])) {
                throw new ClientException\Request($data['error-code-label'], $data['error-code']);
            }

            if ($status === 429) {
                throw new ClientException\Request('too many concurrent requests', $status);
            }

            if ($status !== 200) {
                $e = new ClientException\Request('error status from API', $status);
                $response->getBody()->rewind();
                $e->setEntity($response);

                throw $e;
            }

            if (empty($data)) {
                $e = new ClientException\Request('no message found for `' . $id . '`');
                $response->getBody()->rewind();
                $e->setEntity($response);

                throw $e;
            }

            switch ($data['type']) {
                case 'MT':
                    $new = new Message($data['message-id']);
                    break;
                case 'MO':
                    $new = new InboundMessage($data['message-id']);
                    break;
                default:
                    $e = new ClientException\Request('unexpected response from API');
                    $e->setEntity($data);
                    throw $e;
            }

            if (isset($message) && !($message instanceof $new)) {
                throw new ClientException\Exception(
                    sprintf(
                        'searched for message with type `%s` but message of type `%s`',
                        get_class($message),
                        get_class($new)
                    )
                );
            }

            if (!isset($message)) {
                $message = $new;
            }

            @$message->setResponse($response);

            return $message;
        }

        return null;
    }

    /**
     * @todo Fix all this error detection so it's standard
     *
     * @throws ClientExceptionInterface
     * @throws ClientException\Exception
     * @throws ClientException\Request
     *
     * @deprecated Please use the Reports API instead
     */
    public function searchRejections(Query $query): array
    {
        $params = $query->getParams();
        $api = $this->getApiResource();

        try {
            $data = $api->get('/search/rejections', (new KeyValueFilter($params))->getQuery());
        } catch (ClientException\Request $e) {
            if ($e->getMessage() === 'unexpected response from API') {
                $entity = $e->getEntity();
                $e = new ClientException\Request('error status from API', $e->getCode());
                $e->setEntity($entity);
                throw $e;
            }

            throw $e;
        }

        $response = $api->getLastResponse();

        if (null !== $response) {
            $status = $response->getStatusCode();

            if ($status !== 200 && isset($data['error-code'])) {
                throw new ClientException\Request($data['error-code-label'], $data['error-code']);
            }

            if ($status !== 200) {
                $e = new ClientException\Request('error status from API', $status);
                $response->getBody()->rewind();
                $e->setEntity($response);

                throw $e;
            }

            if (!isset($data['items'])) {
                $e = new ClientException\Request('unexpected response from API');
                $e->setEntity($data);

                throw $e;
            }

            if (count($data['items']) === 0) {
                return [];
            }

            $collection = [];

            foreach ($data['items'] as $index => $item) {
                switch ($item['type']) {
                    case 'MT':
                        $new = new Message($item['message-id']);
                        break;
                    case 'MO':
                        $new = new InboundMessage($item['message-id']);
                        break;
                    default:
                        $e = new ClientException\Request('unexpected response from API');
                        $e->setEntity($data);
                        throw $e;
                }

                @$new->setResponse($response);
                $new->setIndex($index);

                $collection[] = $new;
            }

            return $collection;
        }

        return [];
    }

    /**
     * @param MessageInterface|array $message
     */
    protected function createMessageFromArray($message): Message
    {
        if (!is_array($message)) {
            throw new RuntimeException('message must implement `' . MessageInterface::class . '` or be an array`');
        }

        foreach (['to', 'from'] as $param) {
            if (!isset($message[$param])) {
                throw new InvalidArgumentException('missing expected key `' . $param . '`');
            }
        }

        $to = $message['to'];
        $from = $message['from'];

        unset($message['to'], $message['from']);

        return new Message($to, $from, $message);
    }

    /**
     * Convenience feature allowing messages to be sent without creating a message object first.
     *
     * @throws ClientExceptionInterface
     * @throws ClientException\Exception
     * @throws ClientException\Request
     * @throws ClientException\Server
     * @throws ReflectionException
     *
     * @return array|Message|MessageInterface
     */
    public function __call(string $name, $arguments)
    {
        if (strpos($name, "send") !== 0) {
            throw new RuntimeException(
                sprintf(
                    '`%s` is not a valid method on `%s`',
                    $name,
                    get_class($this)
                )
            );
        }

        $class = substr($name, 4);
        $class = 'Vonage\\Message\\' . ucfirst(strtolower($class));

        if (!class_exists($class)) {
            throw new RuntimeException(
                sprintf(
                    '`%s` is not a valid method on `%s`',
                    $name,
                    get_class($this)
                )
            );
        }

        $reflection = new ReflectionClass($class);
        /** @var Message $message */
        $message = $reflection->newInstanceArgs($arguments);

        return $this->send($message);
    }
}
