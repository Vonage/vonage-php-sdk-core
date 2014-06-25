<?php
/**
 * @author Tim Lytle <tim@timlytle.net>
 */

namespace Nexmo\Client\Request;


abstract class AbstractRequest implements RequestInterface
{
    protected $params = array();

    /**
     * @return array
     */
    public function getParams()
    {
        return array_filter($this->params, 'is_scalar');
    }
} 