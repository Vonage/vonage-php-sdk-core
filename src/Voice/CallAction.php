<?php

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
