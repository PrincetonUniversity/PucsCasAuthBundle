<?php

namespace Pucs\CasAuthBundle\Security\Authentication\Provider;

use Pucs\CasAuthBundle\Cas\Validator\Validator;
use Pucs\CasAuthBundle\Event\CasAuthenticationEvent;
use Pucs\CasAuthBundle\Exception\ValidationException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Pucs\CasAuthBundle\Authentication\Token\CasUserToken;

/**
 * Class CasAuthenticationProviderer
 */
class CasAuthenticationProvider implements AuthenticationProviderInterface
{
    /**
     * @var \Symfony\Component\Security\Core\User\UserProviderInterface
     */
    private $userProvider;

    /**
     * @var \Pucs\CasAuthBundle\Cas\Validator\Validator
     */
    private $validator;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @param Validator                $validator
     * @param UserProviderInterface    $userProvider
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(Validator $validator, UserProviderInterface $userProvider, EventDispatcherInterface $eventDispatcher)
    {
        $this->validator = $validator;
        $this->userProvider = $userProvider;
        $this->eventDispatcher = $eventDispatcher;
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
            $casLoginData = $this->validator->validate($token->getCredentials(), $token->getCheckPath());
        } catch (ValidationException $e) {
            throw new AuthenticationException('CAS validation failed: ' . $e->getMessage());
        }

        // If validaiton succeeded, then dispatch event with login data. This allows other bundles the
        // opportunity to reject login or modify the login data.
        if ($casLoginData->isSuccess()) {
            $authenticationEvent = new CasAuthenticationEvent($casLoginData);
            $this->eventDispatcher->dispatch('pucs.cas_auth.event.authentication', $authenticationEvent);
        }

        if (!$casLoginData->isSuccess()) {
            throw new AuthenticationException('CAS validation failed: ' . $casLoginData->getFailureMessage());
        }

        try {
            $user = $this->userProvider->loadUserByUsername($casLoginData->getUsername());
        } catch (UsernameNotFoundException $e) {
            // We can decide to obfuscate this error and provide a different one later on if we want.
            throw $e;
        }

        $authenticatedToken = new CasUserToken($token->getCredentials(), $token->getCheckPath(), $user->getRoles());
        $authenticatedToken->setUser($user);

        return $authenticatedToken;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(TokenInterface $token)
    {
        return $token instanceof CasUserToken;
    }
}
