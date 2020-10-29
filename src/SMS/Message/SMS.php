<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace Vonage\SMS\Message;

use Vonage\SMS\EncodingDetector;

use function function_exists;

class SMS extends OutboundMessage
{
    /**
     * @var string
     */
    protected $message;

    /**
     * @var string
     */
    protected $type = 'text';

    public function __construct(string $to, string $from, string $message)
    {
        parent::__construct($to, $from);
        $encoder = new EncodingDetector();
        if (function_exists('mb_convert_encoding') && $encoder->requiresUnicodeEncoding($message)) {
            $this->type = 'unicode';
        }

        $this->message = $message;
    }

    /**
     * @return mixed
     */
    public function toArray(): array
    {
        $data = ['text' => $this->getMessage()];
        $data = $this->appendUniversalOptions($data);

        return $data;
    }

    public function getMessage(): string
    {
        return $this->message;
    }
}
