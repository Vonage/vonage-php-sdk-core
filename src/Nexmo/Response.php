<?php
namespace Nexmo;

/**
 * Wrapper for Nexmo API Response, provides access to the count and status of 
 * the messages.
 * 
 * @author Tim Lytle <tim.lytle@nexmo.com>
 */
class Response
{
    protected $data;
    
    public function __construct($data)
    {
        if(!is_array($data)){
            throw new \InvalidArgumentException('expected response data to be an array');
        }
        
        $this->data = $data;
    }
    
    public function toArray()
    {
        return $this->data;
    }
}