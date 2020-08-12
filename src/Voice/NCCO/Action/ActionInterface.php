<?php
declare(strict_types=1);

namespace Vonage\Voice\NCCO\Action;

interface ActionInterface extends \JsonSerializable
{
    /**
     * @return array<string, string>
     */
    public function toNCCOArray() : array;
}
