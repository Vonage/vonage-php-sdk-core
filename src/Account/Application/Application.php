<?php
/**
 * Nexmo Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Nexmo, Inc. (http://nexmo.com)
 * @license   https://github.com/Nexmo/nexmo-php/blob/master/LICENSE.txt MIT License
 */

namespace Nexmo\Account\Application;


use Nexmo\Entity\JsonResponseTrait;
use Nexmo\Entity\Psr7Trait;
use Nexmo\Entity\RequestArrayTrait;

class Application
{
    use RequestArrayTrait {
        getRequestData as _getRequestData;
    }
    use Psr7Trait;
    use JsonResponseTrait;

    protected $voiceConfig;

    public function __construct($name)
    {
        $this->requestData['name'] = $name;
        $this->requestData['type'] = 'voice';
    }

    public function getRequestData($sent = true)
    {
        if($sent AND $this->getRequest()){
            return $this->_getRequestData($sent);
        }

        $data = $this->_getRequestData($sent);
        $data['event_url']  = (string) $this->getVoiceConfig()->getWebhook(VoiceConfig::EVENT);
        $data['answer_url'] = (string) $this->getVoiceConfig()->getWebhook(VoiceConfig::ANSWER);

        return $data;
    }

    public function setVoiceConfig(VoiceConfig $config)
    {
        $this->voiceConfig = $config;
    }

    public function getVoiceConfig()
    {
        if(!isset($this->voiceConfig)){
            $this->voiceConfig = new VoiceConfig();
            $data = $this->getResponseData();
            if(isset($data['voice']) AND isset($data['voice']['webhooks'])){
                foreach($data['voice']['webhooks'] as $webhook){
                    $this->voiceConfig->setWebhook($webhook['endpoint_type'], $webhook['endpoint'], $webhook['http_method']);
                }
            }
        }

        return $this->voiceConfig;
    }

    public function getPublicKey()
    {
        $response = $this->getResponseData();
        if(isset($response['keys']) AND isset($response['keys']['public_key'])){
            return $response['keys']['public_key'];
        }
    }

    public function getPrivateKey()
    {
        $response = $this->getResponseData();
        if(isset($response['keys']) AND isset($response['keys']['private_key'])){
            return $response['keys']['private_key'];
        }
    }

    public function setName($name)
    {
        $this->requestData['name'] = $name;
    }

    public function getName()
    {
        return $this->getData('name');
    }

    protected function getData($name)
    {
        $request = $this->_getRequestData(false);
        if(isset($request[$name])){
            return $request[$name];
        }

        $response = $this->getResponseData();
        if(isset($response[$name])){
            return $response[$name];
        }
    }
}