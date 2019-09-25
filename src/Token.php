<?php

namespace Spoody\Intra;

class Token
{

    /**
     * @var \Zend\Http\Client
     */
	private	$client;

    /**
     * @var string
     */
	private $api_endpoint;

    /**
     * @var string
     */
	private $client_id;

    /**
     * @var string
     */
	private $client_secret;

    /**
     * @var \stdClass
     */
	private $token = NULL;

	public function __construct(\Zend\Http\Client $client, string $api_endpoint, string $client_id, string $client_secret)
	{
		$this->client = $client;
		$this->api_endpoint	= $api_endpoint;
		$this->client_id = $client_id;
		$this->client_secret = $client_secret;
	}

	private function fetch_token():void
	{
		$client = $this->client;
		$client->setUri($this->api_endpoint."/oauth/token");
		$client->setMethod('POST');
		$client->setOptions([
			'maxredirects'	=> 2,
			'timeout'	=> 30,
		]);
		$client->setParameterPost([
			'grant_type'	=> 'client_credentials',
			'client_id'	=> $this->client_id,
			'client_secret'	=> $this->client_secret,
		]);
        $response = $client->send()->getBody();
		$this->token = json_decode($response);
	}

	private function is_expired():bool
    {
        if ($this->token === NULL || empty($this->token->access_token))
            return TRUE;
        return (time() >= ($this->token->created_at + $this->token->expires_in));
    }

	public function get_token():string
	{
	    if ($this->is_expired())
	        $this->fetch_token();
		return $this->token->access_token;
	}
}
