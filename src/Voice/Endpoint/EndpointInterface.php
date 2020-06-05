<?php
declare(strict_types=1);

namespace Nexmo\Voice\Endpoint;

interface EndpointInterface extends \JsonSerializable
{
    /**
     * @return string
     */
    public function getId() : string;

    /**
     * @return array<string, array|scalar>
     */
    public function toArray() : array;
}
