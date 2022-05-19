<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace Vonage\Messages\MessageType;

interface Message
{
    public function toArray(): array;
    public function getMessageType(): string;
    public function setMessageType(string $messageType): void;
    public function getTo(): string;
    public function setTo(string $to): void;
    public function getSubType(): string;
    public function setSubType(string $subType): void;
    public function getClientRef(): string;
    public function setClientRef(string $clientRef): void;
}
