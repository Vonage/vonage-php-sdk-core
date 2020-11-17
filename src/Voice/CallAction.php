<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace Vonage\Voice;

/**
 * Collection of actions that can be used to modify a call
 */
class CallAction
{
    public const EARMUFF = 'earmuff';
    public const HANGUP = 'hangup';
    public const MUTE = 'mute';
    public const UNEARMUFF = 'unearmuff';
    public const UNMUTE = 'unmute';
}
