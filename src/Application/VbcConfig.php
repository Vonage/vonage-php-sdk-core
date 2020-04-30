<?php
declare(strict_types=1);
/**
 * Nexmo Client Library for PHP
 *
 * @copyright Copyright (c) 2019 Nexmo, Inc. (http://nexmo.com)
 * @license   https://github.com/Nexmo/nexmo-php/blob/master/LICENSE.txt MIT License
 */

namespace Nexmo\Application;

class VbcConfig
{
    /**
     * @var bool
     */
    protected $enabled = false;

    public function enable() : void
    {
        $this->enabled = true;
    }

    public function disable() : void
    {
        $this->enabled = false;
    }

    public function isEnabled() : bool
    {
        return $this->enabled;
    }
}
