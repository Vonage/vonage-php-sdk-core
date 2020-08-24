<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Vonage, Inc. (http://vonage.com)
 * @license   https://github.com/vonage/vonage-php/blob/master/LICENSE MIT License
 */

namespace Vonage\Verify;

interface VerificationInterface extends \Vonage\Entity\EntityInterface
{
    public function getNumber();
    public function setCountry($country);
    public function setSenderId($id);
    public function setCodeLength($length);
    public function setLanguage($language);
    public function setRequireType($type);
    public function setPinExpiry($time);
    public function setWaitTime($time);
    public function setWorkflowId($workflow_id);
}
