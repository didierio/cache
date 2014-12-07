<?php

namespace Ddr\Component\OAuth2\Model;

use OAuth2\Model\IOAuth2Token;

class AccessToken implements IOAuth2Token
{
    protected $id;
    protected $data;
    protected $user;

    public function __construct($id, array $data = array())
    {
        $this->id = $id;
        $this->data = $data;
        $this->user = new User($data['email']);
    }

    public function getClientId()
    {
        throw new \LogicException('Not implemented yet');
    }

    public function getExpiresIn()
    {
        throw new \LogicException('Not implemented yet');
    }

    public function hasExpired()
    {
        return false;
    }

    public function getToken()
    {
        return $this->id;
    }

    public function getScope()
    {
        return (isset($this->data['scopes']) && !empty($this->data['scopes']) ? $this->data['scopes'] : null);
    }

    public function getData()
    {
        return $this->data;
    }

    public function getUser()
    {
        return $this->user;
    }
}
