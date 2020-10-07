<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license   MIT <https://github.com/vonage/vonage-php/blob/master/LICENSE>
 */
declare(strict_types=1);

namespace Vonage\SMS\Message;

use Vonage\SMS\EncodingDetector;

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

    /**
     * SMS constructor.
     *
     * @param string $to
     * @param string $from
     * @param string $message
     */
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

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }
}
