<?php
/**
 * Nexmo Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Nexmo, Inc. (http://nexmo.com)
 * @license   https://github.com/Nexmo/nexmo-php/blob/master/LICENSE.txt MIT License
 */

namespace Nexmo\Verify;

use Nexmo\Client\APIResource;
use Nexmo\Client\ClientAwareInterface;
use Nexmo\Client\ClientAwareTrait;
use Nexmo\Client\Exception;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class Client implements ClientAwareInterface
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
     * Shim to handle older instatiations of this class
     * @deprecated Will remove in v3
     */
    protected function getApiResource() : APIResource
    {
        if (is_null($this->api)) {
            $api = new APIResource();
            $api->setClient($this->getClient())
                ->setIsHAL(false)
                ->setBaseUri('/verify')
            ;
            $this->api = $api;
        }
        return clone $this->api;
    }

    /**
     * @param string|array|Verification $verification
     */
    public function start($verification) : Verification
    {
        if (is_array($verification)) {
            trigger_error('Passing an array to Nexmo\Verification\Client::start() is deprecated, please pass a Verification object instead');
        }
        if (is_string($verification)) {
            trigger_error('Passing a string to Nexmo\Verification\Client::start() is deprecated, please pass a Verification object instead');
        }

        $api = $this->getApiResource();
        $verification = $this->createVerification($verification);
        $response = $api->get('json', $verification->toArray());

        $this->processReqRes($verification, $api->getLastRequest(), $api->getLastResponse(), true);
        return $this->checkError($verification, $response);
    }

    /**
     * @param string|Verification $verification
     */
    public function search($verification)
    {
        if (is_string($verification)) {
            trigger_error('Passing a Verification object to Nexmo\Verification\Client::search() is deprecated, please pass a string ID instead');
        }

        $api = $this->getApiResource();
        $verification = $this->createVerification($verification);

        $params = [
            'request_id' => $verification->getRequestId()
        ];

        $data = $api->get('search/json', $params);
        $this->processReqRes($verification, $api->getLastRequest(), $api->getLastResponse(), true);

        return $this->checkError($verification, $data);
    }

    public function cancel($verification)
    {
        return $this->control($verification, 'cancel');
    }

    public function trigger($verification)
    {
        return $this->control($verification, 'trigger_next_event');
    }

    /**
     * @param string|array|Verification $verification
     */
    public function check($verification, string $code, string $ip = null) : Verification
    {
        if (is_array($verification)) {
            @trigger_error('Passing an array for parameter 1 to Nexmo\Verification\Client::check() is deprecated, please pass a string ID instead');
        }
        if ($verification instanceof Verification) {
            @trigger_error('Passing a Verification object for parameter 1 to Nexmo\Verification\Client::check() is deprecated, please pass a string ID instead');
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

        $data = $api->get('check/json', $params);

        $this->processReqRes($verification, $api->getLastRequest(), $api->getLastResponse(), false);
        return $this->checkError($verification, $data);
    }

    public function serialize(Verification $verification)
    {
        return serialize($verification);
    }

    public function unserialize($verification)
    {
        if (is_string($verification)) {
            $verification = unserialize($verification);
        }

        if (!($verification instanceof Verification)) {
            throw new \InvalidArgumentException('expected verification object or serialize verification object');
        }

        $verification->setClient($this);
        return $verification;
    }

    /**
     * @param string|array|Verification $verification
     * @param string $cmd Next command to execute, must be `cancel` or `trigger_next_event`
     */
    protected function control($verification, string $cmd) : Verification
    {
        if (is_array($verification)) {
            trigger_error('Passing an array to Nexmo\Verification\Client::control() is deprecated, please pass a Verification object instead');
        }
        if (is_string($verification)) {
            trigger_error('Passing a string to Nexmo\Verification\Client::control() is deprecated, please pass a Verification object instead');
        }

        $api = $this->getApiResource();
        $verification = $this->createVerification($verification);

        $params = [
            'request_id' => $verification->getRequestId(),
            'cmd' => $cmd
        ];

        $data = $api->get('control/json', $params);
        $this->processReqRes($verification, $api->getLastRequest(), $api->getLastResponse(), false);
        return $this->checkError($verification, $data);
    }

    protected function checkError(Verification $verification, $data)
    {
        if (!isset($data['status'])) {
            $e = new Exception\Request('unexpected response from API');
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
                $e = new Exception\Server($data['error_text'], $data['status']);
                $e->setEntity($data);
                break;
            default:
                $e = new Exception\Request($data['error_text'], $data['status']);
                $e->setEntity($data);
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
    )
    {
        $verification->setClient($this);

        if ($replace || !$verification->getRequest()) {
            $verification->setRequest($req);
        }

        if ($replace || !$verification->getResponse()) {
            $verification->setResponse($res);
        }

        if ($res->getBody()->isSeekable()) {
            $res->getBody()->rewind();
        }
    }

    /**
     * Creates a verification object from a variety of sources
     *
     * @throws \RuntimeException
     * @return Verification
     */
    protected function createVerification($verification)
    {
        if ($verification instanceof Verification) {
            return $verification;
        }

        if (is_array($verification)) {
            return $this->createVerificationFromArray($verification);
        }

        if (\is_string($verification)) {
            return new Verification($verification);
        }

        throw new \RuntimeException('Unable to create Verification object from source data');
    }

    /**
     * @param $array
     * @return Verification
     */
    protected function createVerificationFromArray($array)
    {
        if (!is_array($array)) {
            throw new \RuntimeException('verification must implement `' . VerificationInterface::class . '` or be an array`');
        }

        foreach (['number', 'brand'] as $param) {
            if (!isset($array[$param])) {
                throw new \InvalidArgumentException('missing expected key `' . $param . '`');
            }
        }

        $number = $array['number'];
        $brand  = $array['brand'];

        unset($array['number']);
        unset($array['brand']);

        return new Verification($number, $brand, $array);
    }
}
