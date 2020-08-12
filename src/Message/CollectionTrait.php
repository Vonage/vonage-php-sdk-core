<?php
namespace Vonage\Message;

trait CollectionTrait
{
    protected $index = null;

    public function setIndex($index)
    {
        $this->index = (int) $index;
    }
}
