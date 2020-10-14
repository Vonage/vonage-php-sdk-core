<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */
declare(strict_types=1);

namespace Vonage\Message;

use Vonage\Client\Exception\Exception;

/**
 * SMS Text Message
 */
class Unicode extends Message
{
    public const TYPE = 'unicode';

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
     */
    public function __construct($to, $from, $text)
    {
        parent::__construct($to, $from);

        $this->text = (string)$text;
    }

    /**
     * Get an array of params to use in an API request.
     *
     * @param bool $sent
     * @return array
     * @throws Exception
     */
    public function getRequestData($sent = true): array
    {
        return array_merge(parent::getRequestData($sent), [
            'text' => $this->text
        ]);
    }
}
