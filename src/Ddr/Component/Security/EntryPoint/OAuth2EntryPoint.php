<?php

namespace Ddr\Component\Security\EntryPoint;

use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\HttpFoundation\Request;
use OAuth2\OAuth2AuthenticateException;
use OAuth2\OAuth2;

class OAuth2EntryPoint implements AuthenticationEntryPointInterface
{
    public function start(Request $request, AuthenticationException $authException = null)
    {
        $exception = new OAuth2AuthenticateException(
            OAuth2::HTTP_UNAUTHORIZED,
            OAuth2::TOKEN_TYPE_BEARER,
            OAuth2::DEFAULT_WWW_REALM,
            OAuth2::ERROR_USER_DENIED,
            'OAuth2 authentication required'
        );

        return $exception->getHttpResponse();
    }
}
