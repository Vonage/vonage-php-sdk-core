<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace Vonage\Voice\Message;

use Vonage\Client\Request\AbstractRequest;

use function is_null;

/**
 * @deprecated This objects are no longer viable and will be removed in a future version
 */
class Message extends AbstractRequest
{
    protected $params;

    /**
     * @param $text
     * @param $to
     * @param $from
     */
    public function __construct($text, $to, $from = null)
    {
        $this->params['text'] = $text;
        $this->params['to'] = $to;
        $this->params['from'] = $from;
    }

    /**
     * @param $lang
     *
     * @return $this
     */
    public function setLanguage($lang): self
    {
        $this->params['lg'] = $lang;

        return $this;
    }

    /**
     * @param $voice
     *
     * @return $this
     */
    public function setVoice($voice): self
    {
        $this->params['voice'] = $voice;

        return $this;
    }

    /**
     * @param $count
     *
     * @return $this
     */
    public function setRepeat($count): self
    {
        $this->params['repeat'] = (int)$count;

        return $this;
    }

    /**
     * @param $url
     * @param $method
     *
     * @return $this
     */
    public function setCallback($url, $method = null): self
    {
        $this->params['callback'] = $url;

        if (!is_null($method)) {
            $this->params['callback_method'] = $method;
        } else {
            unset($this->params['callback_method']);
        }

        return $this;
    }

    /**
     * @param bool $hangup
     * @param $timeout
     *
     * @return $this
     */
    public function setMachineDetection($hangup = true, $timeout = null): self
    {
        $this->params['machine_detection'] = ($hangup ? 'hangup' : 'true');

        if (!is_null($timeout)) {
            $this->params['machine_timeout'] = (int)$timeout;
        } else {
            unset($this->params['machine_timeout']);
        }

        return $this;
    }

    public function getURI(): string
    {
        return '/tts/json';
    }
}
