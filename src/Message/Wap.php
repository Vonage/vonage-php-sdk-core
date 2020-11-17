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
class Wap extends Message
{
    public const TYPE = 'wappush';

    /**
     * Message Title
     *
     * @var string
     */
    protected $title;

    /**
     * Message URL
     *
     * @var string
     */
    protected $url;

    /**
     * Message Timeout
     *
     * @var int
     */
    protected $validity;

    /**
     * Create a new SMS text message.
     */
    public function __construct(string $to, string $from, string $title, string $url, int $validity)
    {
        parent::__construct($to, $from);

        $this->title = $title;
        $this->url = $url;
        $this->validity = $validity;
    }

    /**
     * Get an array of params to use in an API request.
     *
     * @throws ClientException
     */
    public function getRequestData(bool $sent = true): array
    {
        return array_merge(parent::getRequestData($sent), [
            'title' => $this->title,
            'url' => $this->url,
            'validity' => $this->validity,
        ]);
    }
}
