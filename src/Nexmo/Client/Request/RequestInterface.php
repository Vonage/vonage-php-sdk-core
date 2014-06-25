<?php
/**
 * @author Tim Lytle <tim@timlytle.net>
 */
namespace Nexmo\Client\Request;

interface RequestInterface
{
    /**
     * @return array
     */
    public function getParams();

    /**
     * @return string
     */
    public function getURI();
}