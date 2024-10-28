<?php

declare(strict_types=1);

namespace Vonage\Application;

use Exception;
use JsonSerializable;
use StdClass;
use Vonage\Entity\EntityInterface;
use Vonage\Entity\Hydrator\ArrayHydrateInterface;
use Vonage\Entity\JsonResponseTrait;
use Vonage\Entity\JsonSerializableTrait;
use Vonage\Entity\Psr7Trait;

use function count;
use function ucfirst;

class Application implements EntityInterface, JsonSerializable, ArrayHydrateInterface, \Stringable
{
    use JsonSerializableTrait;
    use Psr7Trait;
    use JsonResponseTrait;

    protected VoiceConfig $voiceConfig;

    protected MessagesConfig $messagesConfig;

    protected RtcConfig $rtcConfig;

    protected VbcConfig $vbcConfig;

    protected ?string $name = null;

    protected array $keys = [];

    public function __construct(protected ?string $id = null)
    {
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setVoiceConfig(VoiceConfig $config): self
    {
        $this->voiceConfig = $config;

        return $this;
    }

    public function setMessagesConfig(MessagesConfig $config): self
    {
        $this->messagesConfig = $config;
        return $this;
    }

    public function setRtcConfig(RtcConfig $config): self
    {
        $this->rtcConfig = $config;

        return $this;
    }

    public function setVbcConfig(VbcConfig $config): self
    {
        $this->vbcConfig = $config;

        return $this;
    }

    /**
     * @throws Exception
     */
    public function getVoiceConfig(): VoiceConfig
    {
        if (!isset($this->voiceConfig)) {
            $this->setVoiceConfig(new VoiceConfig());
            $data = $this->getResponseData();

            if (isset($data['voice']['webhooks'])) {
                foreach ($data['voice']['webhooks'] as $webhook) {
                    $this->voiceConfig->setWebhook(
                        $webhook['endpoint_type'],
                        $webhook['endpoint'],
                        $webhook['http_method']
                    );
                }
            }
        }

        return $this->voiceConfig;
    }

    /**
     * @throws Exception
     */
    public function getMessagesConfig(): MessagesConfig
    {
        if (!isset($this->messagesConfig)) {
            $this->setMessagesConfig(new MessagesConfig());
            $data = $this->getResponseData();

            if (isset($data['messages']['webhooks'])) {
                foreach ($data['messages']['webhooks'] as $webhook) {
                    $this->getMessagesConfig()->setWebhook(
                        $webhook['endpoint_type'],
                        $webhook['endpoint'],
                        $webhook['http_method']
                    );
                }
            }
        }

        return $this->messagesConfig;
    }

    /**
     * @throws Exception
     */
    public function getRtcConfig(): RtcConfig
    {
        if (!isset($this->rtcConfig)) {
            $this->setRtcConfig(new RtcConfig());
            $data = $this->getResponseData();

            if (isset($data['rtc']['webhooks'])) {
                foreach ($data['rtc']['webhooks'] as $webhook) {
                    $this->getRtcConfig()->setWebhook(
                        $webhook['endpoint_type'],
                        $webhook['endpoint'],
                        $webhook['http_method']
                    );
                }
            }
        }

        return $this->rtcConfig;
    }

    public function getVbcConfig(): VbcConfig
    {
        if (!isset($this->vbcConfig)) {
            $this->setVbcConfig(new VbcConfig());
        }

        return $this->vbcConfig;
    }

    public function setPublicKey(?string $key): self
    {
        $this->keys['public_key'] = $key;

        return $this;
    }

    public function getPublicKey(): ?string
    {
        return $this->keys['public_key'] ?? null;
    }

    public function getPrivateKey(): ?string
    {
        return $this->keys['private_key'] ?? null;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    public function __toString(): string
    {
        return (string)$this->getId();
    }

    public function fromArray(array $data): void
    {
        $this->name = $data['name'];
        $this->id = $data['id'] ?? null;
        $this->keys = $data['keys'] ?? [];

        if (isset($data['capabilities'])) {
            $capabilities = $data['capabilities'];

            //todo: make voice  hydrate-able
            $this->voiceConfig = new VoiceConfig();
            if (isset($capabilities['voice']['webhooks'])) {
                foreach ($capabilities['voice']['webhooks'] as $name => $details) {
                    $this->voiceConfig->setWebhook($name, new Webhook($details['address'], $details['http_method']));
                }
            }

            //todo: make messages  hydrate-able
            $this->messagesConfig = new MessagesConfig();
            if (isset($capabilities['messages']['webhooks'])) {
                foreach ($capabilities['messages']['webhooks'] as $name => $details) {
                    $this->messagesConfig->setWebhook($name, new Webhook($details['address'], $details['http_method']));
                }
            }

            //todo: make rtc  hydrate-able
            $this->rtcConfig = new RtcConfig();
            if (isset($capabilities['rtc']['webhooks'])) {
                foreach ($capabilities['rtc']['webhooks'] as $name => $details) {
                    $this->rtcConfig->setWebhook($name, new Webhook($details['address'], $details['http_method']));
                }
            }

            if (isset($capabilities['vbc'])) {
                $this->getVbcConfig()->enable();
            }
        }
    }

    public function toArray(): array
    {
        // Build up capabilities that are set
        $availableCapabilities = [
            'voice' => [VoiceConfig::ANSWER, VoiceConfig::EVENT, VoiceConfig::FALLBACK_ANSWER_URL],
            'messages' => [MessagesConfig::INBOUND, MessagesConfig::STATUS],
            'rtc' => [RtcConfig::EVENT]
        ];

        $capabilities = [];

        foreach ($availableCapabilities as $type => $values) {
            $configAccessorMethod = 'get' . ucfirst($type) . 'Config';

            foreach ($values as $constant) {
                /** @var Webhook|\Vonage\Voice\Webhook $webhook */
                $webhook = $this->$configAccessorMethod()->getWebhook($constant);

                if ($webhook) {
                    if (!isset($capabilities[$type])) {
                        $capabilities[$type]['webhooks'] = [];
                    }

                    $capabilities[$type]['webhooks'][$constant] = [
                        'address' => $webhook->getUrl(),
                        'http_method' => $webhook->getMethod(),
                    ];

                    if (!is_null($webhook->getConnectionTimeout())) {
                        $capabilities[$type]['webhooks'][$constant]['connection_timeout'] = $webhook->getConnectionTimeout();
                    }

                    if (!is_null($webhook->getSocketTimeout())) {
                        $capabilities[$type]['webhooks'][$constant]['socket_timeout'] = $webhook->getSocketTimeout();
                    }
                }
            }
        }

        // Handle other Voice capabilities outside of that needlessly complicated webhook loop
        if (!is_null($this->getVoiceConfig()->getRegion())) {
            $capabilities['voice']['region'] = $this->getVoiceConfig()->getRegion();
        }

        if (!is_null($this->getVoiceConfig()->getConversationsTtl())) {
            $capabilities['voice']['conversations_ttl'] = $this->getVoiceConfig()->getConversationsTtl();
        }

        if (!is_null($this->getVoiceConfig()->getSignedCallbacks())) {
            $capabilities['voice']['signed_callbacks'] = $this->getVoiceConfig()->getSignedCallbacks();
        }

        // Handle VBC specifically
        if ($this->getVbcConfig()->isEnabled()) {
            $capabilities['vbc'] = new StdClass();
        }

        // Workaround API bug. It expects an object and throws 500
        // if it gets an array
        if (!count($capabilities)) {
            $capabilities = (object)$capabilities;
        }

        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'keys' => [
                'public_key' => $this->getPublicKey()
            ],
            'capabilities' => $capabilities
        ];
    }
}
