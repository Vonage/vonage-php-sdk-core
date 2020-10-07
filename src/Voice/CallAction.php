<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license   MIT <https://github.com/vonage/vonage-php/blob/master/LICENSE>
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
