<?php

namespace Ddr\Component\Security\Core\User;

use Ddr\Component\Security\Core\Authentication\Token\OAuth2Token;
use GuzzleHttp\Client;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use GuzzleHttp\Exception\ClientException;

class OAuth2UserProvider implements UserProviderInterface
{
    protected $checkPath;

    public function __construct($checkPath)
    {
        $this->checkPath = $checkPath;
    }

    /**
     * {@inheritDoc}
     */
    public function loadUserByUsername($username)
    {
        return new OAuthUser($username);
    }

    /**
     * {@inheritDoc}
     */
    public function refreshUser(UserInterface $user)
    {
        if (!$this->supportsClass(get_class($user))) {
            throw new UnsupportedUserException(sprintf('Unsupported user class "%s"', get_class($user)));
        }

        return $this->loadUserByUsername($user->getUsername());
    }

    /**
     * {@inheritDoc}
     */
    public function supportsClass($class)
    {
        return $class === 'Ddr\\Component\\Security\\Core\\User\\OAuth2User';
    }

    public function supports(TokenInterface $token)
    {
        return $token instanceof OAuth2Token;
    }

    /**
     * {@inheritdoc}
     */
    public function authenticate(TokenInterface $token)
    {
        if (!$this->supports($token)) {
            return null;
        }

        try {
            $tokenString = $token->getUser();
            $client = new Client();
            $response = $client->get($this->checkPath, [
                'headers' => [
                    'Authorization' => sprintf('Bearer %s', $tokenString),
                ]
            ]);
die(var_dump($response));
            if ($accessToken = $this->serverService->verifyAccessToken($tokenString)) {
                $scope = $accessToken->getScope();
                $user  = $accessToken->getUser();

                if (null !== $user) {

                    try {
                        $this->userChecker->checkPreAuth($user);
                    } catch (AccountStatusException $e) {
                        throw new OAuth2AuthenticateException(OAuth2::HTTP_UNAUTHORIZED,
                            OAuth2::TOKEN_TYPE_BEARER,
                            $this->serverService->getVariable(OAuth2::CONFIG_WWW_REALM),
                            'access_denied',
                            $e->getMessage()
                        );
                    }

                    $token->setUser($user);
                }

                $roles = (null !== $user) ? $user->getRoles() : array();

                if (!empty($scope)) {
                    foreach (explode(' ', $scope) as $role) {
                        $roles[] = 'ROLE_' . strtoupper($role);
                    }
                }

                $token = new OAuthToken($roles);
                $token->setAuthenticated(true);
                $token->setToken($tokenString);

                if (null !== $user) {

                    try {
                        $this->userChecker->checkPostAuth($user);
                    } catch (AccountStatusException $e) {
                        throw new OAuth2AuthenticateException(OAuth2::HTTP_UNAUTHORIZED,
                            OAuth2::TOKEN_TYPE_BEARER,
                            $this->serverService->getVariable(OAuth2::CONFIG_WWW_REALM),
                            'access_denied',
                            $e->getMessage()
                        );
                    }

                    $token->setUser($user);
                }

                return $token;
            }
        } catch (ClientException $e) {
            throw new AuthenticationException('OAuth2 authentication failed', 0, $e);
        }

        throw new AuthenticationException('OAuth2 authentication failed');
    }
}
