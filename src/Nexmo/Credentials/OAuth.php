<?php
namespace Nexmo\Credentials;
use Nexmo\CredentialsInterface;

class OAuth implements CredentialsInterface
{
    protected $credentials;

    /**
     * Create a credential set with OAuth credentials.
     *
     * @param string $consumerToken
     * @param string $consumerSecret
     * @param string $token
     * @param string $secret
    */
    public function __construct($consumerToken, $consumerSecret, $token, $secret)
    {
        //using keys that match guzzle 
        $this->credentials = array_combine(array('consumer_key', 'consumer_secret', 'token', 'token_secret'), func_get_args());
    }
    
    /**
     * @see Nexmo\CredentialsInterface::getCredentials()
     */
    public function getCredentials() 
    {
        return $this->credentials;
    }
}