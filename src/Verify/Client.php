<?php

declare(strict_types=1);

namespace Vonage\Verify;

use InvalidArgumentException;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;
use Vonage\Client\APIClient;
use Vonage\Client\APIResource;
use Vonage\Client\ClientAwareInterface;
use Vonage\Client\ClientAwareTrait;
use Vonage\Client\Exception as ClientException;

use function is_array;
use function is_string;
use function serialize;
use function trigger_error;
use function unserialize;

class Client implements ClientAwareInterface, APIClient
{
    use ClientAwareTrait;

    protected APIResource $api;

    public function __construct(?APIResource $api = null, ?\Vonage\Client $client = null)
    {
        if ($api === null) {
            $this->api = (new APIResource())
                ->setIsHAL(false)
                ->setBaseUri('/verify');
        } else {
            $this->api = $api;
        }

        if ($client !== null) {
            $this->client = $client;
            $this->api->setClient($client);
        }
    }

    /**
     * Shim to handle older instantiations of this class
     * Will change in v3 to just return the required API object
     *
     * @deprecated This method will be removed in the next major version.
     */
    public function getApiResource(): APIResource
    {
        trigger_error(
            'Vonage\\Verify\\Client::getApiResource() is deprecated and will be removed in the next major version.',
            E_USER_DEPRECATED
        );
        return $this->api;
    }

