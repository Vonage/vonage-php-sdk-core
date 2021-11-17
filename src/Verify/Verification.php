<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace Vonage\Verify;

use Cassandra\Date;
use DateTime;
use Exception;
use Laminas\Diactoros\Request\Serializer as RequestSerializer;
use Laminas\Diactoros\Response\Serializer as ResponseSerializer;
use Vonage\Entity\Hydrator\ArrayHydrateInterface;
use Vonage\Entity\JsonResponseTrait;
use Vonage\Entity\Psr7Trait;
use Vonage\Entity\RequestArrayTrait;

use function serialize;

class Verification implements VerificationInterface, ArrayHydrateInterface
{
    use Psr7Trait;

    /**
     * @deprecated
     */
    use RequestArrayTrait;
    use JsonResponseTrait;

    protected $requestId;

    protected $accountId;

    protected $number;

    protected $senderId;

    protected $dateSubmitted;

    protected $dateFinalized;

    protected $firstEventDate;

    protected $lastEventDate;

    protected $price;

    protected $currency;

    protected $status;

    protected $country;

    protected $checks;

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
     * Create a verification with a request ID or Request Object
     *
     * @param string|Request $idOrRequest
     */
    public function __construct($idOrRequest)
    {
        if (!$idOrRequest instanceof Request) {
            $this->requestData['request_id'] = $idOrRequest;
        } else {
            $this->requestData = $idOrRequest->toArray();
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
     * @return Verification
     */
    public function setCountry($country): Verification
    {
        $this->setRequestDataProxyKey('country', $country);

        return $this;
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
     * @return Verification
     */
    public function setSenderId($id): Verification
    {
        $this->requestData['sender_id'] = $id;

        return $this;
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
     * @return Verification
     */
    public function setCodeLength($length): Verification
    {
        $this->requestData['code_length'] = $length;

        return $this;
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
     * @return Verification
     */
    public function setLanguage($language): Verification
    {
        $this->requestData['lg'] = $language;

        return $this;
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
     * @return Verification
     */
    public function setRequireType($type): Verification
    {
        $this->requestData['require_type'] = $type;

        return $this;
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
     * @return Verification
     */
    public function setPinExpiry($time): Verification
    {
        $this->requestData['pin_expiry'] = $time;

        return $this;
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
     * @return Verification
     */
    public function setWaitTime($time): Verification
    {
        $this->requestData['next_event_wait'] = $time;

        return $this;
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
     * @return Verification
     */
    public function setWorkflowId($workflow_id): Verification
    {
        $this->requestData['workflow_id'] = $workflow_id;

        return $this;
    }

    /**
     * Get the verification request id, if available.
     */
    public function getRequestId()
    {
        return $this->requestData['request_id'];
    }

    /**
     * Get the number verified / to be verified.
     *
     * @see  \Vonage\Verify\Verification::__construct()
     */
    public function getNumber()
    {
        return $this->requestData['number'];
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
        return $this->getRequestDataProxyKey('account_id');
    }

    /**
     * Get the sender id, if available.
     *
     * @see  \Vonage\Verify\Verification::setSenderId();
     * @see  \Vonage\Verify\Client::search();
     */
    public function getSenderId()
    {
        return $this->requestData['sender_id'];
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
        return $this->requestData['price'];
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
        return $this->requestData['currency'];
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
        return $this->requestData['status'];
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
        $checks = $this->requestData['checks'];

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
    public function getSubmitted(): DateTime
    {
        return new DateTime($this->getRequestDataProxyKey('date_submitted'));
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
        return $this->requestData['date_finalized'];
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
    public function getFirstEvent(): DateTime
    {
        return new DateTime($this->getRequestDataProxyKey('first_event_date'));
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
    public function getLastEvent(): DateTime
    {
        return new DateTime($this->getRequestDataProxyKey('last_event_date'));
    }

    private function getRequestDataProxyKey(string $key)
    {
        return $this->requestData[$key];
    }

    private function setRequestDataProxyKey(string $key, $value): Verification
    {
        $this->requestData[$key] = $value;

        return $this;
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
