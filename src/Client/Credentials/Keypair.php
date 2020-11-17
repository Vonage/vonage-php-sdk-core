<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace Vonage\Client\Credentials;

use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Key;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\Token;
use Vonage\Application\Application;

use function base64_encode;
use function mt_rand;
use function time;

/**
 * @property mixed application
 */
class Keypair extends AbstractCredentials
{
    /**
     * @var Key
     */
    protected $key;

    /**
     * @var Sha256
     */
    protected $signer;

    public function __construct($privateKey, $application = null)
    {
        $this->credentials['key'] = $privateKey;

        if ($application) {
            if ($application instanceof Application) {
                $application = $application->getId();
            }

            $this->credentials['application'] = $application;
        }

        $this->key = new Key($privateKey);
        $this->signer = new Sha256();
    }

    public function generateJwt(array $claims = []): Token
    {
        $exp = time() + 60;
        $iat = time();
        $jti = base64_encode((string)mt_rand());

        if (isset($claims['exp'])) {
            $exp = $claims['exp'];

            unset($claims['exp']);
        }

        if (isset($claims['iat'])) {
            $iat = $claims['iat'];

            unset($claims['iat']);
        }

        if (isset($claims['jti'])) {
            $jti = $claims['jti'];

            unset($claims['jti']);
        }

        $builder = new Builder();
        $builder->issuedAt($iat)
            ->expiresAt($exp)
            ->identifiedBy($jti);

        if (isset($claims['nbf'])) {
            $builder->canOnlyBeUsedAfter($claims['nbf']);

            unset($claims['nbf']);
        }

        if (isset($this->credentials['application'])) {
            $builder->withClaim('application_id', $this->credentials['application']);
        }

        if (!empty($claims)) {
            foreach ($claims as $claim => $value) {
                $builder->withClaim($claim, $value);
            }
        }

        return $builder->getToken($this->signer, $this->key);
    }
}
