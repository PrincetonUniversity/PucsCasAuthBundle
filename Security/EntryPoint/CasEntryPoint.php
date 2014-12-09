<?php

/*
 * This file is part of the PucsCasAuthBundle package.
 *
 * (c) 2014 The Trustees of Princeton University
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
