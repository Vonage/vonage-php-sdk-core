<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license   MIT <https://github.com/vonage/vonage-php/blob/master/LICENSE>
 */
declare(strict_types=1);

namespace Vonage\Message;

/**
 * SMS Text Message
 */
class Text extends Message
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

        $this->requestData['text'] = (string)$text;
    }
}
