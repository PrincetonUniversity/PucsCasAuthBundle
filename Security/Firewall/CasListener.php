<?php

namespace Pucs\CasAuthBundle\Security\Firewall;

use Pucs\CasAuthBundle\Authentication\Token\CasUserToken;
use Symfony\Component\Security\Http\Session\SessionAuthenticationStrategyInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Security\Http\RememberMe\RememberMeServicesInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\SessionUnavailableException;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\SecurityEvents;
use Symfony\Component\Security\Http\HttpUtils;
use Symfony\Component\Security\Http\Firewall\ListenerInterface;

/**
 * This is mostly a copy of AbstractAuthenticationListner. We had to implement our own
 * because the abstract version assumes you're using a UsernamePasswordToken.
 */
class CasListener implements ListenerInterface
{
    protected $options;
    protected $logger;
    protected $authenticationManager;
    protected $providerKey;
    protected $httpUtils;

    private $securityContext;
    private $sessionStrategy;
    private $dispatcher;
    private $successHandler;
    private $failureHandler;
    private $rememberMeServices;

    /**
     * Constructor.
     *
     * @param SecurityContextInterface               $securityContext       A SecurityContext instance
     * @param AuthenticationManagerInterface         $authenticationManager An AuthenticationManagerInterface instance
     * @param SessionAuthenticationStrategyInterface $sessionStrategy
     * @param HttpUtils                              $httpUtils             An HttpUtilsInterface instance
     * @param string                                 $providerKey
     * @param AuthenticationSuccessHandlerInterface  $successHandler
     * @param AuthenticationFailureHandlerInterface  $failureHandler
     * @param array                                  $options               An array of options for the processing of a
     *                                                                      successful, or failed authentication attempt
     * @param LoggerInterface                        $logger                A LoggerInterface instance
     * @param EventDispatcherInterface               $dispatcher            An EventDispatcherInterface instance
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(SecurityContextInterface $securityContext, AuthenticationManagerInterface $authenticationManager, SessionAuthenticationStrategyInterface $sessionStrategy, HttpUtils $httpUtils, $providerKey, AuthenticationSuccessHandlerInterface $successHandler, AuthenticationFailureHandlerInterface $failureHandler, array $options = array(), LoggerInterface $logger = null, EventDispatcherInterface $dispatcher = null)
    {
        if (empty($providerKey)) {
            throw new \InvalidArgumentException('$providerKey must not be empty.');
        }

        $this->securityContext = $securityContext;
        $this->authenticationManager = $authenticationManager;
        $this->sessionStrategy = $sessionStrategy;
        $this->providerKey = $providerKey;
        $this->successHandler = $successHandler;
        $this->failureHandler = $failureHandler;
        $this->options = array_merge(array(
                'check_path'                     => '/login_check',
                'login_path'                     => '/login',
                'always_use_default_target_path' => false,
                'default_target_path'            => '/',
                'target_path_parameter'          => '_target_path',
                'use_referer'                    => false,
                'failure_path'                   => null,
                'failure_forward'                => false,
                'require_previous_session'       => true,
            ), $options);
        $this->logger = $logger;
        $this->dispatcher = $dispatcher;
        $this->httpUtils = $httpUtils;
    }

    /**
     * Sets the RememberMeServices implementation to use
     *
     * @param RememberMeServicesInterface $rememberMeServices
     */
    public function setRememberMeServices(RememberMeServicesInterface $rememberMeServices)
    {
        $this->rememberMeServices = $rememberMeServices;
    }

    /**
     * Handles authentication.
     *
     * @param GetResponseEvent $event A GetResponseEvent instance
     *
     * @throws \RuntimeException
     * @throws SessionUnavailableException
     */
    final public function handle(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        if (!$this->requiresAuthentication($request)) {
            return;
        }

        if (!$request->hasSession()) {
            throw new \RuntimeException('This authentication method requires a session.');
        }

        try {
            if ($this->options['require_previous_session'] && !$request->hasPreviousSession()) {
                throw new SessionUnavailableException('Your session has timed out, or you have disabled cookies.');
            }

            // The request should have a ticket query string parameter that CAS server set when redirecting
            // back to the application. Initialize our token with this ticket and try and validate the token.
            $token = new CasUserToken($request->query->get('ticket'), $this->options['check_path']);
            if (null === $returnValue = $this->authenticationManager->authenticate($token)) {
                return;
            }

            if ($returnValue instanceof TokenInterface) {
                $this->sessionStrategy->onAuthentication($request, $returnValue);

                $response = $this->onSuccess($request, $returnValue);
            } elseif ($returnValue instanceof Response) {
                $response = $returnValue;
            } else {
                throw new \RuntimeException('Authentication provider must either return a Response, an implementation of TokenInterface, or null.');
            }
        } catch (AuthenticationException $e) {
            $response = $this->onFailure($request, $e);
        }

        $event->setResponse($response);
    }

    /**
     * Whether this request requires authentication.
     *
     * The default implementation only processes requests to a specific path,
     * but a subclass could change this to only authenticate requests where a
     * certain parameters is present.
     *
     * @param Request $request
     *
     * @return bool
     */
    protected function requiresAuthentication(Request $request)
    {
        return $this->httpUtils->checkRequestPath($request, $this->options['check_path']);
    }

    private function onFailure(Request $request, AuthenticationException $failed)
    {
        if (null !== $this->logger) {
            $this->logger->info(sprintf('Authentication request failed: %s', $failed->getMessage()));
        }

        $token = $this->securityContext->getToken();
        if ($token instanceof CasUserToken && $this->providerKey === $token->getProviderKey()) {
            $this->securityContext->setToken(null);
        }

        $response = $this->failureHandler->onAuthenticationFailure($request, $failed);

        if (!$response instanceof Response) {
            throw new \RuntimeException('Authentication Failure Handler did not return a Response.');
        }

        return $response;
    }

    private function onSuccess(Request $request, TokenInterface $token)
    {
        if (null !== $this->logger) {
            $this->logger->info(sprintf('User "%s" has been authenticated successfully', $token->getUsername()));
        }

        $this->securityContext->setToken($token);

        $session = $request->getSession();
        $session->remove(SecurityContextInterface::AUTHENTICATION_ERROR);
        $session->remove(SecurityContextInterface::LAST_USERNAME);

        if (null !== $this->dispatcher) {
            $loginEvent = new InteractiveLoginEvent($request, $token);
            $this->dispatcher->dispatch(SecurityEvents::INTERACTIVE_LOGIN, $loginEvent);
        }

        $response = $this->successHandler->onAuthenticationSuccess($request, $token);

        if (!$response instanceof Response) {
            throw new \RuntimeException('Authentication Success Handler did not return a Response.');
        }

        if (null !== $this->rememberMeServices) {
            $this->rememberMeServices->loginSuccess($request, $response, $token);
        }

        return $response;
    }
}
