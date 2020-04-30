<?php
/**
 * Nexmo Client Library for PHP
 *
 * @copyright Copyright (c) 2019 Nexmo, Inc. (http://nexmo.com)
 * @license   https://github.com/Nexmo/nexmo-php/blob/master/LICENSE.txt MIT License
 */

namespace Nexmo\Application;

class MessagesConfig implements WebhookConfigInterface
{
    use WebhookConfigTrait;

    const INBOUND  = 'inbound_url';
    const STATUS = 'status_url';
}
