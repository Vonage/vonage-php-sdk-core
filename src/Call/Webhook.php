<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace Vonage\Call;

use JsonSerializable;

use function is_array;
use function trigger_error;

/**
 * @deprecated Please use Vonage\Voice\Webhook instead
 */
class Webhook implements JsonSerializable
{
    /**
     * @var array
     */
    protected $urls;

    protected $method;

    protected $type;

    public function __construct($type, $urls, $method = null)
    {
        trigger_error(
            'Vonage\Call\Webhook is deprecated, please use Vonage\Voice\Webhook instead',
            E_USER_DEPRECATED
        );

        if (!is_array($urls)) {
            $urls = [$urls];
        }

        $this->urls = $urls;
        $this->type = $type;
        $this->method = $method;
    }

    public function getType()
    {
        return $this->type;
    }

    public function add($url): void
    {
        $this->urls[] = $url;
    }

    public function jsonSerialize(): array
    {
        $data = [
            $this->type . '_url' => $this->urls
        ];

        if (isset($this->method)) {
            $data[$this->type . '_method'] = $this->method;
        }

        return $data;
    }
}
