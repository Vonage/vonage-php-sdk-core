<?php
namespace Nexmo\Credentials;
use Nexmo\CredentialsInterface;

class OAuth implements CredentialsInterface
{
    protected $credentials;
    
    /**
     * Create a credential set with OAuth credentials.
     * 
     * @param string $appToken
     * @param string $appSecret
     * @param string $clientToken
     * @param string $clientSecret
     */
    public function __construct($appToken, $appSecret, $clientToken, $clientSecret)
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