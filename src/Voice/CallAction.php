<?php
declare(strict_types=1);

namespace Nexmo\Voice;

/**
 * Collection of actions that can be used to modify a call
 */
class CallAction
{
    const EARMUFF = 'earmuff';
    const HANGUP = 'hangup';
    const MUTE = 'mute';
    const UNEARMUFF = 'unearmuff';
    const UNMUTE = 'unmute';
}
