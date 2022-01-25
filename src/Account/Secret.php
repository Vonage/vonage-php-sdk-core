<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace Vonage\Account;

use ArrayAccess;
use Vonage\Client\Exception\Exception as ClientException;
use Vonage\InvalidResponseException;

use function get_class;
use function trigger_error;

/**
 * @deprecated Use the Vonage\Secrets\Secret object instead
 */
class Secret
{
    protected $data;

    /**
     * @throws InvalidResponseException
     */
    public function __construct(array $data)
    {
        if (!isset($data['id'])) {
            throw new InvalidResponseException("Missing key: 'id'");
        }

        if (!isset($data['created_at'])) {
            throw new InvalidResponseException("Missing key: 'created_at'");
        }

        $this->data = $data;
    }

    public function getId()
    {
        return $this->data['id'];
    }

    public function getCreatedAt()
    {
        return $this->data['created_at'];
    }

    public function getLinks()
    {
        return $this->data['_links'];
    }

    /**
     * @throws InvalidResponseException
     *
     * @deprecated Instantiate the object directly
     */
    public static function fromApi(array $data): self
    {
        trigger_error('Please instantiate a Vonage\Account\Secret object instead of using fromApi', E_USER_DEPRECATED);

        return new self($data);
    }
}
