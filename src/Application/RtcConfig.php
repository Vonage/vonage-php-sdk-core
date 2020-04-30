<?php
declare(strict_types=1);
/**
 * Nexmo Client Library for PHP
 *
 * @copyright Copyright (c) 2019 Nexmo, Inc. (http://nexmo.com)
 * @license   https://github.com/Nexmo/nexmo-php/blob/master/LICENSE.txt MIT License
 */

namespace Nexmo\Application;

class RtcConfig implements WebhookConfigInterface
{
    use WebhookConfigTrait;

    const EVENT  = 'event_url';
}
