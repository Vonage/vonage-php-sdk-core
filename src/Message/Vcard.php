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
 * SMS Text Message
 */
class Vcard extends Message
{
    public const TYPE = 'vcard';

    /**
     * Message Body
     *
     * @var string
     */
    protected $vcard;

    /**
     * Create a new SMS text message.
     */
    public function __construct(string $to, string $from, string $vcard)
    {
        parent::__construct($to, $from);

        $this->vcard = $vcard;
    }

    /**
     * Get an array of params to use in an API request.
     *
     * @throws ClientException
     */
    public function getRequestData(bool $sent = true): array
    {
        return array_merge(parent::getRequestData($sent), ['vcard' => $this->vcard]);
    }
}
