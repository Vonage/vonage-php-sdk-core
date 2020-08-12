<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Vonage, Inc. (http://vonage.com)
 * @license   https://github.com/vonage/vonage-php/blob/master/LICENSE MIT License
 */

namespace Vonage\Client\Response;

class Response extends AbstractResponse implements ResponseInterface
{
    /**
     * Allow specific responses to easily define required parameters.
     * @var array
     */
    protected $expected = array();

    public function __construct(array $data)
    {
        $keys = array_keys($data);
        $missing = array_diff($this->expected, $keys);

        if ($missing) {
            throw new \RuntimeException('missing expected response keys: ' . implode(', ', $missing));
        }

        $this->data = $data;
    }
}
