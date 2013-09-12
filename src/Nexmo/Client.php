<?php
namespace Nexmo;

/**
 * Nexmo API Client, allows access to the API from PHP.
 * @author Tim Lytle <tim.lytle@nexmo.com>
 */
class Client
{
    const URL_BASE = 'https://rest.nexmo.com';
    const URL_SMS  = '/sms/json';
    
    /**
     * API Credentials
     * @var Nexmo\CredentialsInterface
     */
    protected $credentials;
    
    /**
     * HTTP Client
     */
    protected $client;
    
    /**
     * API Endpoint
     * @var string
     */
    protected $base;
    
    /**
     * Create a new API client using the provided credentials.
     * @param Credentials\Generic $credentials
     */
    public function __construct(CredentialsInterface $credentials, $endpont = self::URL_BASE)
    {
        //make sure we know how to use the credentials
        if(!($credentials instanceof Credentials\Basic) AND !($credentials instanceof Credentials\OAuth)){
            throw new \RuntimeException('unknown credentials type: ' . get_class($credentials));
        }
        
        $this->credentials = $credentials;
        
        $this->base = $endpont;
    }
    
    /**
     * Send a message via SMS.

     * @param \Nexmo\MessageInterface $message
     * @param string $url
     * @return \Nexmo\Response
     */
    public function sendSMS(MessageInterface $message, $url = self::URL_SMS)
    {
        $request = $this->getClient()->post($this->base . $url);
        $this->authRequest($request);
        $request->addPostFields($message->getParams());
       
        $response = $request->send();
        return $this->parseResponse($response);
    }
    
    /**
     * Check a response for errors, and get return value.
     * @param \Guzzle\Http\Message\Response $response
     */
    private function parseResponse(\Guzzle\Http\Message\Response $response)
    {
        if($response->isError()){
            throw new \RuntimeException('http request error: ' . $response->getStatusCode());
        }
        
        return new Response($response->getBody(true));
    }

    /**
     * Auth a request object.
     *  
     * @todo if multiple HTTP clients are supported, this concept may not be 
     * universal, better to push this to a http client wrapper.
     */
    private function authRequest(\Guzzle\Http\Message\Request $request)
    {
        
        if($this->credentials instanceof Credentials\OAuth){
            $oauth = new \Guzzle\Plugin\Oauth\OauthPlugin(
                $this->credentials->getCredentials()
            );
            $this->getClient()->addSubscriber($oauth);
        }
        
        if($this->credentials instanceof Credentials\Basic){
            $credentials = $this->credentials->getCredentials();
            $request->getQuery()->set('api_key', $credentials['key'])
                                ->set('api_secret', $credentials['secret']);
        }
    }
    
    /**
     * Get the current HTTP client.
     * @return \Guzzle\Http\Client
     */
    public function getClient()
    {
        //lazy load client
        if(empty($this->client)){
            $this->setClient(new \Guzzle\Http\Client());
        }
        
        return $this->client;
    }
    
    /**
     * Set the HTTP Client for making requests.
     * @param \Guzzle\Http\Client $client
     * 
     * @todo Currently only supporting Guzzle, should use an interface to 
     * support multiple http clients (or allow custom clients).
     */
    public function setClient(\Guzzle\Http\Client $client)
    {
        //TODO: add version number from source
        $client->setUserAgent('NexmoSDK/0', true);
        $this->client = $client;
    }
}