<?php
/**
 * Nexmo Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Nexmo, Inc. (http://nexmo.com)
 * @license   https://github.com/Nexmo/nexmo-php/blob/master/LICENSE.txt MIT License
 */

namespace Nexmo\Application;


use Nexmo\Entity\JsonUnserializableInterface;
use Nexmo\Entity\EntityInterface;
use Nexmo\Entity\JsonResponseTrait;
use Nexmo\Entity\JsonSerializableTrait;
use Nexmo\Entity\Psr7Trait;

class Application implements EntityInterface, \JsonSerializable, JsonUnserializableInterface
{
    use JsonSerializableTrait;
    use Psr7Trait;
    use JsonResponseTrait;

    protected $voiceConfig;
    protected $messagesConfig;
    protected $rtcConfig;

    protected $name;

    protected $keys = [];

    protected $id;

    public function __construct($id = null)
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setVoiceConfig(VoiceConfig $config)
    {
        $this->voiceConfig = $config;
        return $this;
    }

    public function setMessagesConfig(MessagesConfig $config)
    {
        $this->messagesConfig = $config;
        return $this;
    }

    public function setRtcConfig(RtcConfig $config)
    {
        $this->rtcConfig = $config;
        return $this;
    }

    /**
     * @return VoiceConfig
     */
    public function getVoiceConfig()
    {
        if(!isset($this->voiceConfig)){
            $this->setVoiceConfig(new VoiceConfig());
            $data = $this->getResponseData();
            if(isset($data['voice']) AND isset($data['voice']['webhooks'])){
                foreach($data['voice']['webhooks'] as $webhook){
                    $this->voiceConfig->setWebhook($webhook['endpoint_type'], $webhook['endpoint'], $webhook['http_method']);
                }
            }
        }

        return $this->voiceConfig;
    }

    /**
     * @return MessagesConfig
     */
    public function getMessagesConfig()
    {
        if(!isset($this->messagesConfig)){
            $this->setMessagesConfig(new MessagesConfig());
            $data = $this->getResponseData();
            if(isset($data['messages']) AND isset($data['messages']['webhooks'])){
                foreach($data['messages']['webhooks'] as $webhook){
                    $this->getMessagesConfig()->setWebhook($webhook['endpoint_type'], $webhook['endpoint'], $webhook['http_method']);
                }
            }
        }

        return $this->messagesConfig;
    }

    /**
     * @return RtcConfig
     */
    public function getRtcConfig()
    {
        if(!isset($this->rtcConfig)){
            $this->setRtcConfig(new RtcConfig());
            $data = $this->getResponseData();
            if(isset($data['rtc']) AND isset($data['rtc']['webhooks'])){
                foreach($data['rtc']['webhooks'] as $webhook){
                    $this->getRtcConfig()->setWebhook($webhook['endpoint_type'], $webhook['endpoint'], $webhook['http_method']);
                }
            }
        }

        return $this->rtcConfig;
    }

    public function getPublicKey()
    {
        if(isset($this->keys['public_key'])){
            return $this->keys['public_key'];
        }
    }

    public function getPrivateKey()
    {
        if(isset($this->keys['private_key'])){
            return $this->keys['private_key'];
        }
    }

    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    public function getName()
    {
        return $this->name;
    }

    public function jsonUnserialize(array $json)
    {
        $this->name = $json['name'];
        $this->id   = $json['id'];
        $this->keys = $json['keys'];

        //todo: make voice  hydrate-able
        $this->voiceConfig = new VoiceConfig();
        if(isset($json['voice']) AND isset($json['voice']['webhooks'])){
            foreach($json['voice']['webhooks'] as $webhook){
                $this->voiceConfig->setWebhook($webhook['endpoint_type'], new Webhook($webhook['endpoint'], $webhook['http_method']));
            }
        }

        //todo: make messages  hydrate-able
        $this->messagesConfig = new MessagesConfig();
        if(isset($json['messages']) AND isset($json['messages']['webhooks'])){
            foreach($json['messages']['webhooks'] as $webhook){
                $this->messagesConfig->setWebhook($webhook['endpoint_type'], new Webhook($webhook['endpoint'], $webhook['http_method']));
            }
        }

        //todo: make rtc  hydrate-able
        $this->messagesConfig = new MessagesConfig();
        if(isset($json['rtc']) AND isset($json['rtc']['webhooks'])){
            foreach($json['rtc']['webhooks'] as $webhook){
                $this->messagesConfig->setWebhook($webhook['endpoint_type'], new Webhook($webhook['endpoint'], $webhook['http_method']));
            }
        }
    }

    public function jsonSerialize()
    {
        return [
            'name' => $this->getName(),
            //currently, the request data does not match the response data
            'capabilities' => [
                'voice' =>
                    [
                        'webhooks' => [
                            'answer_url' => [
                                'address' => $this->getVoiceConfig()->getWebhook(VoiceConfig::ANSWER)->getUrl(),
                                'http_method' => $this->getVoiceConfig()->getWebhook(VoiceConfig::ANSWER)->getMethod(),
                            ],
                            'event_url' => [
                                'address' => $this->getVoiceConfig()->getWebhook(VoiceConfig::EVENT)->getUrl(),
                                'http_method' => $this->getVoiceConfig()->getWebhook(VoiceConfig::EVENT)->getMethod(),
                            ]
                        ]
                    ]
                ,
                'messages' => [
                    'webhooks' => [
                        'inbound_url' => [
                            'address' => $this->getMessagesConfig()->getWebhook(MessagesConfig::INBOUND)->getUrl(),
                            'http_method' => $this->getMessagesConfig()->getWebhook(MessagesConfig::INBOUND)->getMethod(),
                        ],
                        'status_url' => [
                            'address' => $this->getMessagesConfig()->getWebhook(MessagesConfig::STATUS)->getUrl(),
                            'http_method' => $this->getMessagesConfig()->getWebhook(MessagesConfig::STATUS)->getMethod(),
                        ]
                    ]
                ],
                'rtc' => [
                    'webhooks' => [
                        'event_url' => [
                            'address' => $this->getRtcConfig()->getWebhook(RtcConfig::EVENT)->getUrl(),
                            'http_method' => $this->getRtcConfig()->getWebhook(RtcConfig::EVENT)->getMethod(),
                        ],
                    ]
                ],
                'vbc' => (object) array()
            ]
            //'type' => 'voice' //currently the only type
        ];
    }

    public function __toString()
    {
        return (string) $this->getId();
    }
}