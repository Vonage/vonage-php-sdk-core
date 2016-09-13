<?php
/**
 * Nexmo Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Nexmo, Inc. (http://nexmo.com)
 * @license   https://github.com/Nexmo/nexmo-php/blob/master/LICENSE.txt MIT License
 */

namespace Nexmo\Client\Credentials;

use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Key;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Nexmo\Application\Application;

class Keypair extends AbstractCredentials  implements CredentialsInterface
{

    protected $key;

    protected $signer;

    public function __construct($privateKey, $application = null)
    {
        $this->credentials['key'] = $privateKey;
        if($application){
            if($application instanceof Application){
                $application = $application->getId();
            }

            $this->credentials['application'] = $application;
        }

        $this->key = new Key($privateKey);
        $this->signer = new Sha256();
    }

    public function getJwt($exp = null, $nfb = null, $jti = null, $iat = null)
    {
        if(is_null($exp)){
            $exp = time() + 60;
        }

        if(is_null($iat)){
            $iat = time();
        }

        if(is_null($jti)){
            $jti = base64_encode(mt_rand());
        }

        $builder = new Builder();
        $builder->setIssuedAt($iat)
                ->setExpiration($exp);


        if(isset($this->credentials['application'])){
            $builder->set('application_id', $this->credentials['application']);
        }

        if(!is_null($nfb)){
            $builder->setNotBefore($nfb);
        }

        if(!is_null($jti)){
            $builder->setId($jti);
        }

        if(isset($this->credentials['application'])){
            $builder->set('application_id', $this->credentials['application']);
        }

        return $builder->sign($this->signer, $this->key)->getToken();
    }
}