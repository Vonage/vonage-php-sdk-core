<?php
declare(strict_types=1);

namespace Vonage\Client;

interface APIClient
{
    public function getAPIResource(): APIResource;
}
