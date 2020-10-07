<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license   MIT <https://github.com/vonage/vonage-php/blob/master/LICENSE>
 */
declare(strict_types=1);

namespace Vonage\Entity;

use RuntimeException;
use Vonage\Client\Exception\Exception;
use Vonage\Message\Message;

/**
 * Implements getRequestData from EntityInterface with a simple array. Request data stored in an array, and locked once
 * a request object has been set.
 *
 * @deprecated This information will be available at API client level as opposed to the model level
 *
 * @see EntityInterface::getRequestData()
 */
trait RequestArrayTrait
{
    /**
     * @var array
     */
    protected $requestData = [];

    /**
     * Get an array of params to use in an API request.
     *
     * @param bool $sent
     * @return array
     * @throws Exception
     */
    public function getRequestData($sent = true): array
    {
        if (!($this instanceof EntityInterface)) {
            throw new Exception(sprintf(
                '%s can only be used if the class implements %s',
                __TRAIT__,
                EntityInterface::class
            ));
        }

        if ($sent && ($request = $this->getRequest())) {
            $query = [];
            parse_str($request->getUri()->getQuery(), $query);
            return $query;
        }

        // Trigger a pre-getRequestData() hook for any last minute
        // decision making that needs to be done, but only if
        // it hasn't been sent already
        if (method_exists($this, 'preGetRequestDataHook')) {
            $this->preGetRequestDataHook();
        }

        return $this->requestData;
    }

    /**
     * @param $name
     * @param $value
     * @return Message|$this
     * @throws \Exception
     */
    protected function setRequestData($name, $value)
    {
        if (!($this instanceof EntityInterface)) {
            throw new RuntimeException(sprintf(
                '%s can only be used if the class implements %s',
                __TRAIT__,
                EntityInterface::class
            ));
        }

        if (@$this->getResponse()) {
            throw new RuntimeException(sprintf(
                'can not set request parameter `%s` for `%s` after API request has be made',
                $name,
                get_class($this)
            ));
        }

        $this->requestData[$name] = $value;

        return $this;
    }
}
