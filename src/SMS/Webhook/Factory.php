<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */
declare(strict_types=1);

namespace Vonage\SMS\Webhook;

use Exception;
use InvalidArgumentException;
use Vonage\Webhook\Factory as WebhookFactory;

class Factory extends WebhookFactory
{
    /**
     * @param array $data
     * @return mixed|DeliveryReceipt|InboundSMS
     * @throws Exception
     */
    public static function createFromArray(array $data)
    {
        if (array_key_exists('scts', $data)) {
            return new DeliveryReceipt($data);
        }

        if (count(array_intersect(array_keys($data), InboundSMS::$requiredFields))
            === count(InboundSMS::$requiredFields)
        ) {
            return new InboundSMS($data);
        }

        throw new InvalidArgumentException("Unable to determine incoming webhook type");
    }
}
