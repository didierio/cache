<?php

namespace Ddr\Component\Security\Http\Firewall;

use Ddr\Component\Security\Core\Authentication\Token\OAuth2Token;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Http\Firewall\ListenerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;

class OAuth2Listener implements ListenerInterface
{
    private $securityContext;
    private $authenticationManager;
    private $EntryPoint;
    private $logger;
    private $ignoreFailure;

    public function __construct(SecurityContextInterface $securityContext, AuthenticationManagerInterface $authenticationManager, AuthenticationEntryPointInterface $entryPoint, LoggerInterface $logger = null)
    {
        $this->securityContext = $securityContext;
        $this->authenticationManager = $authenticationManager;
        $this->entryPoint = $entryPoint;
        $this->logger = $logger;
        $this->ignoreFailure = false;
    }

    /**
     * Handles basic authentication.
     *
     * @param GetResponseEvent $event A GetResponseEvent instance
     */
    public function handle(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        $authorizationRegex = '/Bearer ([^"]+)/';
        if (!$request->headers->has('Authorization') || 1 !== preg_match($wsseRegex, $request->headers->get('Authorization'), $matches)) {
            if (null !== $this->logger) {
                $this->logger->info('Authorization header malformed');
            }

            return;
        }

        $token = new OAuth2Token();
        $token->setUser($matches[1]);

        try {
            $authToken = $this->authenticationManager->authenticate($token);
            $this->securityContext->setToken($authToken);

            return;
        } catch (AuthenticationException $failed) {
            if (null !== $this->logger) {
                $this->logger->info(sprintf('Authentication request failed for user "%s": %s', $username, $failed->getMessage()));
            }

            $this->entryPoint->start($request, $failed);
        }

        // By default deny authorization
        $response = new Response();
        $response->setStatusCode(Response::HTTP_FORBIDDEN);
        $event->setResponse($response);
    }
}
