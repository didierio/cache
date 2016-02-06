<?php

namespace Ddr\Component\OAuth2\Storage;

use Ddr\Component\OAuth2\Model\AccessToken;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use OAuth2\IOAuth2Storage;
use OAuth2\Model\IOAuth2Client;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class GuzzleStorage implements IOAuth2Storage
{
    protected $uri;

    public function __construct($uri)
    {
        $this->uri = $uri;
    }

    public function getClient($client_id)
    {
        throw new \LogicException('Not implemented yet');
    }

    public function checkClientCredentials(IOAuth2Client $client, $client_secret = NULL)
    {
        throw new \LogicException('Not implemented yet');
    }

    public function getAccessToken($oauth_token)
    {
        $client = new Client();
        try {
            $response = $client->get(sprintf('%s/api/me', $this->uri), [
                'headers' => [
                    'Authorization' => sprintf('Bearer %s', $oauth_token),
                ]
            ]);
        } catch (ClientException $e) {
            throw new AuthenticationException('OAuth2 authentication failed', 0, $e);
        }

        $data = json_decode($response->getBody()->getContents(), true);

        return new AccessToken($oauth_token, $data);
    }

    public function createAccessToken($oauth_token, IOAuth2Client $client, $data, $expires, $scope = NULL)
    {
        throw new \LogicException('Not implemented yet');
    }

    public function checkRestrictedGrantType(IOAuth2Client $client, $grant_type)
    {
        throw new \LogicException('Not implemented yet');
    }
}
