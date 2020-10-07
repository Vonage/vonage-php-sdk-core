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
 * SMS Text Message
 */
class Vcal extends Message
{
    public const TYPE = 'vcal';

    /**
     * Message Body
     * @var string
     */
    protected $vcal;

    /**
     * Create a new SMS text message.
     *
     * @param $to
     * @param $from
     * @param $vcal
     */
    public function __construct($to, $from, $vcal)
    {
        parent::__construct($to, $from);

        $this->vcal = (string)$vcal;
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
        return array_merge(parent::getRequestData($sent), ['vcal' => $this->vcal]);
    }
}
