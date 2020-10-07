<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license   MIT <https://github.com/vonage/vonage-php/blob/master/LICENSE>
 */
declare(strict_types=1);

namespace Vonage\Client;

use InvalidArgumentException;
use Vonage\Client\Exception\Exception;

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
     * @param $signatureMethod
     * @throws Exception
     */
    public function __construct(array $params, $secret, $signatureMethod)
    {
        $this->params = $params;
        $this->signed = $params;

        if (!isset($this->signed['timestamp'])) {
            $this->signed['timestamp'] = time();
        }

        //remove signature if present
        unset($this->signed['sig']);

        //sort params
        ksort($this->signed);

        $signed = [];

        foreach ($this->signed as $key => $value) {
            $signed[$key] = str_replace(["&", "="], "_", $value);
        }

        //create base string
        $base = '&' . urldecode(http_build_query($signed));

        $this->signed['sig'] = $this->sign($signatureMethod, $base, $secret);
    }

    /**
     * @param $signatureMethod
     * @param $data
     * @param $secret
     * @return string
     * @throws Exception
     */
    protected function sign($signatureMethod, $data, $secret): string
    {
        switch ($signatureMethod) {
            case 'md5hash':
                // md5hash needs the secret appended
                $data .= $secret;

                return md5($data);
            case 'md5':
            case 'sha1':
            case 'sha256':
            case 'sha512':
                return strtoupper(hash_hmac($signatureMethod, $data, $secret));
            default:
                throw new Exception(
                    'Unknown signature algorithm: ' . $signatureMethod .
                    '. Expected: md5hash, md5, sha1, sha256, or sha512'
                );
        }
    }

    /**
     * Get the original parameters.
     *
     * @return array
     */
    public function getParams(): array
    {
        return $this->params;
    }

    /**
     * Get the signature for the parameters.
     *
     * @return string
     */
    public function getSignature(): string
    {
        return $this->signed['sig'];
    }

    /**
     * Get a full set of parameters including the signature and timestamp.
     *
     * @return array
     */
    public function getSignedParams(): array
    {
        return $this->signed;
    }

    /**
     * Check that a signature (or set of parameters) is valid.
     *
     * First instantiate a Signature object: this will drop any supplied
     * signature parameter and calculate the correct one. Then call this
     * method and supply the signature that came in with the request.
     *
     * @param array| string $signature The incoming sig parameter to check
     *      (or all incoming params)
     * @return bool
     * @throws InvalidArgumentException
     */
    public function check($signature): bool
    {
        if (is_array($signature) && isset($signature['sig'])) {
            $signature = $signature['sig'];
        }

        if (!is_string($signature)) {
            throw new InvalidArgumentException('signature must be string, or present in array or parameters');
        }

        return strtolower($signature) === strtolower($this->signed['sig']);
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
