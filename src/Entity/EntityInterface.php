<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license   MIT <https://github.com/vonage/vonage-php/blob/master/LICENSE>
 */
declare(strict_types=1);

namespace Vonage\Entity;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

interface EntityInterface
{
    public function getRequest();

    public function getRequestData($sent = true);

    public function getResponse();

    public function getResponseData();

    public function setResponse(ResponseInterface $response);

    public function setRequest(RequestInterface $request);
}
