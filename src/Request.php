<?php

namespace Spoody\Intra;

class Request
{
    /**
     * @var \Zend\Http\Client
     */
    private $client;

    /**
     * @var IntraToken
     */
    private $token;

    private $api_endpoint;

    public function __construct(\Zend\Http\Client $client, string $api_endpoint, \Spoody\IntraToken $token)
    {
        $this->client = $client;
        $this->token = $token;
        $this->api_endpoint = $api_endpoint;
    }

    private function prepare_request(string $uri):\Zend\Http\Client
    {
        $client = clone $this->client;
        $client->setUri($this->api_endpoint.$uri);
        $client->setOptions([
            'maxredirects'	=> 2,
            'timeout'	=> 30,
        ]);
        $headers = $client->getRequest()->getHeaders();
        $headers->addHeaderLine('Authorization', 'Bearer '.$this->token->get_token());
        return $client;
    }

    private function send(\Zend\Http\Client $client):string
    {
        $seconds = 1;
        $response = $client->send();
        while($response->getStatusCode() === 429)
        {
            sleep($seconds);
            $seconds++;
            $response = $client->send();
        }
        return $response->getBody();
    }

    /**
     * @param string $uri URI starting with /
     * @param array $data Array of GET parameters as key=>value
     * @return string Returns the response
     */
    public function get(string $uri, array $data = []):string
    {
        $client = $this->prepare_request($uri);
        $client->setMethod('GET');
        if (!empty($data))
            $client->setParameterGet($data);
        return $this->send($client);
    }

    public function post(string $uri, array $data = []):string
    {
        $client = $this->prepare_request($uri);
        $client->setMethod('POST');
        if (!empty($data))
            $client->setParameterPost($data);
        return $this->send($client);
    }
}