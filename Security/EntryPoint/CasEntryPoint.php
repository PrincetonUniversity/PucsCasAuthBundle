<?php

namespace Pucs\CasAuthBundle\Security\EntryPoint;

use Pucs\CasAuthBundle\Cas\Protocol\ProtocolInterface;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Class CasEntryPoint
 */
class CasEntryPoint implements AuthenticationEntryPointInterface
{
    /**
     * @var array
     */
    protected $config;

    /**
     * @var ProtocolInterface
     */
    protected $protocol;

    /**
     * @param ProtocolInterface $protocol
     * @param array             $config
     */
    public function __construct(ProtocolInterface $protocol, array $config)
    {
        $this->protocol = $protocol;
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        return new RedirectResponse($this->protocol->getLoginUri($this->config['check_path']));
    }
}
