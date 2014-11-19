<?php

namespace Pucs\CasAuthBundle\Security\Authentication\Provider;

use Guzzle\Http\Exception\RequestException;
use Pucs\CasAuthBundle\Cas\Server;
use Pucs\CasAuthBundle\Exception\ValidationException;
use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Pucs\CasAuthBundle\Authentication\Token\CasUserToken;
use Guzzle\Http\Client as GuzzleClient;

/**
 * Class CasAuthenticationProviderer
 */
class CasAuthenticationProvider implements AuthenticationProviderInterface
{
    /**
     * @var \Guzzle\Http\Client
     */
    private $guzzleClient;

    /**
     * @var \Symfony\Component\Security\Core\User\UserProviderInterface
     */
    private $userProvider;

    /**
     * @var \Pucs\CasAuthBundle\Cas\Server
     */
    private $casServer;

    /**
     * @var string
     */
    private $caPem;

    /**
     * @param GuzzleClient          $guzzleClient
     * @param Server                $casServer
     * @param UserProviderInterface $userProvider
     * @param string                $caPem
     */
    public function __construct(GuzzleClient $guzzleClient, Server $casServer, UserProviderInterface $userProvider, $caPem)
    {
        $this->guzzleClient = $guzzleClient;
        $this->casServer = $casServer;
        $this->userProvider = $userProvider;
        $this->caPem = $caPem;
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
        $validationUrl .= '&ticket=' . urlencode($ticket);

        // If user provided a CA PEM file to verify the CAS server, attach it to request.
        // Otherwise we don't perform any validation.
        if (!empty($this->caPem)) {
            $this->guzzleClient->setSslVerification($this->caPem);
        } else {
            $this->guzzleClient->setSslVerification(false);
        }

        $request = $this->guzzleClient->get($validationUrl);
        try {
            $response = $request->send();
        } catch (RequestException $e) {
            throw new ValidationException("Validation of CAS service ticked failed, request to CAS server failed: " . $e->getMessage());
        }

        $content = (string) $response->getBody();

        $data = explode("\n", str_replace("\n\n", "\n", str_replace("\r", "\n", $content)));
        $success = strtolower($data[0] === 'yes');

        if ($success) {
            // Extract the username from the message field and return it.
            return (count($data) > 1 && $data[1]) ? $data[1] : null;
        } else {
            throw new ValidationException("Validation of CAS service ticket failed, no username found in server response.");
        }

    }

    /**
     * {@inheritdoc}
     */
    public function supports(TokenInterface $token)
    {
        return $token instanceof CasUserToken;
    }
}
