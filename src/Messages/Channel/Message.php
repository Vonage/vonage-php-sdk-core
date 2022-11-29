<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2022 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace Vonage\Messages\Channel;

interface Message
{
    public function toArray(): array;
    public function getTo(): string;
    public function setTo(string $to): void;
    public function getFrom(): string;
    public function setFrom(string $from): void;
    public function getClientRef(): ?string;
    public function getChannel(): string;
    public function getSubType(): string;
    public function setClientRef(string $clientRef): void;

    /**
     * All message types have shared outputs required by the endpoint.
     * Child classes are required to call this before assembling their
     * own specific output
     *
     * @return array
     */
    public function getBaseMessageUniversalOutputArray(): array;
}
