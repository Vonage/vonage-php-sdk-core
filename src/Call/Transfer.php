<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2017 Vonage, Inc. (http://vonage.com)
 * @license   https://github.com/vonage/vonage-php/blob/master/LICENSE MIT License
 */

namespace Vonage\Call;

/**
 * @deprecated Please use Vonage\Voice\Client::transferCall() instead
 */
class Transfer implements \JsonSerializable
{
    protected $urls;

    public function __construct($urls)
    {
        trigger_error(
            'Vonage\Call\Transfer is deprecated, please use Vonage\Voice\Client::transferCall() instead',
            E_USER_DEPRECATED
        );

        if (!is_array($urls)) {
            $urls = array($urls);
        }

        $this->urls = $urls;
    }

    public function jsonSerialize()
    {
        return [
            'action' => 'transfer',
            'destination' => [
                'type' => 'ncco',
                'url' => $this->urls
            ]
        ];
    }
}
