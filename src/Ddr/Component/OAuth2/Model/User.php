<?php

namespace Ddr\Component\OAuth2\Model;

use Symfony\Component\Security\Core\User\UserInterface;

class User implements UserInterface
{
    protected $email;

    public function __construct($email)
    {
        $this->email = $email;
    }

    public function getRoles()
    {
        return ['ROLE_USER'];
    }

    public function getPassword()
    {
        return null;
    }

    public function getSalt()
    {
        return null;
    }

    public function getUsername()
    {
        return $this->email;
    }

    public function eraseCredentials()
    {
        return $this->getRoles();
    }

}
