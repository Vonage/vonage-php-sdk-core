<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2019 Vonage, Inc. (http://vonage.com)
 * @license   https://github.com/vonage/vonage-php/blob/master/LICENSE MIT License
 */

namespace Vonage\Application;

class VbcConfig
{
    protected $enabled = false;

    public function enable()
    {
        $this->enabled = true;
    }

    public function disable()
    {
        $this->enabled = false;
    }

    public function isEnabled()
    {
        return $this->enabled;
    }
}
