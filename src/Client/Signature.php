<?php

declare(strict_types=1);

namespace Vonage\Client;

use InvalidArgumentException;
use Vonage\Client\Exception\Exception as ClientException;

use function hash_hmac;
use function http_build_query;
use function is_array;
use function is_string;
use function ksort;
use function md5;
use function str_replace;
use function strtolower;
use function strtoupper;
use function time;
use function urldecode;

class Signature implements \Stringable
{
    /**
     * Params with Signature (and timestamp if not present)
     *
     * @var array
     */
    protected $signed;

    /**
     * Create a signature from a set of parameters.
     *
     * @throws ClientException
     */
    public function __construct(/**
     * Params to Sign
     */
    protected array $params, $secret, $signatureMethod)
    {
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
            $signed[$key] = str_replace(["&", "="], "_", (string) $value);
        }

        //create base string
        $base = '&' . urldecode(http_build_query($signed));

        $this->signed['sig'] = $this->sign($signatureMethod, $base, $secret);
    }

    /**
     * @param $signatureMethod
     * @param $data
     * @param $secret
     *
     * @throws ClientException
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
                return strtoupper(hash_hmac((string) $signatureMethod, (string) $data, (string) $secret));
            default:
                throw new ClientException(
                    'Unknown signature algorithm: ' . $signatureMethod .
                    '. Expected: md5hash, md5, sha1, sha256, or sha512'
                );
        }
    }

    /**
     * Get the original parameters.
     */
    public function getParams(): array
    {
        return $this->params;
    }

    /**
     * Get the signature for the parameters.
     */
    public function getSignature(): string
    {
        return $this->signed['sig'];
    }

    /**
     * Get a full set of parameters including the signature and timestamp.
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
     * @param array|string $signature The incoming sig parameter to check (or all incoming params)
     *
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

        return strtolower($signature) === strtolower((string) $this->signed['sig']);
    }

    /**
     * Allow easy comparison.
     */
    public function __toString(): string
    {
        return $this->getSignature();
    }
}
