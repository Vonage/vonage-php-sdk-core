<?php
namespace Nexmo\Credentials;
use Nexmo\CredentialsInterface;

class Basic implements CredentialsInterface
{
    protected $credentials;
    
    /**
     * Create a credential set with an API key and secret.
     * @param string $key
     * @param string $secret
     */
    public function __construct($key, $secret)
    {
        $this->credentials['key'] = $key;
        $this->credentials['secret'] = $secret; 
    }

    /**
     * @see Nexmo\CredentialsInterface::getCredentials()
     */
    public function getCredentials() 
    {
        return $this->credentials;
    }
}