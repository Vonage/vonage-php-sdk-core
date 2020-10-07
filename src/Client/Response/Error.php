<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license   MIT <https://github.com/vonage/vonage-php/blob/master/LICENSE>
 */
declare(strict_types=1);

namespace Vonage\Client\Response;

class Error extends Response
{
    /**
     * Error constructor.
     *
     * @param $data
     */
    public function __construct($data)
    {
        //normalize the data
        if (isset($data['error_text'])) {
            $data['error-text'] = $data['error_text'];
        }

        $this->expected = ['status', 'error-text'];

        parent::__construct($data);
    }

    /**
     * @return bool
     */
    public function isError(): bool
    {
        return true;
    }

    /**
     * @return bool
     */
    public function isSuccess(): bool
    {
        return false;
    }

    /**
     * @return mixed
     */
    public function getCode()
    {
        return $this->data['status'];
    }

    /**
     * @return mixed
     */
    public function getMessage()
    {
        return $this->data['error-text'];
    }
}
