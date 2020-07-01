<?php
declare(strict_types=1);

namespace Nexmo\SMS\Message;

use Nexmo\SMS\EncodingDetector;

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

    public function toArray(): array
    {
        $data = ['text' => $this->getMessage()];

        return $this->appendUniversalOptions($data);
    }

    public function getMessage() : string
    {
        return $this->message;
    }
}
