<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2022 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace Vonage\Voice\Call;

use Vonage\Client\Request\AbstractRequest;

use function is_null;

/**
 * @deprecated This objects are no longer viable and will be removed in a future version
 */
class Call extends AbstractRequest
{
    /**
     * @param $url
     * @param $to
     * @param $from
     */
    public function __construct($url, $to, $from = null)
    {
        $this->params['answer_url'] = $url;
        $this->params['to'] = $to;

        if (!is_null($from)) {
            $this->params['from'] = $from;
        }
    }

    /**
     * @param $url
     * @param $method
     *
     * @return $this
     */
    public function setAnswer($url, $method = null): Call
    {
        $this->params['answer_url'] = $url;

        if (!is_null($method)) {
            $this->params['answer_method'] = $method;
        } else {
            unset($this->params['answer_method']);
        }

        return $this;
    }

    /**
     * @param $url
     * @param $method
     *
     * @return $this
     */
    public function setError($url, $method = null): Call
    {
        $this->params['error_url'] = $url;
        if (!is_null($method)) {
            $this->params['error_method'] = $method;
        } else {
            unset($this->params['error_method']);
        }

        return $this;
    }

    /**
     * @param $url
     * @param $method
     *
     * @return $this
     */
    public function setStatus($url, $method = null): Call
    {
        $this->params['status_url'] = $url;
        if (!is_null($method)) {
            $this->params['status_method'] = $method;
        } else {
            unset($this->params['status_method']);
        }

        return $this;
    }

    /**
     * @param bool $hangup
     * @param $timeout
     *
     * @return $this
     */
    public function setMachineDetection($hangup = true, $timeout = null): Call
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
        return '/call/json';
    }
}
