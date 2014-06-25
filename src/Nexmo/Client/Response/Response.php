<?php
/**
 * @author Tim Lytle <tim@timlytle.net>
 */

namespace Nexmo\Client\Response;

class Response extends AbstractResponse implements ResponseInterface
{
    protected $expected = array('status');

    public function __construct(array $data)
    {
        $keys = array_keys($data);
        $missing = array_diff($this->expected, $keys);

        if($missing){
            throw new \RuntimeException('missing expected response keys: ' . implode(', ', $missing));
        }

        $this->data = $data;
    }
}