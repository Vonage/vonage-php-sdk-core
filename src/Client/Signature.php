<?php
/**
 * Nexmo Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Nexmo, Inc. (http://nexmo.com)
 * @license   https://github.com/Nexmo/nexmo-php/blob/master/LICENSE.txt MIT License
 */

namespace Nexmo\Client;

class Signature
{
    /**
     * Params to Sign
     * @var array
     */
    protected $params;

    /**
     * Params with Signature (and timestamp if not present)
     * @var array
     */
    protected $signed;

    /**
     * Create a signature from a set of parameters.
     *
     * @param array $params
     * @param $secret
     */
    public function __construct(array $params, $secret)
    {
        $this->params = $params;
        $this->signed = $params;

        if(!isset($this->signed['timestamp'])){
            $this->signed['timestamp'] = time();
        }

        //remove signature if present
        unset($this->signed['sig']);

        //sort params
        ksort($this->signed);

        $signed = [];
        foreach ($this->signed as $key => $value) {
            $signed[$key] = str_replace(array("&", "="), "_", $value);
        }

        //create base string
        $base = '&'.urldecode(http_build_query($signed));

        //append the secret
        $base .= $secret;

        //create hash
        $this->signed['sig'] = md5($base);
    }

    /**
     * Get the original parameters.
     *
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * Get the signature for the parameters.
     *
     * @return string
     */
    public function getSignature()
    {
        return $this->signed['sig'];
    }

    /**
     * Get a full set of parameters including the signature and timestamp.
     *
     * @return array
     */
    public function getSignedParams()
    {
        return $this->signed;
    }

    /**
     * Check that a signature (or set of parameters) is valid.
     *
     * @param array| string $signature
     * @return bool
     * @throws \InvalidArgumentException
     */
    public function check($signature)
    {
        if(is_array($signature) AND isset($signature['sig'])){
            $signature = $signature['sig'];
        }

        if(!is_string($signature)){
            throw new \InvalidArgumentException('signature must be string, or present in array or parameters');
        }

        return $signature == $this->signed['sig'];
    }

    /**
     * Allow easy comparison.
     * 
     * @return string
     */
    public function __toString()
    {
        return $this->getSignature();
    }
}