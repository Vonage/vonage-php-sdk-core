<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace Vonage\SMS\Message;

class WAPPush extends OutboundMessage
{
    /**
     * @var string
     */
    protected $title;

    /**
     * @var string
     */
    protected $type = 'wappush';

    /**
     * @var string
     */
    protected $url;

    /**
     * @var int
     */
    protected $validity;

    /**
     * WAPPush constructor.
     *
     * @param string $to
     * @param string $from
     * @param string $title
     * @param string $url
     * @param int $validity
     */
    public function __construct(string $to, string $from, string $title, string $url, int $validity)
    {
        parent::__construct($to, $from);

        $this->title = $title;
        $this->url = $url;
        $this->validity = $validity;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $data = [
            'title' => $this->getTitle(),
            'url' => $this->getUrl(),
            'validity' => $this->getValidity(),
        ];

        $data = $this->appendUniversalOptions($data);

        return $data;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @return int
     */
    public function getValidity(): int
    {
        return $this->validity;
    }
}
