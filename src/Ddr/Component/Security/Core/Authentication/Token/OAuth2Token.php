<?php

namespace Ddr\Component\Security\Core\Authentication\Token;

use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;

class OAuth2Token extends AbstractToken
{
    /**
     * {@inheritDoc}
     */
    public function getCredentials()
    {
        return array();
    }
}
