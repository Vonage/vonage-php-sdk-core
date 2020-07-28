<?php
/**
 * Nexmo Client Library for PHP
 *
 * @copyright Copyright (c) 2017 Nexmo, Inc. (http://nexmo.com)
 * @license   https://github.com/Nexmo/nexmo-php/blob/master/LICENSE.txt MIT License
 */

namespace Nexmo\Call;

/**
 * @deprecated Please use Nexmo\Voice\Client::transferCall() instead
 */
class Transfer implements \JsonSerializable
{
    protected $urls;

    public function __construct($urls)
    {
        trigger_error(
            'Nexmo\Call\Transfer is deprecated, please use Nexmo\Voice\Client::transferCall() instead',
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
