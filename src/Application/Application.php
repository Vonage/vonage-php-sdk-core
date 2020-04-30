<?php
declare(strict_types=1);
/**
 * Nexmo Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Nexmo, Inc. (http://nexmo.com)
 * @license   https://github.com/Nexmo/nexmo-php/blob/master/LICENSE.txt MIT License
 */

namespace Nexmo\Application;

use Nexmo\Entity\Hydrator\ArrayHydrateInterface;

class Application implements \JsonSerializable, ArrayHydrateInterface
{

    /**
     * @var VoiceConfig
     */
    protected $voiceConfig;

    /**
     * @var MessagesConfig
     */
    protected $messagesConfig;

    /**
     * @var RtcConfig
     */
    protected $rtcConfig;

    /**
     * @var VbcConfig
     */
    protected $vbcConfig;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var array<string, string>
     */
    protected $keys = [];

    /**
     * @var string
     */
    protected $id;

    public function __construct(string $id = null)
    {
        $this->id = $id;
    }

    public function getId() : ?string
    {
        return $this->id;
    }

    public function setVoiceConfig(VoiceConfig $config) : self
    {
        $this->voiceConfig = $config;
        return $this;
    }

    public function setMessagesConfig(MessagesConfig $config) : self
    {
        $this->messagesConfig = $config;
        return $this;
    }

    public function setRtcConfig(RtcConfig $config) : self
    {
        $this->rtcConfig = $config;
        return $this;
    }

    public function setVbcConfig(VbcConfig $config) : self
    {
        $this->vbcConfig = $config;
        return $this;
    }

    /**
     * @return VoiceConfig
     */
    public function getVoiceConfig()
    {
        if (!isset($this->voiceConfig)) {
            $this->setVoiceConfig(new VoiceConfig());
        }

        return $this->voiceConfig;
    }

    /**
     * @return MessagesConfig
     */
    public function getMessagesConfig()
    {
        if (!isset($this->messagesConfig)) {
            $this->setMessagesConfig(new MessagesConfig());
        }

        return $this->messagesConfig;
    }

    /**
     * @return RtcConfig
     */
    public function getRtcConfig()
    {
        if (!isset($this->rtcConfig)) {
            $this->setRtcConfig(new RtcConfig());
        }

        return $this->rtcConfig;
    }

    /**
     * @return VbcConfig
     */
    public function getVbcConfig() : VbcConfig
    {
        if (!isset($this->vbcConfig)) {
            $this->setVbcConfig(new VbcConfig());
        }

        return $this->vbcConfig;
    }

    public function setPublicKey(string $key) : self
    {
        $this->keys['public_key'] = $key;
        return $this;
    }

    public function getPublicKey() : ?string
    {
        if (isset($this->keys['public_key'])) {
            return $this->keys['public_key'];
        }

        return null;
    }

    public function getPrivateKey() : ?string
    {
        if (isset($this->keys['private_key'])) {
            return $this->keys['private_key'];
        }

        return null;
    }

    public function setName(string $name) : self
    {
        $this->name = $name;
        return $this;
    }

    public function getName() : string
    {
        return $this->name;
    }

    /**
     * @return array<string, array|scalar>
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }

    public function __toString()
    {
        return (string) $this->getId();
    }

    /**
     * @param array<string, array|scalar> $data Data to parse for the Application
     */
    public function fromArray(array $data) : void
    {
        $this->name = $data['name'];
        $this->id   = $data['id'] ?? null;
        $this->keys = $data['keys'] ?? [];

        if (isset($data['capabilities'])) {
            $capabilities = $data['capabilities'];

            //todo: make voice  hydrate-able
            $this->voiceConfig = new VoiceConfig();
            if (isset($capabilities['voice']) and isset($capabilities['voice']['webhooks'])) {
                foreach ($capabilities['voice']['webhooks'] as $name => $details) {
                    $this->voiceConfig->setWebhook($name, new Webhook($details['address'], $details['http_method']));
                }
            }

            //todo: make messages  hydrate-able
            $this->messagesConfig = new MessagesConfig();
            if (isset($capabilities['messages']) and isset($capabilities['messages']['webhooks'])) {
                foreach ($capabilities['messages']['webhooks'] as $name => $details) {
                    $this->messagesConfig->setWebhook($name, new Webhook($details['address'], $details['http_method']));
                }
            }

            //todo: make rtc  hydrate-able
            $this->rtcConfig = new RtcConfig();
            if (isset($capabilities['rtc']) and isset($capabilities['rtc']['webhooks'])) {
                foreach ($capabilities['rtc']['webhooks'] as $name => $details) {
                    $this->rtcConfig->setWebhook($name, new Webhook($details['address'], $details['http_method']));
                }
            }

            if (isset($capabilities['vbc'])) {
                $this->getVbcConfig()->enable();
            }
        }
    }

    /**
     * @return array<string, array|scalar>
     */
    public function toArray(): array
    {
        // Build up capabilities that are set
        $availableCapabilities = [
            'voice' => [VoiceConfig::ANSWER, VoiceConfig::EVENT],
            'messages' => [MessagesConfig::INBOUND, MessagesConfig::STATUS],
            'rtc' => [RtcConfig::EVENT]
        ];

        $capabilities = [];
        foreach ($availableCapabilities as $type => $values) {
            $configAccessorMethod = 'get'.ucfirst($type).'Config';
            foreach ($values as $constant) {
                $webhook = $this->$configAccessorMethod()->getWebhook($constant);
                if ($webhook) {
                    if (!isset($capabilities[$type])) {
                        $capabilities[$type]['webhooks'] = [];
                    }
                    $capabilities[$type]['webhooks'][$constant] = [
                        'address' => $webhook->getUrl(),
                        'http_method' => $webhook->getMethod(),
                    ];
                }
            }
        }

        // Handle VBC specifically
        if ($this->getVbcConfig()->isEnabled()) {
            $capabilities['vbc'] = new \StdClass;
        }

        // Workaround API bug. It expects an object and throws 500
        // if it gets an array
        if (!count($capabilities)) {
            $capabilities = (object) $capabilities;
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
