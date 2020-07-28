<?php
declare(strict_types=1);

namespace Nexmo\Client;

interface APIClient
{
    public function getAPIResource() : APIResource;
}
