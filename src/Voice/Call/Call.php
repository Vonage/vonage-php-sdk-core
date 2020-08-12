<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Vonage, Inc. (http://vonage.com)
 * @license   https://github.com/vonage/vonage-php/blob/master/LICENSE MIT License
 */

namespace Vonage\Voice\Call;

use Vonage\Client\Request\AbstractRequest;
use Vonage\Client\Request\RequestInterface;

/**
 * @deprecated This objects are no longer viable and will be removed in a future version
 */
class Call extends AbstractRequest implements RequestInterface
{
    public function __construct($url, $to, $from = null)
    {
        $this->params['answer_url'] = $url;
        $this->params['to'] = $to;

        if (!is_null($from)) {
            $this->params['from'] = $from;
        }
    }

    public function setAnswer($url, $method = null)
    {
        $this->params['answer_url'] = $url;
        if (!is_null($method)) {
            $this->params['answer_method'] = $method;
        } else {
            unset($this->params['answer_method']);
        }

        return $this;
    }

    public function setError($url, $method = null)
    {
        $this->params['error_url'] = $url;
        if (!is_null($method)) {
            $this->params['error_method'] = $method;
        } else {
            unset($this->params['error_method']);
        }

        return $this;
    }

    public function setStatus($url, $method = null)
    {
        $this->params['status_url'] = $url;
        if (!is_null($method)) {
            $this->params['status_method'] = $method;
        } else {
            unset($this->params['status_method']);
        }

        return $this;
    }


    public function setMachineDetection($hangup = true, $timeout = null)
    {
        $this->params['machine_detection'] = ($hangup ? 'hangup' : 'true');
        if (!is_null($timeout)) {
            $this->params['machine_timeout'] = (int) $timeout;
        } else {
            unset($this->params['machine_timeout']);
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getURI()
    {
        return '/call/json';
    }
}
