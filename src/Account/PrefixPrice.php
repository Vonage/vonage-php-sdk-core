<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license   MIT <https://github.com/vonage/vonage-php/blob/master/LICENSE>
 */
declare(strict_types=1);

namespace Vonage\Account;

use Vonage\Client\Exception\Exception;

class PrefixPrice extends Price
{
    protected $priceMethod = 'getPrefixPrice';

    /**
     * @return mixed|void
     * @throws Exception
     */
    public function getCurrency()
    {
        throw new Exception('Currency is unavailable from this endpoint');
    }
}
