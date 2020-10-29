<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace Vonage\Voice\Webhook;

use Exception;
use InvalidArgumentException;
use Vonage\Webhook\Factory as WebhookFactory;

use function array_diff;
use function array_key_exists;
use function array_keys;
use function count;

class Factory extends WebhookFactory
{
    /**
     * @throws Exception
     *
     * @return mixed|Answer|Error|Event|Input|Notification|Record|Transfer
     */
    public static function createFromArray(array $data)
    {
        if (array_key_exists('status', $data)) {
            return new Event($data);
        }

        // Answer webhooks have no defining type other than length and keys
        if (count($data) === 4 && array_diff(array_keys($data), ['to', 'from', 'uuid', 'conversation_uuid']) === []) {
            return new Answer($data);
        }

        if (array_key_exists('type', $data) && $data['type'] === 'transfer') {
            return new Transfer($data);
        }

        if (array_key_exists('recording_url', $data)) {
            return new Record($data);
        }

        if (array_key_exists('reason', $data)) {
            return new Error($data);
        }

        if (array_key_exists('payload', $data)) {
            return new Notification($data);
        }

        if (array_key_exists('speech', $data) || array_key_exists('dtmf', $data)) {
            return new Input($data);
        }

        throw new InvalidArgumentException('Unable to detect incoming webhook type');
    }
}
