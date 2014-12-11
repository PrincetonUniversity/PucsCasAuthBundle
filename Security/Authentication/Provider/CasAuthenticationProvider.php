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

use BeSimple\SsoAuthBundle\Security\Core\User\UserFactoryInterface;
use Pucs\CasAuthBundle\Cas\CasLoginData;
use Pucs\CasAuthBundle\Cas\Validator\Validator;
use Pucs\CasAuthBundle\Event\CasAuthenticationEvent;
use Pucs\CasAuthBundle\Exception\ValidationException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\AuthenticationServiceException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
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
     * @var bool
     */
    private $createUsers;

    /**
     * @param Validator                $validator
     * @param UserProviderInterface    $userProvider
     * @param EventDispatcherInterface $eventDispatcher
     * @param bool                     $createUsers
     */
    public function __construct(Validator $validator, UserProviderInterface $userProvider, EventDispatcherInterface $eventDispatcher, $createUsers = false)
    {
        $this->validator = $validator;
        $this->userProvider = $userProvider;
        $this->eventDispatcher = $eventDispatcher;
        $this->createUsers = $createUsers;
    }

    /**
     * {@inheritdoc}
     */
    public function authenticate(TokenInterface $token)
    {
        if (!$this->supports($token)) {
            return null;
        }

        // Try to validate the token. The validator will return an object containing the username if successful.
        try {
            $casLoginData = $this->validator->validate($token->getCredentials(), $token->getCheckPath());
        } catch (ValidationException $e) {
            throw new AuthenticationException($e->getMessage());
        }

        $this->checkLoginFailure($casLoginData);

        try {
            $user = $this->retrieveUser($casLoginData->getUsername());
        } catch (UsernameNotFoundException $notFound) {
            if ($this->createUsers) {
                $user = $this->createUser($casLoginData->getUsername());
            } else {
                throw $notFound;
            }
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
    protected function checkLoginFailure(CasLoginData $loginData)
    {
        if (!$loginData->isSuccess()) {
            throw new AuthenticationException($loginData->getFailureMessage());
        }
    }

    /**
     * Lookup and return a user object based on username.
     *
     * @param string $username
     *
     * @return UserInterface
     */
    protected function retrieveUser($username)
    {
        try {
            $user = $this->userProvider->loadUserByUsername($username);
            if (!$user instanceof UserInterface) {
                throw new AuthenticationServiceException('The user provider must return an UserInterface object.');
            }
        } catch (UsernameNotFoundException $notFound) {
            throw $notFound;
        } catch (\Exception $repositoryProblem) {
            throw new AuthenticationServiceException($repositoryProblem->getMessage(), 0, $repositoryProblem);
        }

        return $user;
    }

    /**
     * Create a user with the provided username.
     *
     * @param string $username
     *
     * @return UserInterface $user
     */
    protected function createUser($username)
    {
        if (!$this->userProvider instanceof UserFactoryInterface) {
            throw new AuthenticationServiceException("UserProvider must implement UserFactoryInterface to create unknown users.");
        }

        try {
            $user = $this->userProvider->createUser($username, array(), array());

            if (!$user instanceof UserInterface) {
                throw new AuthenticationServiceException("The user provider must return a UserInterface object.");
            }
        } catch (\Exception $repositoryProblem) {
            throw new AuthenticationServiceException($repositoryProblem->getMessage(), 0, $repositoryProblem);
        }

        return $user;
    }
}