    /**
     * @throws ClientExceptionInterface
     * @throws ClientException\Exception
     * @throws ClientException\RequestException
     * @throws ClientException\ServerException
     *
     * @deprecated Use startVerification() with a StartVerification object instead.
     */
    public function start(Request|Verification|array|string $verification): Verification
    {
        if (is_array($verification)) {
            trigger_error(
                'Passing an array to Vonage\Verification\Client::start() is deprecated, ' .
                'please pass a Vonage\Verify\Request object instead',
                E_USER_DEPRECATED
            );
        } elseif (is_string($verification)) {
            trigger_error(
                'Passing a string to Vonage\Verification\Client::start() is deprecated, ' .
                'please pass a Vonage\Verify\Request object instead',
                E_USER_DEPRECATED
            );
        }

        if ($verification instanceof Request) {
            // Reformat to an array to work with v2.x code, but prep for v3.0.0
            $verification = $verification->toArray();
        }

        $verification = $this->createVerification($verification);
        $response = $this->api->create($verification->toArray(), '/json');

        $this->processReqRes($verification, $this->api->getLastRequest(), $this->api->getLastResponse(), true);

        return $this->checkError($verification, $response);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws ClientException\Exception
     * @throws ClientException\RequestException
     * @throws ClientException\ServerException
     *
     * @return array{request_id: string, status: string}
     *
     * @deprecated Use startPsd2Verification() with a StartPSD2 object instead.
     */
    public function requestPSD2(RequestPSD2 $request): array
    {
        $response = $this->api->create($request->toArray(), '/psd2/json');

        $this->checkError($request, $response);

        return $response;
    }

    /**
     * @throws ClientExceptionInterface
     * @throws ClientException\Exception
     * @throws ClientException\RequestException
     * @throws ClientException\ServerException
     */
    public function search(Verification|string $verification)
    {
        if ($verification instanceof Verification) {
            trigger_error(
                'Passing a Verification object to Vonage\Verification\Client::search() is deprecated, ' .
                'please pass a string ID instead',
                E_USER_DEPRECATED
            );
        }

        $verification = $this->createVerification($verification);

        $params = [
            'request_id' => $verification->getRequestId()
        ];

        $data = $this->api->create($params, '/search/json');
        $this->processReqRes($verification, $this->api->getLastRequest(), $this->api->getLastResponse(), true);

        return $this->checkError($verification, $data);
    }

    /**
     * @param $verification
     *
     * @throws ClientExceptionInterface
     * @throws ClientException\Exception
     * @throws ClientException\RequestException
     * @throws ClientException\ServerException
     */
    public function cancel($verification): Verification
    {
        if ($verification instanceof Verification) {
            trigger_error(
                'Passing a Verification object to Vonage\Verification\Client::cancel() is deprecated, ' .
                'please pass a string ID instead',
                E_USER_DEPRECATED
            );
        }

        return $this->control($verification, 'cancel');
    }

    /**
     * @param $verification
     *
     * @throws ClientExceptionInterface
     * @throws ClientException\Exception
     * @throws ClientException\RequestException
     * @throws ClientException\ServerException
     *
     * @deprecated Use triggerNextEvent() with a request ID string instead.
     */
    public function trigger($verification): Verification
    {
        if ($verification instanceof Verification) {
            trigger_error(
                'Passing a Verification object to Vonage\Verification\Client::trigger() is deprecated, ' .
                'please pass a string ID instead',
                E_USER_DEPRECATED
            );
        }

        return $this->control($verification, 'trigger_next_event');
    }

    /**
     * @throws ClientExceptionInterface
     * @throws ClientException\Exception
     * @throws ClientException\RequestException
     * @throws ClientException\ServerException
     */
    public function check(Verification|array|string $verification, string $code, ?string $ip = null): Verification
    {
        if (is_array($verification)) {
            trigger_error(
                'Passing an array for parameter 1 to Vonage\Verification\Client::check() is deprecated, ' .
                'please pass a string ID instead',
                E_USER_DEPRECATED
            );
        } elseif ($verification instanceof Verification) {
            trigger_error(
                'Passing a Verification object for parameter 1 to Vonage\Verification\Client::check() is deprecated, ' .
                'please pass a string ID instead',
                E_USER_DEPRECATED
            );
        }

        $verification = $this->createVerification($verification);
        $params = [
            'request_id' => $verification->getRequestId(),
            'code' => $code
        ];

        if ($ip !== null) {
            $params['ip'] = $ip;
        }

        $data = $this->api->create($params, '/check/json');

        $this->processReqRes($verification, $this->api->getLastRequest(), $this->api->getLastResponse(), false);

        return $this->checkError($verification, $data);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws ClientException\Exception
     * @throws ClientException\Request
     * @throws ClientException\Server
     */
    public function startVerification(StartVerification $request): string
    {
        $response = $this->api->create($request->toArray(), '/json');

        return $response['request_id'];
    }

    /**
     * @throws ClientExceptionInterface
     * @throws ClientException\Exception
     * @throws ClientException\Request
     * @throws ClientException\Server
     */
    public function startPsd2Verification(StartPSD2 $request): string
    {
        $response = $this->api->create($request->toArray(), '/psd2/json');

        return $response['request_id'];
    }

    /**
     * @throws ClientExceptionInterface
     * @throws ClientException\Exception
     * @throws ClientException\Request
     * @throws ClientException\Server
     */
    public function triggerNextEvent(string $requestId): bool
    {
        $data = $this->api->create(
            ['request_id' => $requestId, 'cmd' => 'trigger_next_event'],
            '/control/json'
        );

        return ($data['status'] ?? '') === '0';
    }

    /**
     * @deprecated Serialize the Verification object directly instead
     */
    public function serialize(Verification $verification): string
    {
        trigger_error(
            static::class . '::serialize() is deprecated, serialize the Verification object directly',
            E_USER_DEPRECATED
        );

        return serialize($verification);
    }

    /**
     * @param $verification
     */
    public function unserialize($verification): Verification
    {
        trigger_error(
            static::class . '::unserialize() is deprecated, unserialize the Verification object directly',
            E_USER_DEPRECATED
        );

        if (is_string($verification)) {
            $verification = unserialize($verification, [Verification::class]);
        }

        if (!($verification instanceof Verification)) {
            throw new InvalidArgumentException('expected verification object or serialize verification object');
        }

        @$verification->setClient($this);
        return $verification;
    }

    /**
     * @param string $cmd Next command to execute, must be `cancel` or `trigger_next_event`
     *
     * @throws ClientExceptionInterface
     * @throws ClientException\Exception
     * @throws ClientException\RequestException
     * @throws ClientException\ServerException
     */
    protected function control(Verification|array|string $verification, string $cmd): Verification
    {
        if (is_array($verification)) {
            trigger_error(
                'Passing an array for parameter 1 to Vonage\Verification\Client::control() is deprecated,' .
                'please pass a string ID instead',
                E_USER_DEPRECATED
            );
        } elseif ($verification instanceof Verification) {
            trigger_error(
                'Passing a Verification object for parameter 1 to Vonage\Verification\Client::control() ' .
                'is deprecated, please pass a string ID instead',
                E_USER_DEPRECATED
            );
        }

        $verification = $this->createVerification($verification);

        $params = [
            'request_id' => $verification->getRequestId(),
            'cmd' => $cmd
        ];

        $data = $this->api->create($params, '/control/json');
        $this->processReqRes($verification, $this->api->getLastRequest(), $this->api->getLastResponse(), false);

        return $this->checkError($verification, $data);
    }

    /**
     * @throws ClientException\RequestException
     * @throws ClientException\ServerException
     */
    protected function checkError($verification, array $data)
    {
        if (!isset($data['status'])) {
            $e = new ClientException\RequestException('unexpected response from API');
            $e->setEntity($data);

            throw $e;
        }

        //normalize errors (client vrs server)
        switch ($data['status']) {
            // These exist because `status` is valid in both the error
            // response and a success response, but serve different purposes
            // in each case
            case 'IN PROGRESS':
            case 'SUCCESS':
            case 'FAILED':
            case 'EXPIRED':
            case 'CANCELLED':
            case '0':
                return $verification;
            case '5':
                $e = new ClientException\ServerException($data['error_text'], (int)$data['status']);
                $e->setEntity($data);
                break;
            default:
                $e = new ClientException\RequestException($data['error_text'], (int)$data['status']);
                $e->setEntity($data);

                if (array_key_exists('request_id', $data)) {
                    $e->setRequestId($data['request_id']);
                }

                if (array_key_exists('network', $data)) {
                    $e->setNetworkId($data['network']);
                }

                break;
        }

        $e->setEntity($verification);

        throw $e;
    }

    protected function processReqRes(
        Verification $verification,
        RequestInterface $req,
        ResponseInterface $res,
        $replace = true
    ): void {
        @$verification->setClient($this);

        if ($replace || !@$verification->getRequest()) {
            @$verification->setRequest($req);
        }

        if ($replace || !@$verification->getResponse()) {
            @$verification->setResponse($res);
        }

        if ($res->getBody()->isSeekable()) {
            $res->getBody()->rewind();
        }
    }

    /**
     * Creates a verification object from a variety of sources
     */
    protected function createVerification($verification): Verification
    {
        if ($verification instanceof Verification) {
            return $verification;
        }

        if (is_array($verification)) {
            return $this->createVerificationFromArray($verification);
        }

        if (is_string($verification)) {
            return new Verification($verification);
        }

        throw new RuntimeException('Unable to create Verification object from source data');
    }

    /**
     * @param $array
     */
    protected function createVerificationFromArray($array): Verification
    {
        if (!is_array($array)) {
            throw new RuntimeException('verification must implement `' . VerificationInterface::class .
                '` or be an array`');
        }

        foreach (['number', 'brand'] as $param) {
            if (!isset($array[$param])) {
                throw new InvalidArgumentException('missing expected key `' . $param . '`');
            }
        }

        $number = $array['number'];
        $brand = $array['brand'];

        unset($array['number'], $array['brand']);

        return @new Verification($number, $brand, $array);
    }
}
