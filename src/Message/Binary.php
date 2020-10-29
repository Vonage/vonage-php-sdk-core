<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace Vonage\Message;

use Vonage\Client\Exception\Exception as ClientException;

use function array_merge;

/**
 * SMS Binary Message
 */
class Binary extends Message
{
    public const TYPE = 'binary';

    /**
     * Message Body
     *
     * @var string
     */
    protected $body;

    /**
     * Message UDH
     *
     * @var string
     */
    protected $udh;

    public function __construct(string $to, string $from, string $body, string $udh)
    {
        parent::__construct($to, $from);

        $this->body = $body;
        $this->udh = $udh;
    }

    /**
     * Get an array of params to use in an API request.
     *
     * @throws ClientException
     */
    public function getRequestData(bool $sent = true): array
    {
        return array_merge(parent::getRequestData($sent), [
            'body' => $this->body,
            'udh' => $this->udh,
        ]);
    }
}
