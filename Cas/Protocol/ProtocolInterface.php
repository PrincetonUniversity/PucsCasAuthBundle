<?php

/*
 * This file is part of the PucsCasAuthBundle package.
 *
 * (c) 2014 The Trustees of Princeton University
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pucs\CasAuthBundle\Cas\Protocol;

/**
 * Interface ProtocolInterface
 */
interface ProtocolInterface
{
    /**
     * Returns the login URI of the CAS Server.
     *
     * @param string $service
     *
     * @return string
     */
    function getLoginUri($service);

    /**
     * Returns the logout URL of the CAS Server.
     *
     * @param string $service
     *
     * @return string
     */
    function getLogoutUri($service);

    /**
     * Returns the validation URI of the CAS Server.
     *
     * @param string $service
     * @param string $ticket
     *
     * @return string
     */
    function getValidationUri($service, $ticket);
}
