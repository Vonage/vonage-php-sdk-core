<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

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

use function get_class;
use function is_array;
use function is_null;
use function is_string;
use function serialize;
use function trigger_error;
use function unserialize;

class Client implements ClientAwareInterface, APIClient
{
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
     * Will change in v3 to just return the required API object
     */
    public function getApiResource(): APIResource
    {
        if (is_null($this->api)) {
            $api = new APIResource();
            $api->setClient($this->getClient())
                ->setIsHAL(false)
                ->setBaseUri('/verify');
            $this->api = $api;
        }

        return $this->api;
    }

    /**
     * @param string|array|Verification|Request $verification
     *
     * @throws ClientExceptionInterface
     * @throws ClientException\Exception
     * @throws ClientException\Request
     * @throws ClientException\Server
     */
    public function start($verification): Verification
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

        $api = $this->getApiResource();
        $verification = $this->createVerification($verification);
        $response = $api->create($verification->toArray(), '/json');

        $this->processReqRes($verification, $api->getLastRequest(), $api->getLastResponse(), true);

        return $this->checkError($verification, $response);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws ClientException\Exception
     * @throws ClientException\Request
     * @throws ClientException\Server
     *
     * @return array{request_id: string, status: string}
     */
    public function requestPSD2(RequestPSD2 $request): array
    {
        $api = $this->getApiResource();
        $response = $api->create($request->toArray(), '/psd2/json');

        $this->checkError($request, $response);

        return $response;
    }

    /**
     * @param string|Verification $verification
     *
     * @throws ClientExceptionInterface
     * @throws ClientException\Exception
     * @throws ClientException\Request
     * @throws ClientException\Server
     */
    public function search($verification)
    {
        if ($verification instanceof Verification) {
            trigger_error(
                'Passing a Verification object to Vonage\Verification\Client::search() is deprecated, ' .
                'please pass a string ID instead',
                E_USER_DEPRECATED
            );
        }

        $api = $this->getApiResource();
        $verification = $this->createVerification($verification);

        $params = [
            'request_id' => $verification->getRequestId()
        ];

        $data = $api->create($params, '/search/json');
        $this->processReqRes($verification, $api->getLastRequest(), $api->getLastResponse(), true);

        return $this->checkError($verification, $data);
    }

    /**
     * @param $verification
     *
     * @throws ClientExceptionInterface
     * @throws ClientException\Exception
     * @throws ClientException\Request
     * @throws ClientException\Server
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
     * @throws ClientException\Request
     * @throws ClientException\Server
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
     * @param string|array|Verification $verification
     *
     * @throws ClientExceptionInterface
     * @throws ClientException\Exception
     * @throws ClientException\Request
     * @throws ClientException\Server
     */
    public function check($verification, string $code, string $ip = null): Verification
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

        $api = $this->getApiResource();
        $verification = $this->createVerification($verification);
        $params = [
            'request_id' => $verification->getRequestId(),
            'code' => $code
        ];

        if (!is_null($ip)) {
            $params['ip'] = $ip;
        }

        $data = $api->create($params, '/check/json');

        $this->processReqRes($verification, $api->getLastRequest(), $api->getLastResponse(), false);

        return $this->checkError($verification, $data);
    }

    /**
     * @deprecated Serialize the Verification object directly instead
     */
    public function serialize(Verification $verification): string
    {
        trigger_error(
            get_class($this) . '::serialize() is deprecated, serialize the Verification object directly',
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
            get_class($this) . '::unserialize() is deprecated, unserialize the Verification object directly',
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
     * @param string|array|Verification $verification
     * @param string $cmd Next command to execute, must be `cancel` or `trigger_next_event`
     *
     * @throws ClientExceptionInterface
     * @throws ClientException\Exception
     * @throws ClientException\Request
     * @throws ClientException\Server
     */
    protected function control($verification, string $cmd): Verification
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

        $api = $this->getApiResource();
        $verification = $this->createVerification($verification);

        $params = [
            'request_id' => $verification->getRequestId(),
            'cmd' => $cmd
        ];

        $data = $api->create($params, '/control/json');
        $this->processReqRes($verification, $api->getLastRequest(), $api->getLastResponse(), false);

        return $this->checkError($verification, $data);
    }

    /**
     * @param $verification
     * @param $data
     *
     * @throws ClientException\Request
     * @throws ClientException\Server
     */
    protected function checkError($verification, $data)
    {
        if (!isset($data['status'])) {
            $e = new ClientException\Request('unexpected response from API');
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
                $e = new ClientException\Server($data['error_text'], (int)$data['status']);
                $e->setEntity($data);
                break;
            default:
                $e = new ClientException\Request($data['error_text'], (int)$data['status']);
                $e->setEntity($data);
                break;
        }

        $e->setEntity($verification);

        throw $e;
    }

    /**
     * @param bool $replace
     */
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
     *
     * @param $verification
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
