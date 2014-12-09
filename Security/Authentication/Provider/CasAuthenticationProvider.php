<?php

/*
 * This file is part of the PucsCasAuthBundle package.
 *
 * (c) 2014 The Trustees of Princeton University
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pucs\CasAuthBundle\Security\Authentication\Provider;

use Pucs\CasAuthBundle\Cas\CasLoginData;
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

        // Try to validate the token. The validator will populate an object containiner the username
        // if successful.
        try {
            $casLoginData = $this->validator->validate($token->getCredentials(), $token->getCheckPath());
        } catch (ValidationException $e) {
            throw new AuthenticationException($e->getMessage());
        }

        $this->checkLoginFailure($casLoginData);

        try {
            $user = $this->userProvider->loadUserByUsername($casLoginData->getUsername());
        } catch (UsernameNotFoundException $e) {
            // We can decide to obfuscate this error and provide a different one later on if we want.
            throw $e;
        }

        // Dispatch event allowing others to modify the login data (possibly denying login)
        $authenticationEvent = new CasAuthenticationEvent($casLoginData, $user);
        $this->eventDispatcher->dispatch('pucs.cas_auth.event.authentication', $authenticationEvent);

        $this->checkLoginFailure($casLoginData);

        $authenticatedToken = new CasUserToken($token->getCredentials(), $token->getCheckPath(), $user->getRoles());
        $authenticatedToken->setUser($user);
        $authenticatedToken->setAuthenticated(true);

        return $authenticatedToken;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(TokenInterface $token)
    {
        return $token instanceof CasUserToken;
    }

    /**
     * Inspects login data object for a failure and throws exception if needed.
     *
     * @param CasLoginData $loginData
     */
    private function checkLoginFailure(CasLoginData $loginData)
    {
        if (!$loginData->isSuccess()) {
            throw new AuthenticationException($loginData->getFailureMessage());
        }
    }
}
