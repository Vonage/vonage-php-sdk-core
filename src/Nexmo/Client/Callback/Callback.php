<?php
/**
 * @author Tim Lytle <tim@timlytle.net>
 */

namespace Nexmo\Client\Callback;


class Callback implements CallbackInterface
{
    const ENV_ALL =  'all';
    const ENV_POST = 'post';
    const ENV_GET  = 'get';

    protected $data;


    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function getData()
    {
        return $this->data;
    }

    public static function fromEnv($source = self::ENV_ALL)
    {
        switch(strtolower($source)){
            case 'post':
                $data = $_POST;
                break;
            case 'get':
                $data = $_GET;
                break;
            case 'all':
                $data = array_merge($_GET, $_POST);
                break;
            default:
                throw new \InvalidArgumentException('invalid source: ' . $source);
        }

        return new static($data);
    }
}