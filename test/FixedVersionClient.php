<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */
declare(strict_types=1);

namespace VonageTest;

use Vonage\Client;

class FixedVersionClient extends Client
{
    /**
     * @return string
     */
    public function getVersion(): string
    {
        return '1.2.3';
    }
}
