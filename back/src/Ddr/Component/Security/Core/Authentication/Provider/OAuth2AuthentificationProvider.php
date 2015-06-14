<?php

namespace Ddr\Component\Security\Core\Authentication\Provider;

use Ddr\Component\Security\Core\Authentication\Token\OAuth2Token;
use OAuth2\OAuth2;
use OAuth2\OAuth2AuthenticateException;
use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AccountStatusException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;

class OAuth2AuthentificationProvider implements AuthenticationProviderInterface
{
    protected $service;
    protected $userChecker;

    public function __construct(OAuth2 $service, UserCheckerInterface $userChecker)
    {
        $this->service = $service;
        $this->userChecker = $userChecker;
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

        if ($accessToken = $this->service->verifyAccessToken($token->getUser())) {
            $scope = $accessToken->getScope();
            $user  = $accessToken->getUser();

            if (null !== $user) {
                try {
                    $this->userChecker->checkPreAuth($user);
                } catch (AccountStatusException $e) {
                    throw new OAuth2AuthenticateException(OAuth2::HTTP_UNAUTHORIZED,
                        OAuth2::TOKEN_TYPE_BEARER,
                        $this->service->getVariable(OAuth2::CONFIG_WWW_REALM),
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
        throw new AuthenticationException('OAuth2 authentication failed');
    }
}
