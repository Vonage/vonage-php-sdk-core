<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */
declare(strict_types=1);

namespace Vonage\Message;

/**
 * SMS Text Message
 */
class AutoDetect extends Message
{
    public const TYPE = 'text';

    /**
     * Message Body
     * @var string
     */
    protected $text;

    /**
     * Create a new SMS text message.
     *
     * @param $to
     * @param $from
     * @param $text
     * @param array $additional
     */
    public function __construct($to, $from, $text, $additional = [])
    {
        parent::__construct($to, $from, $additional);

        $this->enableEncodingDetection();
        $this->requestData['text'] = (string)$text;
    }
}
