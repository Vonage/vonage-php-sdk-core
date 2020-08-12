<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Vonage, Inc. (http://vonage.com)
 * @license   https://github.com/vonage/vonage-php/blob/master/LICENSE MIT License
 */

namespace Vonage\Verify;

use Vonage\Client\Exception\Request as RequestException;
use Vonage\Entity\Hydrator\ArrayHydrateInterface;
use Vonage\Entity\JsonResponseTrait;
use Vonage\Entity\Psr7Trait;
use Vonage\Entity\RequestArrayTrait;

class Verification implements VerificationInterface, \ArrayAccess, \Serializable, ArrayHydrateInterface
{
    use Psr7Trait;
    /**
     * @deprecated
     */
    use RequestArrayTrait;
    use JsonResponseTrait;

    /**
     * Possible verification statuses.
     */
    const FAILED = 'FAILED';
    const SUCCESSFUL = 'SUCCESSFUL';
    const EXPIRED = 'EXPIRED';
    const IN_PROGRESS = 'IN PROGRESS';

    protected $dirty = true;

    /**
     * @deprecated Use the Vonage\Verify\Client instead to interact with the API
     * @var Client;
     */
    protected $client;

    /**
     * Create a verification with a number and brand, or the `request_id` of an existing verification.
     * Note that in the future, this constructor will accept only the ID as the first parameter
     *
     * @param string $idOrNumber The number to verify, or the `request_id` of an existing verification.
     * @param null|string $brand The brand that identifies your application to the user.
     * @param array $additional Additional parameters can be set as keys / values.
     */
    public function __construct($idOrNumber, $brand = null, $additional = [])
    {
        if (is_null($brand)) {
            $this->dirty = false;
            $this->requestData['request_id'] = $idOrNumber;
        } else {
            trigger_error(
                'Using ' . get_class($this) . ' for starting a verification is deprecated, please use Vonage\Verify\Request instead',
                E_USER_DEPRECATED
            );

            $this->dirty = true;
            $this->requestData['number'] = $idOrNumber;
            $this->requestData['brand']  = $brand;
            $this->requestData = array_merge($this->requestData, $additional);
        }
    }

    /**
     * Allow Verification to have actions.
     *
     * @deprecated Use the Vonage\Verfication\Client service object directly
     * @param Client $client Verify Client
     * @return $this
     */
    public function setClient(Client $client)
    {
        trigger_error(
            'Setting a client directly on a Verification object is deprecated, please use the Vonage\Verfication\Client service object directly',
            E_USER_DEPRECATED
        );
        $this->client = $client;
        return $this;
    }

    /**
     * @deprecated Use the Vonage\Verification\Client service object directly
     * @return Client
     */
    protected function useClient()
    {
        if (isset($this->client)) {
            return $this->client;
        }

        throw new \RuntimeException('can not act on the verification directly unless a verify client has been set');
    }

    /**
     * Check if the code is correct. Unlike the method it proxies, an invalid code does not throw an exception.
     *
     * @deprecated Use Vonage\Verfication\Client::check()
     * @uses \Vonage\Verify\Client::check()
     * @param string $code Numeric code provided by the user.
     * @param null|string $ip IP address to be used for the verification.
     * @return bool Code is valid.
     * @throws RequestException
     */
    public function check($code, $ip = null)
    {
        trigger_error(
            'Vonage\Verify\Verification::check() is deprecated, use Vonage\Verfication\Client::check()',
            E_USER_DEPRECATED
        );
        try {
            $this->useClient()->check($this, $code, $ip);
            return true;
        } catch (RequestException $e) {
            if ($e->getCode() == 16 || $e->getCode() == 17) {
                return false;
            }

            throw $e;
        }
    }

    /**
     * Cancel the verification.
     *
     * @deprecated Use Vonage\Verfication\Client::cancel()
     * @uses \Vonage\Verify\Client::cancel()
     */
    public function cancel()
    {
        trigger_error(
            'Vonage\Verify\Verification::cancel() is deprecated, use Vonage\Verfication\Client::cancel()',
            E_USER_DEPRECATED
        );
        $this->useClient()->cancel($this);
    }

    /**
     * Trigger the next verification.
     *
     * @deprecated Use Vonage\Verfication\Client::trigger()
     * @uses \Vonage\Verify\Client::trigger()
     */
    public function trigger()
    {
        trigger_error(
            'Vonage\Verify\Verification::trigger() is deprecated, use Vonage\Verfication\Client::trigger()',
            E_USER_DEPRECATED
        );
        $this->useClient()->trigger($this);
    }

    /**
     * Update Verification from the API.
     *
     * @deprecated Use Vonage\Verfication\Client::get() to retrieve the object directly
     * @uses \Vonage\Verify\Client::search()
     */
    public function sync()
    {
        trigger_error(
            'Vonage\Verify\Verification::sync() is deprecated, use Vonage\Verfication\Client::search() to get a new copy of this object',
            E_USER_DEPRECATED
        );
        $this->useClient()->search($this);
    }

    /**
     * Check if the user provided data has sent to the API yet.
     *
     * @deprecated This object will not hold this information in the future
     * @return bool
     */
    public function isDirty()
    {
        trigger_error(
            'Vonage\Verify\Verification::isDirty() is deprecated',
            E_USER_DEPRECATED
        );
        return $this->dirty;
    }

    /**
     * If do not set number in international format or you are not sure if number is correctly formatted, set country
     * with the two-character country code. For example, GB, US. Verify works out the international phone number for
     * you.
     * @link https://docs.nexmo.com/verify/api-reference/api-reference#vrequest
     *
     * Can only be set before the verification is created.
     * @uses \Vonage\Entity\RequestArrayTrait::setRequestData
     *
     * @param $country
     * @return $this
     * @throws \Exception
     */
    public function setCountry($country)
    {
        return $this->setRequestData('country', $country);
    }

    /**
     * An 11 character alphanumeric string to specify the SenderID for SMS sent by Verify. Depending on the destination
     * of the phone number you are applying, restrictions may apply. By default, sender_id is VERIFY.
     * @link https://docs.nexmo.com/verify/api-reference/api-reference#vrequest
     *
     * Can only be set before the verification is created.
     * @uses \Vonage\Entity\RequestArrayTrait::setRequestData
     *
     * @param $id
     * @return $this
     * @throws \Exception
     */
    public function setSenderId($id)
    {
        return $this->setRequestData('sender_id', $id);
    }

    /**
     * The length of the PIN. Possible values are 6 or 4 characters. The default value is 4.
     * @link https://docs.nexmo.com/verify/api-reference/api-reference#vrequest
     *
     * Can only be set before the verification is created.
     * @uses \Vonage\Entity\RequestArrayTrait::setRequestData
     *
     * @param $length
     * @return $this
     * @throws \Exception
     */
    public function setCodeLength($length)
    {
        return $this->setRequestData('code_length', $length);
    }

    /**
     * By default, TTS are generated in the locale that matches number. For example, the TTS for a 33* number is sent in
     * French. Use this parameter to explicitly control the language, accent and gender used for the Verify request. The
     * default language is en-us.
     * @link https://docs.nexmo.com/verify/api-reference/api-reference#vrequest
     *
     * Can only be set before the verification is created.
     * @uses \Vonage\Entity\RequestArrayTrait::setRequestData
     *
     * @param $language
     * @return $this
     * @throws \Exception
     */
    public function setLanguage($language)
    {
        return $this->setRequestData('lg', $language);
    }

    /**
     * Restrict verification to a certain network type. Possible values are:
     * - All (Default)
     * - Mobile
     * - Landline
     *
     * Note: contact support@Vonage.com to enable this feature.
     * @link https://docs.nexmo.com/verify/api-reference/api-reference#vrequest
     *
     * Can only be set before the verification is created.
     * @uses \Vonage\Entity\RequestArrayTrait::setRequestData
     *
     * @param $type
     * @return $this
     * @throws \Exception
     */
    public function setRequireType($type)
    {
        return $this->setRequestData('require_type', $type);
    }

    /**
     * The PIN validity time from generation. This is an integer value between 30 and 3600 seconds. The default is 300
     * seconds. When specified together, pin_expiry must be an integer multiple of next_event_wait. Otherwise,
     * pin_expiry is set to next_event_wait.
     * @link https://docs.nexmo.com/verify/api-reference/api-reference#vrequest
     *
     * Can only be set before the verification is created.
     * @uses \Vonage\Entity\RequestArrayTrait::setRequestData
     *
     * @param $time
     * @return $this
     * @throws \Exception
     */
    public function setPinExpiry($time)
    {
        return $this->setRequestData('pin_expiry', $time);
    }

    /**
     * An integer value between 60 and 900 seconds inclusive that specifies the wait time between attempts to deliver
     * the PIN. Verify calculates the default value based on the average time taken by users to complete verification.
     * @link https://docs.nexmo.com/verify/api-reference/api-reference#vrequest
     *
     * Can only be set before the verification is created.
     * @uses \Vonage\Entity\RequestArrayTrait::setRequestData
     *
     * @param $time
     * @return $this
     * @throws \Exception
     */
    public function setWaitTime($time)
    {
        return $this->setRequestData('next_event_wait', $time);
    }

    /**
     * Which workflow to use, default is 1 for SMS -> TTS -> TTS
     * @link https://developer.nexmo.com/verify/guides/workflows-and-events
     *
     * Can only be set before the verification is created.
     * @uses \Vonage\Entity\RequestArrayTrait::setRequestData
     *
     * @param int $workflow_id Which workflow to use
     * @return $this
     */
    public function setWorkflowId($workflow_id)
    {
        return $this->setRequestData('workflow_id', $workflow_id);
    }

    /**
     * Get the verification request id, if available.
     *
     * @uses \Vonage\Verify\Verification::proxyArrayAccess()
     *
     * @return string|null
     */
    public function getRequestId()
    {
        return $this->proxyArrayAccess('request_id');
    }

    /**
     * Get the number verified / to be verified.
     *
     * @see \Vonage\Verify\Verification::__construct()
     * @uses \Vonage\Verify\Verification::proxyArrayAccess()
     *
     * @return string|null
     */
    public function getNumber()
    {
        return $this->proxyArrayAccess('number');
    }

    /**
     * Get the account id, if available.
     *
     * Only available after a searching for a verification.
     * @see \Vonage\Verify\Client::search();
     *
     * However still @uses \Vonage\Verify\Verification::proxyArrayAccess()
     *
     * @return string|null
     */
    public function getAccountId()
    {
        return $this->proxyArrayAccess('account_id');
    }

    /**
     * Get the sender id, if available.
     *
     * @see \Vonage\Verify\Verification::setSenderId();
     * @see \Vonage\Verify\Client::search();
     *
     * @uses \Vonage\Verify\Verification::proxyArrayAccess()
     *
     * @return string|null
     */
    public function getSenderId()
    {
        return $this->proxyArrayAccess('sender_id');
    }

    /**
     * Get the price of the verification, if available.
     *
     * Only available after a searching for a verification.
     * @see \Vonage\Verify\Client::search();
     *
     * However still @uses \Vonage\Verify\Verification::proxyArrayAccess()
     *
     * @return string|null
     */
    public function getPrice()
    {
        return $this->proxyArrayAccess('price');
    }

    /**
     * Get the currency used to price the verification, if available.
     *
     * Only available after a searching for a verification.
     * @see \Vonage\Verify\Client::search();
     *
     * However still @uses \Vonage\Verify\Verification::proxyArrayAccess()
     *
     * @return string|null
     */
    public function getCurrency()
    {
        return $this->proxyArrayAccess('currency');
    }

    /**
     * Get the status of the verification, if available.
     *
     * Only available after a searching for a verification.
     * @see \Vonage\Verify\Client::search();
     *
     * However still @uses \Vonage\Verify\Verification::proxyArrayAccess()
     *
     * @return string|null
     */
    public function getStatus()
    {
        return $this->proxyArrayAccess('status');
    }

    /**
     * Get an array of verification checks, if available. Will return an empty array if no check have been made, or if
     * the data is not available.
     *
     * Only available after a searching for a verification.
     * @see \Vonage\Verify\Client::search();
     *
     * However still @uses \Vonage\Verify\Verification::proxyArrayAccess()
     *
     * @return \Vonage\Verify\Check[]|\Vonage\Verify\Check
     */
    public function getChecks()
    {
        $checks = $this->proxyArrayAccess('checks');
        if (!$checks) {
            return [];
        }

        foreach ($checks as $i => $check) {
            $checks[$i] = new Check($check);
        }

        return $checks;
    }

    /**
     * Get the date the verification started.
     *
     * Only available after a searching for a verification.
     * @see \Vonage\Verify\Client::search();
     *
     * However still @uses \Vonage\Verify\Verification::proxyArrayAccessDate()
     *
     * @return \DateTime|null
     */
    public function getSubmitted()
    {
        return $this->proxyArrayAccessDate('date_submitted');
    }

    /**
     * Get the date the verification stopped.
     *
     * Only available after a searching for a verification.
     * @see \Vonage\Verify\Client::search();
     *
     * However still @uses \Vonage\Verify\Verification::proxyArrayAccessDate()
     *
     * @return \DateTime|null
     */
    public function getFinalized()
    {
        return $this->proxyArrayAccessDate('date_finalized');
    }

    /**
     * Get the date of the first verification event.
     *
     * Only available after a searching for a verification.
     * @see \Vonage\Verify\Client::search();
     *
     * However still @uses \Vonage\Verify\Verification::proxyArrayAccessDate()
     *
     * @return \DateTime|null
     */
    public function getFirstEvent()
    {
        return $this->proxyArrayAccessDate('first_event_date');
    }

    /**
     * Get the date of the last verification event.
     *
     * Only available after a searching for a verification.
     * @see \Vonage\Verify\Client::search();
     *
     * However still @uses \Vonage\Verify\Verification::proxyArrayAccessDate()
     *
     * @return \DateTime|null
     */
    public function getLastEvent()
    {
        return $this->proxyArrayAccessDate('last_event_date');
    }

    /**
     * Proxies `proxyArrayAccess()` and returns a DateTime if the parameter is found.
     * @uses \Vonage\Verify\Verification::proxyArrayAccess()
     *
     * @param string $param Parameter to look for.
     * @return \DateTime
     */
    protected function proxyArrayAccessDate($param)
    {
        $date = $this->proxyArrayAccess($param);
        if ($date) {
            return new \DateTime($date);
        }
    }

    /**
     * Simply proxies array access to check for a parameter in the response, request, or user provided data.
     *
     * @uses \Vonage\Verify\Verification::offsetGet();
     * @uses \Vonage\Verify\Verification::offsetExists();
     *
     * @param string $param Parameter to look for.
     * @return mixed
     */
    protected function proxyArrayAccess($param)
    {
        $value = @$this[$param];
        if (isset($value)) {
            return @$this[$param];
        }
    }

    /**
     * Allow the object to access the data from the API response, a sent API request, or the user set data that the
     * request will be created from - in that order.
     *
     * @deprecated Array access will be removed in the future
     * @param mixed $offset
     * @return bool
     * @throws \Exception
     */
    public function offsetExists($offset)
    {
        trigger_error(
            'Using Vonage\Verify\Verification as an array is deprecated',
            E_USER_DEPRECATED
        );
        $response = $this->getResponseData();
        $request  = $this->getRequestData();
        $dirty    = $this->requestData;
        return isset($response[$offset]) || isset($request[$offset]) || isset($dirty[$offset]);
    }

    /**
     * Allow the object to access the data from the API response, a sent API request, or the user set data that the
     * request will be created from - in that order.
     *
     * @deprecated Array access will be removed in the future
     * @param mixed $offset
     * @return mixed
     * @throws \Exception
     */
    public function offsetGet($offset)
    {
        trigger_error(
            'Using Vonage\Verify\Verification as an array is deprecated',
            E_USER_DEPRECATED
        );
        $response = $this->getResponseData();
        $request  = $this->getRequestData();
        $dirty    = $this->requestData;

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
     * @deprecated Array access will be removed in the future
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value)
    {
        trigger_error(
            'Using Vonage\Verify\Verification as an array is deprecated',
            E_USER_DEPRECATED
        );
        throw $this->getReadOnlyException($offset);
    }

    /**
     * All properties are read only.
     *
     * @deprecated Array access will be removed in the future
     * @param mixed $offset
     */
    public function offsetUnset($offset)
    {
        trigger_error(
            'Using Vonage\Verify\Verification as an array is deprecated',
            E_USER_DEPRECATED
        );
        throw $this->getReadOnlyException($offset);
    }

    /**
     * All properties are read only.
     *
     * @deprecated Array access will be removed in the future
     * @param string $offset
     * @return \RuntimeException
     */
    protected function getReadOnlyException(string $offset)
    {
        trigger_error(
            'Using Vonage\Verify\Verification as an array is deprecated',
            E_USER_DEPRECATED
        );
        return new \RuntimeException(sprintf(
            'can not modify `%s` using array access',
            $offset
        ));
    }

    /**
     * @todo Will need updated with the Laminas namespace
     */
    public function serialize()
    {
        $data = [
            'requestData'  => $this->requestData
        ];

        if ($request = @$this->getRequest()) {
            $data['request'] = \Zend\Diactoros\Request\Serializer::toString($request);
        }

        if ($response = @$this->getResponse()) {
            $data['response'] = \Zend\Diactoros\Response\Serializer::toString($response);
        }

        return serialize($data);
    }

    /**
     * @todo Will need updated with the Laminas namespace
     */
    public function unserialize($serialized)
    {
        $data = unserialize($serialized);

        $this->requestData = $data['requestData'];

        if (isset($data['request'])) {
            $this->request = \Zend\Diactoros\Request\Serializer::fromString($data['request']);
        }

        if (isset($data['response'])) {
            $this->response = \Zend\Diactoros\Response\Serializer::fromString($data['response']);
        }
    }

    /**
     * @return array<string, scalar>
     */
    public function toArray() : array
    {
        return $this->requestData;
    }

    /**
     * @param array<string, scalar> $data
     */
    public function fromArray(array $data) : void
    {
        $this->requestData = $data;
    }
}
