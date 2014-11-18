<?php

namespace Pucs\CasAuthBundle\Security\Authentication\Provider;

use Pucs\CasAuthBundle\Cas\Server;
use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Pucs\CasAuthBundle\Authentication\Token\CasUserToken;
use Buzz\Message\Request;
use Buzz\Client\Curl;
use Buzz\Message\Response;

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
     * @var \Pucs\CasAuthBundle\Cas\Server
     */
    private $casServer;

    /**
     * @param Server                $casServer
     * @param UserProviderInterface $userProvider
     */
    public function __construct(Server $casServer, UserProviderInterface $userProvider)
    {
        $this->casServer = $casServer;
        $this->userProvider = $userProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function authenticate(TokenInterface $token)
    {
        if (!$this->supports($token)) {
            return null;
        }

        $username = $this->validateTicket($token->getCredentials(), $token->getCheckPath());
        if (!$username) {
            throw new AuthenticationException('CAS validation failed.');
        }

        try {
            $user = $this->userProvider->loadUserByUsername($username);
        } catch (UsernameNotFoundException $e) {
            // We can decide to obfuscate this error and provide a different one later on if we want.
            throw $e;
        }

        $authenticatedToken = new CasUserToken($token->getCredentials(), $token->getCheckPath(), $user->getRoles());
        $authenticatedToken->setUser($user);

        return $authenticatedToken;
    }

    /**
     * Validate the service ticket that CAS server has provided.
     *
     * @param string $ticket    The authentication ticket provided by CAS server.
     * @param string $checkPath The check path.
     *
     * @return mixed The username of the person if auth was success, or null otherwise.
     */
    private function validateTicket($ticket, $checkPath)
    {
        $validationUrl = $this->casServer->getValidateUrl($checkPath);

        $request = new Request();
        $request->fromUrl(sprintf(
            '%s&ticket=%s',
            $validationUrl,
            $ticket
        ));

        $response = new Response();
        $client = new Curl();
        $client->send($request, $response);

        $content = $response->getContent();
        $data = explode("\n", str_replace("\n\n", "\n", str_replace("\r", "\n", $content)));
        $success = strtolower($data[0] === 'yes');

        if ($success) {
            // Extract the username from the message field and return it.
            return (count($data) > 1 && $data[1]) ? $data[1] : null;
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(TokenInterface $token)
    {
        return $token instanceof CasUserToken;
    }
}
