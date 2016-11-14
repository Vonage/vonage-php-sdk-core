<?php
/**
 * Nexmo Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Nexmo, Inc. (http://nexmo.com)
 * @license   https://github.com/Nexmo/nexmo-php/blob/master/LICENSE.txt MIT License
 */

namespace Nexmo\Calls\Call;
use Nexmo\Entity\CollectionAwareInterface;
use Nexmo\Entity\CollectionAwareTrait;
use Nexmo\Entity\JsonSerializableInterface;

/**
 * Lightweight resource, only has put / delete.
 */
class Stream implements JsonSerializableInterface, CollectionAwareInterface
{
    use CollectionAwareTrait;

    protected $id;

    protected $data = [];

    public function __construct($id = null)
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setUrl($url)
    {
        $this->data['stream_url'] = (string) $url;
    }

    public function setLoop($times)
    {
        $this->data['loop'] = (int) $times;
    }

    public function put($stream = null)
    {
        if(!$stream){
            $stream = $this;
        }

        return $this->getCollection()->put($stream, $this->getId(), 'stream');
    }

    public function delete()
    {
        return $this->getCollection()->delete($this, 'stream');
    }

    function jsonSerialize()
    {
        return $this->data;
    }


}