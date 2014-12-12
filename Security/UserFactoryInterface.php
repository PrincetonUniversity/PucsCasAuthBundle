<?php

/*
 * This file is part of the PucsCasAuthBundle package.
 *
 * (c) 2014 The Trustees of Princeton University
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pucs\CasAuthBundle\Security;

/**
 * Interface UserFactoryInterface
 */
interface UserFactoryInterface
{
    /**
     * Creates a new user for the given username
     *
     * @param string $username The username
     *
     * @return \Symfony\Component\Security\Core\User\UserInterface
     */
    public function createUser($username);
}
