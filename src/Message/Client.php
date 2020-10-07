<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license   MIT <https://github.com/vonage/vonage-php/blob/master/LICENSE>
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
use Vonage\Client\Exception;
use Vonage\Client\Exception\ThrottleException;
use Vonage\Entity\Filter\FilterInterface;
use Vonage\Entity\Filter\KeyValueFilter;

/**
 * Class Client
 * @deprecated Use \Vonage\SMS\Client instead
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

    /**
     * Message Client constructor.
     *
     * @param APIResource|null $api
     */
    public function __construct(APIResource $api = null)
    {
        $this->api = $api;
    }

    /**
     * Shim to handle older instantiations of this class
     *
     * @return APIResource
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
            $api->setExceptionErrorHandler(function (ResponseInterface $response) {
                //check for valid data, as well as an error response from the API
                if ((int)$response->getStatusCode() === 429) {
                    throw new Exception\Request('too many concurrent requests', $response->getStatusCode());
                }

                $data = json_decode($response->getBody()->getContents(), true);

                if (!isset($data['messages'])) {
                    if (isset($data['error-code'], $data['error-code-label'])) {
                        $e = new Exception\Request($data['error-code-label'], (int)$data['error-code']);
                    } else {
                        $e = new Exception\Request('unexpected response from API');
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

                            if (preg_match('#\[\s+(\d+)\s+]#', $part['error-text'], $match)) {
                                $e->setTimeout((int)$match[1] + 1);
                            }

                            throw $e;
                        case '5':
                            $e = new Exception\Server($part['error-text'], (int)$part['status']);
                            $e->setEntity($data);
                            throw $e;
                        default:
                            $e = new Exception\Request($part['error-text'], (int)$part['status']);
                            $e->setEntity($data);
                            throw $e;
                    }
                }
            });

            $this->api = $api;
        }

        return clone $this->api;
    }

    /**
     * @param Message|array $message
     * @return Message
     * @throws Exception\Exception
     * @throws Exception\Request
     * @throws Exception\Server
     * @throws ClientExceptionInterface
     */
    public function send($message)
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
        } catch (Exception\Request $e) {
            $e->setEntity($message);

            throw $e;
        }

        return $message;
    }

    /**
     * @param $message
     * @return array
     * @throws ClientExceptionInterface
     * @throws Exception\Exception
     * @throws Exception\Request
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
        } catch (Exception\Request $e) {
            $e->setEntity($message);
            throw $e;
        }

        foreach ($body['messages'] as $m) {
            if ((int)$m['status'] !== 0) {
                $e = new Exception\Request($m['error-text'], $m['status']);
                $e->setEntity($message);
                throw $e;
            }
        }

        return $body;
    }

    /**
     * @todo Fix all this error detection so it's standard
     * @param $query
     * @return array
     * @throws ClientExceptionInterface
     * @throws Exception\Exception
     * @throws Exception\Request
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
            throw new InvalidArgumentException('query must be an instance of Query, ' .
                'MessageInterface, string ID, or array of IDs.');
        }

        $api = $this->getApiResource();

        try {
            $data = $api->get('/search/messages', (new KeyValueFilter($params))->getQuery());
        } catch (Exception\Request $e) {
            $response = $api->getLastResponse();

            if (null !== $response) {
                $response->getBody()->rewind();

                if (null !== $api->getLastResponse()) {
                    $body = $api->getLastResponse()->getBody()->getContents();
                }
            }

            if (empty($body)) {
                $e = new Exception\Request('error status from API', $e->getCode());
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
                $e = new Exception\Request('unexpected response from API');
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
                    $e = new Exception\Request('unexpected response from API');
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
     * @param $idOrMessage
     * @return InboundMessage|Message|MessageInterface|null
     * @throws ClientExceptionInterface
     * @throws Exception\Exception
     * @throws Exception\Request
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
        } catch (Exception\Request $e) {
            if ($e->getCode() !== 200) {
                // This method had a different error, so switch to the expected error message
                if ($e->getMessage() === 'unexpected response from API') {
                    $entity = $e->getEntity();
                    $e = new Exception\Request('error status from API', $e->getCode());
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
                throw new Exception\Request($data['error-code-label'], $data['error-code']);
            }

            if ($status === 429) {
                throw new Exception\Request('too many concurrent requests', $status);
            }

            if ($status !== 200) {
                $e = new Exception\Request('error status from API', $status);
                $response->getBody()->rewind();
                $e->setEntity($response);

                throw $e;
            }

            if (empty($data)) {
                $e = new Exception\Request('no message found for `' . $id . '`');
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
                    $e = new Exception\Request('unexpected response from API');
                    $e->setEntity($data);
                    throw $e;
            }

            if (isset($message) && !($message instanceof $new)) {
                throw new Exception\Exception(sprintf(
                    'searched for message with type `%s` but message of type `%s`',
                    get_class($message),
                    get_class($new)
                ));
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
     * @param Query $query
     * @return array
     * @throws ClientExceptionInterface
     * @throws Exception\Exception
     * @throws Exception\Request
     * @deprecated Please use the Reports API instead
     */
    public function searchRejections(Query $query): array
    {
        $params = $query->getParams();
        $api = $this->getApiResource();

        try {
            $data = $api->get('/search/rejections', (new KeyValueFilter($params))->getQuery());
        } catch (Exception\Request $e) {
            if ($e->getMessage() === 'unexpected response from API') {
                $entity = $e->getEntity();
                $e = new Exception\Request('error status from API', $e->getCode());
                $e->setEntity($entity);
                throw $e;
            }

            throw $e;
        }

        $response = $api->getLastResponse();

        if (null !== $response) {
            $status = $response->getStatusCode();

            if ($status !== 200 && isset($data['error-code'])) {
                throw new Exception\Request($data['error-code-label'], $data['error-code']);
            }

            if ($status !== 200) {
                $e = new Exception\Request('error status from API', $status);
                $response->getBody()->rewind();
                $e->setEntity($response);

                throw $e;
            }

            if (!isset($data['items'])) {
                $e = new Exception\Request('unexpected response from API');
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
                        $e = new Exception\Request('unexpected response from API');
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
     * @param $message
     * @return Message
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
     * @param $name
     * @param $arguments
     * @return array|Message|MessageInterface
     * @throws ClientExceptionInterface
     * @throws Exception\Exception
     * @throws Exception\Request
     * @throws Exception\Server
     * @throws ReflectionException
     */
    public function __call($name, $arguments)
    {
        if (strpos($name, "send") !== 0) {
            throw new RuntimeException(sprintf(
                '`%s` is not a valid method on `%s`',
                $name,
                get_class($this)
            ));
        }

        $class = substr($name, 4);
        $class = 'Vonage\\Message\\' . ucfirst(strtolower($class));

        if (!class_exists($class)) {
            throw new RuntimeException(sprintf(
                '`%s` is not a valid method on `%s`',
                $name,
                get_class($this)
            ));
        }

        $reflection = new ReflectionClass($class);
        /** @var Message $message */
        $message = $reflection->newInstanceArgs($arguments);

        return $this->send($message);
    }
}
