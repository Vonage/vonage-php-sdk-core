<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Vonage, Inc. (http://vonage.com)
 * @license   https://github.com/vonage/vonage-php/blob/master/LICENSE MIT License
 */

namespace Vonage\Numbers;

use Vonage\Application\Application;
use Vonage\Entity\EntityInterface;
use Vonage\Entity\Hydrator\ArrayHydrateInterface;
use Vonage\Entity\JsonResponseTrait;
use Vonage\Entity\JsonSerializableInterface;
use Vonage\Entity\JsonSerializableTrait;
use Vonage\Entity\JsonUnserializableInterface;
use Vonage\Entity\NoRequestResponseTrait;

class Number implements EntityInterface, JsonSerializableInterface, JsonUnserializableInterface, ArrayHydrateInterface
{
    use JsonSerializableTrait;
    use NoRequestResponseTrait;
    use JsonResponseTrait;

    const TYPE_MOBILE = 'mobile-lvn';
    const TYPE_FIXED  = 'landline';

    const FEATURE_VOICE = 'VOICE';
    const FEATURE_SMS   = 'SMS';
    const FEATURE_MMS   = 'MMS';
    const FEATURE_SMS_VOICE   = 'SMS,VOICE';
    const FEATURE_SMS_MMS   = 'SMS,MMS';
    const FEATURE_VOICE_MMS   = 'VOICE,MMS';
    const FEATURE_ALL   = 'SMS,MMS,VOICE';

    const WEBHOOK_MESSAGE      = 'moHttpUrl';
    const WEBHOOK_VOICE_STATUS = 'voiceStatusCallbackUrl';

    const ENDPOINT_SIP  = 'sip';
    const ENDPOINT_TEL  = 'tel';
    const ENDPOINT_VXML = 'vxml';
    const ENDPOINT_APP  = 'app';

    protected $data = [];

    public function __construct($number = null, $country = null)
    {
        $this->data['msisdn'] = $number;
        $this->data['country'] = $country;
    }

    public function getId()
    {
        return $this->fromData('msisdn');
    }

    public function getMsisdn()
    {
        return $this->getId();
    }

    public function getNumber()
    {
        return $this->getId();
    }

    public function getCountry()
    {
        return $this->fromData('country');
    }

    public function getType()
    {
        return $this->fromData('type');
    }

    public function getCost()
    {
        return $this->fromData('cost');
    }

    public function hasFeature($feature)
    {
        if (!isset($this->data['features'])) {
            return false;
        }

        return in_array($feature, $this->data['features']);
    }

    public function getFeatures()
    {
        return $this->fromData('features');
    }

    public function setWebhook($type, $url)
    {
        if (!in_array($type, [self::WEBHOOK_MESSAGE, self::WEBHOOK_VOICE_STATUS])) {
            throw new \InvalidArgumentException("invalid webhook type `$type`");
        }

        $this->data[$type] = $url;
        return $this;
    }

    public function getWebhook($type)
    {
        return $this->fromData($type);
    }

    public function hasWebhook($type)
    {
        return isset($this->data[$type]);
    }

    public function setVoiceDestination($endpoint, $type = null)
    {
        if (is_null($type)) {
            $type = $this->autoType($endpoint);
        }

        if (self::ENDPOINT_APP == $type and !($endpoint instanceof Application)) {
            $endpoint = new Application($endpoint);
        }

        $this->data['voiceCallbackValue'] = $endpoint;
        $this->data['voiceCallbackType'] = $type;

        return $this;
    }

    protected function autoType($endpoint)
    {
        if ($endpoint instanceof Application) {
            return self::ENDPOINT_APP;
        }

        if (false !== strpos($endpoint, '@')) {
            return self::ENDPOINT_SIP;
        }

        if (0 === strpos(strtolower($endpoint), 'http')) {
            return self::ENDPOINT_VXML;
        }

        if (preg_match('#[a-z]+#', $endpoint)) {
            return self::ENDPOINT_APP;
        }

        return self::ENDPOINT_TEL;
    }

    public function getVoiceDestination()
    {
        return $this->fromData('voiceCallbackValue');
    }

    public function getVoiceType()
    {
        if (!isset($this->data['voiceCallbackType'])) {
            return null;
        }

        return $this->data['voiceCallbackType'];
    }

    protected function fromData($name)
    {
        if (!isset($this->data[$name])) {
            throw new \RuntimeException("`{$name}` has not been set");
        }

        return $this->data[$name];
    }

    /**
     * @todo Either make this take JSON, or rename this to `fromArray`
     */
    public function jsonUnserialize(array $json)
    {
        trigger_error(
            get_class($this) . "::jsonUnserialize is deprecated, please fromArray() instead",
            E_USER_DEPRECATED
        );

        $this->fromArray($json);
    }

    public function fromArray(array $data)
    {
        $this->data = $data;
    }

    public function jsonSerialize()
    {
        return $this->toArray();
    }

    public function toArray() : array
    {
        $json = $this->data;

        // Swap to using app_id instead
        if (isset($json['messagesCallbackType'])) {
            $json['app_id'] = $json['messagesCallbackValue'];
            unset($json['messagesCallbackValue'], $json['messagesCallbackType']);
        }

        if (isset($json['voiceCallbackValue']) and ($json['voiceCallbackValue'] instanceof Application)) {
            $json['app_id'] = $json['voiceCallbackValue']->getId();
            unset($json['voiceCallbackValue'], $json['voiceCallbackType']);
        }

        if (isset($json['voiceCallbackValue']) and $json['voiceCallbackType'] === 'app') {
            $json['app_id'] = $json['voiceCallbackValue'];
            unset($json['voiceCallbackValue'], $json['voiceCallbackType']);
        }

        return $json;
    }

    public function __toString()
    {
        return (string) $this->getId();
    }

    public function setAppId(string $appId) : self
    {
        $this->data['messagesCallbackType'] = self::ENDPOINT_APP;
        $this->data['messagesCallbackValue'] = $appId;

        $this->data['voiceCallbackType'] = self::ENDPOINT_APP;
        $this->data['voiceCallbackValue'] = $appId;

        return $this;
    }

    public function getAppId() : ?string
    {
        // These should never be different, but might not both be set
        return $this->data['voiceCallbackValue'] ?? $this->data['messagesCallbackValue'];
    }
}
