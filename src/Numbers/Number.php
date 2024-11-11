<?php

declare(strict_types=1);

namespace Vonage\Numbers;

use InvalidArgumentException;
use RuntimeException;
use Vonage\Application\Application;
use Vonage\Entity\EntityInterface;
use Vonage\Entity\Hydrator\ArrayHydrateInterface;
use Vonage\Entity\JsonResponseTrait;
use Vonage\Entity\JsonSerializableInterface;
use Vonage\Entity\JsonSerializableTrait;
use Vonage\Entity\JsonUnserializableInterface;
use Vonage\Entity\NoRequestResponseTrait;

use function in_array;
use function is_null;
use function json_decode;
use function json_last_error;
use function preg_match;
use function stripos;
use function trigger_error;

class Number implements EntityInterface, JsonSerializableInterface, JsonUnserializableInterface, ArrayHydrateInterface, \Stringable
{
    use JsonSerializableTrait;
    use NoRequestResponseTrait;
    use JsonResponseTrait;

    public const TYPE_MOBILE = 'mobile-lvn';
    public const TYPE_FIXED = 'landline';
    public const TYPE_TOLLFREE = 'landline-toll-free';

    public const FEATURE_VOICE = 'VOICE';
    public const FEATURE_SMS = 'SMS';
    public const FEATURE_MMS = 'MMS';
    public const FEATURE_SMS_VOICE = 'SMS,VOICE';
    public const FEATURE_SMS_MMS = 'SMS,MMS';
    public const FEATURE_VOICE_MMS = 'VOICE,MMS';
    public const FEATURE_ALL = 'SMS,MMS,VOICE';

    public const WEBHOOK_MESSAGE = 'moHttpUrl';
    public const WEBHOOK_VOICE_STATUS = 'voiceStatusCallback';
    public const WEBHOOK_VOICE_STATUS_URL = 'voiceStatusCallbackUrl';

    public const ENDPOINT_SIP = 'sip';
    public const ENDPOINT_TEL = 'tel';
    public const ENDPOINT_VXML = 'vxml';
    public const ENDPOINT_APP = 'app';

    protected array $data = [];

    public function __construct($number = null, $country = null)
    {
        $this->data['msisdn'] = $number;
        $this->data['country'] = $country;
    }

    public function getId(): mixed
    {
        return $this->fromData('msisdn');
    }

    public function getMsisdn(): mixed
    {
        return $this->getId();
    }

    public function getNumber(): mixed
    {
        return $this->getId();
    }

    public function getCountry(): mixed
    {
        return $this->fromData('country');
    }

    public function getType(): mixed
    {
        return $this->fromData('type');
    }

    public function getCost(): mixed
    {
        return $this->fromData('cost');
    }

    public function hasFeature($feature): bool
    {
        if (!isset($this->data['features'])) {
            return false;
        }

        return in_array($feature, $this->data['features'], true);
    }

    public function getFeatures(): mixed
    {
        return $this->fromData('features');
    }

    public function setWebhook($type, $url): self
    {
        if (!in_array($type, [self::WEBHOOK_MESSAGE, self::WEBHOOK_VOICE_STATUS], true)) {
            throw new InvalidArgumentException("invalid webhook type `$type`");
        }

        $this->data[$type] = $url;
        return $this;
    }

    public function getWebhook($type)
    {
        return $this->fromData($type);
    }

    public function hasWebhook($type): bool
    {
        return isset($this->data[$type]);
    }

    public function setVoiceDestination($endpoint, $type = null): self
    {
        if (is_null($type)) {
            $type = $this->autoType($endpoint);
        }

        if (self::ENDPOINT_APP === $type && !($endpoint instanceof Application)) {
            $endpoint = new Application($endpoint);
        }

        $this->data['voiceCallbackValue'] = $endpoint;
        $this->data['voiceCallbackType'] = $type;

        return $this;
    }

    protected function autoType($endpoint): string
    {
        if ($endpoint instanceof Application) {
            return self::ENDPOINT_APP;
        }

        if (str_contains((string) $endpoint, '@')) {
            return self::ENDPOINT_SIP;
        }

        if (0 === stripos((string) $endpoint, 'http')) {
            return self::ENDPOINT_VXML;
        }

        if (preg_match('#[a-z]+#', (string) $endpoint)) {
            return self::ENDPOINT_APP;
        }

        return self::ENDPOINT_TEL;
    }

    public function getVoiceDestination(): mixed
    {
        return $this->fromData('voiceCallbackValue');
    }

    public function getVoiceType(): mixed
    {
        return $this->data['voiceCallbackType'] ?? null;
    }

    protected function fromData($name)
    {
        if (!isset($this->data[$name])) {
            throw new RuntimeException("`{$name}` has not been set");
        }

        return $this->data[$name];
    }

    /**
     * @param string|array $json
     */
    public function jsonUnserialize($json): void
    {
        trigger_error(
            static::class . "::jsonUnserialize is deprecated, please fromArray() instead",
            E_USER_DEPRECATED
        );

        $jsonArr = json_decode($json, true);

        if (json_last_error() === JSON_ERROR_NONE) {
            $json = $jsonArr;
        }

        $this->fromArray($json);
    }

    public function fromArray(array $data): void
    {
        $this->data = $data;
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    public function toArray(): array
    {
        $json = $this->data;

        // Swap to using app_id instead
        if (isset($json['messagesCallbackType'])) {
            $json['app_id'] = $json['messagesCallbackValue'];
            unset($json['messagesCallbackValue'], $json['messagesCallbackType']);
        }

        if (isset($json['voiceCallbackValue']) && ($json['voiceCallbackValue'] instanceof Application)) {
            $json['app_id'] = $json['voiceCallbackValue']->getId();
            unset($json['voiceCallbackValue'], $json['voiceCallbackType']);
        }

        if (isset($json['voiceCallbackValue']) && $json['voiceCallbackType'] === 'app') {
            $json['app_id'] = $json['voiceCallbackValue'];
            unset($json['voiceCallbackValue'], $json['voiceCallbackType']);
        }

        return $json;
    }

    public function __toString(): string
    {
        return (string)$this->getId();
    }

    public function setAppId(string $appId): self
    {
        $this->data['messagesCallbackType'] = self::ENDPOINT_APP;
        $this->data['messagesCallbackValue'] = $appId;

        $this->data['voiceCallbackType'] = self::ENDPOINT_APP;
        $this->data['voiceCallbackValue'] = $appId;

        return $this;
    }

    public function getAppId(): ?string
    {
        // These should never be different, but might not both be set
        return $this->data['voiceCallbackValue'] ?? $this->data['messagesCallbackValue'] ?? null;
    }
}
