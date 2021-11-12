<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace Vonage\Verify;

use ArrayAccess;
use DateTime;
use Exception;
use Laminas\Diactoros\Request\Serializer as RequestSerializer;
use Laminas\Diactoros\Response\Serializer as ResponseSerializer;
use RuntimeException;
use Serializable;
use Vonage\Client\Exception\Exception as ClientException;
use Vonage\Entity\Hydrator\ArrayHydrateInterface;
use Vonage\Entity\JsonResponseTrait;
use Vonage\Entity\Psr7Trait;
use Vonage\Entity\RequestArrayTrait;

use function array_merge;
use function get_class;
use function is_null;
use function serialize;
use function sprintf;
use function trigger_error;

class Verification implements VerificationInterface, ArrayAccess, Serializable, ArrayHydrateInterface
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
    public const FAILED = 'FAILED';
    public const SUCCESSFUL = 'SUCCESSFUL';
    public const EXPIRED = 'EXPIRED';
    public const IN_PROGRESS = 'IN PROGRESS';

    /**
     * Verification constructor.
     *
     * Create a verification with a number and brand, or the `request_id` of an existing verification.
     * Note that in the future, this constructor will accept only the ID as the first parameter.
     *
     * @TODO v3.0 untangle this mess when removing deprecations
     *
     * @param $idOrNumber
     * @param $brand
     * @param array $additional
     */
    public function __construct($idOrNumber, $brand = null, array $additional = [])
    {
        if (is_null($brand)) {
            $this->requestData['request_id'] = $idOrNumber;
        } else {
            trigger_error(
                'Using ' . get_class($this) . ' for starting a verification is deprecated, ' .
                'please use Vonage\Verify\Request instead',
                E_USER_DEPRECATED
            );

            $this->requestData['number'] = $idOrNumber;
            $this->requestData['brand'] = $brand;
            $this->requestData = array_merge($this->requestData, $additional);
        }
    }

    /**
     * If do not set number in international format or you are not sure if number is correctly formatted, set country
     * with the two-character country code. For example, GB, US. Verify works out the international phone number for
     * you.
     *
     * Can only be set before the verification is created.
     *
     * @link https://developer.nexmo.com/verify/overview#vrequest
     *
     * @param $country
     *
     * @throws Exception
     *
     * @return RequestArrayTrait|Verification
     */
    public function setCountry($country)
    {
        return $this->setRequestData('country', $country);
    }

    /**
     * An 11 character alphanumeric string to specify the SenderID for SMS sent by Verify. Depending on the destination
     * of the phone number you are applying, restrictions may apply. By default, sender_id is VERIFY.
     *
     * Can only be set before the verification is created.
     *
     * @link https://developer.nexmo.com/verify/overview#vrequest
     *
     * @param $id
     *
     * @throws Exception
     *
     * @return RequestArrayTrait|Verification
     */
    public function setSenderId($id)
    {
        return $this->setRequestData('sender_id', $id);
    }

    /**
     * The length of the PIN. Possible values are 6 or 4 characters. The default value is 4.
     *
     * Can only be set before the verification is created.
     *
     * @link https://developer.nexmo.com/verify/overview#vrequest
     *
     * @param $length
     *
     * @throws Exception
     *
     * @return RequestArrayTrait|Verification
     */
    public function setCodeLength($length)
    {
        return $this->setRequestData('code_length', $length);
    }

    /**
     * By default, TTS are generated in the locale that matches number. For example, the TTS for a 33* number is sent in
     * French. Use this parameter to explicitly control the language, accent and gender used for the Verify request. The
     * default language is en-us.
     *
     * Can only be set before the verification is created.
     *
     * @link https://developer.nexmo.com/verify/overview#vrequest
     *
     * @param $language
     *
     * @throws Exception
     *
     * @return RequestArrayTrait|Verification
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
     * Note: contact support@vonage.com to enable this feature.
     *
     * Can only be set before the verification is created.
     *
     * @link https://developer.nexmo.com/verify/overview#vrequest
     *
     * @param $type
     *
     * @throws Exception
     *
     * @return RequestArrayTrait|Verification
     */
    public function setRequireType($type)
    {
        return $this->setRequestData('require_type', $type);
    }

    /**
     * The PIN validity time from generation. This is an integer value between 30 and 3600 seconds. The default is 300
     * seconds. When specified together, pin_expiry must be an integer multiple of next_event_wait. Otherwise,
     * pin_expiry is set to next_event_wait.
     *
     * Can only be set before the verification is created.
     *
     * @link https://developer.nexmo.com/verify/overview#vrequest
     *
     * @param $time
     *
     * @throws Exception
     *
     * @return RequestArrayTrait|Verification
     */
    public function setPinExpiry($time)
    {
        return $this->setRequestData('pin_expiry', $time);
    }

    /**
     * An integer value between 60 and 900 seconds inclusive that specifies the wait time between attempts to deliver
     * the PIN. Verify calculates the default value based on the average time taken by users to complete verification.
     *
     * Can only be set before the verification is created.
     *
     * @link https://developer.nexmo.com/verify/overview#vrequest
     *
     * @param $time
     *
     * @throws Exception
     *
     * @return RequestArrayTrait|Verification
     */
    public function setWaitTime($time)
    {
        return $this->setRequestData('next_event_wait', $time);
    }

    /**
     * Which workflow to use, default is 1 for SMS -> TTS -> TTS
     *
     * Can only be set before the verification is created.
     *
     * @link https://developer.nexmo.com/verify/guides/workflows-and-events
     *
     * @param $workflow_id
     *
     * @throws Exception
     *
     * @return RequestArrayTrait|Verification
     */
    public function setWorkflowId($workflow_id)
    {
        return $this->setRequestData('workflow_id', $workflow_id);
    }

    /**
     * Get the verification request id, if available.
     */
    public function getRequestId()
    {
        return $this->proxyArrayAccess('request_id');
    }

    /**
     * Get the number verified / to be verified.
     *
     * @see  \Vonage\Verify\Verification::__construct()
     */
    public function getNumber()
    {
        return $this->proxyArrayAccess('number');
    }

    /**
     * Get the account id, if available.
     *
     * Only available after a searching for a verification.
     *
     * @see \Vonage\Verify\Client::search()
     */
    public function getAccountId(): ?string
    {
        return $this->proxyArrayAccess('account_id');
    }

    /**
     * Get the sender id, if available.
     *
     * @see  \Vonage\Verify\Verification::setSenderId();
     * @see  \Vonage\Verify\Client::search();
     */
    public function getSenderId()
    {
        return $this->proxyArrayAccess('sender_id');
    }

    /**
     * Get the price of the verification, if available.
     *
     * Only available after a searching for a verification.
     *
     * @see \Vonage\Verify\Client::search();
     */
    public function getPrice()
    {
        return $this->proxyArrayAccess('price');
    }

    /**
     * Get the currency used to price the verification, if available.
     *
     * Only available after a searching for a verification.
     *
     * @see \Vonage\Verify\Client::search();
     */
    public function getCurrency()
    {
        return $this->proxyArrayAccess('currency');
    }

    /**
     * Get the status of the verification, if available.
     *
     * Only available after a searching for a verification.
     *
     * @see \Vonage\Verify\Client::search();
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
     *
     * @see \Vonage\Verify\Client::search();
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
     *
     * @throws Exception
     *
     * @see \Vonage\Verify\Client::search();
     */
    public function getSubmitted()
    {
        return $this->proxyArrayAccessDate('date_submitted');
    }

    /**
     * Get the date the verification stopped.
     *
     * Only available after a searching for a verification.
     *
     * @throws Exception
     *
     * @see \Vonage\Verify\Client::search();
     */
    public function getFinalized()
    {
        return $this->proxyArrayAccessDate('date_finalized');
    }

    /**
     * Get the date of the first verification event.
     *
     * Only available after a searching for a verification.
     *
     * @throws Exception
     *
     * @see \Vonage\Verify\Client::search();
     */
    public function getFirstEvent()
    {
        return $this->proxyArrayAccessDate('first_event_date');
    }

    /**
     * Get the date of the last verification event.
     *
     * Only available after a searching for a verification.
     *
     * @throws Exception
     *
     * @see \Vonage\Verify\Client::search();
     */
    public function getLastEvent()
    {
        return $this->proxyArrayAccessDate('last_event_date');
    }

    /**
     * Proxies `proxyArrayAccess()` and returns a DateTime if the parameter is found.
     *
     * @param $param
     *
     * @throws Exception
     */
    protected function proxyArrayAccessDate($param): ?DateTime
    {
        $date = $this->proxyArrayAccess($param);

        if ($date) {
            return new DateTime($date);
        }

        return null;
    }

    /**
     * Simply proxies array access to check for a parameter in the response, request, or user provided data.
     *
     * @param $param
     *
     * @return mixed|null
     */
    protected function proxyArrayAccess($param)
    {
        $value = @$this[$param];

        if (isset($value)) {
            return @$this[$param];
        }

        return null;
    }

    /**
     * Allow the object to access the data from the API response, a sent API request, or the user set data that the
     * request will be created from - in that order.
     *
     * @throws ClientException
     * @throws Exception
     */
    public function offsetExists($offset): bool
    {
        trigger_error(
            'Using Vonage\Verify\Verification as an array is deprecated',
            E_USER_DEPRECATED
        );

        $response = $this->getResponseData();
        $request = $this->getRequestData();
        $dirty = $this->requestData;

        return isset($response[$offset]) || isset($request[$offset]) || isset($dirty[$offset]);
    }

    /**
     * Allow the object to access the data from the API response, a sent API request, or the user set data that the
     * request will be created from - in that order.
     *
     * @throws ClientException
     * @throws Exception
     *
     * @return mixed|null
     */
    public function offsetGet($offset)
    {
        trigger_error(
            'Using Vonage\Verify\Verification as an array is deprecated',
            E_USER_DEPRECATED
        );

        $response = $this->getResponseData();
        $request = $this->getRequestData();
        $dirty = $this->requestData;

        return $response[$offset] ?? $request[$offset] ?? $dirty[$offset] ?? null;
    }

    /**
     * All properties are read only.
     */
    public function offsetSet($offset, $value): void
    {
        trigger_error(
            'Using Vonage\Verify\Verification as an array is deprecated',
            E_USER_DEPRECATED
        );

        throw $this->getReadOnlyException($offset);
    }

    /**
     * All properties are read only.
     */
    public function offsetUnset($offset): void
    {
        trigger_error(
            'Using Vonage\Verify\Verification as an array is deprecated',
            E_USER_DEPRECATED
        );

        throw $this->getReadOnlyException($offset);
    }

    /**
     * All properties are read only.
     */
    protected function getReadOnlyException(string $offset): RuntimeException
    {
        trigger_error(
            'Using Vonage\Verify\Verification as an array is deprecated',
            E_USER_DEPRECATED
        );

        return new RuntimeException(
            sprintf(
                'can not modify `%s` using array access',
                $offset
            )
        );
    }

    public function serialize(): string
    {
        $data = [
            'requestData' => $this->requestData
        ];

        if ($request = @$this->getRequest()) {
            $data['request'] = RequestSerializer::toString($request);
        }

        if ($response = @$this->getResponse()) {
            $data['response'] = ResponseSerializer::toString($response);
        }

        return serialize($data);
    }

    /**
     * @param $serialized
     */
    public function unserialize($serialized): void
    {
        $data = unserialize($serialized, [true]);

        $this->requestData = $data['requestData'];

        if (isset($data['request'])) {
            $this->request = RequestSerializer::fromString($data['request']);
        }

        if (isset($data['response'])) {
            $this->response = ResponseSerializer::fromString($data['response']);
        }
    }

    /**
     * @return array<string>
     */
    public function toArray(): array
    {
        return $this->requestData;
    }

    /**
     * @param array<string> $data
     */
    public function fromArray(array $data): void
    {
        $this->requestData = $data;
    }
}
