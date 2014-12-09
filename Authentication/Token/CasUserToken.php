<?php

/*
 * This file is part of the PucsCasAuthBundle package.
 *
 * (c) 2014 The Trustees of Princeton University
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pucs\CasAuthBundle\Authentication\Token;

use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;

/**
 * Class CasUserToken
 */
class CasUserToken extends AbstractToken
{
    /**
     * @var string
     */
    private $serviceTicket;

    /**
     * @var string
     */
    private $checkPath;

    /**
     * @param string $serviceTicket
     * @param string $checkPath
     * @param array  $roles
     */
    public function __construct($serviceTicket, $checkPath, array $roles = array())
    {
        parent::__construct($roles);

        $this->serviceTicket = $serviceTicket;
        $this->checkPath = $checkPath;
    }

    /**
     * Returns the CAS service ticket.
     *
     * @return string
     */
    public function getCredentials()
    {
        return $this->serviceTicket;
    }

    /**
     * {@inheritdoc}
     */
    public function eraseCredentials()
    {
        parent::eraseCredentials();

        $this->serviceTicket = null;
    }

    /**
     * Return the check path used used to authentiate the token.
     *
     * This is stored in the token, because we need an easy way to pass it around the security component.
     * We need the check_path, which we use to construct the CAS service URL, when we construct the various
     * CAS URLs for things like logging in, logging out, and validating the credentials.
     *
     * @return string
     */
    public function getCheckPath()
    {
        return $this->checkPath;
    }
}
