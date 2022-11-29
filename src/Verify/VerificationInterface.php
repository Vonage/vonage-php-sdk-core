<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2022 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace Vonage\Verify;

use Vonage\Entity\EntityInterface;

interface VerificationInterface extends EntityInterface
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
