<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2022 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace Vonage\SMS\Message;

class WAPPush extends OutboundMessage
{
    /**
     * @var string
     */
    protected string $type = 'wappush';

    public function __construct(string $to, string $from, protected string $title, protected string $url, protected int $validity)
    {
        parent::__construct($to, $from);
    }

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

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getValidity(): int
    {
        return $this->validity;
    }
}
