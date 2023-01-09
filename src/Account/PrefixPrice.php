<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2022 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace Vonage\Account;

use Vonage\Client\Exception\Exception as ClientException;

class PrefixPrice extends Price
{
    protected $priceMethod = 'getPrefixPrice';

    /**
     * @throws ClientException
     */
    public function getCurrency(): ?string
    {
        throw new ClientException('Currency is unavailable from this endpoint');
    }
}
