<?php

namespace Pucs\CasAuthBundle\Security\EntryPoint;

use Pucs\CasAuthBundle\Cas\Server;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;

class CasEntryPoint implements AuthenticationEntryPointInterface
{
    /**
     * @var array
     */
    protected $config;

    /**
     * @var Server
     */
    protected $casServer;

    /**
     * @param Server $casServer
     * @param array $config
     */
    public function __construct(Server $casServer, array $config)
    {
        $this->casServer = $casServer;
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        return new RedirectResponse($this->casServer->getLoginUrl($this->config['check_path']));
    }
}
