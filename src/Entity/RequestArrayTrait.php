<?php

declare(strict_types=1);

namespace Vonage\Entity;

use Exception;
use RuntimeException;
use Vonage\Client\Exception\Exception as ClientException;

use function get_class;
use function method_exists;
use function parse_str;
use function sprintf;

/**
 * Implements getRequestData from EntityInterface with a simple array. Request data stored in an array, and locked once
 * a request object has been set.
 *
 * @deprecated This information will be available at API client level as opposed to the model level
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
     * @throws ClientException
     */
    public function getRequestData(bool $sent = true): array
    {
        if (!($this instanceof EntityInterface)) {
            throw new ClientException(
                sprintf(
                    '%s can only be used if the class implements %s',
                    __TRAIT__,
                    EntityInterface::class
                )
            );
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
     * @throws Exception
     */
    protected function setRequestData($name, $value): self
    {
        if (!($this instanceof EntityInterface)) {
            throw new RuntimeException(
                sprintf(
                    '%s can only be used if the class implements %s',
                    __TRAIT__,
                    EntityInterface::class
                )
            );
        }

        if (@$this->getResponse()) {
            throw new RuntimeException(
                sprintf(
                    'can not set request parameter `%s` for `%s` after API request has be made',
                    $name,
                    $this::class
                )
            );
        }

        $this->requestData[$name] = $value;

        return $this;
    }
}
