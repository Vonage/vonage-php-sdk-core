<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license   MIT <https://github.com/vonage/vonage-php/blob/master/LICENSE>
 */
declare(strict_types=1);

namespace Vonage\Message;

use Vonage\Client\Exception\Exception;

/**
 * SMS Binary Message
 */
class Wap extends Message
{
    public const TYPE = 'wappush';

    /**
     * Message Title
     * @var string
     */
    protected $title;

    /**
     * Message URL
     * @var string
     */
    protected $url;

    /**
     * Message Timeout
     * @var int
     */
    protected $validity;

    /**
     * Create a new SMS text message.
     *
     * @param $to
     * @param $from
     * @param $title
     * @param $url
     * @param $validity
     */
    public function __construct($to, $from, $title, $url, $validity)
    {
        parent::__construct($to, $from);

        $this->title = (string)$title;
        $this->url = (string)$url;
        $this->validity = (int)$validity;
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
            'title' => $this->title,
            'url' => $this->url,
            'validity' => $this->validity,
        ]);
    }
}
